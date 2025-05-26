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
