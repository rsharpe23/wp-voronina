var RSharpeTemplate = (function ($) {
  'use strict';

  var F = function (html, settings) {
    this.compiled = _.template(
      html, 
      $.extend({}, F.defaults, settings)
    );
  };

  F.defaults = {
    evaluate: /<#([\s\S]+?)#>/g,
    interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
    escape: /\{\{([^\}]+?)\}\}(?!\})/g,
    variable: 'data',
  };

  F.prototype.getHtml = function (data) {
    return this.compiled(data);
  };

  return F;

})(jQuery);