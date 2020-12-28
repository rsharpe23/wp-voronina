;

(function ($) {
  'use strict';

  $.widget('rsharpe.fullpage', $.rsharpe.slider, {
    options: {
      scrollStep: 100,
    },

    _create: function () {
      this._super();

      // Tоже что и this._on(this.element, {});
      this._on({
        wheel: function (e) {
          if (!this.isReady || e.ctrlKey) {
            return;
          }

          var rawStep = e.originalEvent.deltaY;
          rawStep = Utils.clamp(rawStep, -1, 1);
          rawStep *= this.options.scrollStep;

          this._scrollScreen(rawStep);
        },
      });
    },

    // Этот метод также вызывается из коробки, 
    // сразу после вызова _create
    _init: function () {
      if (!this._hasScreen()) {
        var self = this;

        this.$item.screen({
          scrolled: function (e, data) {
            self.slide(data.sType);
          }
        });
      }

      // Дополнительно обновляем экран, 
      // т.к. при перелистывании могут быть 
      // расхождения в позиционировании area
      this._refreshScreen();
    },

    _onSlideEnd: function () {
      this._superApply(arguments);
      this._init();
    },

    refresh: function () {
      this._refreshScreen();
    },

    _refreshScreen: function () {
      if (!this._hasScreen()) {
        return;
      }

      this.$item.screen('refresh');
    },

    _scrollScreen: function (rawStep) {
      if (!this._hasScreen()) {
        return;
      }

      this.$item.screen('scroll', rawStep);
    },

    _hasScreen: function () {
      return !!this.$item.screen('instance');
    },
  });

  $(document).ready(function () {
    var $fp = $('.fp-container:first')
      .fullpage();

    $(this).on('keyup keydown', function (e) {
      switch (e.key) {
        case 'ArrowUp':
          $fp.fullpage('slide', 'prev');
          e.preventDefault();
          break;

        case 'ArrowDown':
          $fp.fullpage('slide', 'next');
          e.preventDefault();
          break;
      }
    });

    $(window).resize(function () {
      // На моб. экранах плагин не нужен, 
      // поэтому отключаем его, чтобы не выполнялось 
      // лишний раз событие wheel из fullpage

      var mQuery = '(min-width: 768px)';
      var state = this.matchMedia(mQuery).matches ?
        'enable' :
        'disable';

      $fp.fullpage(state);
      state == 'enable' && $fp.fullpage('refresh');
    });
  });

})(jQuery);

