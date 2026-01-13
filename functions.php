<?php

//prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

//theme setup file
require_once get_template_directory() . '/inc/theme-setup.php';

//hooks file
require_once get_template_directory() . '/inc/woo-hooks.php';