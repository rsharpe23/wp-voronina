<?php

final class RSharpe_Sanitize_Helper {
  public static function sanitize_number( $number ) {
    return absint( $number );
  }

  public static function sanitize_text( $text ) {
    return sanitize_text_field( $text );
  }

  public static function sanitize_html( $content ) {
    return wp_kses_post( $content );
  }

  public static function sanitize_checkbox( $checkbox ) {
    return empty( $checkbox ) ? false : true;
  }

  public static function sanitize_radio( $input, $setting ) {
    $input = self::sanitize_text( $input );
    $choices = $setting->manager->get_control( $setting->id )->choices;
    return array_key_exists( $input, $choices ) ? $input : $setting->default;
  }

  public static function sanitize_select( $input, $setting ){
    $input = sanitize_key( $input );
    $control = $setting->manager->get_control( $setting->id );
    return array_key_exists( $input, $control->choices ) ? $input : $setting->default;                
  }

  public static function sanitize_color( $color ) {
    return sanitize_hex_color( $color );
  }

  public static function sanitize_image( $input ) {
    $output = '';
 
    $file_type = wp_check_filetype( $input );
    $mime_type = $file_type['type'];
 
    if ( strpos( $mime_type, 'image' ) !== false ) {
      $output = $input;
    }
 
    return $output;
  }
}

class RSharpe_Customize_Control_Factory {
  public $wp_customize;

  public function __construct( $wp_customize ) {
    $this->wp_customize = $wp_customize;

    require_once get_template_directory() . '/classes/class-rsharpe-customize-editor-control.php';
    $this->wp_customize->register_control_type( 'RSharpe_Customize_Editor_Control' );

    require_once get_template_directory() . '/classes/class-rsharpe-customize-link-control.php';
    $this->wp_customize->register_control_type( 'RSharpe_Customize_Link_Control' );

    require_once get_template_directory() . '/classes/class-rsharpe-customize-portlets-control.php';
    $this->wp_customize->register_control_type( 'RSharpe_Customize_Portlets_Control' );
  }

  public function create_text( $id, $args, $extra ) {
    $args['type'] = 'text';

    $this->wp_customize->add_setting( $id, array(
      'default'   => $extra['default'] ?: '',
      'sanitize_callback' => 'RSharpe_Sanitize_Helper::sanitize_text',
    ) );

    if ( $selector = $extra['selector'] ) {
      $this->init_selective_refresh( $id, $selector );
    }

    return $this->wp_customize->add_control( $id, $args );
  }

  public function create_number( $id, $args, $extra ) {
    $args['type'] = 'number';

    $this->wp_customize->add_setting( $id, array(
      'default' => $extra['default'] ?: 1,
      'sanitize_callback' => 'RSharpe_Sanitize_Helper::sanitize_number',
    ) );

    return $this->wp_customize->add_control( $id, $args );
  }

  public function create_checkbox( $id, $args, $extra ) {
    $args['type'] = 'checkbox';

    $this->wp_customize->add_setting( $id, array(
      'default' => $extra['default'] ?: false,
      'sanitize_callback' => 'RSharpe_Sanitize_Helper::sanitize_checkbox',
    ) );

    return $this->wp_customize->add_control( $id, $args );
  }

  public function create_radio( $id, $args, $extra ) {
    $args['type'] = 'radio';

    // В данном случае установка значения по умолчанию (кроме 1)
    // приведет к ошибке при первой загрузке страницы, когда в базе еще нет значения, 
    // а в preview показывает дефолтное
    $this->wp_customize->add_setting( $id, array(
      'default' => $extra['default'] ?: 1,
      'sanitize_callback' => 'RSharpe_Sanitize_Helper::sanitize_radio',
    ) );

    return $this->wp_customize->add_control( $id, $args );
  }

  public function create_select( $id, $args, $extra ) {
    $args['type'] = 'select';

    $this->wp_customize->add_setting( $id, array(
      'default' => $extra['default'] ?: '',
      'sanitize_callback' => 'RSharpe_Sanitize_Helper::sanitize_select',
    ) );

    return $this->wp_customize->add_control( $id, $args );
  }

  public function create_color( $id, $args, $extra ) {
    $this->wp_customize->add_setting( $id, array(
      'default' => $extra['default'] ?: '#fff',
      'sanitize_callback' => 'RSharpe_Sanitize_Helper::sanitize_color',
    ) );

    // if ( $selector = $extra['selector'] ) {
    //   $this->init_selective_refresh( $id, $selector );
    // }

    return $this->wp_customize->add_control(
      new WP_Customize_Color_Control( $this->wp_customize, $id, $args )
    );
  }

