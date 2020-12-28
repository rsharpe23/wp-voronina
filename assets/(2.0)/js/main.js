import '@babel/polyfill';
import $ from 'jquery';
import './jquery-ui.min';
import './jquery.ui.touch-punch.min';
import './jquery.slider.min';
import './jquery.fullpage.min';
import './jquery.scrollpoint.min';
import './jquery.parallax-fx.min';
import './jquery.anim-fx.min';

const delay = 500;
const parallaxMinScreenSize = 1024;

$(window).on('load', () => {
  setTimeout(() => {
    const $headerMenuLIs = $('.header__menu > li')
      .each((index, element) => {
        setTimeout(() => $(element).animFx(), index * delay);
      });

    setTimeout(() => {
      $('.header__phone').animFx();
      $('.intro__item:first').animFx().__promise__
        .then(elements => $(elements[0]).next('.intro__item').animFx());
      // ========
    }, $headerMenuLIs.length * delay);
    // =======
  }, delay);
});

$('.header__menu-icon').click(e => {
  $(e.currentTarget).toggleClass('menu-icon--active');
  $('.intro__mobile-nav').fadeToggle();
});

// =====================

let $workItems = null;
const wiScales = [1.5, 1, 2.5];

$('.work').scrollpoint({
  enter() {
    // HACK: Задержка необходима для корректной работы scrollpoint с fullpage. 
    // При прокрутке экранов через fullpage первое значение scrollDelta у событий 
    // scrollpoint очеь большое, и чтобы его пропустить делаем небольшую задержку.
    setTimeout(() => {
      $workItems = $('.work__item')
        .parallaxFx();
    });
  },

  stay(data) {
    if ($workItems && isReadyToParallaxFx()) {
      $workItems.each((index, element) => {
        $(element).parallaxFx(data.scrollDelta * wiScales[index]);
      });
    }
  },

  exit() {
    if ($workItems) {
      $workItems.parallaxFx('reset');
      $workItems = null;
    }
  },
});

// =====================

let $priceItems = null;
const piScales = [2.5, 1, 1.5];

$('.price').scrollpoint({
  enter() {
    // HACK: Задержка необходима для корректной работы scrollpoint с fullpage. 
    // При прокрутке экранов через fullpage первое значение scrollDelta у событий 
    // scrollpoint очеь большое, и чтобы его пропустить делаем небольшую задержку.
    setTimeout(() => {
      $priceItems = $('.price__item')
        .parallaxFx();
    });
  },

  stay(data) {
    if ($priceItems && isReadyToParallaxFx()) {
      $priceItems.each((index, element) => {
        $(element).parallaxFx(data.scrollDelta * piScales[index]);
      });
    }
  },

  exit() {
    if ($priceItems) {
      $priceItems.parallaxFx('reset');
      $priceItems = null;
    }
  },
});

// =====================

$('.work__more').scrollpoint({
  offset: '50%',
  once: true,

  enter() {
    $('.work__link').animFx();
  },
});

$('.about__panel').scrollpoint({
  offset: '50%',
  once: true,

  enter() {
    $('.about__link').animFx();
  },
});

// =====================

$('.footer').scrollpoint({
  once: true,

  enter() {
    const $footerMenuLIs = $('.footer__menu > li')
      .each((index, element) => {
        setTimeout(() => $(element).animFx(), index * delay);
      });

    setTimeout(() => {
      $('.footer__socials').animFx();
      // =======
    }, $footerMenuLIs.length * delay);
  },
});

function isReadyToParallaxFx() {
  const query = `(min-width: ${parallaxMinScreenSize}px)`;
  return window.matchMedia(query).matches;
}