(function ($) {
  'use strict';

  $.widget('rsharpe.screen', {
    _create: function () {
      var self = this;

      this.$inner = this.element
        .find('.fp-screen-inner:first');

      this.$area = this.$inner
        .find('.fp-screen-area:first')
        .draggable({
          axis: 'y',
          scroll: false,
          revert: true,
          drag: function (e, ui) {
            if (self.isReady) {
              // Значение ui более корректно, по сравнению с aGm.top
              // Если вместо ui брать aGm тогда будут происходить 
              // маленькие сдвиги назад. Значение aGm статично, 
              // тогда как ui может плавать, после того 
              // как событие drag уже произошло
              var top = Utils.clamp(ui.position.top, self.HDelta, 0);
              ui.originalPosition.top = top;
              self._repositionScrollbar(top);
            }
          },
          stop: function () {
            // Событие вызывается после того как 
            // revert draggable элемент (area) вернется обратно.

            // ~~~~~
            // Если не обновить здесь еще раз top, тогда в нем 
            // будет храниться значение из последнего drag 
            // (т.е. выше или ниже допустимых границ), а нужно то,
            // что будет после того как area, вернется обратно
            // ~~~~~

            if (self.isReady) {
              self.aGm.reset('top');
            }
          },
        });

      this.$scrollbar = createScrollbar()
        .appendTo(this.$inner)
        .scrollbar({
          trackDrag: function (e, data) {
            if (self.isReady) {
              self._repositionAreaBy(data.normalTop);
            }
          },
        });

      this.iGm = GeometryManager.create(this.$inner);
      this.aGm = GeometryManager.create(this.$area);

      this.isReady = true;
    },

    refresh: function () {
      this.iGm.reset();
      this.aGm.reset();

      this.HDelta = this.iGm.height - this.aGm.height;
      this.HRatio = this.iGm.height / this.aGm.height;

      this._repositionArea();
      this._updateAreaDraggable();

      this._refreshScrollbar();
    },

    _repositionArea: function () {
      var y = this.aGm.top - this.HDelta;

      if (y < 0) {
        this.aGm.top = Math.min(0, this.aGm.top - y);
      }
    },

    _repositionAreaBy: function (sbTrackNormalTop) {
      this.aGm.top = this.aGm.height * sbTrackNormalTop * -1;
    },

    _updateAreaDraggable: function () {
      var state = this.canScroll() ? 'enable' : 'disable';
      this.$area.draggable(state);
    },

    _refreshScrollbar: function () {
      var sbTrackPercentageHeight = this.HRatio * 100;

      this.$scrollbar
        .scrollbar('refresh')
        .scrollbar(
          'option',
          'trackHeight',
          sbTrackPercentageHeight
        );

      this._repositionScrollbar();
    },

    _repositionScrollbar: function (top) {
      top === undefined && (top = this.aGm.top);

      var sbTrackNormalTop = top < 0 ?
        top / this.aGm.height * -1 :
        0;

      this.$scrollbar.scrollbar(
        'option',
        'trackTop',
        sbTrackNormalTop
      );
    },

    scroll: function (rawStep) {
      if (!rawStep) {
        return;
      }

      if (!this.canScroll()) {
        this._onScrolled(rawStep);
        return;
      }

      this._doScroll(rawStep, function (isDone) {
        if (isDone) {
          this._onScrolled(rawStep);
          return;
        }

        this._repositionScrollbar();
      });
    },

    canScroll: function () {
      return this.HRatio < 1;
    },

    _doScroll: function (step, cb) {
      if (!step) {
        return;
      }

      var y = this.aGm.top - step;
      var dy = Math.abs(step);

      if ((y - 1) < (this.HDelta - dy) || (y + 1) > dy) {
        cb && cb.call(this, true);
        return;
      }

      this.aGm.top = Utils.clamp(y, this.HDelta, 0);
      cb && cb.call(this);

      // Вариант ленивой прокрутки, когда area может прокрутиться
      // не до конца, и перейти к следующему экрану
      // if (y < this.HDelta || y > 0) {
      //   cb && cb.call(this, true);
      //   return;
      // }

      // Вариант когда area прокручивается до конца, 
      // и только после этого переходит к следующему экрану
      // if ((y - 1) < (this.HDelta - Math.abs(step) || (y + 1) > Math.abs(step)) {
      //   cb && cb.call(this, true);
      //   return;
      // }
    },

    _onScrolled: function (rawStep) {
      if (!rawStep) {
        return;
      }

      var sType = rawStep > 0 ? 'next' : 'prev';
      this._trigger('scrolled', null, { sType: sType });
    },
  });

  function createScrollbar() {
    return $('<div>')
      .addClass('fp-scrollbar')
      .append(
        $('<div>')
          .addClass('fp-scrollbar-inner')
          .append(
            $('<div>').addClass('fp-scrollbar-track')
          )
      );
  }

})(jQuery);

(function ($) {
  'use strict';

  $.widget('rsharpe.scrollbar', {
    _create: function () {
      var self = this;

      this.$inner = this.element
        .find('.fp-scrollbar-inner:first');

      this.$track = this.$inner
        .find('.fp-scrollbar-track:first')
        .draggable({
          axis: 'y',
          scroll: false,
          containment: 'parent',
          drag: function (e) {
            if (self.isReady) {
              self.tGm.reset('top');

              var top = self.tGm.top > 0 ?
                self.tGm.top / self.iGm.height :
                0;

              // Если передавать сразу top вместо объекта, 
              // тогда при значении 0 он будет преобразован 
              // в пустой объект, что вызовет ошибку
              self._trigger('trackDrag', e, { normalTop: top });
            }
          },
        });

      this.iGm = GeometryManager.create(this.$inner);
      this.tGm = GeometryManager.create(this.$track);

      this.isReady = true;
    },

    refresh: function () {
      this.iGm.reset();
      this.tGm.reset();
    },

    _setOption: function (key, value) {
      switch (key) {
        case 'trackHeight':
          this._setTrackHeight(value);
          break;

        case 'trackTop':
          this._setTrackTop(value);
          break;
      }

      this._super(key, value);
    },

    _setTrackHeight: function (percentageHeight) {
      if (percentageHeight > 99) {
        this.element.hide();
        return;
      }

      this.tGm.height = percentageHeight + '%';
      this.element.show();
    },

    _setTrackTop: function (normalTop) {
      this.tGm.top = this.iGm.height * normalTop;
    },
  });

})(jQuery);


