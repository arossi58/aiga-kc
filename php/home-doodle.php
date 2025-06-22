<?php
//-------------------------------------------------------------------------------------------------------------------------------- Home page doodle
function home_doodle_shortcode( $atts ) {
    // Accept an optional 'id' attribute (if you want to target a specific post)
    $atts = shortcode_atts( array(
        'id' => '',
    ), $atts, 'home_doodle' );

    $post_id = $atts['id'] ? $atts['id'] : get_the_ID();

    // Retrieve ACF fields (assuming image fields return arrays)
    $hero_background_image   = get_field( 'hero_background_image', $post_id );
    $artist_page_link        = get_field( 'artist_page_link', $post_id );
    $artist_profile_image    = get_field( 'artist_profile_image', $post_id );
    $artist_name             = get_field( 'artist_name', $post_id );
    $artist_description      = get_field( 'artist_description', $post_id );
    $show_artist_link_card   = get_field( 'show_artist_link_card', $post_id ); // true/false

    ob_start();
    ?>
    <section class="hero-wrapper">
        <div class="hero-container" <?php if ( $hero_background_image ) : ?>style="background-image: url(<?php echo esc_url( $hero_background_image['url'] ); ?>); background-size: cover; background-position: center;"<?php endif; ?>>
        </div>

        <?php if ( $show_artist_link_card && $artist_page_link ) : ?>
        <a href="<?php echo esc_url( $artist_page_link ); ?>" class="artist-link-card">
            <?php if ( $artist_profile_image ) : ?>
                <img src="<?php echo esc_url( $artist_profile_image['url'] ); ?>" alt="<?php echo esc_attr( isset($artist_profile_image['alt']) && $artist_profile_image['alt'] ? $artist_profile_image['alt'] : $artist_name ); ?>" class="profile-img">
            <?php endif; ?>
            <div class="artist-text">
                <?php if ( $artist_name ) : ?>
                    <p class="body-2"><?php echo esc_html( $artist_name ); ?></p>
                <?php endif; ?>
                <?php if ( $artist_description ) : ?>
                    <p class="body-3"><?php echo esc_html( $artist_description ); ?></p>
                <?php endif; ?>
            </div>
        </a>
        <?php endif; ?>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode( 'home_doodle', 'home_doodle_shortcode' );

// Doodle feature page

function render_main_feature() {
    $latest = new WP_Query([
        'post_type'      => 'member-feature',
        'posts_per_page' => 1,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_status'    => 'publish',
    ]);

    if ( ! $latest->have_posts() ) {
        wp_reset_postdata();
        return '<p>No Member Feature found.</p>';
    }

    $latest->the_post();
    $post_id = get_the_ID();

    // ACF fields
    $main_image       = get_field( 'main_image',       $post_id );
    $profile_image    = get_field( 'profile_image',    $post_id );
    $name             = get_field( 'name',             $post_id );
    $description      = get_field( 'description',      $post_id );
    $portfolio_link   = get_field( 'portfolio_link',   $post_id );  // renamed

    // If your image fields still return an array:
    if ( is_array( $main_image    ) ) $main_image_id    = $main_image['ID'];
    else                             $main_image_id    = $main_image;
    if ( is_array( $profile_image ) ) $profile_image_id = $profile_image['ID'];
    else                             $profile_image_id = $profile_image;

    ob_start(); ?>
    <section class="page-section">
      <div class="main-feature">
        <div class="main-feature__img-wrapper">
          <?php if ( $main_image_id ) :
            echo wp_get_attachment_image( 
              $main_image_id, 'full', false, [
                'class' => 'main-feature__img',
                'alt'   => esc_attr( $name ),
              ] 
            );
          endif; ?>
        </div>

        <div class="main-feature__description">
          <div class="feature-description__left">
            <?php if ( $name ) : ?>
              <h3><?php echo esc_html( $name ); ?></h3>
            <?php endif; ?>

            <?php if ( $description ) : ?>
              <p class="body-1"><?php echo wp_kses_post( $description ); ?></p>
            <?php endif; ?>

            <?php if ( $portfolio_link ) : ?>
              <a href="<?php echo esc_url( $portfolio_link ); ?>"
                 target="_blank"
                 class="primary large button">
                View Portfolio
              </a>
            <?php endif; ?>
          </div>

          <div class="feature-description__right">
            <?php if ( $profile_image_id ) :
              echo wp_get_attachment_image( 
                $profile_image_id, 'thumbnail', false, [
                  'class' => 'feature-profile-img',
                  'alt'   => esc_attr( $name . ' profile' ),
                ] 
              );
            endif; ?>
          </div>
        </div>
      </div>
    </section>
    <?php

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode( 'main_feature', 'render_main_feature' );

// Register the [aiga_doodle_description] shortcode
add_shortcode( 'aiga_doodle_description', 'render_aiga_doodle_description' );

function render_aiga_doodle_description( $atts ) {
    // Pull ACF values
    $title             = get_field( 'aiga_doodle_title' );
    $description       = get_field( 'aiga_doodle_description' );
    $figma_url         = get_field( 'figma_template_url' );
    $illustrator_url   = get_field( 'illustrator_template_url' );
    $submit_url        = get_field( 'submit_doodle_url' );

    // Build markup
    ob_start(); ?>
    <section class="page-section">
		<div class="main-feature">
			
		
      <?php if ( $title ) : ?>
        <h3><?php echo esc_html( $title ); ?></h3>
      <?php endif; ?>

      <?php if ( $description ) : ?>
        <p><?php echo wp_kses_post( $description ); ?></p>
      <?php endif; ?>

      <?php if ( $figma_url ) : ?>
        <a href="<?php echo esc_url( $figma_url ); ?>" target="_blank">Figma Template</a>
      <?php endif; ?>

      <?php if ( $illustrator_url ) : ?>
        <a href="<?php echo esc_url( $illustrator_url ); ?>" target="_blank">
          Illustrator/Photoshop Template
        </a>
      <?php endif; ?>

      <?php if ( $submit_url ) : ?>
        <a href="<?php echo esc_url( $submit_url ); ?>"
           class="button primary large"
           target="_blank">
          Submit A Doodle
        </a>
      <?php endif; ?>
			</div>
    </section>
    <?php

    return ob_get_clean();
}