(function ($, api) {
  'use strict';

  // NOTE: Здесь метод bind означает привязать ф-цию к переменной, 
  // т.е. когда изменяется переменная, ф-ция вызывается как колбек.
  // Метод bind - это часть wp core, а не стандартная ф-ция js.

  // [НЕОБЯЗАТЕЛЬНО] Реализовать основные настройки для Landing Page 
  // (шрифт страницы, цвет ссылок и т.д.)

  // api('pagelead_home_my_text', function (value) {
  //   value.bind(function (to) {
  //     $('.container > p').text(to);
  //   });
  // });

  // api('pagelead_home_my_color', function (value) {
  //   value.bind(function (to) {
  //     console.log(to);
  //     $('body').css('background-color', to);
  //   });
  // });

  // ----------------------------

  // api('phone', function (value) {
  //   value.bind(function (to) {
  //     $('.header__phone').text(to);
  //   });
  // });

  // api('email', function (value) {
  //   value.bind(function (to) {
  //     // $('...').text(to);
  //   });
  // });

  // api('skype', function (value) {
  //   value.bind(function (to) {
  //     // $('...').text(to);
  //   });
  // });

  // ----------------------------

  var items = JSON.parse(data.jsonText);

  items.forEach(function (item) {
    switch (item.type) {
      case 'text': 
        api(item.id, function (value) {
          value.bind(function (to) {
            $(item.selector).text(to);
          });
        });
        break;

      case 'editor':
        api(item.id, function (value) {
          value.bind(function (to) {
            to = wpautop(to);
            $(item.selector).html(to);
          });
        });
        break;

      case 'link': 
        api(item.id, function (value) {
          value.bind(function (to) {
            to = JSON.parse(to);
            
            if (!Array.isArray(to)) {
              throw new Error('"to" must be an array');
            }

            $(item.selector).attr('href', to[0]).text(to[1]);
          }); 
        });
        break;
    }
  });

})(jQuery, wp.customize);