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

/**
 * Require widget code
 */
require( __DIR__ . '/includes/class-politico-widget.php' );

/**
 * Widget
 */
function register_widget() {
	\register_widget( 'politico\widget\Candidate' );
}
add_action( 'widgets_init', 'politico\register_widget' );

/**
 * Return an array of states
 */
function get_states() {
	return array(
		"AL" => "Alabama",
		"AK" => "Alaska",
		"AZ" => "Arizona",
		"AR" => "Arkansas",
		"CA" => "California",
		"CO" => "Colorado",
		"CT" => "Connecticut",
		"DE" => "Delaware",
		"DC" => "District Of Columbia",
		"FL" => "Florida",
		"GA" => "Georgia",
		"HI" => "Hawaii",
		"ID" => "Idaho",
		"IL" => "Illinois",
		"IN" => "Indiana",
		"IA" => "Iowa",
		"KS" => "Kansas",
		"KY" => "Kentucky",
		"LA" => "Louisiana",
		"ME" => "Maine",
		"MD" => "Maryland",
		"MA" => "Massachusetts",
		"MI" => "Michigan",
		"MN" => "Minnesota",
		"MS" => "Mississippi",
		"MO" => "Missouri",
		"MT" => "Montana",
		"NE" => "Nebraska",
		"NV" => "Nevada",
		"NH" => "New Hampshire",
		"NJ" => "New Jersey",
		"NM" => "New Mexico",
		"NY" => "New York",
		"NC" => "North Carolina",
		"ND" => "North Dakota",
		"OH" => "Ohio",
		"OK" => "Oklahoma",
		"OR" => "Oregon",
		"PA" => "Pennsylvania",
		"PR" => "Puerto Rico",
		"RI" => "Rhode Island",
		"SC" => "South Carolina",
		"SD" => "South Dakota",
		"TN" => "Tennessee",
		"TX" => "Texas",
		"UT" => "Utah",
		"VT" => "Vermont",
		"VI" => "Virgin Islands",
		"VA" => "Virginia",
		"WA" => "Washington",
		"WV" => "West Virginia",
		"WI" => "Wisconsin",
		"WY" => "Wyoming"
	);
}