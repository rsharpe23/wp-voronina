<?php

class RSharpe_Customize_Control extends WP_Customize_Control {
  public function enqueue() {
    wp_enqueue_style( 
      'rsarpe-controls',
      get_template_directory_uri() . '/css/rsharpe-controls.css' 
    );

    wp_enqueue_script(
      'rsharpe-controls',
      get_template_directory_uri() . '/js/rsharpe-controls.js', 
      array( 'jquery' ), false, true
    );
  }

  protected function get_default() {
    return $this->setting->default;
  }
}