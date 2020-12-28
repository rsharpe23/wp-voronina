<?php
global $store;
$_store = new RSharpe_Data_Store_Derived( $store, 'pagelead_work_' );

if ( $_store->get( 'enabled' ) ) :
  $work = json_decode( $_store->get( 'work' ) );
  $panel_thumbnail = $_store->get( 'panel_thumbnail' );

  // Стили подключаются только в футер
  add_action( 'wp_footer', function () use ( $_store, $panel_thumbnail ) {
  ?>
    <style>
      .work__title::before {
        content: "<?php $_store->e( 'bg_title' ); ?>";
      }

      .work__more::after {
        background-image: url("<?php esc_html_e( $panel_thumbnail ); ?>");
      }
    </style>
  <?php
  } );
?>

<div class="fp-screen">
  <div class="fp-screen-inner">
    <div class="fp-screen-area">

      <div class="screen work">
        <div class="container">
          <div class="screen__separator"></div>

          <h2 class="title work__title"><?php $_store->e( 'title' ); ?></h2>

          <div class="item-group">
            <?php // HACK: Не можем использовать цикл, т.к. верстка некорректная ?>
            <?php if ( is_array( $work ) ) : ?>
              
              <div class="item work__item">
                <figure>
                  <?php if ( $work[0]->attachment ) : ?>
                    <a href="#"><img src="<?php echo esc_url( $work[0]->attachment ); ?>" class="img"></a>
                  <?php endif; ?>

                  <figcaption>
                    <div>
                      <?php rsharpe_content_e( $work[0]->text ); ?>
                    </div>

                    <h4><?php esc_html_e( $work[0]->title ); ?></h4>
                  </figcaption>
                </figure>
              </div>

              <div class="item work__item">
                <figure>
                  <?php if ( $work[1]->attachment ) : ?>
                    <a href="#"><img src="<?php echo esc_url( $work[1]->attachment ); ?>" class="img"></a>
                  <?php endif; ?>

                  <figcaption>
                    <div>
                      <?php rsharpe_content_e( $work[1]->text ); ?>
                    </div>

                    <h4><?php esc_html_e( $work[1]->title ); ?></h4>
                  </figcaption>
                </figure>

                <figure>
                  <?php if ( $work[2]->attachment ) : ?>
                    <a href="#"><img src="<?php echo esc_url( $work[2]->attachment ); ?>" class="img"></a>
                  <?php endif; ?>

                  <figcaption>
                    <div>
                      <?php rsharpe_content_e( $work[2]->text ); ?>
                    </div>

                    <h4><?php esc_html_e( $work[2]->title ); ?></h4>
                  </figcaption>
                </figure>
              </div>

              <div class="item work__item">
                <figure>
                  <?php if ( $work[3]->attachment ) : ?>
                    <a href="#"><img src="<?php echo esc_url( $work[3]->attachment ); ?>" class="img"></a>
                  <?php endif; ?>

                  <figcaption>
                    <div>
                      <?php rsharpe_content_e( $work[3]->text ); ?>
                    </div>

                    <h4><?php esc_html_e( $work[3]->title ); ?></h4>
                  </figcaption>
                </figure>
              </div>

            <?php endif; ?>
          </div>
        </div>

        <div class="work__more">
          <div class="container">
            <div class="work__panel">
              <h3><?php $_store->e( 'panel_title' ); ?></h3>

              <div>
                <?php $_store->e( 'panel_content' ); ?>
              </div>

              <?php rsharpe_link( $_store->get( 'panel_link' ), 'link work__link anim-fx anim-to-right', true ); ?>
            </div>
          </div>
        </div>

        <span class="index screen__index work__index">02</span>

        <?php
        rsharpe_socials( 'screen__socials work__socials' );
        rsharpe_socials( 'screen__socials work__socials' );
        ?>
      </div>

    </div>
  </div>
</div>

<?php
endif;