  public function create_image( $id, $args, $extra ) {
    $this->wp_customize->add_setting( $id, array(
      'default'   => $extra['default'] ?: '',
      'sanitize_callback' => 'RSharpe_Sanitize_Helper::sanitize_image',
    ) );

    return $this->wp_customize->add_control( 
      new WP_Customize_Image_Control( $this->wp_customize, $id, $args ) 
    );
  }

  // Custom

  public function create_editor( $id, $args, $extra ) {
    $this->wp_customize->add_setting( $id, array(
      'default'   => $extra['default'] ?: '',
      'sanitize_callback' => 'RSharpe_Sanitize_Helper::sanitize_html',
      'transport' => 'postMessage',
    ) );

    if ( $selector = $extra['selector'] ) {
      $this->init_selective_refresh( $id, $selector, function ( $id ) {
        if ( $value = get_theme_mod( $id ) ) {
          return rsharpe_esc_content( $value );
        }

        return false;
      } );
    }

    return $this->wp_customize->add_control( 
      new RSharpe_Customize_Editor_Control( $this->wp_customize, $id, $args ) 
    );
  }

  public function create_link( $id, $args, $extra ) {
    $this->wp_customize->add_setting( $id, array(
      'default' => $extra['default'] ?: '["http://", ""]',
      'sanitize_callback' => 'RSharpe_Sanitize_Helper::sanitize_html',
    ) );

    if ( $selector = $extra['selector'] ) {
      $this->init_selective_refresh( $id, $selector, function ( $id ) {
        if ( $value = get_theme_mod( $id ) ) {
          $value = json_decode( $value );

          if ( is_array( $value ) ) {
            return $value[1];
          }
        }
 
        return false;
      } );
    }

    return $this->wp_customize->add_control( 
      new RSharpe_Customize_Link_Control( $this->wp_customize, $id, $args ) 
    );
  }

  public function create_portlets( $id, $args, $extra ) {
    $this->wp_customize->add_setting( $id, array(
      'default'   => $extra['default'] ?: '[]',
      'sanitize_callback' => 'RSharpe_Sanitize_Helper::sanitize_html',
    ) );

    // if ( $selector = $extra['selector'] ) {
    //   $this->init_selective_refresh( $id, $selector );
    // }

    $this->wp_customize->add_control( 
      new RSharpe_Customize_Portlets_Control( $this->wp_customize, $id, $args ) 
    );
  }

  // [НЕТОЧНО]: Чтобы работал режим transform->postMessage необходимо container_inclusive назначить false
  protected final function init_selective_refresh( $id, $selector, $render_func = false ) {
    if ( isset( $this->wp_customize->selective_refresh ) ) {
      $this->wp_customize->get_setting( $id )->transport = 'postMessage';
      $this->wp_customize->selective_refresh->add_partial( $id, array(
        'selector'        => $selector,
        'container_inclusive' => false,
        'render_callback' => function () use ( $id, $render_func ) {
          if ( $render_func ) {
            return $render_func( $id );
          }

          return get_theme_mod( $id );
        },
      ) );
    }
  }
}

final class RSharpe_PageLead_Layout {
  public static function get_choices( $min, $max ) {
    if ($max < $min) {
      throw new Exception( 'Min layout num can`t be less than Max' );
    }
    
    $choices = array();
    for ( $i = $min; $i <= $max; $i++ ) {
      $choices["{$i}"] = __( "{$i} колоночный макет" );
    }

    return $choices;
  }
}

class RSharpe_Customizer {
  public $control_factory;

  public function __construct( $wp_customize ) {
    $this->control_factory = new RSharpe_Customize_Control_Factory( $wp_customize );
  }

  public function __call( $method, $args ) {
    switch ( $method ) {
      case 'add_panel':
        $args[0]->$method( $args[1], $args[2] );
        break;

      case 'add_section':
        $on_ready_method = "on_{$args[1]}_ready";
        $section = $args[0]->$method( $args[1], $args[2] );
        $this->$on_ready_method( $section );
        break;
    }
  }

