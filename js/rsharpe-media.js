var RSharpeMedia = (function (Base) {
  'use strict';

  var F = function (options) {
    var that = this;

    this.options = options;
    this.frame = createFrame();

    this.frame.on('select', function () {
      var newAttachment = that.frame.state()
        .get('selection')
        .first()
        .toJSON();

      that.trigger('select', newAttachment);
    });
  };

  F.prototype = Object.create(Base.prototype);
  F.prototype.constructor = F;

  // TODO: Доделать правильную установку названий кнопки и заголовка
  function createFrame() {
    return wp.media({
      button: { text: 'Выбор изображения' },

      states: [
        new wp.media.controller.Library({
          title: 'Выбрать изображение',
          library: wp.media.query({ type: 'image' }),
          multiple: false,
          date: false,
        }),
      ],
    });
  }

  F.prototype.openFrame = function () {
    this.frame.open();
  };

  return F;

})(RSharpeObject);