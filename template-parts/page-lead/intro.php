<?php
global $store;
$_store = new RSharpe_Data_Store_Derived( $store, 'pagelead_intro_' );

if ( $_store->get( 'enabled' ) ) :
  $bg_title = explode( ' ', $_store->get( 'bg_title' ) );

  // Стили подключаются только в футер
  add_action( 'wp_footer', function () use ( $_store, $bg_title ) {
  ?>
    <style>
      .intro::before {
        background-image: url(<?php $_store->e( 'bg_thumbnail' ); ?>);
      }
    
      .intro__title::before {
        content: "<?php esc_html_e( $bg_title[0] ); ?>";
      }
      
      .intro__title::after {
        content: "<?php esc_html_e( $bg_title[1] ); ?>";
      }
    </style>
  <?php
  } );
?>

<div class="fp-screen active">
  <div class="fp-screen-inner">
    <div class="fp-screen-area">

      <header class="header">
        <?php rsharpe_header_logo(); ?>

        <div class="header__navbar">
          <nav class="header__nav">
            <?php 
            rsharpe_header_menu_icon();
            rsharpe_header_menu(); 
            ?>
          </nav>

          <span class="header__phone anim-fx anim-to-right"><?php $store->e( 'phone' ); ?></span>
        </div>
      </header>

      <div class="screen intro">
        <div class="container intro__container">
          <div class="item-group">
            <div class="item intro__item anim-fx anim-to-right">
              <h1 class="intro__title"><?php $_store->e( 'title' ); ?></h1>

              <div class="intro__content">
                <?php rsharpe_content_e( $_store->get( 'content' ) ); ?>
              </div>

              <?php rsharpe_link( $_store->get( 'link' ), 'link intro__link' ); ?>
            </div>

            <div class="item intro__item intro__thumbnail anim-fx anim-to-up">
              <?php if ( $_store->get( 'thumbnail' ) ) : ?>
                <img src="<?php $_store->e( 'thumbnail', 'esc_url' ); ?>" alt="Дарья Воронина" class="img">
              <?php endif; ?>
            </div>
          </div>

          <?php rsharpe_socials( 'intro__socials' ); ?>
        </div>

        <span class="index screen__index intro__index">01</span>
        
        <?php rsharpe_mobile_menu(); ?>
      </div>

    </div>
  </div>
</div>

<?php
endif;