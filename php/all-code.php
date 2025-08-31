<?php
/**
 * Register CPT: member-feature
 */
add_action('init', function () {
    register_post_type('member-feature', [
        'labels' => [
            'name'          => __('Member Features', 'aiga'),
            'singular_name' => __('Member Feature', 'aiga'),
        ],
        'public'             => true,
        'has_archive'        => false,
        'publicly_queryable' => false,
        'rewrite'            => false,
        'show_in_rest'       => true,
        'supports'           => ['title', 'editor', 'thumbnail'],
    ]);
});

/**
 * Enqueue child stylesheet
 */
if (!function_exists('bridge_qode_child_theme_enqueue_scripts')) {
    function bridge_qode_child_theme_enqueue_scripts() {
        wp_register_style('bridge-childstyle', get_stylesheet_directory_uri() . '/style.css', [], null, 'all');
        wp_enqueue_style('bridge-childstyle');
    }
    add_action('wp_enqueue_scripts', 'bridge_qode_child_theme_enqueue_scripts', 11);
}

/**
 * [acf_field field="field_name"]
 * Safer ACF field output with minimal assumptions.
 */
function shortcode_acf_field($atts) {
    $atts = shortcode_atts(['field' => ''], $atts, 'acf_field');
    $field = $atts['field'];
    if (!$field) return '';

    $value = get_field($field);

    if (is_string($value)) {
        // Allow basic HTML but sanitize
        return wp_kses_post($value);
    }

    if (is_array($value)) {
        // Common ACF structures
        if (!empty($value['url'])) {
            return esc_url($value['url']);
        }
        if (!empty($value['ID'])) {
            // Return responsive image markup
            return wp_get_attachment_image(
                intval($value['ID']),
                'large',
                false,
                ['loading' => 'lazy', 'decoding' => 'async']
            );
        }
    }

    if (is_scalar($value)) {
        return esc_html((string)$value);
    }

    return '';
}
add_shortcode('acf_field', 'shortcode_acf_field');


/**
 * [home_doodle id=""]
 */
