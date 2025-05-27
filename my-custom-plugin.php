<?php
/*
Plugin Name: AIGA KC Custom Plugin
Description:  All of our site-specific PHP utilities for AIGA KC.
Version:     1.0.1
Author:      Andrew Rossi


// then pull in all your php/*.php modules:
foreach ( glob( __DIR__ . '/php/*.php' ) as $file ) {
  require_once $file;
}

function aiga_kc_enqueue_styles() {
    $css_dir = plugin_dir_path( __FILE__ ) . 'css/';
    $css_url = plugin_dir_url( __FILE__ ) . 'css/';

    foreach ( glob( $css_dir . '*.css' ) as $file_path ) {
        $filename = basename( $file_path );                                // e.g. "layout.css"
        $handle   = 'aiga-kc-' . sanitize_title( $filename, '-' );         // e.g. "aiga-kc-layout-css"
        $version  = filemtime( $file_path );                              // cache-busting

        wp_enqueue_style(
            $handle,
            $css_url . $filename,
            [],           // dependencies: add other handles here if needed
            $version
        );
    }
}
add_action( 'wp_enqueue_scripts', 'aiga_kc_enqueue_styles' );

*/

