<?php
global $store;
$_store = new RSharpe_Data_Store_Derived( $store, 'pagelead_about_' );

if ( $_store->get( 'enabled' ) ) :
  // Стили подключаются только в футер
  add_action( 'wp_footer', function () use ( $_store ) {
  ?>
    <style>
      .about__title::before {
        content: "<?php $_store->e( 'bg_title' ); ?>";
      }
    </style>
  <?php
  } );
?>

<div class="fp-screen">
  <div class="fp-screen-inner">
    <div class="fp-screen-area">

      <div class="screen about">
        <div class="container">
          <div class="screen__separator"></div>

          <h2 class="title about__title"><?php $_store->e( 'title' ); ?></h2>

          <div class="item-group">
            <div class="item about__item">
              <?php if ( $_store->get( 'thumbnail' ) ) : ?>
                <img src="<?php $_store->e( 'thumbnail', 'esc_url' ) ?>" class="img">
              <?php endif; ?>
            </div>

            <div class="item about__item">
              <div class="about__panel">
                <div class="about__content">
                  <?php rsharpe_content_e( $_store->get( 'content' ) ); ?>
                </div>

                <?php rsharpe_link( $_store->get( 'link' ), 'link about__link anim-fx anim-to-left' ); ?>
              </div>
            </div>
          </div>
        </div>

        <span class="index screen__index about__index">03</span>

        <?php rsharpe_socials( 'screen__socials about__socials' ); ?>
      </div>

    </div>
  </div>
</div>

<?php
endif;