  public function add_control( $type, $section, $slug, $args ) {
    $method = "create_{$type}";

    $sect_id = $section->id;
    $id = $slug ? $sect_id . "_{$slug}" : '';
    $args['section'] = $sect_id;

    $extra = array_filter( $args, function ( $value, $key ) {
      return $key == 'selector' || $key == 'default';
    }, ARRAY_FILTER_USE_BOTH );

    if ( $extra ) {
      $args = array_diff_assoc( $args, $extra );
    }

    return $this->control_factory->$method( $id, $args, $extra );
  }
}

final class RSharpe_PageLead_Customizer extends RSharpe_Customizer  {
  public function __construct( $wp_customize ) {
    parent::__construct( $wp_customize );

    $this->add_panel( $wp_customize, 'pagelead', array(
      'title'    => __( 'Главная страница' ),
      'priority' => 1,
    ) );

    $this->add_section( $wp_customize, 'pagelead_intro', array(
      'title' => __( 'Секция "Главный экран"' ),
      'panel' => 'pagelead',
    ) );

    $this->add_section( $wp_customize, 'pagelead_work', array(
      'title' => __( 'Секция "Портфолио"' ),
      'panel' => 'pagelead',
    ) );

    $this->add_section( $wp_customize, 'pagelead_about', array(
      'title' => __( 'Секция "Об авторе"' ),
      'panel' => 'pagelead',
    ) );

    $this->add_section( $wp_customize, 'pagelead_prices', array(
      'title' => __( 'Секция "Стоимость"' ),
      'panel' => 'pagelead',
    ) );

    $this->add_section( $wp_customize, 'pagelead_contacts', array(
      'title' => __( 'Секция "Контакты"' ),
      'panel' => 'pagelead',
    ) );
  }

  public function on_pagelead_intro_ready( $section ) {
    $enabled_control = $this->add_control( 'checkbox', $section, 'enabled', array( 
      'label' => __( 'Включить секцию' ),
    ) );

    $active_callback = array( 
      $enabled_control->setting, 
      'value' 
    );

    $this->add_control( 'text', $section, 'title', array(
      'label'    => __( 'Заголовок' ),
      'selector' => '.intro__title',
      'active_callback' => $active_callback,
    ) );

    $this->add_control( 'text', $section, 'bg_title', array(
      'label' => __( 'Фоновый заголовок' ),
      'active_callback' => $active_callback,
    ) );

    $this->add_control( 'image', $section, 'bg_thumbnail', array(
      'label' => __( 'Фоновое изображение' ),
      'active_callback' => $active_callback,
    ) );

    $this->add_control( 'editor', $section, 'content', array(
      'label'    => __( 'Содержимое' ),
      'selector' => '.intro__content',
      'active_callback' => $active_callback,
    ) );

    $this->add_control( 'image', $section, 'thumbnail', array(
      'label' => __( 'Изображение' ),
      'active_callback' => $active_callback,
    ) );

    $this->add_control( 'link', $section, 'link', array(
      'label'    => __( 'Ссылка' ),
      'selector' => '.intro__link',
      'active_callback' => $active_callback,
    ) );
  }

  public function on_pagelead_work_ready( $section ) {
    $enabled_control = $this->add_control( 'checkbox', $section, 'enabled', array( 
      'label' => __( 'Включить секцию' ),
    ) );

    $active_callback = array( 
      $enabled_control->setting, 
      'value' 
    );

    $this->add_control( 'text', $section, 'title', array(
      'label'    => __( 'Заголовок' ),
      'selector' => '.work__title',
      'active_callback' => $active_callback,
    ) );

    $this->add_control( 'text', $section, 'bg_title', array(
      'label' => __( 'Фоновый заголовок' ),
      'active_callback' => $active_callback,
    ) );

    $this->add_control( 'portlets', $section, 'work', array(
      'label' => __( 'Работы' ),
      'active_callback' => $active_callback,
    ) );

    $this->add_control( 'text', $section, 'panel_title', array(
      'label'    => __( 'Заголовок панели' ),
      'selector' => '.work__panel > h3',
      'active_callback' => $active_callback,
    ) );

    $this->add_control( 'editor', $section, 'panel_content', array(
      'label'    => __( 'Контент панели' ),
      'selector' => '.work__panel > div',
      'active_callback' => $active_callback,
    ) );

    $this->add_control( 'image', $section, 'panel_thumbnail', array(
      'label' => __( 'Изображение панели' ),
      'active_callback' => $active_callback,
    ) );

    $this->add_control( 'link', $section, 'panel_link', array(
      'label'    => __( 'Ссылка панели' ),
      'selector' => '.work__panel > a',
      'active_callback' => $active_callback,
    ) );
  }

