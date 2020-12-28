<?php
require_once get_template_directory() . '/classes/class-rsharpe-customize-items-control.php';

class RSharpe_Customize_Portlets_Control extends RSharpe_Customize_Items_Control {
  public $type = 'rsharpe_portlets';

  public function enqueue() {
    wp_enqueue_editor();
    wp_enqueue_media();

    wp_register_script( 
      'rsharpe-template', 
      get_template_directory_uri() . '/js/rsharpe-template.js', 
      array( 'jquery', ), false, true 
    );

    wp_register_script(
      'rsharpe-object',
      get_template_directory_uri() . '/js/rsharpe-object.js', 
      array( 'jquery' ), false, true
    );

    wp_register_script( 
      'rsharpe-editor', 
      get_template_directory_uri() . '/js/rsharpe-editor.js', 
      array( 'rsharpe-object' ), false, true 
    );

    wp_register_script( 
      'rsharpe-media', 
      get_template_directory_uri() . '/js/rsharpe-media.js', 
      array( 'rsharpe-object' ), false, true
    );

    wp_enqueue_script( 
      'rsharpe-portlet', 
      get_template_directory_uri() . '/js/rsharpe-portlet.js', 
      array( 'rsharpe-template', 'rsharpe-editor', 'rsharpe-media' ), false, true 
    );

    parent::enqueue();
  }

  public function to_json() {
    parent::to_json();

    // Изначально attachment это url картинки, 
    // а после преобразования - объект с данными картинки.
    foreach ( $this->json['items'] as $item ) {
      if ( $item->attachment ) {
        if ( $attachment_id = attachment_url_to_postid( $item->attachment ) ) {
          $item->attachment = wp_prepare_attachment_for_js( $attachment_id );
        }
      }
    }

    $this->json['canUpload'] = current_user_can( 'upload_files' );
  }

  protected function content_template() {
    ?>
    <# if ( data.label ) { #>
      <span class="customize-control-title">{{ data.label }}</span>
    <# } #>

    <# if ( data.description ) { #>
      <span class="description customize-control-description">{{ data.description }}</span>
    <# } #>

    <div class="rsharpe-input-group">
      <input type="text" class="rsharpe-input" placeholder="<?php esc_attr_e( 'Добавить новую', 'rsharpe' ); ?>">
      <button type="button" class="button rsharpe-btn"><span class="ui-icon ui-icon-plus"></span></button>
    </div>

    <div class="rsharpe-portlets-area"></div>
    <?php
  }
}