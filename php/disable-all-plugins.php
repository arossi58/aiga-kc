<?php
/*
Plugin Name: Temporary Plugin Disabler
Description: Disables all plugins and self-deletes
*/

add_action( 'init', function() {
    if ( is_admin() && current_user_can( 'activate_plugins' ) ) {
        deactivate_plugins( get_option( 'active_plugins' ) );

  
        // Self-delete this plugin
        $plugin_file = __FILE__;
        unlink( $plugin_file );
    }
});
