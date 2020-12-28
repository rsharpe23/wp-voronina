var RSharpeObject = (function () {
  'use strict';

  var F = function (options) {
    this.options = options;
  };

  F.prototype.trigger = function (event, data) {
    var action = this.options[event];
    action && action.call(this, data);
  };

  return F;

})();