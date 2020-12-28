<?php
global $store;
$_store = new RSharpe_Data_Store_Derived( $store, 'pagelead_contacts_' );

if ( $_store->get( 'enabled' ) ) :
  // Стили подключаются только в футер
  add_action( 'wp_footer', function () use ( $_store ) {
  ?>
    <style>
      .footer__menu::before {
        content: "<?php $_store->e( 'bg_title' ); ?>";
      }
    </style>
  <?php
  } );
?>

<div class="fp-screen">
  <div class="fp-screen-inner">
    <div class="fp-screen-area">

      <footer class="screen footer">
        <div class="container">
          <div class="screen__separator"></div>

          <h2 class="title footer__title"><?php $_store->e( 'title' ); ?></h2>

          <ul class="footer__menu">
            <?php if ( $store->get( 'skype' ) ) : ?>
              <li class="anim-fx anim-to-right"><a href="#">Skype /// <span><?php $store->e( 'skype' ); ?></span></a></li>
            <?php endif; ?>

            <?php if ( $store->get( 'email' ) ) : ?>
              <li class="anim-fx anim-to-right"><a href="#">E-mail /// <span><?php $store->e( 'email' ); ?><span></a></li>
            <?php endif; ?>

            <?php if ( $store->get( 'phone' ) ) : ?>
              <li class="anim-fx anim-to-right"><a href="#">Phone /// <span><?php $store->e( 'phone' ); ?></span></a></li>
            <?php endif; ?>
          </ul>

          <?php rsharpe_footer_socials(); ?>
        </div>
      </footer>

    </div>
  </div>
</div>

<?php
endif;