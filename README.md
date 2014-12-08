# 10up Hackathon


## Create a custom post type for people called “Person”.

A [custom post type](http://codex.wordpress.org/Post_Types) is one of the most useful data storage mechanisms in WordPress. In this exercise we will create a new content type or custom post type called `Person`.

We can use this post type to store information about influential people from biography to grouping of related blog posts.

We will build a child theme off of `TwentyFifteen`. [Child themes](http://codex.wordpress.org/Child_Themes) let us selectively overwrite content from the parent. We could easily create a new theme or a plugin, but this would leave us with no front-end styling.

1. First make sure `TwentyFifteen` is installed. You can download it [here](https://drive.google.com/file/d/0B_ch3gGXDEHhX1U2MzcxaElMRlk/view?usp=sharing).

	It should live in your themes folder like so:
	`VAGRANT-LOCAL/wordpress-trunk/wp-content/themes/twentyfifteen`

1. Let’s create a new child theme to extend “TwentyFifteen” and scaffold out some files.

	* Create a new folder for the theme:
		* `VAGRANT-LOCAL/wordpress-trunk/wp-content/themes/politico`
	* Create a file style.css:
		* `VAGRANT-LOCAL/wordpress-trunk/wp-content/themes/politico/style.css`
	* Create a file functions.php:
		* `VAGRANT-LOCAL/wordpress-trunk/wp-content/themes/politico/functions.php`
	* Within style.css insert the following code:

	```/*
	 Theme Name: Politico
	 Description: A proof of concept theme for Politico
	 Author: Your Name Here!
	 Template: twentyfifteen
	*/```

	WordPress parses style.css and pulls out that information to use for internal mechanisms as well as within the admin UI. “Template” tells WordPress this is a child theme of “twentyfifteen”

	Within functions.php add the following code:
	
	```php
	<?php
	
	namespace politico;
	
	function enqueue_parent_theme_style() {
		wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
	}
	add_action( 'wp_enqueue_scripts', 'politico\enqueue_parent_theme_style' );
	```
	
	The first thing you might notice is the namespace. We use namespaces so our function names don't collide with other plugins and themes.
	
	This code includes the parent themes style.css. Since we created a style.css file in the child, the parent style.css will not be included. `add_action` is a WordPress function that let’s us execute code at a defined point. In this case we are executing code on `wp_enqueue_scripts` which is where WP sets up scripts and styles. More on hooks and filters [here](http://codex.wordpress.org/Plugin_API).

3. Now it's time to register our custom post type.
	* Add this code within `functions.php`:
	```php
	<?php
	require( __DIR__ . '/includes/person.php' );
	```
		We will include our code in another file for organizational purposes. Create the directory `includes` and the file:
		`VAGRANT-LOCAL/wordpress-trunk/wp-content/themes/politico/includes/person.php`
	* Within `person.php` add the following code:

	```php
	<?php
	
	namespace politico\person;
	
	function setup_cpt() {
		$labels = array(
			'name'               => esc_html_x( 'People', 'post type general name', 'politico' ),
			'singular_name'      => esc_html_x( 'Person', 'post type singular name', 'politico' ),
			'menu_name'          => esc_html_x( 'People', 'admin menu', 'politico' ),
			'name_admin_bar'     => esc_html_x( 'Person', 'add new on admin bar', 'politico' ),
			'add_new'            => esc_html_x( 'Add New', 'person', 'politico' ),
			'add_new_item'       => esc_html__( 'Add New Person', 'politico' ),
			'new_item'           => esc_html__( 'New Person', 'politico' ),
			'edit_item'          => esc_html__( 'Edit Person', 'politico' ),
			'view_item'          => esc_html__( 'View Person', 'politico' ),
			'all_items'          => esc_html__( 'All People', 'politico' ),
			'search_items'       => esc_html__( 'Search People', 'politico' ),
			'not_found'          => esc_html__( 'No people found.', 'politico' ),
			'not_found_in_trash' => esc_html__( 'No people found in Trash.', 'politico' )
		);
	
		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'person' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
		);
	
		register_post_type( 'politico_person', $args );
	}
	add_action( 'init', 'politico\person\setup_cpt' );
	```







