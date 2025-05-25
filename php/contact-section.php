function contact_section_shortcode( $atts ) {
    $atts = shortcode_atts(
        [ 'id' => '' ],
        $atts,
        'contact_section'
    );
    $post_id = $atts['id'] ?: get_the_ID();

    // ACF fields
    $heading    = get_field( 'contact_heading',      $post_id );
    $form_embed = get_field( 'contact_form_embed',   $post_id );
    $images     = [
      get_field( 'contact_bento_image_1', $post_id ),
      get_field( 'contact_bento_image_2', $post_id ),
      get_field( 'contact_bento_image_3', $post_id ),
    ];

    ob_start();
    ?>
    <section class="contact-section">
      <div class="contact-section__form">
        <?php if ( $heading ) : ?>
          <h3 class="contact-section__form-heading">
            <?php echo esc_html( $heading ); ?>
          </h3>
        <?php endif; ?>

        <?php if ( $form_embed ) : ?>
          <div class="contact-section__form-embed">
            <?php echo $form_embed; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="contact-section__gallery-grid">
        <div class="contact-section__gallery-col contact-section__gallery-col--left">
          <?php for ( $i = 0; $i < 2; $i++ ) : 
                  $img = $images[ $i ];
                  if ( ! $img ) continue;
          ?>
            <figure class="contact-section__gallery-item">
              <img
                class="contact-section__gallery-image"
                src="<?php echo esc_url( $img['url'] ); ?>"
                alt="<?php echo esc_attr( $img['alt'] ?: "Contact image ". ( $i + 1 ) ); ?>"
              />
            </figure>
          <?php endfor; ?>
        </div>

        <div class="contact-section__gallery-col contact-section__gallery-col--right">
          <?php if ( $images[2] ) : ?>
            <figure class="contact-section__gallery-item">
              <img
                class="contact-section__gallery-image"
                src="<?php echo esc_url( $images[2]['url'] ); ?>"
                alt="<?php echo esc_attr( $images[2]['alt'] ?: 'Contact image 3' ); ?>"
              />
            </figure>
          <?php endif; ?>
        </div>
      </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode( 'contact_section', 'contact_section_shortcode' );