<?php

if ( ! function_exists( 'rsharpe_setup' ) ) :
  function rsharpe_setup() {
    // Загружает файл перевода темы (.mo) в память, для дальнейшей работы с ним
    load_theme_textdomain( 'rsharpe', get_template_directory() . '/languages' );

    // Добавляет ссылки на RSS фиды постов и комментариев в head часть HTML документа
    add_theme_support( 'automatic-feed-links' );

    // Позволит плагинам и темам изменять метатег <title>
    add_theme_support( 'title-tag' );

    // Позволяет устанавливать миниатюру посту
    add_theme_support( 'post-thumbnails' );

    register_nav_menus( array(
      'header_menu' => __( 'Меню в шапке' ),
      'mobile_menu' => __( 'Главное меню на моб. экранах' ),
      'socials'     => __( 'Соцсети' ),
      'footer_socials' => __( 'Соцсети в подвале' ),
    ) );

    // Меняет разметку ядра wp у перечисленных компонентов на html5-совместимую
    add_theme_support( 'html5', array(
      'search-form',
      'comment-form',
      'comment-list',
      'gallery',
      'caption',
    ) );

    // Добавляет возможность загрузить картинку логотипа в настройках темы в админке
    // add_theme_support( 'custom-logo', array(
    //   'width'       => 202,
    //   'height'      => 34,
    //   'flex-width'  => false,
    //   'flex-height' => false,
    // ));
    add_theme_support( 'custom-logo' );

    // Включает поддержку «Selective Refresh» (выборочное обновление) для виджетов в кастомайзере
    add_theme_support( 'customize-selective-refresh-widgets' );
  }
endif;
add_action( 'after_setup_theme', 'rsharpe_setup' );

if ( ! function_exists( 'rsharpe_scripts' ) ) :
  function rsharpe_scripts() {
    if ( ! is_page() ) {
      return;
    }

    // ============
    // Styles
    // ============

    wp_register_style( 
      'font-awesome', 
      'https://use.fontawesome.com/releases/v5.7.2/css/all.css' 
    );

    wp_enqueue_style( 
      'rsh-bundle',
      get_template_directory_uri() . '/assets/(2.0)/dist/bundle.css',
      array( 'font-awesome' )
    );

    wp_enqueue_style( 'style', get_stylesheet_uri() ); // style.css должен всегда идти последним стилем

    // ============
    // Scripts
    // ============

    wp_enqueue_script( 
      'rsh-bundle', 
      get_template_directory_uri() . '/assets/(2.0)/dist/bundle.js', 
      array(), false, true 
    );
  }
endif;
add_action( 'wp_enqueue_scripts', 'rsharpe_scripts' );

if ( ! function_exists( 'rsharpe_admin_scripts' ) ) :
  function rsharpe_admin_scripts() {
    wp_register_style( 
      'jquery-ui', 
      'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css' 
    );

    wp_enqueue_style( 'jquery-ui' );
  }
endif;
add_action( 'admin_enqueue_scripts', 'rsharpe_admin_scripts' );

function rsharpe_ie_ofi() {
  ?>
  <script>
    if (/MSIE \d|Trident.*rv:/.test(navigator.userAgent)) {
      document.write('<script src="assets/(^2.0)/js/ofi.min.js"><\/script>');
      window.addEventListener('load', function (event) {
        objectFitImages('.work__item figure > a .img, .price__item figure > a .img');
      });
    }
  </script>
  <?php
}
add_action( 'wp_footer', 'rsharpe_ie_ofi', 1000 ); // можно также повесить на wp_print_scripts

require get_template_directory() . '/classes/class-rsharpe-walker-nav-menu.php';

require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/template-functions.php';
require get_template_directory() . '/inc/template-tags.php';

// NOTE: Классы customize-типа здесь подключать нельзя, 
// т.к. их базовым классом является WP_Customize_Control, 
// который загружается только после выполнения хука customize_register 