// -------------------------


// (function ($) {
//   'use strict';

//   $.widget('rsharpe.fullpage', $.rsharpe.slider, {
//     options: {
//       scrollStep: 100,
//     },

//     _create: function () {
//       this._super();

//       this._on(this.element, {
//         wheel: function (e) {
//           if (!this.isReady || e.ctrlKey) {
//             return;
//           }

//           var rawStep = e.originalEvent.deltaY;
//           rawStep = Utils.clamp(rawStep, -1, 1);
//           rawStep *= this.options.scrollStep;

//           this._scrollScreen(rawStep);
//         },
//       });
//     },

//     _onSlideEnd: function () {
//       this._superApply(arguments);
//       this._init();
//     },

//     // Этот метод также вызывается из коробки, 
//     // сразу после вызова _create
//     _init: function () {
//       if (this._hasScreen()) {
//         return;
//       }

//       var self = this;

//       this.$item.screen({
//         scrolled: function (e, data) {
//           self.slide(data.sType);
//         }
//       });
//     },

//     refresh: function () {
//       this._refreshScreen(); // -> $('.fp-screen').screen('refresh');
//     },

//     _refreshScreen: function () {
//       if (!this._hasScreen()) {
//         return;
//       }

//       this.$item.screen('refresh');
//     },

//     _scrollScreen: function (rawStep) {
//       if (!this._hasScreen()) {
//         return;
//       }

//       this.$item.screen('scroll', rawStep);
//     },

//     _hasScreen: function () {
//       return !!this.$item.screen('instance');
//     },
//   });

// })(jQuery);

// (function ($) {
//   'use strict';

//   $.widget('rsharpe.screen', {
//     _create: function () {
//       var self = this;

//       this.$inner = this.element
//         .find('.fp-screen-inner:first');

//       this.$area = this.$inner
//         .find('.fp-screen-area:first')
//         .draggable({
//           axis: 'y',
//           scroll: false,
//           revert: true,
//           drag: function (e, ui) {
//             if (self.isReady) {
//               // Значение ui более корректно, по сравнению с aGm.top
//               // Если вместо ui брать aGm тогда будут происходить 
//               // маленькие сдвиги назад. Значение aGm статично, 
//               // тогда как ui может плавать, после того 
//               // как событие drag уже произошло
//               var top = Utils.clamp(ui.position.top, self.HDelta, 0);
//               ui.originalPosition.top = top;
//               self._repositionScrollbar(top);
//             }
//           },
//           stop: function () {
//             // Событие вызывается после того как 
//             // revert draggable элемент (area) вернется обратно.

//             // ~~~~~
//             // Если не обновить здесь еще раз top, тогда в нем 
//             // будет храниться значение из последнего drag 
//             // (т.е. выше или ниже допустимых границ), а нужно то,
//             // что будет после того как area, вернется обратно
//             // ~~~~~

//             if (self.isReady) {
//               self.aGm.reset('top');
//             }
//           },
//         });

//       this.$scrollbar = createScrollbar()
//         .appendTo(this.$inner)
//         .scrollbar({
//           trackDrag: function (e, data) {
//             if (self.isReady) {
//               self._repositionAreaBy(data.normalTop);
//             }
//           },
//         });

//       this.iGm = GeometryManager.create(this.$inner);
//       this.aGm = GeometryManager.create(this.$area);

//       this.refresh();
//       this.isReady = true;
//     },

//     refresh: function () {
//       this.iGm.reset();
//       this.aGm.reset();

//       this.HDelta = this.iGm.height - this.aGm.height;
//       this.HRatio = this.iGm.height / this.aGm.height;

//       this._repositionArea();
//       this._updateAreaDrag();

//       this._refreshScrollbar();
//     },

//     _repositionArea: function () {
//       var y = this.aGm.top - this.HDelta;

//       if (y < 0) {
//         this.aGm.top = Math.min(0, this.aGm.top - y);
//       }
//     },

//     _repositionAreaBy: function (sbTrackNormalTop) {
//       this.aGm.top = this.aGm.height * sbTrackNormalTop * -1;
//     },

//     _updateAreaDrag: function () {
//       var state = this.canScroll() ? 'enable' : 'disable';
//       this.$area.draggable(state);
//     },

