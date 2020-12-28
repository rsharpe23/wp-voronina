<?php
require_once get_template_directory() . '/classes/class-rsharpe-customize-items-control.php';

class RSharpe_Customize_Link_Control extends RSharpe_Customize_Items_Control {
  public $type = 'rsharpe_link';
  private $item_labels = array();

  public function __construct( $manager, $id, $args = array() ) {
    parent::__construct( $manager, $id, $args );

    $this->item_labels = wp_parse_args( 
      $this->item_labels, 
      $this->get_default_item_labels() 
    );
  }

  public function to_json() {
    parent::to_json();

    $this->json['items'] = array_combine( 
      $this->item_labels, 
      $this->json['items'] 
    );
  }

  protected function content_template() {
    ?>
    <# if ( data.label ) { #>
      <span class="customize-control-title">{{ data.label }}</span>
    <# } #>

    <div class="customize-control-notifications-container"></div>

    <# if ( data.description ) { #>
      <span class="description customize-control-description">{{ data.description }}</span>
    <# } #>

    <# for ( var key in data.items ) { #>
      <# var inputId = _.uniqueId( 'rsharpe-input-' ); #>
      <div class="rsharpe-input-group">
        <label for="{{ inputId }}" class="rsharpe-label">{{ key }}</label>
        <input type="text" id="{{ inputId }}" class="rsharpe-input" value="{{ data.items[ key ] }}">
      </div>
    <# } #>
    <?php
  }

  protected function get_default_item_labels() {
    return array(
      'url'  => __( 'URL' ),
      'text' => __( 'Текст' ),
    );
  }
}