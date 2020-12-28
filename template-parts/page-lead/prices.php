<?php
global $store;
$_store = new RSharpe_Data_Store_Derived( $store, 'pagelead_prices_' );

if ( $_store->get( 'enabled' ) ) :
  $prices = json_decode( $_store->get( 'prices' ) );

  // Стили подключаются только в футер
  add_action( 'wp_footer', function () use ( $_store ) {
  ?>
    <style>
      .price__title::before {
        content: "<?php $_store->e( 'bg_title' ); ?>";
      }
    </style>
  <?php
  } );
?>

<div class="fp-screen">
  <div class="fp-screen-inner">
    <div class="fp-screen-area">

      <div class="screen price">
        <div class="container">
          <div class="screen__separator"></div>

          <h2 class="title price__title"><?php $_store->e( 'title' ) ?></h2>

          <div class="price__bg">
            <div class="item-group">

              <?php if ( is_array( $prices ) ) : ?>
                <?php foreach ( $prices as $price ) : ?>

                  <div class="item price__item">
                    <figure>
                      <?php if ( $img_url = $price->attachment ) : ?>
                        <a href="#"><img src="<?php echo esc_url( $img_url ); ?>" class="img"></a>
                      <?php endif; ?>

                      <figcaption>
                        <?php $title = explode( '—', $price->title ); ?>
                        <h3><?php esc_html_e( $title[0] ); ?><p>&mdash;<small><?php esc_html_e( $title[1] ); ?></small></p></h3>
                        <div>
                          <?php rsharpe_content_e( $price->text ); ?>
                        </div>
                      </figcaption>
                    </figure>
                  </div>

                <?php endforeach; ?>
              <?php endif; ?>
              
            </div>
          </div>
        </div>

        <span class="index screen__index price__index">04</span>

        <?php rsharpe_socials( 'screen__socials price__socials' ); ?>
      </div>

    </div>
  </div>
</div>

<?php
endif;