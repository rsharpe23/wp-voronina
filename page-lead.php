<?php
global $store;
$store = new RSharpe_Data_Store( ! is_customize_preview() );

get_header( 'lead' );

// $start = microtime(true);
$screens = array( 'intro', 'work', 'about', 'prices', 'contacts' );
foreach ( $screens as $screen ) {
  get_template_part( 'template-parts/page-lead/' . $screen );
}
// var_dump( microtime(true) - $start );

get_footer( 'lead' );