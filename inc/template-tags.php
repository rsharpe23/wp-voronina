<?php
/**
 * Функции которые выводят html темы
 */

if ( ! function_exists( 'rsharpe_header_logo' ) ) :
  function rsharpe_header_logo() {
    global $store;

    if ( $logo_id = $store->get( 'custom_logo' ) ) : 
      $logo = wp_get_attachment_image_url( $logo_id, 'full' );
    ?>
      <a href="#" class="header__img-wrap">
        <img class="logo img" src="<?php echo $logo ?>" alt="Логотип">
      </a>
    <?php
    endif;
  }
endif;

if ( ! function_exists( 'rsharpe_header_menu_icon' ) ) :
  function rsharpe_header_menu_icon() {
  ?>
    <div class="menu-icon header__menu-icon">
      <div class="menu-icon__bar menu-icon__bar--first"></div>
      <div class="menu-icon__bar menu-icon__bar--second"></div>
      <div class="menu-icon__bar menu-icon__bar--third"></div>
    </div>
  <?php
  }
endif;

if ( ! function_exists( 'rsharpe_header_menu' ) ) :
  function rsharpe_header_menu() {
    rsharpe_nav_menu( array(
      'theme_location' => 'header_menu',
      'container'  => false,
      'menu_class' => 'header__menu',
      'walker'     => new RSharpe_Walker_Nav_Menu( array(
        '_class' => 'anim-fx anim-to-right',
      ) ),
    ) );
  }
endif;

if ( ! function_exists( 'rsharpe_mobile_menu' ) ) :
  function rsharpe_mobile_menu() {
    rsharpe_nav_menu( array(
      'theme_location'  => 'mobile_menu',
      'container'  => 'nav',
      'container_class' => 'intro__mobile-nav',
      'menu_class' => 'intro__mobile-menu',
      'walker'     => new RSharpe_Walker_Nav_Menu(),
    ) );
  }
endif;

if ( ! function_exists( 'rsharpe_socials' ) ) :
  function rsharpe_socials( $extra_class ) {
    $menu_class = 'socials';
    if ( $extra_class ) {
      $menu_class .= " {$extra_class}";
    }

    rsharpe_nav_menu( array(
      'theme_location' => 'socials',
      'container'  => false,
      'menu_class' => $menu_class,
      'walker'     => new RSharpe_Walker_Nav_Menu( array( 
        '_class'     => 'socials__item', 
        'item_class' => 'socials__link',
      ) ),
    ) );
  }
endif;

if ( ! function_exists( 'rsharpe_footer_socials' ) ) :
  function rsharpe_footer_socials() {
    rsharpe_nav_menu( array(
      'theme_location' => 'footer_socials',
      'container'   => false,
      'menu_class'  => 'footer__socials anim-fx anim-to-left',
      'link_before' => '<i class="',
      'link_after'  => '"></i>',
      'walker'      => new RSharpe_Walker_Nav_Menu(),
    ) );
  }
endif;

if ( ! function_exists( 'rsharpe_nav_menu' ) ) :
  function rsharpe_nav_menu( $args ) {
    $defaults = array(
      'theme_location'  => '',
      'container'       => 'nav',
      'container_id'		=> '',
      'container_class' => '',
      'menu_class'      => 'navbar',
      'items_wrap'      => '<ul class="%2$s">%3$s</ul>',
      'walker'          => new RSharpe_Walker_Nav_Menu(),
      'echo'            => true,
    );

    $args = wp_parse_args( $args, $defaults );

    if ( ! has_nav_menu( $args['theme_location'] ) ) {
      return;
    }

    $nav_menu = wp_nav_menu( 
      array_merge( $args, array( 
        'container' => false, 
        'echo'      => false 
      ) ) 
    );

    if ( $container = $args['container'] ) {
      $atts = '';

      if ( $id = $args['container_id'] ) {
        $atts .= ' id="' . $id . '"';
      }

      if ( $class_name = $args['container_class'] ) {
        $atts .= ' class="' . $class_name . '"';
      }

      $nav_menu = sprintf( '<%1$s%2$s>%3$s</%1$s>', $container, $atts, $nav_menu );
    }

    if ( ! $args['echo'] ) {
      return $nav_menu;
    }

    echo $nav_menu;
  }
endif;

if ( ! function_exists( 'rsharpe_link' ) ) :
  function rsharpe_link( $value, $_class = '', $has_shortcode = false ) {
    $value = json_decode( $value );

    if ( ! is_array( $value ) ) {
      return;
    }

    // Вызов $store->get( $key, true ) для json строки не работает
    if ( $has_shortcode && ( $text = $value[1] ) ) {
      $value[1] = do_shortcode( $text );
    }

    if ( $_class ) {
      $_class = sprintf( ' class="%s"', esc_html( $_class ) );
    }
    ?>
      <a href="<?php echo esc_url( $value[0] ); ?>"<?php echo $_class; ?>><?php echo wp_kses_post( $value[1] ); ?></a>
    <?php
  }
endif;