//     _refreshScrollbar: function () {
//       var sbTrackPercentageHeight = this.HRatio * 100;

//       this.$scrollbar
//         .scrollbar('refresh')
//         .scrollbar(
//           'option',
//           'trackHeight',
//           sbTrackPercentageHeight
//         );

//       this._repositionScrollbar();
//     },

//     _repositionScrollbar: function (top) {
//       top === undefined && (top = this.aGm.top);

//       var sbTrackNormalTop = top < 0 ?
//         top / this.aGm.height * -1 :
//         0;

//       this.$scrollbar.scrollbar(
//         'option',
//         'trackTop',
//         sbTrackNormalTop
//       );
//     },

//     scroll: function (rawStep) {
//       if (!rawStep) {
//         return;
//       }

//       if (!this.canScroll()) {
//         this._onScrolled(rawStep);
//         return;
//       }

//       this._doScroll(rawStep, function (isDone) {
//         if (isDone) {
//           this._onScrolled(rawStep);
//           return;
//         }

//         this._repositionScrollbar();
//       });
//     },

//     canScroll: function () {
//       return this.HRatio < 1;
//     },

//     _doScroll: function (step, cb) {
//       if (!step) {
//         return;
//       }

//       var y = this.aGm.top - step;
//       var dy = Math.abs(step);

//       if ((y - 1) < (this.HDelta - dy) || (y + 1) > dy) {
//         cb && cb.call(this, true);
//         return;
//       }

//       this.aGm.top = Utils.clamp(y, this.HDelta, 0);
//       cb && cb.call(this);

//       // Вариант ленивой прокрутки, когда area может прокрутиться
//       // не до конца, и перейти к следующему экрану
//       // if (y < this.HDelta || y > 0) {
//       //   cb && cb.call(this, true);
//       //   return;
//       // }

//       // Вариант когда area прокручивается до конца, 
//       // и только после этого переходит к следующему экрану
//       // if ((y - 1) < (this.HDelta - Math.abs(step) || (y + 1) > Math.abs(step)) {
//       //   cb && cb.call(this, true);
//       //   return;
//       // }
//     },

//     _onScrolled: function (rawStep) {
//       if (!rawStep) {
//         return;
//       }

//       var sType = rawStep > 0 ? 'next' : 'prev';
//       this._trigger('scrolled', null, { sType: sType });
//     },
//   });

//   function createScrollbar() {
//     return $('<div>')
//       .addClass('fp-scrollbar')
//       .append(
//         $('<div>')
//           .addClass('fp-scrollbar-inner')
//           .append(
//             $('<div>').addClass('fp-scrollbar-track')
//           )
//       );
//   }

// })(jQuery);

// (function ($) {
//   'use strict';

//   $.widget('rsharpe.scrollbar', {
//     _create: function () {
//       var self = this;

//       this.$inner = this.element
//         .find('.fp-scrollbar-inner:first');

//       this.$track = this.$inner
//         .find('.fp-scrollbar-track:first')
//         .draggable({
//           axis: 'y',
//           scroll: false,
//           containment: 'parent',
//           drag: function (e) {
//             if (self.isReady) {
//               self.tGm.reset('top');

//               var top = self.tGm.top > 0 ?
//                 self.tGm.top / self.iGm.height :
//                 0;

//               // Если передавать сразу top вместо объекта, 
//               // тогда при значении 0 он будет преобразован 
//               // в пустой объект, что вызовет ошибку
//               self._trigger('trackDrag', e, { normalTop: top });
//             }
//           },
//         });

//       this.iGm = GeometryManager.create(this.$inner);
//       this.tGm = GeometryManager.create(this.$track);

//       this.isReady = true;
//     },

//     refresh: function () {
//       this.iGm.reset();
//       this.tGm.reset();
//     },

//     _setOption: function (key, value) {
//       switch (key) {
//         case 'trackHeight':
//           this._setTrackHeight(value);
//           break;

//         case 'trackTop':
//           this._setTrackTop(value);
//           break;
//       }

//       this._super(key, value);
//     },

//     _setTrackHeight: function (percentageHeight) {
//       if (percentageHeight > 99) {
//         this.element.hide();
//         return;
//       }

//       this.tGm.height = percentageHeight + '%';
//       this.element.show();
//     },

//     _setTrackTop: function (normalTop) {
//       this.tGm.top = this.iGm.height * normalTop;
//     },
//   });

// })(jQuery);