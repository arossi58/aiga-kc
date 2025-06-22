
<?php
//-------------------------------------------------------------------------------------------------------------------------------- Home Member Preview Section
function home_member_preview_shortcode( $atts ) {
    // Accept an optional 'id' attribute to target a specific post; otherwise, use current post
    $atts = shortcode_atts( array(
        'id' => '',
    ), $atts, 'home_member_preview' );
    
    $post_id = $atts['id'] ? $atts['id'] : get_the_ID();

    // Retrieve ACF fields
    $top_left_image   = get_field('top_left_image', $post_id);
    $top_left_caption = get_field('top_left_caption', $post_id);

    $top_right_image   = get_field('top_right_image', $post_id);
    $top_right_caption = get_field('top_right_caption', $post_id);

    $cta_heading      = get_field('cta_heading', $post_id);
    $cta_link         = get_field('cta_link', $post_id);
    $cta_button_text  = get_field('cta_button_text', $post_id);

    $bottom_image   = get_field('bottom_image', $post_id);
    $bottom_caption = get_field('bottom_caption', $post_id);

    ob_start();
    ?>
    <section class="home-member-preview">
        <!-- Top Left Image Block -->
        <figure class="home-member-preview__image-block home-member-preview__image-block--top-left">
            <?php if ( $top_left_image ) : ?>
                <img src="<?php echo esc_url( $top_left_image ); ?>" alt="<?php echo esc_attr( $top_left_caption ? $top_left_caption : 'Top Left Image' ); ?>" class="home-member-preview__image">
            <?php endif; ?>
            <?php if ( $top_left_caption ) : ?>
                <figcaption class="home-member-preview__label">
                    <p class="body-2"><?php echo esc_html( $top_left_caption ); ?></p>
                </figcaption>
            <?php endif; ?>
        </figure>

        <!-- Top Right Image Block -->
        <figure class="home-member-preview__image-block home-member-preview__image-block--top-right">
            <?php if ( $top_right_image ) : ?>
                <img src="<?php echo esc_url( $top_right_image ); ?>" alt="<?php echo esc_attr( $top_right_caption ? $top_right_caption : 'Top Right Image' ); ?>" class="home-member-preview__image">
            <?php endif; ?>
            <?php if ( $top_right_caption ) : ?>
                <figcaption class="home-member-preview__label">
                    <p class="body-2"><?php echo esc_html( $top_right_caption ); ?></p>
                </figcaption>
            <?php endif; ?>
        </figure>

        <!-- CTA Box in Center -->
        <div class="home-member-preview__cta">
            <?php if ( $cta_heading ) : ?>
                <h2><?php echo esc_html( $cta_heading ); ?></h2>
            <?php endif; ?>
            <?php if ( $cta_link && $cta_button_text ) : ?>
                <a href="<?php echo esc_url( $cta_link ); ?>" class="button large secondary">
                    <?php echo esc_html( $cta_button_text ); ?>
                </a>
            <?php endif; ?>
        </div>

        <!-- Bottom Image Block -->
        <figure class="home-member-preview__image-block home-member-preview__image-block--bottom">
            <?php if ( $bottom_image ) : ?>
                <img src="<?php echo esc_url( $bottom_image ); ?>" alt="<?php echo esc_attr( $bottom_caption ? $bottom_caption : 'Bottom Image' ); ?>" class="home-member-preview__image">
            <?php endif; ?>
            <?php if ( $bottom_caption ) : ?>
                <figcaption class="home-member-preview__label">
                    <p class="body-2"><?php echo esc_html( $bottom_caption ); ?></p>
                </figcaption>
            <?php endif; ?>
        </figure>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode( 'home_member_preview', 'home_member_preview_shortcode' );