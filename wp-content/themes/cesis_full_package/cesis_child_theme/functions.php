<?php

function theme_enqueue_styles() {

    global $tt_data;

    wp_enqueue_style( 'cesis-style', get_template_directory_uri() . '/style.css' );
	  wp_enqueue_style( 'cesis-child-style', get_stylesheet_directory_uri() . '/style.css');
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );

// if you want to add some custom function

?>
