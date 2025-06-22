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

add_filter('widget_text', 'do_shortcode');
add_filter('the_content', 'do_shortcode');