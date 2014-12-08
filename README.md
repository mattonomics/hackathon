# 10up Hackathon


## Create a custom post for people called “Person”.

A [custom post type](http://codex.wordpress.org/Post_Types) is one of the most useful data storage mechanisms in WordPress. In this exercise we will create a new content type or custom post type called “Person”.

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

add_action( 'wp_enqueue_scripts', 'politico\enqueue_parent_theme_style' );
function enqueue_parent_theme_style() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}
```

The first thing you might notice is the namespace. We use namespaces so our functions to collide with other plugins and themes.

This code includes the parent themes style.css. Since we created a style.css file in the child, the parent style.css will not be included. `add_action` is a WordPress function that let’s us execute code at a defined point. In this case we are executing code on `wp_enqueue_scripts` which is where WP sets up scripts and styles. More on hooks and filters [here](http://codex.wordpress.org/Plugin_API).


