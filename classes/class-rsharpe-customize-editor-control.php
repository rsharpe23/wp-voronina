<?php
require_once get_template_directory() . '/classes/class-rsharpe-customize-control.php';

class RSharpe_Customize_Editor_Control extends RSharpe_Customize_Control {
  public $type = 'rsharpe_editor';

  public function enqueue() {
    wp_enqueue_editor();

    wp_register_script(
      'rsharpe-object',
      get_template_directory_uri() . '/js/rsharpe-object.js', 
      array( 'jquery' ), false, true
    );

    wp_enqueue_script( 
      'rsharpe-editor', 
      get_template_directory_uri() . '/js/rsharpe-editor.js', 
      array( 'rsharpe-object' ), false, true 
    );

    parent::enqueue();
  }

  public function to_json() {
    parent::to_json();

    $this->json['value'] = $this->value() ?: 
      $this->get_default();
  }
  
  protected function content_template() {
    ?>
    <# var editorId = _.uniqueId( 'rsharpe-editor-' ); #>

    <# if ( data.label ) { #>
      <label for="{{ editorId }}" class="customize-control-title">{{ data.label }}</label>
    <# } #>

    <div class="customize-control-notifications-container"></div>

    <# if ( data.description ) { #>
      <span class="description customize-control-description">{{ data.description }}</span>
    <# } #>

    <textarea id="{{ editorId }}" class="rsharpe-editor">{{ data.value }}</textarea>
    <?php
  }
}