# 10up Hackathon


## Create a custom post type for people called “Presidential Candidate”.

Pretend we are creating a microsite for Presidential Candidates for the 2016 election. It's primary purpose will be to show off candidates. However, it will also have a blog. We want it to look similar to the Politico main site but have some stylish differences to make it feel special. Therefore the microsite will inherit templates from the main Politico theme but will add functionality and templates for candidates.

A [custom post type](http://codex.wordpress.org/Post_Types) is one of the most useful data storage mechanisms in WordPress. In this exercise we will create a new content type or custom post type called `Presidential Candidate`.

We can use this post type to store information about candidates from biography to grouping of related blog posts.

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

	```php
	/*
	Theme Name: Politico
	Description: A proof of concept theme for Politico
	Author: Your Name Here!
	Template: twentyfifteen
	*/
	```

	WordPress parses style.css and pulls out that information to use for internal mechanisms as well as within the admin UI. `Template` tells WordPress this is a child theme of `twentyfifteen`

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
	require( __DIR__ . '/includes/candidate.php' );
	```
		We will include our code in another file for organizational purposes. Create the directory `includes` and the file:
		`VAGRANT-LOCAL/wordpress-trunk/wp-content/themes/politico/includes/person.php`
	* Within `person.php` add the following code:

	```php
	<?php
	
	namespace politico\candidate;
	
	function setup_cpt() {
		$labels = array(
			'name'               => esc_html_x( 'Candidates', 'post type general name', 'politico' ),
			'singular_name'      => esc_html_x( 'Candidate', 'post type singular name', 'politico' ),
			'menu_name'          => esc_html_x( 'Candidates', 'admin menu', 'politico' ),
			'name_admin_bar'     => esc_html_x( 'Candidate', 'add new on admin bar', 'politico' ),
			'add_new'            => esc_html_x( 'Add New', 'candidate', 'politico' ),
			'add_new_item'       => esc_html__( 'Add New Candidate', 'politico' ),
			'new_item'           => esc_html__( 'New Candidate', 'politico' ),
			'edit_item'          => esc_html__( 'Edit Candidate', 'politico' ),
			'view_item'          => esc_html__( 'View Candidate', 'politico' ),
			'all_items'          => esc_html__( 'All Candidates', 'politico' ),
			'search_items'       => esc_html__( 'Search Candidates', 'politico' ),
			'not_found'          => esc_html__( 'No candidates found.', 'politico' ),
			'not_found_in_trash' => esc_html__( 'No candidates found in Trash.', 'politico' )
		);
	
		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'candidate' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
		);
	
		register_post_type( 'politico_candidate', $args );
	}
	add_action( 'init', 'politico\candidate\setup_cpt' );
	```
1. Our new custom post type supports title and content among other things. We can store `name` in title and biography in `content`. However, we will need to store more information such as political party and home state. Let's create a metabox with some custom fields.
	* First, we register the metabox. This code should go in `candidate.php` somewhere below our namespace declaration:
	```php
	<?php
	function add_meta_boxes() {
		add_meta_box( 'politico_candidate_information', esc_html__( 'Candidate Information', 'politico' ), 'politico\candidate\output_candidate_information', 'politico_candidate', 'normal', 'core' );
	}
	add_action( 'add_meta_boxes', 'politico\candidate\add_meta_boxes' );
	
	```
	* Now let's output the meta box with it's fields. See how our `add_meta_box` call references `output_candidate_information`? This is a reference to a callback function that will output our meta box's HTML.. Here is the code that should be placed below our namespace definition:

		```php
		<?php
		function output_candidate_information( $post ) {
			wp_nonce_field( 'politico_candidate_information_action', 'politico_candidate_information' );
		
			$party = get_post_meta( $post->ID, 'politico_candidate_party', true );
			$state = get_post_meta( $post->ID, 'politico_candidate_state', true );
			?>
		
			<p>
				<label for="politico_candidate_party">
					<?php esc_html_e( 'Political Party:', 'politico' ); ?>
				</label><br>
				<select name="politico_candidate_party" id="politico_candidate_party">
					<option name="democrat"><?php esc_html_e( 'Democrat', 'politico' ); ?></option>
					<option <?php selected( $party, 'republican' ); ?> name="republican"><?php esc_html_e( 'Republican', 'politico' ); ?></option>
					<option <?php selected( $party, 'independent' ); ?>  name="independent"><?php esc_html_e( 'Independent', 'politico' ); ?></option>
				</select>
			</p>
		
			<p>
				<label for="politico_candidate_state">
					<?php esc_html_e( 'Home State:', 'politico' ); ?>
				</label><br>
				<input name="politico_candidate_state" id="politico_candidate_state" type="text" value="<?php if ( ! empty( $state ) ) echo esc_attr( $state ); ?>">
			</p>
		
			<?php
		}
		```
	* Now that we've outputted our form, we need to handle form saving. WordPress allows us to hook onto the `save_post` action. Here is the code:

		```php
		<?php
		function save_post( $post_id ) {
			if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) || 'revision' == get_post_type( $post_id ) ) {
				return;
			}
		
			if ( ! empty( $_POST['politico_candidate_information'] ) && wp_verify_nonce( $_POST['politico_candidate_information'], 'politico_candidate_information_action' ) ) {
		
				if ( ! empty( $_POST['politico_candidate_party'] ) ) {
					update_post_meta( $post_id, 'politico_candidate_party', sanitize_text_field( $_POST['politico_candidate_party'] ) );
				} else {
					delete_post_meta( $post_id, 'politico_candidate_party' );
				}
		
				if ( ! empty( $_POST['politico_candidate_state'] ) ) {
					update_post_meta( $post_id, 'politico_candidate_state', sanitize_text_field( $_POST['politico_candidate_state'] ) );
				} else {
					delete_post_meta( $post_id, 'politico_candidate_state' );
				}
			}
		}
		add_action( 'save_post', 'politico\candidate\save_post' );
		```
1. Now that we have create our meta box and handled saving. Let's turn our attention to the front of our website. First, make sure pretty permalinks are enabled (see Settings > Permalinks). Let's create a candidate called `Hilary Clinton`. Once we've published our new candidate, a URL will show up like so: `http://local.wordpress-trunk.dev/candidate/hilary-clinton/`. Notice how `candidate` is the slug we chose earlier when registering the post type.

	If we view that URL, we see the title and content but not the state and political party. Remember, this view is inheriting the `single.php` template from the parent theme that knows nothing about the new fields we've created. There are a few directions we can go from here. We could add a hook/filter to the parent theme that allows us to conditionally output code given a specific post type; we can override the view completely; there are probably other creative solutions as well. For the sake of simplicity, let's override the template completely.

	We could create a file `single.php` in our child theme. This would override the single view for ALL post types. This is overkill since we only care about the `policical_candidate` post type. Instead, let's create a file `VAGRANT-LOCAL/wordpress-trunk/wp-content/themes/politico/single-political_candidate.php` which will override the single view only for our specific post type.

	Here is our code for `single-political-candidate.php`:
	```php
	<?php get_header(); ?>
	
	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
	
			<?php
			// Start the loop.
			while ( have_posts() ) : the_post(); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	            	<?php twentyfifteen_post_thumbnail(); ?>
	
	            	<header class="entry-header">
	            		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
	            	</header><!-- .entry-header -->
	
	            	<div class="entry-content">
	            		<?php the_content(); ?>
	            	</div><!-- .entry-content -->
	
	            	<footer class="entry-footer">
	            	    <p>
	            	        <strong><?php esc_html_e( 'Political Party:', 'politico' ); ?></strong>
	            	        <?php echo esc_html( get_post_meta( get_the_ID(), 'politico_candidate_party', true ) ); ?>
	            	    </p>
	            	    <p>
	                        <strong><?php esc_html_e( 'Home State:', 'politico' ); ?></strong>
	                        <?php echo esc_html( get_post_meta( get_the_ID(), 'politico_candidate_state', true ) ); ?>
	                    </p>
	            	</footer><!-- .entry-footer -->
	            </article><!-- #post-## -->
	
				<?php
	
				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;
				// End the loop.
	
			endwhile;
			?>
	
		</main><!-- .site-main -->
	</div><!-- .content-area -->
	
	<?php get_footer(); ?>
	```

II. Let's create a custom template for a new category

II. Create new sidebar for category.

III. Create a widget for category