function home_doodle_shortcode($atts) {
    $atts = shortcode_atts(['id' => ''], $atts, 'home_doodle');
    $post_id = $atts['id'] ? intval($atts['id']) : get_the_ID();

    $hero_bg           = get_field('hero_background_image', $post_id);
    $artist_page_link  = get_field('artist_page_link', $post_id);
    $artist_profile    = get_field('artist_profile_image', $post_id);
    $artist_name       = (string) get_field('artist_name', $post_id);
    $artist_desc       = (string) get_field('artist_description', $post_id);
    $show_card         = (bool) get_field('show_artist_link_card', $post_id);

    ob_start(); ?>
    <section class="hero-wrapper home-hero">
        <div
            class="hero-container home-hero__media"
            <?php if (!empty($hero_bg['url'])) : ?>
                style="background-image:url(<?php echo esc_url($hero_bg['url']); ?>);background-size:cover;background-position:center"
            <?php endif; ?>>
        </div>

        <?php if ($show_card && $artist_page_link) : ?>
            <a href="<?php echo esc_url($artist_page_link); ?>"
               class="artist-link-card home-hero__artist-card"
               aria-label="<?php echo esc_attr($artist_name ? "Visit $artist_name" : 'Visit artist'); ?>">
                <?php
                if (!empty($artist_profile['ID'])) {
                    echo wp_get_attachment_image(
                        intval($artist_profile['ID']),
                        'thumbnail',
                        false,
                        [
                            'class'     => 'profile-img home-hero__artist-avatar',
                            'loading'   => 'lazy',
                            'decoding'  => 'async',
                            'alt'       => esc_attr($artist_profile['alt'] ?: $artist_name ?: 'Artist'),
                        ]
                    );
                }
                ?>
                <div class="artist-text home-hero__artist-text">
                    <?php if ($artist_name) : ?>
                        <p class="body-2 home-hero__artist-name"><?php echo esc_html($artist_name); ?></p>
                    <?php endif; ?>
                    <?php if ($artist_desc) : ?>
                        <p class="body-3 home-hero__artist-desc"><?php echo esc_html($artist_desc); ?></p>
                    <?php endif; ?>
                </div>
            </a>
        <?php endif; ?>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode('home_doodle', 'home_doodle_shortcode');


/**
 * PAGE HERO (formerly aiga_section)
 * Shortcode: [page_hero id=""]
 * Back-compat alias: [aiga_section]
 *
 * Fields used:
 * - aiga_card_one_image, aiga_card_one_text
 * - aiga_card_two_image, aiga_card_two_text
 * - aiga_right_background
 * - aiga_cta_heading, aiga_cta_text, aiga_cta_link, aiga_cta_button_text, link_new_tab
 */
function page_hero_shortcode($atts) {
    $atts = shortcode_atts(['id' => ''], $atts, 'page_hero');
    $post_id = $atts['id'] ? intval($atts['id']) : get_the_ID();

    // Left cards
    $card1_img = get_field('aiga_card_one_image', $post_id);
    $card1_txt = get_field('aiga_card_one_text',  $post_id);

    $card2_img = get_field('aiga_card_two_image', $post_id);
    $card2_txt = get_field('aiga_card_two_text',  $post_id);

    // Right background + CTA
    $right_bg  = get_field('aiga_right_background', $post_id);
    $cta_h     = (string) get_field('aiga_cta_heading', $post_id);
    $cta_p     = get_field('aiga_cta_text', $post_id);
    $cta_link  = get_field('aiga_cta_link', $post_id);
    $cta_label = (string) get_field('aiga_cta_button_text', $post_id);
    $new_tab   = (bool) get_field('link_new_tab', $post_id);
    $targetrel = $new_tab ? ' target="_blank" rel="noopener noreferrer"' : '';

    ob_start(); ?>
    <section class="page-hero">
        <div class="page-hero__left">
            <div class="page-hero__card page-hero__card--one"
                 <?php if (!empty($card1_img['url'])) : ?>
                    style="background-image:url(<?php echo esc_url($card1_img['url']); ?>)"
                 <?php endif; ?>>
                <?php if ($card1_txt) : ?>
                    <div class="page-hero__card-text"><?php echo wp_kses_post($card1_txt); ?></div>
                <?php endif; ?>
            </div>

            <div class="page-hero__card page-hero__card--two"
                 <?php if (!empty($card2_img['url'])) : ?>
                    style="background-image:url(<?php echo esc_url($card2_img['url']); ?>)"
                 <?php endif; ?>>
                <?php if ($card2_txt) : ?>
                    <div class="page-hero__card-text"><?php echo wp_kses_post($card2_txt); ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="page-hero__right"
             <?php if (!empty($right_bg['url'])) : ?>
                style="background-image:url(<?php echo esc_url($right_bg['url']); ?>)"
             <?php endif; ?>>
            <div class="page-hero__cta" role="region" aria-label="Call to action">
                <?php if ($cta_h) : ?>
                    <h2 class="page-hero__cta-title"><?php echo esc_html($cta_h); ?></h2>
                <?php endif; ?>
                <?php if ($cta_p) : ?>
                    <p class="body-2 page-hero__cta-text"><?php echo wp_kses_post($cta_p); ?></p>
                <?php endif; ?>
                <?php if ($cta_link && $cta_label) : ?>
                    <a href="<?php echo esc_url($cta_link); ?>" class="button secondary large page-hero__cta-button"<?php echo $targetrel; ?>>
                        <?php echo esc_html($cta_label); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode('page_hero', 'page_hero_shortcode');

/**
 * Back-compat alias: keep existing content working
 * [aiga_section] will render the new Page Hero
 */
function aiga_section_shortcode_alias($atts) {
    return page_hero_shortcode($atts);
}
add_shortcode('aiga_section', 'aiga_section_shortcode_alias');


/**
 * Helpers
 */
function aiga_format_event_date($ymd) {
    if (!$ymd) return '';
    $dt = DateTime::createFromFormat('Ymd', $ymd);
    return $dt ? $dt->format('F j, Y') : $ymd;
}

/**
 * [aiga_event_page id=""]
 */
function aiga_event_page_shortcode($atts) {
    $atts = shortcode_atts(['id' => ''], $atts, 'aiga_event_page');
    $post_id = $atts['id'] ? intval($atts['id']) : get_the_ID();

    $header_img = get_field('event_header_image', $post_id);
    $date_raw   = get_field('event_date', $post_id);
    $time       = (string) get_field('event_time', $post_id);
    $category   = (string) get_field('event_category', $post_id);
    $venue      = (string) get_field('event_venue', $post_id);
    $address    = (string) get_field('event_address', $post_id);
    $ticket_url = get_field('event_ticket_url', $post_id);
    $ticket_txt = (string) get_field('event_ticket_text', $post_id);
    $desc       = get_field('event_description', $post_id);

    $date_fmt = aiga_format_event_date($date_raw);

    ob_start(); ?>
    <section class="event-page" aria-labelledby="event-title-<?php echo esc_attr($post_id); ?>">
        <div class="event-page-hero">
            <?php
            if (!empty($header_img['ID'])) {
                echo wp_get_attachment_image(
                    intval($header_img['ID']),
                    'full',
                    false,
                    [
                        'class'    => 'event-page-hero__img',
                        'loading'  => 'lazy',
                        'decoding' => 'async',
                        'alt'      => esc_attr($header_img['alt'] ?: get_the_title($post_id)),
                    ]
                );
            }
            ?>
        </div>

        <div class="event-content">
            <div class="event-info">
                <h3 id="event-title-<?php echo esc_attr($post_id); ?>" class="event-info__title"><?php echo esc_html(get_the_title($post_id)); ?></h3>

                <?php if ($category) : ?>
                    <span class="tag tag-<?php echo esc_attr(sanitize_title($category)); ?> event-info__tag">
                        <?php echo esc_html($category); ?>
                    </span>
                <?php endif; ?>

                <?php if ($date_fmt) : ?>
                    <p class="event-info__date"><strong><?php echo esc_html($date_fmt); ?></strong></p>
                <?php endif; ?>
                <?php if ($time) : ?>
                    <p class="event-info__time"><strong><?php echo esc_html($time); ?></strong></p>
                <?php endif; ?>
                <?php if ($venue) : ?>
                    <p class="event-info__venue"><strong><?php echo esc_html($venue); ?></strong></p>
                <?php endif; ?>
                <?php if ($address) : ?>
                    <p class="event-info__address"><?php echo esc_html($address); ?></p>
                <?php endif; ?>

                <?php if ($ticket_url && $ticket_txt) : ?>
                    <a href="<?php echo esc_url($ticket_url); ?>" class="button large primary event-info__cta">
                        <?php echo esc_html($ticket_txt); ?>
                    </a>
                <?php endif; ?>
            </div>

            <div class="event-description">
                <?php if ($desc) echo apply_filters('the_content', $desc); ?>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode('aiga_event_page', 'aiga_event_page_shortcode');


/**
 * Shared renderer for event card (reduces duplication)
 */
function aiga_render_event_card($post_id) {
    $img       = get_field('event_header_image', $post_id);
    $date_raw  = get_field('event_date', $post_id);
    $time      = (string) get_field('event_time', $post_id);
    $category  = (string) get_field('event_category', $post_id);
    $cat_class = $category ? sanitize_title($category) : '';
    $date_fmt  = aiga_format_event_date($date_raw);

    $title     = get_the_title($post_id);
    $link      = get_permalink($post_id);
    ?>
    <a href="<?php echo esc_url($link); ?>"
       class="mix event-card event-grid__card <?php echo esc_attr($cat_class); ?>"
       aria-label="<?php echo esc_attr($title); ?>">
        <div class="image-wrapper event-card__image-wrapper">
            <?php
            if (!empty($img['ID'])) {
                echo wp_get_attachment_image(
                    intval($img['ID']),
                    'large',
                    false,
                    [
                        'class'    => 'event-card__image',
                        'loading'  => 'lazy',
                        'decoding' => 'async',
                        'sizes'    => '(max-width: 600px) 100vw, 300px',
                        'alt'      => esc_attr($img['alt'] ?: $title),
                    ]
                );
            }
            ?>
        </div>
        <div class="event-meta event-card__meta">
            <p class="bold event-card__title"><?php echo esc_html($title); ?></p>
            <?php if ($date_fmt || $time) : ?>
                <p class="date-time event-card__date-time">
                    <?php
                    $when = $date_fmt;
                    if ($date_fmt && $time) $when = $date_fmt . ' • ' . $time;
                    echo esc_html($when);
                    ?>
                </p>
            <?php endif; ?>
            <?php if ($category) : ?>
                <span class="tag tag-<?php echo esc_attr($cat_class); ?> event-card__tag">
                    <?php echo esc_html($category); ?>
                </span>
            <?php endif; ?>
        </div>
    </a>
    <?php
}

/**
 * [aiga_event_grid posts_per_page="-1"]
 */
function aiga_event_grid_shortcode($atts) {
    $atts = shortcode_atts(['posts_per_page' => -1], $atts, 'aiga_event_grid');

    $q = new WP_Query([
        'post_type'      => 'aiga-event',
        'posts_per_page' => intval($atts['posts_per_page']),
        'post_status'    => 'publish',
        'meta_key'       => 'event_date',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
        'no_found_rows'  => true,
    ]);

    ob_start(); ?>
    <section class="event-grid" aria-label="Events">
        <div class="event-preview-header">
            <div class="heder-wrapper">
                <h3>Our Events</h3>
                <div class="underline"></div>
            </div>
        </div>

        <div class="event-card-wrapper event-grid__list">
            <?php
            if ($q->have_posts()) {
                while ($q->have_posts()) {
                    $q->the_post();
                    aiga_render_event_card(get_the_ID());
                }
            } else {
                echo '<p>' . esc_html__('No events found.', 'aiga') . '</p>';
            }
            ?>
        </div>
    </section>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('aiga_event_grid', 'aiga_event_grid_shortcode');


/**
 * [aiga_events_grid_preview posts_per_page="6"]
 */
function aiga_events_grid_preview_shortcode($atts) {
    $atts = shortcode_atts(['posts_per_page' => 6], $atts, 'aiga_events_grid_preview');

    $q = new WP_Query([
        'post_type'      => 'aiga-event',
        'posts_per_page' => intval($atts['posts_per_page']),
        'post_status'    => 'publish',
        'meta_key'       => 'event_date',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
        'no_found_rows'  => true,
    ]);

    $see_all = 'https://kc.aiga.org/events/';

    ob_start(); ?>
    <section class="event-grid" aria-label="Recent events">
        <div class="event-preview-header">
            <div class="heder-wrapper">
                <h3>Our Events</h3>
                <div class="underline"></div>
            </div>
        </div>

        <div class="event-card-wrapper event-grid__list">
            <?php
            if ($q->have_posts()) {
                while ($q->have_posts()) {
                    $q->the_post();
                    aiga_render_event_card(get_the_ID());
                }
            } else {
                echo '<p>' . esc_html__('No events found.', 'aiga') . '</p>';
            }
            ?>
        </div>

        <div class="event-preview-footer">
            <a href="<?php echo esc_url($see_all); ?>" class="button primary large">See All Events</a>
        </div>
    </section>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('aiga_events_grid_preview', 'aiga_events_grid_preview_shortcode');


/**
 * [membership_callout id=""]
 * Replaces: [home_member_preview]
 * - Renders images whether ACF returns array, URL, or attachment ID.
 */
function membership_callout_shortcode( $atts ) {
    $atts = shortcode_atts(['id' => ''], $atts, 'membership_callout');
    $post_id = $atts['id'] ? intval($atts['id']) : get_the_ID();

    // ACF fields
    $tl_img = get_field('top_left_image',  $post_id);
    $tl_cap = (string) get_field('top_left_caption',  $post_id);

    $tr_img = get_field('top_right_image', $post_id);
    $tr_cap = (string) get_field('top_right_caption', $post_id);

    $cta_h  = (string) get_field('cta_heading', $post_id);
    $cta_l  = get_field('cta_link', $post_id);
    $cta_b  = (string) get_field('cta_button_text', $post_id);

    $bt_img = get_field('bottom_image',    $post_id);
    $bt_cap = (string) get_field('bottom_caption',   $post_id);

    // Helper: render an image from array/ID/URL
    $render_img = function ($img, $size, $class, $fallback_alt) {
        if (is_array($img)) {
            if (!empty($img['ID'])) {
                return wp_get_attachment_image(
                    intval($img['ID']), $size, false,
                    ['class'=>$class,'loading'=>'lazy','decoding'=>'async','alt'=>esc_attr($img['alt'] ?? $fallback_alt)]
                );
            }
            if (!empty($img['url'])) {
                $alt = !empty($img['alt']) ? $img['alt'] : $fallback_alt;
                return sprintf(
                    '<img src="%s" alt="%s" class="%s" loading="lazy" decoding="async" />',
                    esc_url($img['url']), esc_attr($alt), esc_attr($class)
                );
            }
        }
        if (is_numeric($img)) {
            return wp_get_attachment_image(
                intval($img), $size, false,
                ['class'=>$class,'loading'=>'lazy','decoding'=>'async','alt'=>esc_attr($fallback_alt)]
            );
        }
        if (is_string($img) && $img !== '') {
            return sprintf(
                '<img src="%s" alt="%s" class="%s" loading="lazy" decoding="async" />',
                esc_url($img), esc_attr($fallback_alt), esc_attr($class)
            );
        }
        return '';
    };

    ob_start(); ?>
    <section class="membership-callout" aria-label="Membership callout">
        <!-- Top Left -->
        <figure class="membership-callout__image-block membership-callout__image-block--top-left">
            <?php echo $render_img($tl_img, 'medium', 'membership-callout__image', $tl_cap ?: 'Top Left Image'); ?>
            <?php if ($tl_cap) : ?>
                <figcaption class="membership-callout__label"><p class="body-2"><?php echo esc_html($tl_cap); ?></p></figcaption>
            <?php endif; ?>
        </figure>

        <!-- Top Right -->
        <figure class="membership-callout__image-block membership-callout__image-block--top-right">
            <?php echo $render_img($tr_img, 'medium', 'membership-callout__image', $tr_cap ?: 'Top Right Image'); ?>
            <?php if ($tr_cap) : ?>
                <figcaption class="membership-callout__label"><p class="body-2"><?php echo esc_html($tr_cap); ?></p></figcaption>
            <?php endif; ?>
        </figure>

        <!-- CTA Center -->
        <div class="membership-callout__cta" role="region" aria-label="Membership call to action">
            <?php if ($cta_h) : ?><h2><?php echo esc_html($cta_h); ?></h2><?php endif; ?>
            <?php if ($cta_l && $cta_b) : ?>
                <a href="<?php echo esc_url($cta_l); ?>" class="button large secondary">
                    <?php echo esc_html($cta_b); ?>
                </a>
            <?php endif; ?>
        </div>

        <!-- Bottom -->
        <figure class="membership-callout__image-block membership-callout__image-block--bottom">
            <?php echo $render_img($bt_img, 'medium', 'membership-callout__image', $bt_cap ?: 'Bottom Image'); ?>
            <?php if ($bt_cap) : ?>
                <figcaption class="membership-callout__label"><p class="body-2"><?php echo esc_html($bt_cap); ?></p></figcaption>
            <?php endif; ?>
        </figure>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode('membership_callout', 'membership_callout_shortcode');

/**
 * Back-compat alias so existing content keeps working:
 * [home_member_preview] → renders the new component.
 */
function home_member_preview_shortcode_alias($atts) {
    return membership_callout_shortcode($atts);
}
add_shortcode('home_member_preview', 'home_member_preview_shortcode_alias');



/**
 * [contact_section id=""]
 */
function contact_section_shortcode($atts) {
    $atts = shortcode_atts(['id' => ''], $atts, 'contact_section');
    $post_id = $atts['id'] ? intval($atts['id']) : get_the_ID();

    $heading    = (string) get_field('contact_heading', $post_id);
    $form_embed = get_field('contact_form_embed', $post_id);
    $img1       = get_field('contact_bento_image_1', $post_id);
    $img2       = get_field('contact_bento_image_2', $post_id);
    $img3       = get_field('contact_bento_image_3', $post_id);

    ob_start(); ?>
    <section class="contact-section" aria-labelledby="contact-title-<?php echo esc_attr($post_id); ?>">
        <div class="contact-form-container">
            <h3 id="contact-title-<?php echo esc_attr($post_id); ?>"><?php echo esc_html($heading); ?></h3>
            <div class="form-embed">
  <?php
  if ($form_embed) {
      $out = '';

      // 1) If it looks like a shortcode, run it (CF7, Gravity Forms, etc.)
      if (preg_match('/\[(.+?)\]/', $form_embed)) {
          $out = do_shortcode($form_embed);

      // 2) If it looks like a bare URL, try oEmbed (Typeform, Google Forms preview links won’t oEmbed, but YouTube/Vimeo would)
      } elseif (filter_var(trim($form_embed), FILTER_VALIDATE_URL)) {
          $oembed = wp_oembed_get(trim($form_embed));
          $out = $oembed ? $oembed : '';

          // Fallback to printing the raw URL (WordPress may auto-embed in some contexts)
          if (!$out) $out = esc_url($form_embed);

      // 3) If it’s raw HTML, allow iframes and form elements (no <script>)
      } else {
          $allowed = [
              'iframe' => [
                  'src'             => true,
                  'width'           => true,
                  'height'          => true,
                  'title'           => true,
                  'frameborder'     => true,
                  'allow'           => true,
                  'allowfullscreen' => true,
                  'referrerpolicy'  => true,
                  'loading'         => true,
                  'style'           => true,
                  'class'           => true,
                  'id'              => true,
              ],
              'form' => [
                  'action'   => true,
                  'method'   => true,
                  'target'   => true,
                  'class'    => true,
                  'id'       => true,
                  'novalidate'=> true,
              ],
              'input' => [
                  'type'      => true,
                  'name'      => true,
                  'value'     => true,
                  'placeholder'=> true,
                  'required'  => true,
                  'checked'   => true,
                  'class'     => true,
                  'id'        => true,
                  'maxlength' => true,
                  'min'       => true,
                  'max'       => true,
                  'step'      => true,
                  'pattern'   => true,
              ],
              'label' => [
                  'for'   => true,
                  'class' => true,
                  'id'    => true,
              ],
              'select' => [
                  'name'     => true,
                  'required' => true,
                  'class'    => true,
                  'id'       => true,
              ],
              'option' => [
                  'value'    => true,
                  'selected' => true,
              ],
              'textarea' => [
                  'name'      => true,
                  'rows'      => true,
                  'cols'      => true,
                  'required'  => true,
                  'class'     => true,
                  'id'        => true,
              ],
              'button' => [
                  'type'   => true,
                  'name'   => true,
                  'value'  => true,
                  'class'  => true,
                  'id'     => true,
              ],
              'div' => ['class' => true, 'id' => true, 'style' => true],
              'span'=> ['class' => true, 'id' => true, 'style' => true],
              'p'   => ['class' => true, 'id' => true, 'style' => true],
              'a'   => ['href' => true, 'target' => true, 'rel' => true, 'class' => true, 'id' => true],
          ];

          // Sanitize without allowing <script>
          $out = wp_kses($form_embed, $allowed);
      }

      echo $out; // final output
  }
  ?>
</div>

        </div>

        <div class="contact-bento" aria-hidden="true">
            <div class="bento__left">
                <figure class="bento__item">
                    <?php
                    if (is_array($img1) && !empty($img1['ID'])) {
                        echo wp_get_attachment_image(intval($img1['ID']), 'large', false, [
                            'class'    => 'bento__image',
                            'loading'  => 'lazy',
                            'decoding' => 'async',
                            'alt'      => esc_attr($img1['alt'] ?: 'Contact image 1'),
                        ]);
                    } ?>
                </figure>
                <figure class="bento__item">
                    <?php
                    if (is_array($img2) && !empty($img2['ID'])) {
                        echo wp_get_attachment_image(intval($img2['ID']), 'large', false, [
                            'class'    => 'bento__image',
                            'loading'  => 'lazy',
                            'decoding' => 'async',
                            'alt'      => esc_attr($img2['alt'] ?: 'Contact image 2'),
                        ]);
                    } ?>
                </figure>
            </div>

            <div class="bento__right">
                <figure class="bento__item">
                    <?php
                    if (is_array($img3) && !empty($img3['ID'])) {
                        echo wp_get_attachment_image(intval($img3['ID']), 'full', false, [
                            'class'    => 'bento__image',
                            'loading'  => 'lazy',
                            'decoding' => 'async',
                            'alt'      => esc_attr($img3['alt'] ?: 'Contact image 3'),
                        ]);
                    } ?>
                </figure>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode('contact_section', 'contact_section_shortcode');


/**
 * [aiga_why_join id=""]
 */
function aiga_why_join_shortcode($atts) {
    $atts = shortcode_atts(['id' => ''], $atts, 'aiga_why_join');
    $post_id = $atts['id'] ? intval($atts['id']) : get_the_ID();

    $heading = get_field('why_join_heading', $post_id);

    $cards = [
        [
            'title' => get_field('why_join_card_1_title', $post_id) ?: 'Education',
            'image' => get_field('why_join_card_1_image', $post_id),
            'text'  => get_field('why_join_card_1_text',  $post_id) ?: 'Gain access to design workshops, expert-led webinars, and toolkits to grow your skills.',
        ],
        [
            'title' => get_field('why_join_card_2_title', $post_id) ?: 'Development',
            'image' => get_field('why_join_card_2_image', $post_id),
            'text'  => get_field('why_join_card_2_text',  $post_id) ?: 'Take your career to the next level with portfolio reviews, mentorship programs, and leadership opportunities.',
        ],
        [
            'title' => get_field('why_join_card_3_title', $post_id) ?: 'Network',
            'image' => get_field('why_join_card_3_image', $post_id),
            'text'  => get_field('why_join_card_3_text',  $post_id) ?: 'Join a diverse and supportive design community across disciplines.',
        ],
        [
            'title' => get_field('why_join_card_4_title', $post_id) ?: 'Discounts',
            'image' => get_field('why_join_card_4_image', $post_id),
            'text'  => get_field('why_join_card_4_text',  $post_id) ?: 'Enjoy exclusive savings on software, tools, events, and more!',
        ],
    ];

    ob_start(); ?>
    <section class="page-section why-join" aria-labelledby="why-join-title-<?php echo esc_attr($post_id); ?>">
        <div class="header-wrapper">
            <h3 id="why-join-title-<?php echo esc_attr($post_id); ?>"><?php echo esc_html($heading ?: 'Why Join?'); ?></h3>
            <div class="underline"></div>
        </div>

        <div class="why-join-grid">
            <?php foreach ($cards as $i => $card) : ?>
                <div class="why-join-card card-bg why-join__card why-join__card--<?php echo intval($i + 1); ?>">
                    <h5 class="why-join__card-title"><?php echo esc_html($card['title']); ?></h5>
                    <div class="why-join-image why-join__image">
                        <?php
                        if (is_array($card['image']) && !empty($card['image']['ID'])) {
                            echo wp_get_attachment_image(
                                intval($card['image']['ID']),
                                'large',
                                false,
                                [
                                    'class'    => 'why-join__img',
                                    'loading'  => 'lazy',
                                    'decoding' => 'async',
                                    'alt'      => esc_attr($card['image']['alt'] ?: $card['title']),
                                ]
                            );
                        }
                        ?>
                    </div>
                    <p class="body-2 why-join__text"><?php echo wp_kses_post($card['text']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode('aiga_why_join', 'aiga_why_join_shortcode');


/**
 * [aiga_membership_levels id=""]
 */
function aiga_membership_levels_shortcode($atts) {
    $atts = shortcode_atts(['id' => ''], $atts, 'aiga_membership_levels');
    $post_id = $atts['id'] ? intval($atts['id']) : get_the_ID();

    $heading = get_field('membership_levels_heading', $post_id);

    $levels = [];
    for ($i = 1; $i <= 6; $i++) {
        $levels[] = [
            'title' => get_field("membership_level_{$i}_title", $post_id) ?: ['Student','Emerging','Professional','Leader','Educator','Organizations'][$i-1],
            'text'  => get_field("membership_level_{$i}_text",  $post_id) ?: '',
            'i'     => $i,
        ];
    }

    ob_start(); ?>
    <section class="page-section membership-levels" aria-labelledby="membership-levels-title-<?php echo esc_attr($post_id); ?>">
        <div class="header-wrapper">
            <h3 id="membership-levels-title-<?php echo esc_attr($post_id); ?>"><?php echo esc_html($heading ?: 'Membership Levels'); ?></h3>
            <div class="underline"></div>
        </div>

        <div class="membership-levels__list">
            <?php foreach ($levels as $level) : ?>
                <article class="membership-levels__item card-bg membership-levels__item--<?php echo intval($level['i']); ?>">
                    <h5 class="membership-levels__title"><?php echo esc_html($level['title']); ?></h5>
                    <?php if ($level['text']) : ?>
                        <div class="membership-levels__desc"><?php echo wp_kses_post($level['text']); ?></div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode('aiga_membership_levels', 'aiga_membership_levels_shortcode');


/**
 * [aiga_how_to_join id=""]
 */
function aiga_how_to_join_shortcode($atts) {
    $atts = shortcode_atts(['id' => ''], $atts, 'aiga_how_to_join');
    $post_id = $atts['id'] ? intval($atts['id']) : get_the_ID();

    $heading   = get_field('how_to_join_heading', $post_id);
    $step_1_t  = get_field('how_to_join_step_1_title', $post_id) ?: 'Visit AIGA';
    $step_1_l  = get_field('how_to_join_step_1_link',  $post_id);
    $step_2_t  = get_field('how_to_join_step_2_title', $post_id) ?: 'Select your membership';
    $step_3_t  = get_field('how_to_join_step_3_title', $post_id) ?: 'Select Kansas City';
    $step_4_t  = get_field('how_to_join_step_4_title', $post_id) ?: 'Enjoy AIGA!';

    ob_start(); ?>
    <section class="page-section how-to-join" aria-labelledby="how-to-join-title-<?php echo esc_attr($post_id); ?>">
        <div class="header-wrapper">
            <h3 id="how-to-join-title-<?php echo esc_attr($post_id); ?>"><?php echo esc_html($heading ?: 'How to Join'); ?></h3>
            <div class="underline"></div>
        </div>

        <div class="how-to-join__steps">
            <div class="how-to-join__step how-to-join__step--1">
                <h5 class="how-to-join__title"><?php echo esc_html($step_1_t); ?></h5>
                <?php if ($step_1_l) : ?>
                    <a class="how-to-join__link" href="<?php echo esc_url($step_1_l); ?>" target="_blank" rel="noopener noreferrer">
                        AIGA.ORG <span aria-hidden="true">&#10142;</span>
                    </a>
                <?php endif; ?>
                <div class="how-to-join__step-number">1</div>
            </div>

            <div class="how-to-join__step how-to-join__step--2">
                <h5 class="how-to-join__title"><?php echo esc_html($step_2_t); ?></h5>
                <div class="how-to-join__step-number">2</div>
            </div>

            <div class="how-to-join__step how-to-join__step--3">
                <h5 class="how-to-join__title"><?php echo esc_html($step_3_t); ?></h5>
                <div class="how-to-join__step-number">3</div>
            </div>

            <div class="how-to-join__step how-to-join__step--4">
                <h5 class="how-to-join__title"><?php echo esc_html($step_4_t); ?></h5>
                <div class="how-to-join__step-number">4</div>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode('aiga_how_to_join', 'aiga_how_to_join_shortcode');


/**
 * [aiga_footer_socials]
 * Uses your existing footer CSS (.aiga-custom-footer .aiga-social-links)
 */
function aiga_footer_socials_shortcode() {
    $instagram_url  = get_field('social_instagram_url', 'option');
    $instagram_icon = get_field('social_instagram_icon', 'option');

    $linkedin_url   = get_field('social_linkedin_url', 'option');
    $linkedin_icon  = get_field('social_linkedin_icon', 'option');

    $facebook_url   = get_field('social_facebook_url', 'option');
    $facebook_icon  = get_field('social_facebook_icon', 'option');

    $items = [
        ['url' => $instagram_url, 'icon' => $instagram_icon, 'label' => 'Instagram'],
        ['url' => $linkedin_url,  'icon' => $linkedin_icon,  'label' => 'LinkedIn'],
        ['url' => $facebook_url,  'icon' => $facebook_icon,  'label' => 'Facebook'],
    ];

    ob_start(); ?>
    <footer class="aiga-custom-footer" aria-label="Social links">
        <ul class="aiga-social-links">
            <?php foreach ($items as $it) :
                if ($it['url'] && is_array($it['icon']) && !empty($it['icon']['ID'])) : ?>
                    <li class="aiga-social-links__item">
                        <a href="<?php echo esc_url($it['url']); ?>" target="_blank" rel="noopener noreferrer">
                            <?php
                            echo wp_get_attachment_image(
                                intval($it['icon']['ID']),
                                'thumbnail',
                                false,
                                [
                                    'loading'  => 'lazy',
                                    'decoding' => 'async',
                                    'alt'      => esc_attr($it['icon']['alt'] ?: $it['label']),
                                ]
                            );
                            ?>
                        </a>
                    </li>
                <?php endif;
            endforeach; ?>
        </ul>
    </footer>
    <?php
    return ob_get_clean();
}
add_shortcode('aiga_footer_socials', 'aiga_footer_socials_shortcode');


/**
 * [about_history]
 */
function about_history_shortcode($atts) {
    $atts = shortcode_atts([
        'heading' => '',
        'content' => '',
        'image'   => '',
    ], $atts, 'about_history');

    $heading = $atts['heading'] ?: (get_field('about_history_heading') ?: 'Showcasing the Heartbeat of Design in the Midwest');
    $content = $atts['content'] ?: (get_field('about_history_content') ?: '');
    $image   = $atts['image']   ?: (get_field('about_history_image') ?: '');

    $img_url = '';
    $img_alt = $heading;

    if (is_array($image)) {
        $img_url = $image['url'] ?? '';
        $img_alt = $image['alt'] ?: $heading;
    } elseif (is_string($image)) {
        $img_url = $image;
    }

    ob_start(); ?>
    <section class="page-section about-history-section" aria-labelledby="about-history-title">
        <div class="about-history">
            <div class="about-column-left">
                <?php if ($heading) : ?>
                    <h3 id="about-history-title"><?php echo esc_html($heading); ?></h3>
                <?php endif; ?>

                <?php if ($content) : ?>
                    <div class="body-1 about-history__content">
                        <?php echo apply_filters('the_content', $content); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="about-image-right">
                <?php if ($img_url) : ?>
                    <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($img_alt); ?>" loading="lazy" decoding="async">
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode('about_history', 'about_history_shortcode');


/**
 * [about_board_card]
 */
function about_board_card_shortcode($atts) {
    $atts = shortcode_atts([
        'heading'   => '',
        'content'   => '',
        'link_text' => '',
        'link_url'  => '',
    ], $atts, 'about_board_card');

    $heading   = $atts['heading']   ?: (get_field('about_board_card_heading')   ?: 'AIGA KC Board is 100% Volunteer-Run');
    $content   = $atts['content']   ?: (get_field('about_board_card_content')   ?: '');
    $link_text = $atts['link_text'] ?: (get_field('about_board_card_link_text') ?: 'About the Board');
    $link_url  = $atts['link_url']  ?: (get_field('about_board_card_link_url')  ?: '#');

    ob_start(); ?>
    <section class="page-section about-board">
        <div class="about-board-card card-bg">
            <?php if ($heading) : ?><h3 class="about-board__title"><?php echo esc_html($heading); ?></h3><?php endif; ?>

            <?php if ($content) : ?>
                <div class="body-1 about-board__content">
                    <?php echo apply_filters('the_content', $content); ?>
                </div>
            <?php endif; ?>

            <?php if ($link_text && $link_url) : ?>
                <a href="<?php echo esc_url($link_url); ?>" class="button primary medium about-board__cta">
                    <?php echo esc_html($link_text); ?>
                </a>
            <?php endif; ?>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode('about_board_card', 'about_board_card_shortcode');


/**
 * [faq_section header="..."]
 */
function faq_section_shortcode($atts) {
    $atts = shortcode_atts(['header' => ''], $atts, 'faq_section');
    $header_text = $atts['header'] ?: (get_field('faq_header_heading') ?: 'Frequently Asked Questions');

    $fallback = [
        1 => ['What is AIGA KC?', 'AIGA KC is the Kansas City chapter of AIGA...', '', ''],
        2 => ['Who can join AIGA KC?', 'Anyone with an interest in design and creativity is welcome!', '', ''],
        3 => ['Do I have to be a designer to join?', 'No! While many of our members are designers, AIGA KC is open to anyone...', '', ''],
        4 => ['What kinds of events does AIGA KC host?', 'We host a variety of events, including design talks, workshops...', '', ''],
        5 => ['How can I get involved?', 'There are many ways to get involved with AIGA KC!...', 'Get Involved Today', '#'],
    ];

    $cards = [];
    for ($i = 1; $i <= 5; $i++) {
        $q  = get_field("faq_question_{$i}")  ?: $fallback[$i][0];
        $a  = get_field("faq_answer_{$i}")    ?: $fallback[$i][1];
        $lt = get_field("faq_link_text_{$i}") ?: $fallback[$i][2];
        $lu = get_field("faq_link_url_{$i}")  ?: $fallback[$i][3];

        if ($q || $a) {
            $cards[] = ['q' => $q, 'a' => $a, 'lt' => $lt, 'lu' => $lu, 'i' => $i];
        }
    }

    ob_start(); ?>
    <section class="page-section faq" aria-labelledby="faq-title">
        <div class="header-wrapper">
            <h3 id="faq-title"><?php echo esc_html($header_text); ?></h3>
            <div class="underline"></div>
        </div>

        <?php foreach ($cards as $c) : ?>
            <div class="faq-card card-bg faq__item faq__item--<?php echo intval($c['i']); ?>">
                <?php if ($c['q']) : ?><h3 class="faq__question"><?php echo esc_html($c['q']); ?></h3><?php endif; ?>
                <?php if ($c['a']) : ?>
                    <div class="body-1 faq__answer"><?php echo apply_filters('the_content', $c['a']); ?></div>
                <?php endif; ?>
                <?php if ($c['lt'] && $c['lu']) : ?>
                    <a class="faq__link" href="<?php echo esc_url($c['lu']); ?>"><?php echo esc_html($c['lt']); ?></a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode('faq_section', 'faq_section_shortcode');


/**
 * [main_feature]
 */
function render_main_feature() {
    $latest = new WP_Query([
        'post_type'      => 'member-feature',
        'posts_per_page' => 1,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_status'    => 'publish',
        'no_found_rows'  => true,
    ]);

    if (!$latest->have_posts()) {
        wp_reset_postdata();
        return '<p>' . esc_html__('No Member Feature found.', 'aiga') . '</p>';
    }

    $latest->the_post();
    $post_id = get_the_ID();

    $main_image     = get_field('main_image',     $post_id);
    $profile_image  = get_field('profile_image',  $post_id);
    $name           = (string) get_field('name',  $post_id);
    $description    = get_field('description',    $post_id);
    $portfolio_link = get_field('portfolio_link', $post_id);

    ob_start(); ?>
    <section class="page-section">
        <div class="main-feature">
            <div class="main-feature__img-wrapper">
                <?php
                if (is_array($main_image) && !empty($main_image['ID'])) {
                    echo wp_get_attachment_image(
                        intval($main_image['ID']),
                        'full',
                        false,
                        [
                            'class'    => 'main-feature__img',
                            'loading'  => 'lazy',
                            'decoding' => 'async',
                            'alt'      => esc_attr($name ?: 'Featured image'),
                        ]
                    );
                }
                ?>
            </div>

            <div class="main-feature__description">
                <div class="feature-description__left">
                    <?php if ($name) : ?><h3 class="main-feature__title"><?php echo esc_html($name); ?></h3><?php endif; ?>
                    <?php if ($description) : ?><div class="body-1 main-feature__text"><?php echo wp_kses_post($description); ?></div><?php endif; ?>
                    <?php if ($portfolio_link) : ?>
                        <a href="<?php echo esc_url($portfolio_link); ?>" target="_blank" rel="noopener" class="primary large button main-feature__cta">
                            <?php esc_html_e('View Portfolio', 'aiga'); ?>
                        </a>
                    <?php endif; ?>
                </div>

                <div class="feature-description__right">
                    <?php
                    if (is_array($profile_image) && !empty($profile_image['ID'])) {
                        echo wp_get_attachment_image(
                            intval($profile_image['ID']),
                            'thumbnail',
                            false,
                            [
                                'class'    => 'feature-profile-img',
                                'loading'  => 'lazy',
                                'decoding' => 'async',
                                'alt'      => esc_attr(($name ? $name . ' profile' : 'Profile')),
                            ]
                        );
                    } ?>
                </div>
            </div>
        </div>
    </section>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('main_feature', 'render_main_feature');


/**
 * [aiga_doodle_description]
 */
function render_aiga_doodle_description($atts) {
    $title           = (string) get_field('aiga_doodle_title');
    $description     = get_field('aiga_doodle_description');
    $figma_url       = get_field('figma_template_url');
    $illustrator_url = get_field('illustrator_template_url');
    $submit_url      = get_field('submit_doodle_url');

    ob_start(); ?>
    <section class="page-section aiga-doodle">
        <div class="main-feature">
            <?php if ($title) : ?><h3 class="aiga-doodle__title"><?php echo esc_html($title); ?></h3><?php endif; ?>
            <?php if ($description) : ?><div class="aiga-doodle__desc"><?php echo wp_kses_post($description); ?></div><?php endif; ?>
            <div class="aiga-doodle__links">
                <?php if ($figma_url) : ?>
                    <a href="<?php echo esc_url($figma_url); ?>" target="_blank" rel="noopener"><?php esc_html_e('Figma Template', 'aiga'); ?></a>
                <?php endif; ?>
                <?php if ($illustrator_url) : ?>
                    <a href="<?php echo esc_url($illustrator_url); ?>" target="_blank" rel="noopener"><?php esc_html_e('Illustrator/Photoshop Template', 'aiga'); ?></a>
                <?php endif; ?>
                <?php if ($submit_url) : ?>
                    <a href="<?php echo esc_url($submit_url); ?>" class="button primary large" target="_blank" rel="noopener">
                        <?php esc_html_e('Submit A Doodle', 'aiga'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode('aiga_doodle_description', 'render_aiga_doodle_description');

/**
 * [gala_carousel id=""]
 * 
 * Responsive image carousel component with up to 6 images and navigation
 * Converted from Figma "gala-carousel" design system component
 */
function gala_carousel_shortcode($atts) {
    $atts = shortcode_atts([
        'id' => '',
        // Allow shortcode overrides but primarily use ACF fields
        'background_color' => '',
    ], $atts, 'gala_carousel');

    $post_id = $atts['id'] ? intval($atts['id']) : get_the_ID();
    
    // Get ACF field values
    $background_color = $atts['background_color'] ?: get_field('gala_carousel_background_color', $post_id) ?: 'blue';
    $title = (string) get_field('gala_carousel_title', $post_id);
    $description = get_field('gala_carousel_description', $post_id);
    $cta_text = (string) get_field('gala_carousel_cta_text', $post_id);
    $cta_url = get_field('gala_carousel_cta_url', $post_id);
    
    // Get up to 6 images from ACF
    $images = [];
    for ($i = 1; $i <= 6; $i++) {
        $image = get_field("gala_carousel_image_{$i}", $post_id);
        if ($image && is_array($image) && !empty($image['ID'])) {
            $images[] = $image;
        }
    }
    
    // Fallback content
    if (!$title) $title = 'Celebrating Our Creative Community';
    if (!$description) $description = 'Our annual gala honors the bold ideas and brilliant work coming out of Kansas City.';
    if (!$cta_text) $cta_text = 'Explore the competition';
    
    // Generate unique ID for this carousel instance
    $carousel_id = 'gala-carousel-' . $post_id . '-' . wp_rand(1000, 9999);
    
    // CSS classes
    $container_classes = ['gala-carousel'];
    $container_classes[] = "gala-carousel--{$background_color}";
    
    ob_start(); ?>
    <section class="<?php echo esc_attr(implode(' ', $container_classes)); ?>" 
             aria-labelledby="gala-carousel-title-<?php echo esc_attr($post_id); ?>"
             data-carousel-id="<?php echo esc_attr($carousel_id); ?>">
        
        <div class="gala-carousel__track-container">
            <div class="gala-carousel__track" id="<?php echo esc_attr($carousel_id); ?>">
                <?php if (!empty($images)) : ?>
                    <?php foreach ($images as $index => $image) : ?>
                        <div class="gala-carousel__slide <?php echo $index === 0 ? 'gala-carousel__slide--active' : ''; ?>" 
                             data-slide-index="<?php echo esc_attr($index); ?>">
                            <div class="gala-carousel__image-wrapper">
                                <?php
                                echo wp_get_attachment_image(
                                    intval($image['ID']),
                                    'large',
                                    false,
                                    [
                                        'class' => 'gala-carousel__image',
                                        'loading' => $index === 0 ? 'eager' : 'lazy',
                                        'decoding' => 'async',
                                        'alt' => esc_attr($image['alt'] ?: $title),
                                    ]
                                );
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <!-- Fallback placeholder when no images -->
                    <?php for ($i = 1; $i <= 3; $i++) : ?>
                        <div class="gala-carousel__slide <?php echo $i === 1 ? 'gala-carousel__slide--active' : ''; ?>" 
                             data-slide-index="<?php echo esc_attr($i - 1); ?>">
                            <div class="gala-carousel__image-wrapper gala-carousel__placeholder">
                                <span class="gala-carousel__placeholder-text">Image <?php echo $i; ?></span>
                            </div>
                        </div>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>
            
            <!-- Navigation Controls -->
            <?php if (count($images) > 1 || empty($images)) : ?>
                <div class="gala-carousel__controls" role="group" aria-label="Carousel navigation">
                    <button class="gala-carousel__nav-button gala-carousel__nav-button--prev" 
                            type="button"
                            aria-label="Previous image"
                            data-carousel-prev="<?php echo esc_attr($carousel_id); ?>">
                        <svg class="gala-carousel__nav-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    
                    <button class="gala-carousel__nav-button gala-carousel__nav-button--next" 
                            type="button"
                            aria-label="Next image"
                            data-carousel-next="<?php echo esc_attr($carousel_id); ?>">
                        <svg class="gala-carousel__nav-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Content -->
        <div class="gala-carousel__content">
            <div class="gala-carousel__text">
                <?php if ($title) : ?>
                    <h3 id="gala-carousel-title-<?php echo esc_attr($post_id); ?>" class="gala-carousel__title">
                        <?php echo esc_html($title); ?>
                    </h3>
                <?php endif; ?>
                
                <?php if ($description) : ?>
                    <div class="gala-carousel__description body-1">
                        <?php echo wp_kses_post($description); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($cta_text && $cta_url) : ?>
                <div class="gala-carousel__cta">
                    <a href="<?php echo esc_url($cta_url); ?>" class="button secondary large gala-carousel__button">
                        <?php echo esc_html($cta_text); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Include JavaScript for responsive carousel functionality -->
    <script>
    (function() {
        const carouselId = '<?php echo esc_js($carousel_id); ?>';
        const carousel = document.getElementById(carouselId);
        const prevButton = document.querySelector('[data-carousel-prev="' + carouselId + '"]');
        const nextButton = document.querySelector('[data-carousel-next="' + carouselId + '"]');
        const slides = carousel.querySelectorAll('.gala-carousel__slide');
        const trackContainer = carousel.parentElement;
        
        if (!carousel || !prevButton || !nextButton || slides.length === 0) return;
        
        let currentSlide = 0;
        const totalSlides = slides.length;
        
        function getVisibleSlides() {
            const width = window.innerWidth;
            if (width <= 768) return 1;      // Mobile: 1 image
            if (width <= 1024) return 2;     // Tablet: 2 images  
            return 3;                        // Desktop: 3 images
        }
        
        function getGapSize() {
            // Responsive gap sizes
            const width = window.innerWidth;
            if (width <= 480) return 16;     // 1rem gap on very small screens
            if (width <= 768) return 24;     // 1.5rem gap on mobile
            return 32;                       // 2rem gap on tablet and desktop
        }
        
        function getAvailableWidth() {
            // Get the actual available width accounting for all padding
            const containerStyle = window.getComputedStyle(trackContainer);
            const containerWidth = trackContainer.offsetWidth;
            const paddingLeft = parseFloat(containerStyle.paddingLeft);
            const paddingRight = parseFloat(containerStyle.paddingRight);
            
            // Return usable width minus any padding
            return containerWidth - paddingLeft - paddingRight;
        }
        
        function calculateSlideWidth() {
            const availableWidth = getAvailableWidth();
            const visibleSlides = getVisibleSlides();
            const gap = getGapSize();
            
            // Calculate width ensuring slides fit perfectly within viewport
            const totalGapWidth = gap * (visibleSlides - 1);
            const slideWidth = Math.floor((availableWidth - totalGapWidth) / visibleSlides);
            
            // Dynamic minimum width based on screen size
            const width = window.innerWidth;
            let minWidth;
            if (width <= 375) minWidth = 280;      // Very small phones
            else if (width <= 480) minWidth = 320; // Small phones  
            else if (width <= 768) minWidth = 350; // Regular mobile
            else minWidth = 200;                   // Tablet and desktop
            
            return Math.max(minWidth, slideWidth);
        }
        
        function updateCarousel() {
            const visibleSlides = getVisibleSlides();
            const slideWidth = calculateSlideWidth();
            const gap = getGapSize();
            
            // Update gap in CSS
            carousel.style.gap = gap + 'px';
            
            // Apply calculated width to all slides
            slides.forEach(slide => {
                slide.style.width = slideWidth + 'px';
                slide.style.flexBasis = slideWidth + 'px';
                slide.style.flexShrink = '0';
                slide.style.flexGrow = '0';
            });
            
            // Mark active slides based on visible count
            slides.forEach((slide, index) => {
                const isActive = index >= currentSlide && index < currentSlide + visibleSlides;
                slide.classList.toggle('gala-carousel__slide--active', isActive);
            });
            
            // Calculate transform offset
            const offset = -currentSlide * (slideWidth + gap);
            carousel.style.transform = `translateX(${offset}px)`;
            
            // Update navigation button states
            const maxSlide = Math.max(0, totalSlides - visibleSlides);
            prevButton.disabled = currentSlide === 0;
            nextButton.disabled = currentSlide >= maxSlide;
            
            // Ensure we don't go past the end
            if (currentSlide > maxSlide) {
                currentSlide = maxSlide;
                carousel.style.transform = `translateX(${-currentSlide * (slideWidth + gap)}px)`;
            }
        }
        
        function nextSlide() {
            const visibleSlides = getVisibleSlides();
            const maxSlide = Math.max(0, totalSlides - visibleSlides);
            
            if (currentSlide < maxSlide) {
                currentSlide++;
            } else {
                currentSlide = 0; // Loop back to start
            }
            updateCarousel();
        }
        
        function prevSlide() {
            const visibleSlides = getVisibleSlides();
            const maxSlide = Math.max(0, totalSlides - visibleSlides);
            
            if (currentSlide > 0) {
                currentSlide--;
            } else {
                currentSlide = maxSlide; // Loop to end
            }
            updateCarousel();
        }
        
        nextButton.addEventListener('click', nextSlide);
        prevButton.addEventListener('click', prevSlide);
        
        // Update on window resize with debouncing
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                // Recalculate but maintain current position if possible
                const visibleSlides = getVisibleSlides();
                const maxSlide = Math.max(0, totalSlides - visibleSlides);
                if (currentSlide > maxSlide) {
                    currentSlide = maxSlide;
                }
                updateCarousel();
            }, 150);
        });
        
        // Keyboard navigation
        carousel.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                prevSlide();
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                nextSlide();
            }
        });
        
        // Initialize after a brief delay to ensure CSS is applied
        setTimeout(() => {
            updateCarousel();
        }, 50);
        
        // Re-update after images load to ensure proper sizing
        const images = carousel.querySelectorAll('img');
        let imagesLoaded = 0;
        const totalImages = images.length;
        
        if (totalImages > 0) {
            images.forEach(img => {
                if (img.complete) {
                    imagesLoaded++;
                } else {
                    img.addEventListener('load', () => {
                        imagesLoaded++;
                        if (imagesLoaded === totalImages) {
                            updateCarousel();
                        }
                    });
                    
                    // Fallback for images that fail to load
                    img.addEventListener('error', () => {
                        imagesLoaded++;
                        if (imagesLoaded === totalImages) {
                            updateCarousel();
                        }
                    });
                }
            });
            
            if (imagesLoaded === totalImages) {
                updateCarousel();
            }
        }
        
    })();
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('gala_carousel', 'gala_carousel_shortcode');


/**
 * Register ACF Field Group for Gala Carousel
 */
function register_gala_carousel_acf_fields() {
    if( function_exists('acf_add_local_field_group') ) {
        
        // Content fields
        $fields = [
            [
                'key' => 'field_gala_carousel_title',
                'label' => 'Carousel Title',
                'name' => 'gala_carousel_title',
                'type' => 'text',
                'instructions' => 'Enter the main heading for the carousel',
                'placeholder' => 'Celebrating Our Creative Community',
            ],
            [
                'key' => 'field_gala_carousel_description',
                'label' => 'Description',
                'name' => 'gala_carousel_description',
                'type' => 'textarea',
                'instructions' => 'Brief description text below the title',
                'rows' => 3,
            ],
            [
                'key' => 'field_gala_carousel_background_color',
                'label' => 'Background Color',
                'name' => 'gala_carousel_background_color',
                'type' => 'select',
                'instructions' => 'Choose the background color for the carousel',
                'choices' => [
                    'blue' => 'Blue',
                    'magenta' => 'Magenta',
                    'amber' => 'Amber',
                    'default' => 'Default (Transparent)',
                ],
                'default_value' => 'blue',
                'ui' => 1,
            ],
            [
                'key' => 'field_gala_carousel_cta_text',
                'label' => 'Button Text',
                'name' => 'gala_carousel_cta_text',
                'type' => 'text',
                'instructions' => 'Text for the call-to-action button',
                'placeholder' => 'Explore the competition',
            ],
            [
                'key' => 'field_gala_carousel_cta_url',
                'label' => 'Button URL',
                'name' => 'gala_carousel_cta_url',
                'type' => 'url',
                'instructions' => 'Where should the button link to?',
            ],
        ];
        
        // Add 6 image fields
        for ($i = 1; $i <= 6; $i++) {
            $fields[] = [
                'key' => "field_gala_carousel_image_{$i}",
                'label' => "Image {$i}",
                'name' => "gala_carousel_image_{$i}",
                'type' => 'image',
                'instructions' => $i === 1 ? 'Upload images for the carousel. You can add up to 6 images.' : '',
                'preview_size' => 'medium',
                'library' => 'all',
            ];
        }
        
        acf_add_local_field_group([
            'key' => 'group_gala_carousel',
            'title' => 'Gala Carousel',
            'fields' => $fields,
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'page',
                    ],
                ],
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'post',
                    ],
                ],
            ],
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
        ]);
    }
}
add_action('acf/init', 'register_gala_carousel_acf_fields');


/**
 * INFORMATION SECTION (formerly "Flexible Page Section")
 * Shortcode: [information_section id="" text_side="" color="" show_title="" show_text="" show_image=""]
 *
 * text_side: "left" | "right"      (default: ACF or "left")
 * color:     "default" | "magenta" | "blue"   (default: ACF or "default")
 * show_*:    "true" | "false"      (default: ACF, true-ish)
 */
function information_section_shortcode( $atts ) {
    $atts = shortcode_atts([
        'id'         => '',
        'text_side'  => '',
        'color'      => '',
        'show_title' => '',
        'show_text'  => '',
        'show_image' => '',
    ], $atts, 'information_section');

    $post_id     = $atts['id'] ? intval($atts['id']) : get_the_ID();

    // Resolve ACF + shortcode overrides
    $text_side   = $atts['text_side'] ?: ( get_field('section_text_side', $post_id) ?: 'left' );
    $color       = $atts['color']     ?: ( get_field('section_color', $post_id)     ?: 'default' );

    $show_title  = $atts['show_title'] !== ''
        ? ($atts['show_title'] === 'true')
        : ( get_field('section_show_title', $post_id) !== false );

    $show_text   = $atts['show_text'] !== ''
        ? ($atts['show_text'] === 'true')
        : ( get_field('section_show_text', $post_id) !== false );

    $show_image  = $atts['show_image'] !== ''
        ? ($atts['show_image'] === 'true')
        : ( get_field('section_show_image', $post_id) !== false );

    // Content
    $title        = (string) get_field('section_title', $post_id);
    $text_content = get_field('section_text', $post_id);
    $image        = get_field('section_image', $post_id);

    if (!$title)        { $title = 'Title'; }
    if (!$text_content) { $text_content = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.'; }

    // Robust image renderer: supports ACF array, attachment ID, or URL
    $render_img = function($img, $size, $class, $fallback_alt) {
        if (is_array($img)) {
            if (!empty($img['ID'])) {
                return wp_get_attachment_image(
                    intval($img['ID']),
                    $size,
                    false,
                    ['class'=>$class,'loading'=>'lazy','decoding'=>'async','alt'=>esc_attr($img['alt'] ?? $fallback_alt)]
                );
            }
            if (!empty($img['url'])) {
                $alt = !empty($img['alt']) ? $img['alt'] : $fallback_alt;
                return sprintf(
                    '<img src="%s" alt="%s" class="%s" loading="lazy" decoding="async" />',
                    esc_url($img['url']), esc_attr($alt), esc_attr($class)
                );
            }
        }
        if (is_numeric($img)) {
            return wp_get_attachment_image(
                intval($img), $size, false,
                ['class'=>$class,'loading'=>'lazy','decoding'=>'async','alt'=>esc_attr($fallback_alt)]
            );
        }
        if (is_string($img) && $img !== '') {
            return sprintf(
                '<img src="%s" alt="%s" class="%s" loading="lazy" decoding="async" />',
                esc_url($img), esc_attr($fallback_alt), esc_attr($class)
            );
        }
        return '';
    };

    // BEM classes
    $container_classes = [
        'information-section',
        'information-section--' . sanitize_html_class($text_side),
        'information-section--' . sanitize_html_class($color),
    ];
    $text_classes  = ['information-section__content'];
    $image_classes = ['information-section__image-container'];

    $title_id = 'information-section-title-' . esc_attr($post_id);

    ob_start(); ?>
    <section class="<?php echo esc_attr(implode(' ', $container_classes)); ?>"
             aria-labelledby="<?php echo $title_id; ?>">

        <?php if ($text_side === 'left') : ?>
            <?php if ($show_title || $show_text) : ?>
                <div class="<?php echo esc_attr(implode(' ', $text_classes)); ?>">
                    <?php if ($show_title && $title) : ?>
                        <h3 id="<?php echo $title_id; ?>" class="information-section__title">
                            <?php echo esc_html($title); ?>
                        </h3>
                    <?php endif; ?>

                    <?php if ($show_text && $text_content) : ?>
                        <div class="information-section__text body-1">
                            <?php echo wp_kses_post($text_content); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($show_image) : ?>
                <div class="<?php echo esc_attr(implode(' ', $image_classes)); ?>">
                    <div class="information-section__image-wrapper">
                        <?php echo $render_img($image, 'large', 'information-section__image', $title); ?>
                    </div>
                </div>
            <?php endif; ?>

        <?php else : ?>
            <?php if ($show_image) : ?>
                <div class="<?php echo esc_attr(implode(' ', $image_classes)); ?>">
                    <div class="information-section__image-wrapper">
                        <?php echo $render_img($image, 'large', 'information-section__image', $title); ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($show_title || $show_text) : ?>
                <div class="<?php echo esc_attr(implode(' ', $text_classes)); ?>">
                    <?php if ($show_title && $title) : ?>
                        <h3 id="<?php echo $title_id; ?>" class="information-section__title">
                            <?php echo esc_html($title); ?>
                        </h3>
                    <?php endif; ?>

                    <?php if ($show_text && $text_content) : ?>
                        <div class="information-section__text body-1">
                            <?php echo wp_kses_post($text_content); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode('information_section', 'information_section_shortcode');

/**
 * Back-compat: alias old shortcodes to the new component
 * (You can remove these once all content is migrated.)
 */
function flexible_page_section_shortcode_alias($atts){ return information_section_shortcode($atts); }
add_shortcode('flexible_page_section', 'flexible_page_section_shortcode_alias');

function content_image_left_shortcode($atts){ $atts['text_side'] = 'left';  return information_section_shortcode($atts); }
add_shortcode('content_image_left', 'content_image_left_shortcode');

function image_content_right_shortcode($atts){ $atts['text_side'] = 'right'; return information_section_shortcode($atts); }
add_shortcode('image_content_right', 'image_content_right_shortcode');

function section_magenta_shortcode($atts){ $atts['color'] = 'magenta'; return information_section_shortcode($atts); }
add_shortcode('section_magenta', 'section_magenta_shortcode');

function section_blue_shortcode($atts){ $atts['color'] = 'blue'; return information_section_shortcode($atts); }
add_shortcode('section_blue', 'section_blue_shortcode');

/**
 * ACF: keep the same field keys so data doesn’t duplicate;
 * just rename the group title shown in WP admin for clarity.
 */
function register_information_section_acf_fields() {
    if ( function_exists('acf_add_local_field_group') ) {
        acf_add_local_field_group([
            'key' => 'group_flexible_page_section', // keep same key for continuity
            'title' => 'Information Section',       // renamed label in the UI
            'fields' => [
                ['key'=>'field_section_title','label'=>'Section Title','name'=>'section_title','type'=>'text','placeholder'=>'Section Title'],
                ['key'=>'field_section_text','label'=>'Section Text','name'=>'section_text','type'=>'wysiwyg','toolbar'=>'basic','media_upload'=>0],
                ['key'=>'field_section_image','label'=>'Section Image','name'=>'section_image','type'=>'image','preview_size'=>'medium','library'=>'all'],
                [
                    'key'=>'field_section_text_side','label'=>'Text Position','name'=>'section_text_side','type'=>'select',
                    'choices'=>['left'=>'Text Left, Image Right','right'=>'Image Left, Text Right'],'default_value'=>'left','ui'=>1
                ],
                [
                    'key'=>'field_section_color','label'=>'Background Color','name'=>'section_color','type'=>'select',
                    'choices'=>['default'=>'Default (Transparent)','magenta'=>'Magenta','blue'=>'Blue'],
                    'default_value'=>'default','ui'=>1
                ],
                ['key'=>'field_section_show_title','label'=>'Show Title','name'=>'section_show_title','type'=>'true_false','default_value'=>1,'ui'=>1],
                ['key'=>'field_section_show_text','label'=>'Show Text','name'=>'section_show_text','type'=>'true_false','default_value'=>1,'ui'=>1],
                ['key'=>'field_section_show_image','label'=>'Show Image','name'=>'section_show_image','type'=>'true_false','default_value'=>1,'ui'=>1],
            ],
            'location' => [
                [ [ 'param'=>'post_type','operator'=>'==','value'=>'page' ] ],
                [ [ 'param'=>'post_type','operator'=>'==','value'=>'post' ] ],
            ],
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
        ]);
    }
}




/** Allow shortcodes in widgets/content */
add_filter('widget_text', 'do_shortcode');
add_filter('the_content', 'do_shortcode');
