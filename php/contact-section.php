<?php
function contact_section_shortcode( $atts ) {
    // Accept an optional 'id' attribute to target a specific post; otherwise, use current post.
    $atts = shortcode_atts( array(
        'id' => '',
    ), $atts, 'contact_section' );
    
    $post_id = $atts['id'] ? $atts['id'] : get_the_ID();

    // Retrieve ACF fields
    $contact_heading      = get_field( 'contact_heading', $post_id );
    $contact_form_embed   = get_field( 'contact_form_embed', $post_id );
    $bento_image_1        = get_field( 'contact_bento_image_1', $post_id );
    $bento_image_2        = get_field( 'contact_bento_image_2', $post_id );
    $bento_image_3        = get_field( 'contact_bento_image_3', $post_id );

    ob_start();
    ?>
    <section class="contact-section">
      <div class="contact-form-container">
        <h3><?php echo esc_html( $contact_heading ); ?></h3>
        <div class="form-embed">
          <?php 
            // Allow HTML embed code (e.g., Mailchimp form) to be output directly
            echo $contact_form_embed;
          ?>
        </div>
      </div>
      <div class="contact-bento">
        <div class="bento__left">
          <figure class="bento__item">
            <?php if ( $bento_image_1 ) : ?>
              <img src="<?php echo esc_url( $bento_image_1['url'] ); ?>" alt="<?php echo esc_attr( isset($bento_image_1['alt']) && $bento_image_1['alt'] ? $bento_image_1['alt'] : 'Image 1' ); ?>" class="bento__image" />
            <?php endif; ?>
          </figure>
          <figure class="bento__item">
            <?php if ( $bento_image_2 ) : ?>
              <img src="<?php echo esc_url( $bento_image_2['url'] ); ?>" alt="<?php echo esc_attr( isset($bento_image_2['alt']) && $bento_image_2['alt'] ? $bento_image_2['alt'] : 'Image 2' ); ?>" class="bento__image" />
            <?php endif; ?>
          </figure>
        </div>
        <div class="bento__right">
          <figure class="bento__item">
            <?php if ( $bento_image_3 ) : ?>
              <img src="<?php echo esc_url( $bento_image_3['url'] ); ?>" alt="<?php echo esc_attr( isset($bento_image_3['alt']) && $bento_image_3['alt'] ? $bento_image_3['alt'] : 'Image 3' ); ?>" class="bento__image" />
            <?php endif; ?>
          </figure>
        </div>
      </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode( 'contact_section', 'contact_section_shortcode' );