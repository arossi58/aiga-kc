<?php
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
