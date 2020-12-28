<?php
require_once get_template_directory() . '/classes/class-rsharpe-customize-control.php';

class RSharpe_Customize_Items_Control extends RSharpe_Customize_Control {
  public function to_json() {
    parent::to_json();
    $this->json['items'] = $this->get_items();
  }

  protected function get_items() {
    $items = $this->value();
    if ( empty( $items ) || $items == '[]' ) {
      $items = $this->get_default();
    }

    return json_decode( $items ) ?: array();
  }
}