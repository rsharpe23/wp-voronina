<?php

if ( ! class_exists( 'RSharpe_Walker_Nav_Menu' ) ) :
  class RSharpe_Walker_Nav_Menu extends Walker_Nav_Menu {
    protected $extra;

    public function __construct( $extra = array() ) {
      $this->extra = $extra;
    }

    // $item в параметрах это тег <a>
    public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
      $_class = $this->extra['_class'];

      if ( in_array( 'current-menu-item', $item->classes ) ) {
        $_class = trim( "{$_class} active" );
      }

      if ( $_class ) {
        $_class = " class=\"$_class\"";
      }

      $output .= "<li{$_class}>";

      $item_url = $item->url ?: '#';
      $item_class = $this->extra['item_class'];

      if ( $item_class ) {
        $item_class = " class=\"$item_class\"";
      }

      $item_output = $args->before;
      $item_output .= "<a href=\"{$item_url}\"{$item_class}>";
      $item_output .= $args->link_before . $item->title . $args->link_after;
      $item_output .= '</a>';
      $item_output .= $args->after;

      $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
    }
  }
endif;