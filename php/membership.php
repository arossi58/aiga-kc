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