  public function on_pagelead_about_ready( $section ) {
    $enabled_control = $this->add_control( 'checkbox', $section, 'enabled', array( 
      'label' => __( 'Включить секцию' ),
    ) );

    $active_callback = array( 
      $enabled_control->setting, 
      'value' 
    );

    $this->add_control( 'text', $section, 'title', array(
      'label'    => __( 'Заголовок' ),
      'selector' => '.about__title',
      'active_callback' => $active_callback,
    ) );

    $this->add_control( 'text', $section, 'bg_title', array(
      'label' => __( 'Фоновый заголовок' ),
      'active_callback' => $active_callback,
    ) );

    $this->add_control( 'editor', $section, 'content', array(
      'label'    => __( 'Содержимое' ),
      'selector' => '.about__content',
      'active_callback' => $active_callback,
    ) );

    $this->add_control( 'image', $section, 'thumbnail', array(
      'label' => __( 'Изображение' ),
      'active_callback' => $active_callback,
    ) );

    $this->add_control( 'link', $section, 'link', array(
      'label'    => __( 'Ссылка' ),
      'selector' => '.about__link',
      'active_callback' => $active_callback,
    ) );
  }

  public function on_pagelead_prices_ready( $section ) {
    $enabled_control = $this->add_control( 'checkbox', $section, 'enabled', array( 
      'label' => __( 'Включить секцию' ),
    ) );

    $active_callback = array( 
      $enabled_control->setting, 
      'value' 
    );

    $this->add_control( 'text', $section, 'title', array(
      'label'    => __( 'Заголовок' ),
      'selector' => '.price__title',
      'active_callback' => $active_callback,
    ) );

    $this->add_control( 'text', $section, 'bg_title', array(
      'label' => __( 'Фоновый заголовок' ),
      'active_callback' => $active_callback,
    ) );

    $this->add_control( 'portlets', $section, 'prices', array(
      'label' => __( 'Цены' ),
      'active_callback' => $active_callback,
    ) );
  }

  public function on_pagelead_contacts_ready( $section ) {
    $enabled_control = $this->add_control( 'checkbox', $section, 'enabled', array( 
      'label' => __( 'Включить секцию' ),
    ) );

    $active_callback = array( 
      $enabled_control->setting, 
      'value' 
    );

    $this->add_control( 'text', $section, 'title', array(
      'label'    => __( 'Заголовок' ),
      'selector' => '.footer__title',
      'active_callback' => $active_callback,
    ) );

    $this->add_control( 'text', $section, 'bg_title', array(
      'label' => __( 'Фоновый заголовок' ),
      'active_callback' => $active_callback,
    ) );
  }

  // public function on_pagelead_home_ready( $section ) {
  //   $this->add_control( 'text', $section, 'my_text', array(
  //     'label'    => __( 'Текст' ),
  //     'selector' => '.container > p',
  //   ) );

  //   $this->add_control( 'number', $section, 'my_number', array(
  //     'label' => __( 'Число' ),
  //   ) );

  //   $this->add_control( 'checkbox', $section, 'my_checkbox', array(
  //     'label' => __( 'Флажок' ),
  //   ) );

  //   $this->add_control( 'radio', $section, 'my_radio', array(
  //     'label'   => __( 'Тумблер' ),
  //     'choices' => RSharpe_PageLead_Layout::get_choices( 1, 3 ),
  //   ) );

  //   $this->add_control( 'select', $section, 'my_select', array(
  //     'label'   => __( 'Список' ),
  //     'choices' => array(
  //       '1' => __( 'Значение #1' ),
  //       '2' => __( 'Значение #2' ),
  //       '3' => __( 'Значение #3' ),
  //     ),
  //     'default' => '2',
  //   ) );

  //   $this->add_control( 'color', $section, 'my_color', array(
  //     'label' => __( 'Цвет' ),
  //   ) );

  //   $this->add_control( 'image', $section, 'my_image', array(
  //     'label' => __( 'Картинка' ),
  //   ) );

  //   $this->add_control( 'editor', $section, 'my_editor', array(
  //     'label' => __( 'Редактор' ),
  //   ) );

  //   $this->add_control( 'link', $section, 'my_link', array(
  //     'label' => __( 'Ссылка' ),
  //   ) );

  //   $this->add_control( 'portlets', $section, 'my_portlets', array(
  //     'label' => __( 'Портлеты' ),
  //   ) );
  // }
}

interface IData_Store {
  public function e( $key, $esc_fn );
  public function get( $key );
}

