var RSharpeEditor = (function (Base) {
  'use strict';

  var F = function (options) {
    this.options = options;
    init.call(this);
  };

  F.prototype = Object.create(Base.prototype);
  F.prototype.constructor = F;

  /**
   * Метод init отличается от initialize тем, 
   * что initialize является статическим методом, 
   * тогда как init - это метод объекта.
   * 
   * Метод init обычно приватный.
   * 
   * Cтичается что метод init это code smell, 
   * поэтому его желательно применять только тогда, 
   * когда необходимо делать дополнительную логику после инициализации.
   * 
   * Если необходима простая инициализация данных, 
   * то для этого подойдет обычный конструктор.
   * 
   * В этом классе, помимо инициализации данных еще выполняется 
   * и инициализация самого редактора, 
   * поэтому наличие метода init здесь оправданно.
   */
  function init() {
    var that = this;

    // NOTE: Строки тулбаров должны быть без пробелов
    //toolbar1: 'bold,italic,strikethrough,bullist,numlist,blockquote,justifyleft,justifycenter,justifyright,link,unlink,wp_more,spellchecker,fullscreen,wp_adv',
    //toolbar2: 'formatselect,underline,justifyfull,forecolor,pastetext,pasteword,removeformat,media,charmap,outdent,indent,undo,redo,wp_help',
    //toolbar3: 'bold,italic,underline,separator,alignleft,aligncenter,alignright,separator,link,unlink,undo,redo'

    wp.editor.remove(this.options.id);
    wp.editor.initialize(this.options.id, {
      tinymce: {
        wpautop: true,
        statusbar: false,
        toolbar1: 'formatselect,bold,italic,underline,strikethrough,blockquote,alignleft,aligncenter,alignright,link,wp_adv',
        toolbar2: 'bullist,numlist,outdent,indent,charmap,fullscreen,wp_help',

        init_instance_callback: function (editor) {
          editor.on('Change KeyUp', function () {
            that.trigger('change', that.getContent());
          });
        },
      },
    });
  }

  F.prototype.getContent = function () {
    return wp.editor.getContent(this.options.id);
  };

  return F;

})(RSharpeObject);