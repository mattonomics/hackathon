<?php

namespace politico;

function enqueue_parent_theme_style() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'politico\enqueue_parent_theme_style' );

require( __DIR__ . '/includes/candidate.php' );