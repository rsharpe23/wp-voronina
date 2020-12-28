var Utils = (function () {
  'use strict';

  var onTransitionEnd = function ($element, cb) {
    var event = [
      'transitionend',
      'webkitTransitionEnd',
      'oTransitionEnd',
      'MSTransitionEnd'
    ].join(' ');

    $element.on(event, function () {
      $element.off();
      cb && cb.call(this);
    });
  };

  // var equals = function ($a, $b) {
  //   if (!$a || !$b || $a.length != $b.length) {
  //     return false;
  //   }

  //   for (var i = 0; i < $a.length; i++) {
  //     if ($a[i] !== $b[i]) {
  //       return false;
  //     }
  //   }

  //   return true;
  // };

  var clamp = function (value, min, max) {
    return Math.min(
      Math.max(value, min), 
      max
    );
  };

  return {
    onTransitionEnd: onTransitionEnd,
    // equals: equals,
    clamp: clamp,
  };

})();