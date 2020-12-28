var RSharpePortlet = (function ($, Base) {
  'use strict';

  var F = function () {
    Base.apply(this, arguments);
    var that = this;

    this.$element = $('<div>')
      .data('rsharpe.portlet', this)
      .attr('id', this.options.id)
      .addClass('rsharpe-portlet closed')
      .on('click', '.rsharpe-btn', function () {
        if (that.isReady) {
          var event = $(this).data('action');
          that.trigger(event);
        }
      })
      .on('click', '.upload-button', function () {
        if (that.isReady) {
          that.media.openFrame();
        }
      })
      .on('click', '.remove-button', function () {
        if (that.isReady) {
          // HACK: Вызываем событие из внешнего кода, 
          // хотя так делать нельзя.
          that.media.trigger('clear');
        }
      });

    this.data = {
      title: that.options.title,
      text: that.options.text,
      attachment: that.options.attachment,
    };

    this.templ = createTempl();

    buildHtml.call(this, function () {
      this.trigger('ready');
    });
  };

  F.prototype = Object.create(Base.prototype);
  F.prototype.constructor = F;

  function createTempl() {
    return new RSharpeTemplate([
      '<div class="rsharpe-portlet-header">',
      '  <#',
      '  var title = data.title || data.text || (data.attachment && data.attachment.title);',
      '  title.length > 20 && (title = title.substr(0, 20) + "...");',
      '  #>',
      '  <h4>',
      '    <span class="ui-icon ui-icon-grip-dotted-vertical"></span>',
      '    <span>{{ title }}</span>',
      '    <span class="ui-icon ui-icon-pencil rsharpe-btn" data-action="toggle"></span>',
      '    <span class="ui-icon ui-icon-trash rsharpe-btn" data-action="remove"></span>',
      '  </h4>',
      '</div>',

      '<div class="rsharpe-portlet-content">',
      '  <div class="rsharpe-input-group">',
      '    <label for="{{ data.titleId }}" class="rsharpe-label">Заголовок</label>',
      '    <input type="text" id="{{ data.titleId }}" class="rsharpe-input" value="{{ data.title }}">',
      '  </div>',

      '  <div class="rsharpe-input-group">',
      '    <label for="{{ data.textId }}" class="rsharpe-label">Текст</label>',
      '    <textarea id="{{ data.textId }}" class="rsharpe-editor">{{ data.text }}</textarea>',
      '  </div>',

      '  <div class="rsharpe-input-group">',
      '    <span class="rsharpe-label">Изображение</span>',
      '    <# if (data.attachment && data.attachment.id) { #>',
      '      <div class="attachment-media-view attachment-media-view-{{ data.attachment.type }} {{ data.attachment.orientation }}">',
      '        <div class="thumbnail thumbnail-{{ data.attachment.type }}">',
      '          <img class="attachment-thumb" src="{{ data.attachment.sizes.full.url }}" draggable="false">',
      '        </div>',
      '        <div class="actions">',
      '          <# if (data.canUpload) { #>',
      '            <button type="button" class="button remove-button">Удалить</button>',
      '            <button type="button" class="button upload-button control-focus">Изменить изображение</button>',
      '          <# } #>',
      '        </div>',
      '      </div>',
      '    <# } else { #>',
      '      <div class="attachment-media-view">',
      '        <# if (data.canUpload) { #>',
      '          <button type="button" class="upload-button button-add-media">Выбрать изображение</button>',
      '        <# } #>',
      '      </div>',
      '    <# } #>',
      '  </div>',
      '</div>',
    ].join('\n'));
  }

  function buildHtml(cb) {
    renderContent.call(this, function (extra) {
      var that = this;

      $('#' + extra.titleId)
        .on('change keyup', function () {
          var newTitle = $(this).val().trim();
          that.data.title = newTitle;
          that.trigger('change');
        });

      this.editor = new RSharpeEditor({
        id: extra.textId,
        change: function (newText) {
          that.data.text = newText;
          that.trigger('change');
        }
      });

      this.media = new RSharpeMedia({
        select: function (newAttachment) {
          that.data.attachment = newAttachment;
          that.trigger('change');
          that.refresh();
        },

        clear: function () {
          delete that.data.attachment;
          that.trigger('change');
          that.refresh();
        },
      });

      cb && cb.call(this);
    });
  }

  function renderContent(cb) {
    var id = this.options.id;
    var canUpload = this.options.canUpload;

    var extra = {
      titleId: id + '_title',
      textId: id + '_text',
      canUpload: canUpload,
    };

    this.$element.html(this.templ.getHtml(
      $.extend({}, this.data, this.temp, extra)
    ));

    // TODO: Проверить во всех браузерах корректность задержки в 0 сек.
    // HACK: DOM не успевает обновиться; нужна задержка
    setTimeout($.proxy(cb, this, extra), 0);
  }

  F.prototype.trigger = function (event) {
    switch (event) {
      case 'ready':
        this.isReady ||
          (this.isReady = true);
        break;

      case 'changed':
        this.$element.hasClass('editable') ||
          this.$element.addClass('editable');
        break;

      case 'toggle':
        this.$element.toggleClass('closed');
        break;

      case 'clear':
        this.$element.remove();
        break;
    }

    Base.prototype.trigger.call(this, event);
  };

  F.prototype.refresh = function () {
    buildHtml.call(this);
  };

  return F;

})(jQuery, RSharpeObject);