// NOTE: Текст из БД не нужно переводить на другой язык
class RSharpe_Data_Store implements IData_Store {
  public $data;

  public function __construct( $non_lazy ) {
    if ( $non_lazy ) {
      $this->data = get_theme_mods();
    }
  }

  public function e( $key, $esc_fn = 'wp_kses_post' ) {
    echo $esc_fn( $this->get( $key ) );
  }

  public function get( $key ) {
    $value = is_array( $this->data ) ? 
      $this->data[ $key ] : 
      get_theme_mod( $key );

    return apply_filters( 'rsharpe_data', $value, $key );
  }
}

class RSharpe_Data_Store_Derived implements IData_Store {
  public $store;
  public $key_prefix;

  public function __construct( $store, $key_prefix ) {
    $this->store = $store;
    $this->key_prefix = $key_prefix;
  }

  public function e( $key, $esc_fn = 'wp_kses_post' ) {
    $this->store->e( $this->get_prefixed_key( $key ), $esc_fn );
  }

  public function get( $key ) {
    return $this->store->get( $this->get_prefixed_key( $key ) );
  }

  protected final function get_prefixed_key( $key ) {
    if ( $this->key_prefix ) {
      $key = $this->key_prefix . $key;
    }

    return $key;
  }
}

function rsharpe_customize_register( $wp_customize ) {
  new RSharpe_PageLead_Customizer( $wp_customize );

  $wp_customize->add_section( 'contacts', array(
    'title'    => __( 'Контакты' ),
    'priority' => 10,
  ) );

  // ---------------------

  $wp_customize->add_setting( 'phone', array(
    'default'   => '',
    'sanitize_callback' => 'RSharpe_Sanitize_Helper::sanitize_text',
    'transport' => 'postMessage',
  ) );

  $wp_customize->add_control( 'phone', array(
    'type'    => 'text',
    'label'   => __( 'Телефон' ),
    'section' => 'contacts',
  ) );
  
  // ---------------------

  $wp_customize->add_setting( 'email', array(
    'default'   => '',
    'sanitize_callback' => 'RSharpe_Sanitize_Helper::sanitize_text',
    'transport' => 'postMessage',
  ) );

  $wp_customize->add_control( 'email', array(
    'type'    => 'text',
    'label'   => __( 'E-mail' ),
    'section' => 'contacts',
  ) );

  // ---------------------

  $wp_customize->add_setting( 'skype', array(
    'default'   => '',
    'sanitize_callback' => 'RSharpe_Sanitize_Helper::sanitize_text',
    'transport' => 'postMessage',
  ) );

  $wp_customize->add_control( 'skype', array(
    'type'    => 'text',
    'label'   => __( 'Skype' ),
    'section' => 'contacts',
  ) );

  // ---------------------

  if ( isset( $wp_customize->selective_refresh ) ) {
    $wp_customize->selective_refresh->add_partial( 'phone', array(
      'selector'        => '.header__phone, .footer__menu > li:nth-child(3) > a > span',
      'container_inclusive' => false,
      'render_callback' => function () {
        return get_theme_mod( 'phone' );
      },
    ) );

    $wp_customize->selective_refresh->add_partial( 'email', array(
      'selector'        => '.footer__menu > li:nth-child(2) > a > span',
      'container_inclusive' => false,
      'render_callback' => function () {
        return get_theme_mod( 'email' );
      },
    ) );

    $wp_customize->selective_refresh->add_partial( 'skype', array(
      'selector'        => '.footer__menu > li:nth-child(1) > a > span',
      'container_inclusive' => false,
      'render_callback' => function () {
        return get_theme_mod( 'skype' );
      },
    ) );
  }
}
add_action( 'customize_register', 'rsharpe_customize_register' );

function rsharpe_customize_preview_js() {
  wp_register_script(
    'wpautop',
    get_template_directory_uri() . '/js/wpautop.js',
    array(), false, true
  );

  wp_enqueue_script( 
    'rsharpe-customize-preview',
    get_template_directory_uri() . '/js/customize-preview.js',
    array( 'jquery', 'customize-preview', 'wpautop' ), false, true
  );

  wp_localize_script( 
    'rsharpe-customize-preview', 
    'data', array( 
      'jsonText' => file_get_contents( get_stylesheet_directory_uri() . '/customize-preview.json' ) 
    ) 
  );
}
add_action( 'customize_preview_init', 'rsharpe_customize_preview_js' );