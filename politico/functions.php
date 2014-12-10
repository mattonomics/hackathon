<?php

namespace politico;

/**
 * Enqueue parent style sheet
 */
function enqueue_parent_theme_style() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'politico\enqueue_parent_theme_style' );

/**
 * Require candidate CPT code
 */
require( __DIR__ . '/includes/candidate.php' );

/**
 * Sidebars
 */
function register_sidebars() {
	register_sidebar( array(
		'name' => esc_html__( 'Green Energy Widget Area', 'politico' ),
		'id' => 'green-energy',
		'description' => esc_html__( 'Widgets here will show up on green energy archive page.', 'politico' ),
		'before_title' => '<h2>',
		'after_title' => '</h2>',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
	) );
}
add_action( 'widgets_init', 'politico\register_sidebars' );