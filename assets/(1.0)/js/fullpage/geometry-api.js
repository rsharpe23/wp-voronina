var Geometry = (function () {
  'use strict';

  var F = function () {
    Object.defineProperty(this, 'value', {
      get: function () {
        if (typeof this._value != 'number') {
          this._value = this.__get();
        }

        return this._value;
      },

      set: function (newValue) {
        this.__set(this._value = newValue);
      },
    });
  };

  F.prototype.__get = function () {
    throw new Error('Not implemented');
  };

  F.prototype.__set = function () {
    throw new Error('Not implemented');
  };

  return F;

})();

var Top = (function (Base) {
  'use strict';

  var F = function ($element) {
    this.$element = $element;
    Base.call(this);
  };

  // Наследование должно всегда идти 
  // сразу после объявления конструктора
  F.prototype = Object.create(Base.prototype);
  F.prototype.constructor = F;

  F.prototype.__get = function () {
    var pos = this.$element.position();
    return pos.top;
  };

  F.prototype.__set = function (newTop) {
    // offset может устанавливать позицию некорректно
    this.$element.css({ top: newTop });
  };

  return F;

})(Geometry);

var Height = (function (Base) {
  'use strict';

  var F = function ($element) {
    this.$element = $element;
    Base.call(this);
  };

  F.prototype = Object.create(Base.prototype);
  F.prototype.constructor = F;

  F.prototype.__get = function () {
    return this.$element.height();
  };

  F.prototype.__set = function (newHeight) {
    this.$element.height(newHeight);
  };

  return F;

})(Geometry);

var GeometryManager = (function ($) {
  'use strict';

  var F = function (top, height) {
    this._top = top;
    this._height = height;

    forEachProp.call(this, function (name) {
      var propName = name.replace('_', '');

      Object.defineProperty(this, propName, {
        get: function () {
          return this[name].value;
        },

        set: function (newValue) {
          this[name].value = newValue;
        },
      });
    });
  };

  function forEachProp(cb) {
    Object.keys(this).forEach($.proxy(cb, this));
  }

  F.prototype.reset = function (propName) {
    if (!propName) {
      forEachProp.call(this, function (name) {
        this[name].value = null;
      });

      return;
    }

    var prop = this['_' + propName];
    prop && (prop.value = null);
  };

  F.create = function ($element) {
    var top = new Top($element);
    var height = new Height($element);
    return new F(top, height);
  };

  return F;

})(jQuery);