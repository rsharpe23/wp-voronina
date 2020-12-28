<?php
/**
 * Функции для работы с данными темы
 */

function rsharpe_content_e( $text, $has_shortcode = false ) {
  $text = apply_filters( 'the_content', wp_kses_post( $text ) );

  if ( $has_shortcode ) {
    $text = do_shortcode( $text );
  }

  echo str_replace( ']]>', ']]&gt;', $text );
}

// $content это либо attachment_id, либо ссылка на картинку
function rsharpe_img_shortcode( $atts, $content ) {
  if ( $attachment_id = intval( $content ) ) {
    return wp_get_attachment_image( $attachment_id, 'full' );
  }

  return sprintf( '<img src="%s">', esc_url( $content ) );
}
add_shortcode( 'img', 'rsharpe_img_shortcode' );

function rsharpe_phone_shortcode( $atts, $content ) {
  return get_theme_mod( 'phone' );
}
add_shortcode( 'phone', 'rsharpe_phone_shortcode' );

function rsharpe_email_shortcode( $atts, $content ) {
  return get_theme_mod( 'email' );
}
add_shortcode( 'email', 'rsharpe_email_shortcode' );

// function rsharpe_data_content( $value, $key ) {
//   if ( substr( $key, -7 ) == 'content' ) {
//     $value = apply_filters( 'the_content', $value );
//     return str_replace( ']]>', ']]&gt;', $value );
//   }

//   return $value;
// }
// add_filter( 'rsharpe_data', 'rsharpe_data_content', 10, 2 );