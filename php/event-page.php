<?php
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