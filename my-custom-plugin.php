<?php
/*
Plugin Name: My Custom Plugin
Description: Site-specific PHP utilities.
Version:     1.0.0
Author:      Your Name
*/
// Autoload everything in /includes
foreach ( glob( __DIR__ . '/includes/*.php' ) as $file ) {
  require_once $file;
}
