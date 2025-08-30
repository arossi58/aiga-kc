<?php

register_post_type( 'member-feature', [
  'public'             => true,   // show in admin
  'has_archive'        => false,  // no archive listing
  'publicly_queryable' => false,  // don’t serve single pages
  'rewrite'            => false,  // no pretty URL
  // …other args…
] );







if(!function_exists('bridge_qode_child_theme_enqueue_scripts')) {

	Function bridge_qode_child_theme_enqueue_scripts() {
		wp_register_style('bridge-childstyle', get_stylesheet_directory_uri() . '/style.css');
		wp_enqueue_style('bridge-childstyle');
	}

	add_action('wp_enqueue_scripts', 'bridge_qode_child_theme_enqueue_scripts', 11);
}

function shortcode_acf_field($atts) {
    $atts = shortcode_atts([
        'field' => '',
    ], $atts);

    if (!$atts['field']) return '';

    $value = get_field($atts['field']);
    
    // Output raw HTML safely
    if (is_string($value)) {
        return $value;
    } elseif (is_array($value) && isset($value['url'])) {
        return $value['url']; // for image fields etc.
    }

    return '';
}
add_shortcode('acf_field', 'shortcode_acf_field');


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

//--------------------------------------------------------------------------------------------------------------------------------  Shortcode function for the AIGA Page Hero
function aiga_section_shortcode( $atts ) {
    // Allow an optional post ID attribute; defaults to the current post if not provided.
    $atts = shortcode_atts( array(
        'id' => '',
    ), $atts, 'aiga_section' );

    $post_id = $atts['id'] ? $atts['id'] : get_the_ID();

    // Retrieve ACF fields for the left column cards.
    $card_one_image = get_field( 'aiga_card_one_image', $post_id );
    $card_one_text  = get_field( 'aiga_card_one_text', $post_id );

    $card_two_image = get_field( 'aiga_card_two_image', $post_id );
    $card_two_text  = get_field( 'aiga_card_two_text', $post_id );

    // Retrieve ACF fields for the right section.
    $aiga_right_bg    = get_field( 'aiga_right_background', $post_id );
    $cta_heading      = get_field( 'aiga_cta_heading', $post_id );
    $cta_text         = get_field( 'aiga_cta_text', $post_id );
    $cta_link         = get_field( 'aiga_cta_link', $post_id );
    $cta_button_text  = get_field( 'aiga_cta_button_text', $post_id );
    $link_new_tab     = get_field( 'link_new_tab', $post_id ); // true or false

    // Conditionally add target and rel attributes
    $link_target = $link_new_tab ? ' target="_blank" rel="noopener noreferrer"' : '';

    ob_start();
    ?>
    <section class="aiga-section">
      <div class="aiga-left">
        <div class="aiga-card"<?php if ( $card_one_image ) : ?> style="background-image: url(<?php echo esc_url( $card_one_image['url'] ); ?>);"<?php endif; ?>>
          <?php if ( $card_one_text ) : ?>
            <div class="card-text"><?php echo wp_kses_post( $card_one_text ); ?></div>
          <?php endif; ?>
        </div>
        <div class="aiga-card"<?php if ( $card_two_image ) : ?> style="background-image: url(<?php echo esc_url( $card_two_image['url'] ); ?>);"<?php endif; ?>>
          <?php if ( $card_two_text ) : ?>
            <div class="card-text"><?php echo wp_kses_post( $card_two_text ); ?></div>
          <?php endif; ?>
        </div>
      </div>
      <div class="aiga-right"<?php if ( $aiga_right_bg ) : ?> style="background-image: url(<?php echo esc_url( $aiga_right_bg['url'] ); ?>);"<?php endif; ?>>
        <div class="aiga-cta">
          <?php if ( $cta_heading ) : ?>
            <h2><?php echo esc_html( $cta_heading ); ?></h2>
          <?php endif; ?>
          <?php if ( $cta_text ) : ?>
            <p class="body-2"><?php echo wp_kses_post( $cta_text ); ?></p>
          <?php endif; ?>
          <?php if ( $cta_link && $cta_button_text ) : ?>
            <a href="<?php echo esc_url( $cta_link ); ?>" class="button secondary large"<?php echo $link_target; ?>>
              <?php echo esc_html( $cta_button_text ); ?>
            </a>
          <?php endif; ?>
        </div>
      </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode( 'aiga_section', 'aiga_section_shortcode' );



//--------------------------------------------------------------------------------------------------------------------------------  Shortcode function for the event page header
function aiga_event_page_shortcode( $atts ) {
    // Accept an optional 'id' attribute to target a specific post
    $atts = shortcode_atts( array(
        'id' => '',
    ), $atts, 'aiga_event_page' );

    $post_id = $atts['id'] ? $atts['id'] : get_the_ID();

    // Retrieve ACF fields for the event page
    $event_header_image = get_field( 'event_header_image', $post_id );
    $event_date         = get_field( 'event_date', $post_id );
    $event_time         = get_field( 'event_time', $post_id );
    $event_category     = get_field( 'event_category', $post_id );
    $event_venue        = get_field( 'event_venue', $post_id );
    $event_address      = get_field( 'event_address', $post_id );
    $event_ticket_url   = get_field( 'event_ticket_url', $post_id );
    $event_ticket_text  = get_field( 'event_ticket_text', $post_id );
    $event_description  = get_field( 'event_description', $post_id ); // WYSIWYG field

    // Convert the event_date from Ymd to Month Day, Year
    $date_formatted = $event_date;
    if ( $event_date ) {
        $date_object = DateTime::createFromFormat( 'Ymd', $event_date );
        if ( $date_object ) {
            $date_formatted = $date_object->format( 'F j, Y' );
        }
    }

    ob_start();
    ?>
    <section class="event-page">
      <div class="event-page-hero">
        <?php if ( $event_header_image ) : ?>
          <img src="<?php echo esc_url( $event_header_image['url'] ); ?>" alt="<?php echo esc_attr( isset( $event_header_image['alt'] ) && $event_header_image['alt'] ? $event_header_image['alt'] : get_the_title( $post_id ) ); ?>">
        <?php endif; ?>
      </div>

      <div class="event-content">
        <div class="event-info">
          <h3><?php the_title(); ?></h3>
          <?php if ( $event_category ) : ?>
            <span class="tag tag-<?php echo esc_attr( sanitize_title( $event_category ) ); ?>"><?php echo esc_html( $event_category ); ?></span>
          <?php endif; ?>

          <p><strong><?php echo esc_html( $date_formatted ); ?></strong></p>
          <p><strong><?php echo esc_html( $event_time ); ?></strong></p>
          <p><strong><?php echo esc_html( $event_venue ); ?></strong></p>
          <p><?php echo esc_html( $event_address ); ?></p>

          <?php if ( $event_ticket_url && $event_ticket_text ) : ?>
            <a href="<?php echo esc_url( $event_ticket_url ); ?>" class="button large primary"><?php echo esc_html( $event_ticket_text ); ?></a>
          <?php endif; ?>
        </div>

        <div class="event-description">
          <?php 
          // Check if there is an event description before outputting.
          if ( $event_description ) {
              // Output the WYSIWYG content with WordPress content filters.
              echo apply_filters( 'the_content', $event_description );
          }
          ?>
        </div>
      </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode( 'aiga_event_page', 'aiga_event_page_shortcode' );





// -------------------------------------------------------------------------------------------------------------------------------- Shortcode function for the event grid
function aiga_event_grid_shortcode( $atts ) {
    // Set default attribute values; default posts_per_page is now -1 (show all events)
    $atts = shortcode_atts( array(
        'posts_per_page' => -1,
    ), $atts, 'aiga_event_grid' );

    // Query the custom post type "aiga-event" sorted by event_date (latest event date first)
    $query = new WP_Query( array(
        'post_type'      => 'aiga-event',
        'posts_per_page' => $atts['posts_per_page'],
        'post_status'    => 'publish',
        'meta_key'       => 'event_date',       // Use ACF event_date field for sorting
        'orderby'        => 'meta_value_num',   // Compare event_date as a number (Ymd format)
        'order'          => 'DESC',             // Latest event dates at the top
    ) );

    ob_start();
    ?>
    <section class="event-grid">
        <div class="event-preview-header">
            <div class="heder-wrapper">
                <h3>Our Events</h3>
                <div class="underline"></div>
            </div>
        </div>

        <!-- Wrapper for all event cards -->
        <div class="event-card-wrapper">
            <?php
            if ( $query->have_posts() ) {
                while ( $query->have_posts() ) {
                    $query->the_post();

                    // Retrieve ACF fields for this event post
                    $event_header_image = get_field( 'event_header_image' );
                    $event_date         = get_field( 'event_date' );
                    $event_time         = get_field( 'event_time' );
                    $event_category     = get_field( 'event_category' );

                    // Format event_date from Ymd (e.g., 20251012) to Month Day, Year (e.g., October 12, 2025) if available
                    $date_formatted = '';
                    if ( $event_date ) {
                        $date_object = DateTime::createFromFormat( 'Ymd', $event_date );
                        if ( $date_object ) {
                            $date_formatted = $date_object->format( 'F j, Y' );
                        } else {
                            $date_formatted = $event_date; // Fallback in case of an unexpected format
                        }
                    }
                    
                    // Create a CSS-friendly class for the event category
                    $category_class = $event_category ? sanitize_title( $event_category ) : '';
                    ?>
                    <a href="<?php the_permalink(); ?>" class="mix event-card <?php echo esc_attr( $category_class ); ?>">
                        <div class="image-wrapper">
                            <?php if ( $event_header_image ) : ?>
                                <img src="<?php echo esc_url( $event_header_image['url'] ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="event-meta">
                            <p class="bold"><?php the_title(); ?></p>
                            <p class="date-time">
                                <?php 
                                if ( $date_formatted && $event_time ) {
                                    echo esc_html( $date_formatted . ' &bull; ' . $event_time );
                                } elseif ( $date_formatted ) {
                                    echo esc_html( $date_formatted );
                                }
                                ?>
                            </p>
                            <?php if ( $event_category ) : ?>
                                <span class="tag tag-<?php echo esc_attr( $category_class ); ?>">
                                    <?php echo esc_html( $event_category ); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </a>
                    <?php
                }
            } else {
                echo '<p>No events found.</p>';
            }
            ?>
        </div>
    </section>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode( 'aiga_event_grid', 'aiga_event_grid_shortcode' );










//--------------------------------------------------------------------------------------------------------------------------------  Shortcode function for recent events grid (latest 6 events)
function aiga_events_grid_preview_shortcode( $atts ) {
    // Set default attribute values; default posts_per_page remains 6
    $atts = shortcode_atts( array(
        'posts_per_page' => 6,
    ), $atts, 'aiga_events_grid_preview' );

    // Query the custom post type "aiga-event" sorted by event_date (latest event date first)
    $query = new WP_Query( array(
        'post_type'      => 'aiga-event',
        'posts_per_page' => $atts['posts_per_page'],
        'post_status'    => 'publish',
        'meta_key'       => 'event_date',       // Use ACF event_date field for sorting
        'orderby'        => 'meta_value_num',   // Compare event_date as a number (Ymd format)
        'order'          => 'DESC',             // Latest event dates at the top
    ) );

    // Hardcode the "See All Events" link URL
    $see_all_events_link = 'https://kc.aiga.org/events/';

    ob_start();
    ?>
    <section class="event-grid">
        <div class="event-preview-header">
            <div class="heder-wrapper">
                <h3>Our Events</h3>
                <div class="underline"></div>
            </div>
        </div>

        <!-- Wrapper for all event cards -->
        <div class="event-card-wrapper">
            <?php
            if ( $query->have_posts() ) {
                while ( $query->have_posts() ) {
                    $query->the_post();

                    // Retrieve ACF fields for this event post
                    $event_header_image = get_field( 'event_header_image' );
                    $event_date         = get_field( 'event_date' );
                    $event_time         = get_field( 'event_time' );
                    $event_category     = get_field( 'event_category' );
                    $category_class     = $event_category ? sanitize_title( $event_category ) : '';

                    // Format event_date from Ymd to Month Day, Year if available
                    $date_formatted = '';
                    if ( $event_date ) {
                        $date_object = DateTime::createFromFormat( 'Ymd', $event_date );
                        if ( $date_object ) {
                            $date_formatted = $date_object->format( 'F j, Y' );
                        } else {
                            $date_formatted = $event_date; // Fallback in case of unexpected format
                        }
                    }
                    ?>
                    <a href="<?php the_permalink(); ?>" class="mix event-card <?php echo esc_attr( $category_class ); ?>">
                        <div class="image-wrapper">
                            <?php if ( $event_header_image ) : ?>
                                <img src="<?php echo esc_url( $event_header_image['url'] ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="event-meta">
                            <p class="bold"><?php the_title(); ?></p>
                            <p class="date-time">
                                <?php 
                                // Display formatted event_date and event_time if available
                                if ( $date_formatted && $event_time ) {
                                    echo esc_html( $date_formatted . ' &bull; ' . $event_time );
                                } elseif ( $date_formatted ) {
                                    echo esc_html( $date_formatted );
                                }
                                ?>
                            </p>
                            <?php if ( $event_category ) : ?>
                                <span class="tag tag-<?php echo esc_attr( $category_class ); ?>">
                                    <?php echo esc_html( $event_category ); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </a>
                    <?php
                }
            } else {
                echo '<p>No events found.</p>';
            }
            ?>
        </div>
        <div class="event-preview-footer">
            <a href="<?php echo esc_url( $see_all_events_link ); ?>" class="button primary large">See All Events</a>
        </div>
    </section>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode( 'aiga_events_grid_preview', 'aiga_events_grid_preview_shortcode' );










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


//-------------------------------------------------------------------------------------------------------------------------------- Contact Bento
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

//---------------------------------------------------------------------------------------------- Why Join
function aiga_why_join_shortcode( $atts ) {
    // Accept an optional 'id' attribute
    $atts = shortcode_atts( array(
        'id' => '',
    ), $atts, 'aiga_why_join' );
    
    $post_id = $atts['id'] ? $atts['id'] : get_the_ID();
    
    // Retrieve section heading
    $heading = get_field('why_join_heading', $post_id);
    
    // Card 1: Education
    $card1_title = get_field('why_join_card_1_title', $post_id);
    $card1_image = get_field('why_join_card_1_image', $post_id);
    $card1_text  = get_field('why_join_card_1_text', $post_id);
    
    // Card 2: Development
    $card2_title = get_field('why_join_card_2_title', $post_id);
    $card2_image = get_field('why_join_card_2_image', $post_id);
    $card2_text  = get_field('why_join_card_2_text', $post_id);
    
    // Card 3: Network
    $card3_title = get_field('why_join_card_3_title', $post_id);
    $card3_image = get_field('why_join_card_3_image', $post_id);
    $card3_text  = get_field('why_join_card_3_text', $post_id);
    
    // Card 4: Discounts
    $card4_title = get_field('why_join_card_4_title', $post_id);
    $card4_image = get_field('why_join_card_4_image', $post_id);
    $card4_text  = get_field('why_join_card_4_text', $post_id);
    
    ob_start();
    ?>
    <section class="page-section">
      <!-- Section Heading -->
      <div class="header-wrapper">
        <h3><?php echo esc_html( $heading ? $heading : 'Why Join?' ); ?></h3>
        <div class="underline"></div>
      </div>
      
      <!-- Cards Container -->
      <div class="why-join-grid">
        <!-- Card 1 -->
        <div class="why-join-card card-bg">
          <h5><?php echo esc_html( $card1_title ? $card1_title : 'Education' ); ?></h5>
          <div class="why-join-image">
            <?php if ( $card1_image ) : ?>
              <img src="<?php echo esc_url( $card1_image['url'] ); ?>" alt="<?php echo esc_attr( isset($card1_image['alt']) && $card1_image['alt'] ? $card1_image['alt'] : 'Education Image' ); ?>">
            <?php endif; ?>
          </div>
          <p class="body-2"><?php echo wp_kses_post( $card1_text ? $card1_text : 'Gain access to design workshops, expert-led webinars, and toolkits to grow your skills.' ); ?></p>
        </div>

        <!-- Card 2 -->
        <div class="why-join-card card-bg">
          <h5><?php echo esc_html( $card2_title ? $card2_title : 'Development' ); ?></h5>
          <div class="why-join-image">
            <?php if ( $card2_image ) : ?>
              <img src="<?php echo esc_url( $card2_image['url'] ); ?>" alt="<?php echo esc_attr( isset($card2_image['alt']) && $card2_image['alt'] ? $card2_image['alt'] : 'Development Image' ); ?>">
            <?php endif; ?>
          </div>
          <p class="body-2"><?php echo wp_kses_post( $card2_text ? $card2_text : 'Take your career to the next level with portfolio reviews, mentorship programs, and leadership opportunities.' ); ?></p>
        </div>

        <!-- Card 3 -->
        <div class="why-join-card card-bg">
          <h5><?php echo esc_html( $card3_title ? $card3_title : 'Network' ); ?></h5>
          <div class="why-join-image">
            <?php if ( $card3_image ) : ?>
              <img src="<?php echo esc_url( $card3_image['url'] ); ?>" alt="<?php echo esc_attr( isset($card3_image['alt']) && $card3_image['alt'] ? $card3_image['alt'] : 'Network Image' ); ?>">
            <?php endif; ?>
          </div>
          <p class="body-2"><?php echo wp_kses_post( $card3_text ? $card3_text : 'Join a diverse and supportive design community across disciplines. From local meetups to national conferences.' ); ?></p>
        </div>

        <!-- Card 4 -->
        <div class="why-join-card card-bg">
          <h5><?php echo esc_html( $card4_title ? $card4_title : 'Discounts' ); ?></h5>
          <div class="why-join-image">
            <?php if ( $card4_image ) : ?>
              <img src="<?php echo esc_url( $card4_image['url'] ); ?>" alt="<?php echo esc_attr( isset($card4_image['alt']) && $card4_image['alt'] ? $card4_image['alt'] : 'Discounts Image' ); ?>">
            <?php endif; ?>
          </div>
          <p class="body-2"><?php echo wp_kses_post( $card4_text ? $card4_text : 'Enjoy exclusive savings on software, tools, events, and more!' ); ?></p>
        </div>
      </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode('aiga_why_join', 'aiga_why_join_shortcode');


//------------------------------------------------------------------------------------------------------- Membership levels
function aiga_membership_levels_shortcode( $atts ) {
    // Accept an optional post ID attribute (useful if you want to target a specific post)
    $atts = shortcode_atts( array(
        'id' => '',
    ), $atts, 'aiga_membership_levels' );
    
    // Use the provided ID or default to current post
    $post_id = $atts['id'] ? $atts['id'] : get_the_ID();
    
    // Retrieve section heading
    $heading = get_field('membership_levels_heading', $post_id);
    
    // Retrieve membership level fields
    $level1_title = get_field('membership_level_1_title', $post_id);
    $level1_text  = get_field('membership_level_1_text', $post_id);
    
    $level2_title = get_field('membership_level_2_title', $post_id);
    $level2_text  = get_field('membership_level_2_text', $post_id);
    
    $level3_title = get_field('membership_level_3_title', $post_id);
    $level3_text  = get_field('membership_level_3_text', $post_id);
    
    $level4_title = get_field('membership_level_4_title', $post_id);
    $level4_text  = get_field('membership_level_4_text', $post_id);
    
    $level5_title = get_field('membership_level_5_title', $post_id);
    $level5_text  = get_field('membership_level_5_text', $post_id);
    
    $level6_title = get_field('membership_level_6_title', $post_id);
    $level6_text  = get_field('membership_level_6_text', $post_id);
    
    ob_start();
    ?>
    <section class="page-section">
      <!-- Section Heading -->
      <div class="header-wrapper">
        <h3><?php echo esc_html( $heading ? $heading : 'Membership Levels' ); ?></h3>
        <div class="underline"></div>
      </div>
      
      <!-- Membership Levels List -->
      <div class="membership-levels__list">
        <!-- Card 1: Student -->
        <article class="membership-levels__item card-bg">
          <h5><?php echo esc_html( $level1_title ? $level1_title : 'Student' ); ?></h5>
          <p><?php echo wp_kses_post( $level1_text ? $level1_text : 'For full-time K-12 students or full-time college/ university undergraduates or graduate students studying design, visual communications, or related fields.' ); ?></p>
        </article>
        <!-- Card 2: Emerging -->
        <article class="membership-levels__item card-bg">
          <h5><?php echo esc_html( $level2_title ? $level2_title : 'Emerging' ); ?></h5>
          <p><?php echo wp_kses_post( $level2_text ? $level2_text : 'Designed for new designers within one to four years of graduation or practitioners with less than four years of industry experience. (Eligibility lasts up to four years.)' ); ?></p>
        </article>
        <!-- Card 3: Professional -->
        <article class="membership-levels__item card-bg">
          <h5><?php echo esc_html( $level3_title ? $level3_title : 'Professional' ); ?></h5>
          <p><?php echo wp_kses_post( $level3_text ? $level3_text : 'Ideal for design or industry-related professionals with five or more years of experience in the field.' ); ?></p>
        </article>
        <!-- Card 4: Leader -->
        <article class="membership-levels__item card-bg">
          <h5><?php echo esc_html( $level4_title ? $level4_title : 'Leader' ); ?></h5>
          <p><?php echo wp_kses_post( $level4_text ? $level4_text : 'For seasoned professionals, industry leaders, or firm owners who wish to invest more in AIGA and the future of the design profession.' ); ?></p>
        </article>
        <!-- Card 5: Educator -->
        <article class="membership-levels__item card-bg">
          <h5><?php echo esc_html( $level5_title ? $level5_title : 'Educator' ); ?></h5>
          <p><?php echo wp_kses_post( $level5_text ? $level5_text : 'Open to full-time educators in design or related fields at K-12 schools, colleges, or universities.' ); ?></p>
        </article>
        <!-- Card 6: Organizations -->
        <article class="membership-levels__item card-bg">
          <h5><?php echo esc_html( $level6_title ? $level6_title : 'Organizations' ); ?></h5>
          <p><?php echo wp_kses_post( $level6_text ? $level6_text : 'If you’re a company or organization interested in AIGA membership packages and would like to empower your team through our extensive resources, contact our Membership team at membership@kansascity.aiga.org. We’d love to help you explore suitable membership options for your organization.' ); ?></p>
        </article>
      </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode('aiga_membership_levels', 'aiga_membership_levels_shortcode');

function aiga_how_to_join_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'id' => '',
    ), $atts, 'aiga_how_to_join' );

    $post_id = $atts['id'] ? $atts['id'] : get_the_ID();

    // Section heading
    $heading = get_field('how_to_join_heading', $post_id);

    // Steps
    $step_1_title = get_field('how_to_join_step_1_title', $post_id);
    $step_1_link  = get_field('how_to_join_step_1_link', $post_id);

    $step_2_title = get_field('how_to_join_step_2_title', $post_id);
    $step_3_title = get_field('how_to_join_step_3_title', $post_id);
    $step_4_title = get_field('how_to_join_step_4_title', $post_id);

    ob_start();
    ?>
    <section class="page-section how-to-join">
      <div class="header-wrapper">
        <h3><?php echo esc_html( $heading ? $heading : 'How to Join' ); ?></h3>
        <div class="underline"></div>
      </div>
      <div class="how-to-join__steps">
        <!-- Step 1 -->
        <div class="how-to-join__step">
          <h5><?php echo esc_html( $step_1_title ? $step_1_title : 'Visit AIGA' ); ?></h5>
          <?php if ( $step_1_link ) : ?>
            <a href="<?php echo esc_url( $step_1_link ); ?>" target="_blank" rel="noopener noreferrer">
              AIGA.ORG <span>&#10142;</span>
            </a>
          <?php endif; ?>
          <div class="how-to-join__step-number">1</div>
        </div>

        <!-- Step 2 -->
        <div class="how-to-join__step">
          <h5><?php echo esc_html( $step_2_title ? $step_2_title : 'Select your membership' ); ?></h5>
          <div class="how-to-join__step-number">2</div>
        </div>

        <!-- Step 3 -->
        <div class="how-to-join__step">
          <h5><?php echo esc_html( $step_3_title ? $step_3_title : 'Select Kansas City' ); ?></h5>
          <div class="how-to-join__step-number">3</div>
        </div>

        <!-- Step 4 -->
        <div class="how-to-join__step">
          <h5><?php echo esc_html( $step_4_title ? $step_4_title : 'Enjoy AIGA!' ); ?></h5>
          <div class="how-to-join__step-number">4</div>
        </div>
      </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode('aiga_how_to_join', 'aiga_how_to_join_shortcode');

//------------------------------------------------------------------------------------------------------------------- Footer
function aiga_footer_socials_shortcode( $atts ) {
    // Use the options page to retrieve global footer assets
    $instagram_url   = get_field( 'social_instagram_url', 'option' );
    $instagram_icon  = get_field( 'social_instagram_icon', 'option' );

    $linkedin_url    = get_field( 'social_linkedin_url', 'option' );
    $linkedin_icon   = get_field( 'social_linkedin_icon', 'option' );

    $facebook_url    = get_field( 'social_facebook_url', 'option' );
    $facebook_icon   = get_field( 'social_facebook_icon', 'option' );

    ob_start();
    ?>
    <footer class="site-footer">
      <div class="footer-container">
        <div class="footer-socials">

          <?php if ( $instagram_url && $instagram_icon ) : ?>
            <a href="<?php echo esc_url( $instagram_url ); ?>" target="_blank" rel="noopener noreferrer">
              <img src="<?php echo esc_url( $instagram_icon['url'] ); ?>" alt="<?php echo esc_attr( $instagram_icon['alt'] ?: 'Instagram' ); ?>">
            </a>
          <?php endif; ?>

          <?php if ( $linkedin_url && $linkedin_icon ) : ?>
            <a href="<?php echo esc_url( $linkedin_url ); ?>" target="_blank" rel="noopener noreferrer">
              <img src="<?php echo esc_url( $linkedin_icon['url'] ); ?>" alt="<?php echo esc_attr( $linkedin_icon['alt'] ?: 'LinkedIn' ); ?>">
            </a>
          <?php endif; ?>

          <?php if ( $facebook_url && $facebook_icon ) : ?>
            <a href="<?php echo esc_url( $facebook_url ); ?>" target="_blank" rel="noopener noreferrer">
              <img src="<?php echo esc_url( $facebook_icon['url'] ); ?>" alt="<?php echo esc_attr( $facebook_icon['alt'] ?: 'Facebook' ); ?>">
            </a>
          <?php endif; ?>

        </div>
      </div>
    </footer>
    <?php
    return ob_get_clean();
}
add_shortcode( 'aiga_footer_socials', 'aiga_footer_socials_shortcode' );

//----------------------------------------------------------------------------------------------------------------------------------- About page


/**
 * Shortcode: [about_history]
 *
 * Outputs a section with a heading, a paragraph (WYSIWYG), and an image.
 * Uses ACF to retrieve content with default fallbacks.
 */
function about_history_shortcode( $atts ) {
    // Start output buffering.
    ob_start();

    // Merge shortcode attributes with defaults.
    $atts = shortcode_atts( array(
        'heading' => '',
        'content' => '',
        'image'   => '',
    ), $atts, 'about_history' );

    // Retrieve ACF fields with fallbacks.
    $heading = get_field( 'about_history_heading' ) ?: 'Showcasing the Hearbeat of Design in the Midwest';
    $content = get_field( 'about_history_content' ) ?: 'Founded in 1989 by Ann Willoughby and a passionate group of creatives, AIGA Kansas City has become a beacon of design excellence in the Midwest. What began as a shared vision for uniting designers in the heartland has blossomed into one of the largest and most vibrant chapters in the AIGA network. The foundation of AIGA KC’s success is built upon collaboration, creativity, and a strong sense of community. We are proud to be the creative hub that continues to inspire and shape regional design professionals.

At AIGA KC, we are more than just a chapter; we are the heart and soul of Midwest design. Our members represent various disciplines, including advertising, marketing, branding, print, interactive, and environmental design. We are home to creatives from boutique firms, large agencies, in-house teams, and independent freelancers. Our community extends beyond just designers—writers, photographers, videographers, graffiti artists, and educators call AIGA KC home. Together, we foster a dynamic environment where innovation thrives and creativity knows no bounds.';
    $image_field = get_field( 'about_history_image' ) ?: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRuEwxZ1PYDSUNrg65QLH-7mPaCrPNI8BUXWA&s';

    // Determine the image URL and alternative text.
    // If the field returns an array, extract the 'url' and 'alt'.
    if ( is_array( $image_field ) ) {
        $image_url = isset( $image_field['url'] ) ? $image_field['url'] : '';
        $image_alt = ! empty( $image_field['alt'] ) ? $image_field['alt'] : $heading;
    } else {
        // Otherwise, assume it's a URL string.
        $image_url = $image_field;
        $image_alt = $heading;
    }

    // Allow shortcode attributes to override the ACF values.
    $heading   = ! empty( $atts['heading'] ) ? $atts['heading'] : $heading;
    $content   = ! empty( $atts['content'] ) ? $atts['content'] : $content;
    $image_url = ! empty( $atts['image'] ) ? $atts['image'] : $image_url;
    ?>

    <!-- Begin component markup -->
    <section class="page-section">
      <div class="about-history">
        <div class="about-column-left">
          <?php if ( $heading ) : ?>
            <h3><?php echo esc_html( $heading ); ?></h3>
          <?php endif; ?>

          <?php if ( $content ) : ?>
            <p class="body-1">
              <?php
              // Apply the 'the_content' filter to ensure proper formatting.
              echo apply_filters( 'the_content', $content );
              ?>
            </p>
          <?php endif; ?>
        </div>
        <div class="about-image-right">
          <?php if ( $image_url ) : ?>
            <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>">
          <?php endif; ?>
        </div>
      </div>
    </section>
    <!-- End component markup -->

    <?php
    // Return the buffered output.
    return ob_get_clean();
}
add_shortcode( 'about_history', 'about_history_shortcode' );

function about_board_card_shortcode( $atts ) {
    // Start output buffering.
    ob_start();

    // Merge shortcode attributes with defaults.
    $atts = shortcode_atts( array(
        'heading'   => '',
        'content'   => '',
        'link_text' => '',
        'link_url'  => '',
    ), $atts, 'about_board_card' );

    // Retrieve content from ACF fields with default fallbacks.
    $heading   = get_field( 'about_board_card_heading' ) ?: 'AIGA KC Board is 100% Volunteer-Run';
    $content   = get_field( 'about_board_card_content' ) ?: 'Behind the scenes of AIGA KC is a group of passionate individuals who volunteer their time to steer the chapter. The AIGA KC Board of Directors is a dynamic team of creative professionals who dedicate their energy to ensuring the success of our programming, fostering a strong design community, and providing opportunities for members to grow.

From organizing events to connecting local talent with national resources, our board drives everything we do. Interested in becoming part of this incredible team and making a real impact?';
    $link_text = get_field( 'about_board_card_link_text' ) ?: 'About the Board';
    $link_url  = get_field( 'about_board_card_link_url' ) ?: '#';

    // Allow shortcode attributes to override ACF values.
    $heading   = ! empty( $atts['heading'] ) ? $atts['heading'] : $heading;
    $content   = ! empty( $atts['content'] ) ? $atts['content'] : $content;
    $link_text = ! empty( $atts['link_text'] ) ? $atts['link_text'] : $link_text;
    $link_url  = ! empty( $atts['link_url'] ) ? $atts['link_url'] : $link_url;
    ?>

    <section class="page-section">
      <div class="about-board-card card-bg">
        <?php if ( $heading ) : ?>
          <h3><?php echo esc_html( $heading ); ?></h3>
        <?php endif; ?>

        <?php if ( $content ) : ?>
          <p class="body-1">
            <?php 
            // Process the content through 'the_content' filter for proper WYSIWYG formatting.
            echo apply_filters( 'the_content', $content ); 
            ?>
          </p>
        <?php endif; ?>

        <?php if ( $link_text && $link_url ) : ?>
          <a href="<?php echo esc_url( $link_url ); ?>" class="button primary medium">
            <?php echo esc_html( $link_text ); ?>
          </a>
        <?php endif; ?>
      </div>
    </section>

    <?php
    // Return the buffered output.
    return ob_get_clean();
}
add_shortcode( 'about_board_card', 'about_board_card_shortcode' );



/**
 * Shortcode: [faq_section]
 *
 * This shortcode outputs a FAQ section that displays a header and several FAQ cards.
 * It uses individual ACF fields for each FAQ card:
 * - faq_question_1, faq_answer_1, faq_link_text_1, faq_link_url_1
 * - faq_question_2, faq_answer_2, etc.
 *
 * If an individual field is empty, it falls back to default content.
 */
function faq_section_shortcode( $atts ) {
    ob_start();

    // Allow an optional header override via shortcode attribute.
    $atts = shortcode_atts( array(
        'header' => '',
    ), $atts, 'faq_section' );

    // Get header text from ACF, or use the default.
    $header_text = get_field( 'faq_header_heading' ) ?: 'Frequently Asked Questions';
    $header_text = ! empty( $atts['header'] ) ? $atts['header'] : $header_text;
    ?>
    <section class="page-section">
      <div class="header-wrapper">
        <h3><?php echo esc_html( $header_text ); ?></h3>
        <div class="underline"></div>
      </div>
    <?php

    // Set up default fallback content for each FAQ card.
    $fallback_defaults = array(
        1 => array(
            'question'  => 'What is AIGA KC?',
            'answer'    => 'AIGA KC is the Kansas City chapter of AIGA, the professional association for design. We’re part of a nationwide network of creatives, and our chapter is one of the largest in the Midwest. We offer events, educational opportunities, and resources for designers of all kinds.',
            'link_text' => '',
            'link_url'  => '',
        ),
        2 => array(
            'question'  => 'Who can join AIGA KC?',
            'answer'    => 'Anyone with an interest in design and creativity is welcome! Our members come from diverse fields, including advertising, branding, marketing, and interactive design, as well as photography, videography, education, and more.',
            'link_text' => '',
            'link_url'  => '',
        ),
        3 => array(
            'question'  => 'Do I have to be a designer to join?',
            'answer'    => 'No! While many of our members are designers, AIGA KC is open to anyone who shares a passion for creativity. Writers, photographers, educators, graffiti artists—if you’re creative, you’re welcome here.',
            'link_text' => '',
            'link_url'  => '',
        ),
        4 => array(
            'question'  => 'What kinds of events does AIGA KC host?',
            'answer'    => 'We host a variety of events, including design talks, workshops, networking meetups, and creative competitions. Whether you’re looking to learn, get inspired, or connect with other creatives, we’ve got something for you.',
            'link_text' => '',
            'link_url'  => '',
        ),
        5 => array(
            'question'  => 'How can I get involved?',
            'answer'    => 'There are many ways to get involved with AIGA KC! Become a member, attend an event, or join our volunteer board. You can learn more about membership and volunteer opportunities.',
            'link_text' => 'Get Involved Today',
            'link_url'  => '#',
        ),
    );

    // Define the number of FAQ cards you expect.
    $max_faqs = 5;
    $faq_items = array();

    // Loop through each FAQ item.
    for ( $i = 1; $i <= $max_faqs; $i++ ) {
        $question_field  = 'faq_question_' . $i;
        $answer_field    = 'faq_answer_' . $i;
        $link_text_field = 'faq_link_text_' . $i;
        $link_url_field  = 'faq_link_url_' . $i;

        // Retrieve each field.
        $question  = get_field( $question_field );
        $answer    = get_field( $answer_field );
        $link_text = get_field( $link_text_field );
        $link_url  = get_field( $link_url_field );

        // Use fallback defaults if individual ACF fields are empty.
        if ( empty( $question ) && isset( $fallback_defaults[ $i ]['question'] ) ) {
            $question = $fallback_defaults[ $i ]['question'];
        }
        if ( empty( $answer ) && isset( $fallback_defaults[ $i ]['answer'] ) ) {
            $answer = $fallback_defaults[ $i ]['answer'];
        }
        if ( empty( $link_text ) && isset( $fallback_defaults[ $i ]['link_text'] ) ) {
            $link_text = $fallback_defaults[ $i ]['link_text'];
        }
        if ( empty( $link_url ) && isset( $fallback_defaults[ $i ]['link_url'] ) ) {
            $link_url = $fallback_defaults[ $i ]['link_url'];
        }

        // Only add the FAQ card if at least one of the main fields is set.
        if ( ! empty( $question ) || ! empty( $answer ) ) {
            $faq_items[] = array(
                'question'  => $question,
                'answer'    => $answer,
                'link_text' => $link_text,
                'link_url'  => $link_url,
            );
        }
    }

    // Render each FAQ card.
    foreach ( $faq_items as $faq ) : ?>
        <div class="faq-card card-bg">
            <?php if ( ! empty( $faq['question'] ) ) : ?>
                <h3><?php echo esc_html( $faq['question'] ); ?></h3>
            <?php endif; ?>
            <?php if ( ! empty( $faq['answer'] ) ) : ?>
                <p class="body-1">
                    <?php echo apply_filters( 'the_content', $faq['answer'] ); ?>
                </p>
            <?php endif; ?>
            <?php if ( ! empty( $faq['link_text'] ) && ! empty( $faq['link_url'] ) ) : ?>
                <a href="<?php echo esc_url( $faq['link_url'] ); ?>">
                    <?php echo esc_html( $faq['link_text'] ); ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    </section>
    <?php

    return ob_get_clean();
}
add_shortcode( 'faq_section', 'faq_section_shortcode' );

// Register the [main_feature] shortcode

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





add_filter('widget_text', 'do_shortcode');
add_filter('the_content', 'do_shortcode');
