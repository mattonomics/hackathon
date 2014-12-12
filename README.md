# 10up Hackathon


## Create a custom post type called “Presidential Candidate”.

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
			'rewrite'            => array( 'slug' => 'candidates' ),
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
1. If you take a look at `http://local.wordpress-trunk.dev/wp-admin/edit.php?post_type=politico_candidate`, you will see our candidates table looks a bit empty. Let's add state and party to this table to help out our editors.
	* First we need to register our custom columns. Add this code to the bottom of the `candidate.php` file:
	```php
	function filter_columns( $columns ) {
		$columns['politico_state']  = esc_html__( 'Home State', 'politico' );
		$columns['politico_party']  = esc_html__( 'Party', 'politico' );
	
		unset( $columns['date'] );
		$columns['date']  = esc_html__( 'Date' );
	
		return $columns;
	}
	add_filter( 'manage_politico_candidate_posts_columns', 'politico\candidate\filter_columns' );
	```
	* Now we need to output our information for each of our custom columns:
	```php
	function output_columns( $column_name, $post_id ) {
		if ( 'politico_state' === $column_name ) {
			$state = get_post_meta( $post_id, 'politico_candidate_state', true );
			if ( ! empty( $state ) ) {
				echo esc_html( $state );
			} else {
				esc_html_e( 'None', 'politico' );
			}
		} elseif ( 'politico_party' === $column_name ) {
			$party = get_post_meta( $post_id, 'politico_candidate_party', true );
			if ( ! empty( $party ) ) {
				echo ucwords( esc_html( $party ) );
			} else {
				esc_html_e( 'None', 'politico' );
			}
		}
	}
	add_action( 'manage_politico_candidate_posts_custom_column', 'politico\candidate\output_columns', 10, 2 );
	```
	* Check out `http://local.wordpress-trunk.dev/wp-admin/edit.php?post_type=politico_candidate`. You should now see the custom columns you just created.

1. Now that we have create our meta box, handled saving, and added some extra table columns. Let's turn our attention to the front of our website. First, make sure pretty permalinks are enabled (see Settings > Permalinks). Let's create a candidate called `Hillary Clinton`. Once we've published our new candidate, a URL will show up like so: `http://local.wordpress-trunk.dev/candidates/hillary-clinton/`. Notice how `candidates` is the slug we chose earlier when registering the post type.

	If we view that URL, we see the title and content but not the state and political party. Remember, this view is inheriting the `single.php` template from the parent theme that knows nothing about the new fields we've created. There are a few directions we can go from here. We could add a hook/filter to the parent theme that allows us to conditionally output code given a specific post type; we can override the view completely; there are probably other creative solutions as well. For the sake of simplicity, let's override the template completely.

	We could create a file `single.php` in our child theme. This would override the single view for ALL post types. This is overkill since we only care about the `policical_candidate` post type. Instead, let's create a file `VAGRANT-LOCAL/wordpress-trunk/wp-content/themes/politico/single-political_candidate.php` which will override the single view only for our specific post type. The WordPress codex has a [nice article](http://codex.wordpress.org/Template_Hierarchy) on the template hierarchy.

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

That's it! We can see a river of candidates if we view `http://local.wordpress-trunk.dev/candidates`. This is automatically generated by WordPress and our parent template. We could modify this the same way we did with `single.php` but with `archive.php` instead.

## Creating custom widgetized areas for specific categories

Remember, our new microsite has a blog. The blog will contain posts associated with specific candidate(s). Posts will be categorized. One of these categories is `Green Energy`. We would like to create a custom category archive template and add specific widgets to this archive.

1. First, create a post called "Hillary Talks Green Energy". Add a category called "Green Energy".
1.  We need to register a sidebar or widgetized areas. Paste this code into `VAGRANT-LOCAL/wordpress-trunk/wp-content/themes/politico/functions.php`:

	```php
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
	```

1.  Now we need to output our widgetized area. There are multiple ways we can modify the archive view using hooks/filters or overriding/creating specific templates. We will create a new template in the child theme. Create the file `VAGRANT-LOCAL/wordpress-trunk/wp-content/themes/politico/category-green-energy.php` and paste the following code:

	```php
	<?php get_header(); ?>
	
	<section id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
	
			<?php if ( have_posts() ) : ?>
	
				<header class="page-header">
					<?php
					the_archive_title( '<h1 class="page-title">', '</h1>' );
					the_archive_description( '<div class="taxonomy-description">', '</div>' );
					?>
				</header><!-- .page-header -->
	
				<?php
				// Start the Loop.
				while ( have_posts() ) : the_post();
					get_template_part( 'content', get_post_format() );
				endwhile;
	
				// Previous/next page navigation.
				the_pagination( array(
					'prev_text'          => __( 'Previous page', 'twentyfifteen' ),
					'next_text'          => __( 'Next page', 'twentyfifteen' ),
					'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'twentyfifteen' ) . ' </span>',
				) );
	
			// If no content, include the "No posts found" template.
			else :
				get_template_part( 'content', 'none' );
	
			endif;
			?>
	
			<section class="content-sidebar">
				<?php dynamic_sidebar( 'green-energy' ); ?>
			</section>
	
		</main><!-- .site-main -->
	</section><!-- .content-area -->
	
	<?php get_footer(); ?>
	```

	This template will show on the following URL: `http://local.wordpress-trunk.dev/category/green-energy`. It will not affect any other taxonomy archive.
	
1. We need to add some styling to our widgetized area. In `VAGRANT-LOCAL/wordpress-trunk/wp-content/themes/politico/style.css` paste the following code:
	```css
	.content-sidebar {
		background-color: #fff;
		margin: 30px 8.3333%;
		box-shadow: 0 0 1px rgba(0, 0, 0, 0.15);
		padding: 2.5% 10%;
	}
	
	.content-sidebar .widget {
		margin: 0 0 20px 0;
		padding: 0;
	}
	
	.content-sidebar .widget:last-child {
		margin-bottom: 0;
	}
	```
1. Finally let's create some widgets. On `http://local.wordpress-trunk.dev/wp-admin/widgets.php` I added two text widgets to the `Green Energy` sidebar for testing purposes. We can add other types of widgets as well as create our own. However, in order for them to look good, a little extra styling work is needed.


##Candidates Widget
WordPress has a very simple API for creating widgets, a mechanism by which users are able to add content or features to sidebars like the one previously created.

Let's first examine the basic widget outline, then work on filling in each method.

```php
<?php
namespace politico\widget;

class Candidate extends \WP_Widget {
	// constructor method
	public function __construct() {}

	// admin side, user facing form
	public function form( $instance ){}

	// runs on admin form save
	public function update( $new_instance, $old_instance ){}

	// front end output
	public function widget( $args, $instance ){}

}
```

The code above illustrates the four basic methods required when creating a widget:

 1. The class constructor that will be used to pass data to the parent class.
 2. The form method that is used to display the admin side, user facing widget form. This is where users will adjust the widget options to change the front end output of the widget.
 3. The update method that runs when a user saves the admin form.
 4. The widget method that will be used for front end output.

Now, let's fill in each method and discuss what's happening with each step.

###Constructor
```php
<?php
namespace politico\widget;

class Candidate extends \WP_Widget {
	// constructor method
	public function __construct() {
		parent::__construct(
			'candidate_widget', // Base ID
			__( 'List Candidates', 'politico' ), // Name
			array( 'description' => __( 'List candidates by Party or state', 'politico' ), ) // Args
		);
	}

	// admin side, user facing form
	public function form( $instance ){}

	// runs on admin form save
	public function update( $new_instance, $old_instance ){}

	// front end output
	public function widget( $args, $instance ){}

}
```
In the class constructor, we pass data to the parent constructor that sets up the widget and creates the basic widget implementation on the WordPress admin side.

The first parameter is a unique id that identifies this widget and will be used throughout the widget output, particularly when creating HTML `name` and `id` attributes. The second and third parameters dictate the widget title and description that will be displayed to the user on the admin side only.

###Widget Form
```php
<?php
namespace politico\widget;

class Candidate extends \WP_Widget {
	// constructor method
	public function __construct() {
		parent::__construct(
			'candidate_widget', // Base ID
			__( 'List Candidates', 'politico' ), // Name
			array( 'description' => __( 'List candidates by Party or state', 'politico' ), ) // Args
		);
	}

	// admin side, user facing form
	public function form( $instance ){
		// outputs the options form on admin
		$args = wp_parse_args( $instance, array(
			'num_candidates'  => 5,
		    'party' => '',
		    'state' => '',
		    'title' => __( 'Political Candidates', 'politico' )
		) );
		?>
		<p>
			<label><?php _e( 'Title:', 'politico' ); ?></label>
			<br>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $args['title'] ); ?>" class="widefat">
		</p>
		<p>
			<label><?php _e( 'Number of Candidates to show:', 'politico' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'num_candidates' ); ?>" type="text" name="<?php echo $this->get_field_name( 'num_candidates' ); ?>" size="2" value="<?php echo esc_attr( $args['num_candidates'] ); ?>">
		</p>
		<p>
			<label><?php _e( 'Candidate Party:', 'politico' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'party' ); ?>" id="<?php echo $this->get_field_id( 'party' ); ?>">
				<option value="0"><?php _e( 'Any Party', 'politico' ); ?></option>
				<option <?php selected( $args['party'], 'democrat' ); ?> value="democrat"><?php esc_html_e( 'Democrat', 'politico' ); ?></option>
				<option <?php selected( $args['party'], 'republican' ); ?> value="republican"><?php esc_html_e( 'Republican', 'politico' ); ?></option>
				<option <?php selected( $args['party'], 'independent' ); ?>  value="independent"><?php esc_html_e( 'Independent', 'politico' ); ?></option>
			</select>
		</p>
		<p>
			<label><?php _e( 'Candidate State:', 'politico' ); ?></label>
			<br>
			<select name="<?php echo $this->get_field_name( 'state' ) ?>" id="<?php echo $this->get_field_id( 'state' ); ?>">
				<option value="0"><?php _e( 'All States', 'politico' ); ?></option>
			<?php
				foreach ( \politico\get_states() as $state_short => $state_long ) {
					echo '<option ' . selected( $state_short, $args['state'], false ) . ' value="' . esc_attr( $state_short ) . '">' . esc_attr( $state_long ) . "</option>\n";
				}
			?>
			</select>
		</p>
		<?php
	}

	// runs on admin form save
	public function update( $new_instance, $old_instance ){}

	// front end output
	public function widget( $args, $instance ){}

}
```

In the form method, we add in the code to display the user facing admin form. In this example, we're creating:

 - Widget title
 - The number of candidates to display
 - The candidate party to filter by
 - The candidate state to filter by

Note the usage of `$this->get_field_id( 'party' )` and `$this->get_field_name( 'party' )`. It is critical that you use these methods on inputs that are going to be saved by the widget. Failure to do so will result in the widget not saving.

###Saving the widget
Now that we have the admin side form in place, let's sanitize and save the data.

```php
<?php
namespace politico\widget;

class Candidate extends \WP_Widget {
	// constructor method
	public function __construct() {
		parent::__construct(
			'candidate_widget', // Base ID
			__( 'List Candidates', 'politico' ), // Name
			array( 'description' => __( 'List candidates by Party or state', 'politico' ), ) // Args
		);
	}

	// admin side, user facing form
	public function form( $instance ){
		// outputs the options form on admin
		$args = wp_parse_args( $instance, array(
			'num_candidates'  => 5,
		    'party' => '',
		    'state' => '',
		    'title' => __( 'Political Candidates', 'politico' )
		) );
		?>
		<p>
			<label><?php _e( 'Title:', 'politico' ); ?></label>
			<br>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $args['title'] ); ?>" class="widefat">
		</p>
		<p>
			<label><?php _e( 'Number of Candidates to show:', 'politico' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'num_candidates' ); ?>" type="text" name="<?php echo $this->get_field_name( 'num_candidates' ); ?>" size="2" value="<?php echo esc_attr( $args['num_candidates'] ); ?>">
		</p>
		<p>
			<label><?php _e( 'Candidate Party:', 'politico' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'party' ); ?>" id="<?php echo $this->get_field_id( 'party' ); ?>">
				<option value="0"><?php _e( 'Any Party', 'politico' ); ?></option>
				<option <?php selected( $args['party'], 'democrat' ); ?> value="democrat"><?php esc_html_e( 'Democrat', 'politico' ); ?></option>
				<option <?php selected( $args['party'], 'republican' ); ?> value="republican"><?php esc_html_e( 'Republican', 'politico' ); ?></option>
				<option <?php selected( $args['party'], 'independent' ); ?>  value="independent"><?php esc_html_e( 'Independent', 'politico' ); ?></option>
			</select>
		</p>
		<p>
			<label><?php _e( 'Candidate State:', 'politico' ); ?></label>
			<br>
			<select name="<?php echo $this->get_field_name( 'state' ) ?>" id="<?php echo $this->get_field_id( 'state' ); ?>">
				<option value="0"><?php _e( 'All States', 'politico' ); ?></option>
			<?php
				foreach ( \politico\get_states() as $state_short => $state_long ) {
					echo '<option ' . selected( $state_short, $args['state'], false ) . ' value="' . esc_attr( $state_short ) . '">' . esc_attr( $state_long ) . "</option>\n";
				}
			?>
			</select>
		</p>
		<?php
	}

	// runs on admin form save
	public function update( $new_instance, $old_instance ){
		// processes widget options to be saved
		$instance                   = array();
		$instance['num_candidates'] = absint( $new_instance['num_candidates'] );
		$instance['party']          = esc_attr( $new_instance['party'] );
		$instance['state']          = esc_attr( $new_instance['state'] );
		$instance['title']          = esc_html( $new_instance['title'] );
		return $instance;
	}

	// front end output
	public function widget( $args, $instance ){}

}
```

Note that the `$new_instance` variable is filled with the data the user just submitted and the `$old_instance` variable is filled with previously saved data.

Also note how the keys of `$new_instance` should line up perfectly with what was passed to `$this->get_field_name()`.

Finally, be sure to properly sanitize the data, preferably using the internal WordPress functions to do so.

###Front end output
Now that we have the form and we're saving the data, let's take that data and use it to create a query on the front end.

**Note:** We'll be using a post meta query which isn't necessarily the optimal way to query data from the WordPress database. However, for the scope of this project this is a great example of the power of the APIs within WordPress.

```php
<?php
namespace politico\widget;

class Candidate extends \WP_Widget {
	// constructor method
	public function __construct() {
		parent::__construct(
			'candidate_widget', // Base ID
			__( 'List Candidates', 'politico' ), // Name
			array( 'description' => __( 'List candidates by Party or state', 'politico' ), ) // Args
		);
	}

	// admin side, user facing form
	public function form( $instance ){
		// outputs the options form on admin
		$args = wp_parse_args( $instance, array(
			'num_candidates'  => 5,
		    'party' => '',
		    'state' => '',
		    'title' => __( 'Political Candidates', 'politico' )
		) );
		?>
		<p>
			<label><?php _e( 'Title:', 'politico' ); ?></label>
			<br>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $args['title'] ); ?>" class="widefat">
		</p>
		<p>
			<label><?php _e( 'Number of Candidates to show:', 'politico' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'num_candidates' ); ?>" type="text" name="<?php echo $this->get_field_name( 'num_candidates' ); ?>" size="2" value="<?php echo esc_attr( $args['num_candidates'] ); ?>">
		</p>
		<p>
			<label><?php _e( 'Candidate Party:', 'politico' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'party' ); ?>" id="<?php echo $this->get_field_id( 'party' ); ?>">
				<option value="0"><?php _e( 'Any Party', 'politico' ); ?></option>
				<option <?php selected( $args['party'], 'democrat' ); ?> value="democrat"><?php esc_html_e( 'Democrat', 'politico' ); ?></option>
				<option <?php selected( $args['party'], 'republican' ); ?> value="republican"><?php esc_html_e( 'Republican', 'politico' ); ?></option>
				<option <?php selected( $args['party'], 'independent' ); ?>  value="independent"><?php esc_html_e( 'Independent', 'politico' ); ?></option>
			</select>
		</p>
		<p>
			<label><?php _e( 'Candidate State:', 'politico' ); ?></label>
			<br>
			<select name="<?php echo $this->get_field_name( 'state' ) ?>" id="<?php echo $this->get_field_id( 'state' ); ?>">
				<option value="0"><?php _e( 'All States', 'politico' ); ?></option>
			<?php
				foreach ( \politico\get_states() as $state_short => $state_long ) {
					echo '<option ' . selected( $state_short, $args['state'], false ) . ' value="' . esc_attr( $state_short ) . '">' . esc_attr( $state_long ) . "</option>\n";
				}
			?>
			</select>
		</p>
		<?php
	}

	// runs on admin form save
	public function update( $new_instance, $old_instance ){
		// processes widget options to be saved
		$instance                   = array();
		$instance['num_candidates'] = absint( $new_instance['num_candidates'] );
		$instance['party']          = esc_attr( $new_instance['party'] );
		$instance['state']          = esc_attr( $new_instance['state'] );
		$instance['title']          = esc_html( $new_instance['title'] );
		return $instance;
	}

	// front end output
	public function widget( $args, $instance ){
		// outputs the content of the widget
		$query_args = array(
			'post_type'  => 'politico_candidate',
			'num_posts'  => absint( $instance['num_candidates'] )
		);
		if ( !empty( $instance['state'] ) ) {
			$query_args['meta_query'][] = array(
				'key'     => 'politico_candidate_state',
			    'value'   => esc_attr( $instance['state'] )
			);
		}
		if ( !empty( $instance['party'] ) ) {
			$query_args['meta_query'][] = array(
				'key'    => 'politico_candidate_party',
			    'value'  => esc_attr( $instance['party'] )
			);
		}
		$query = new \WP_Query( $query_args );
		if ( $query->have_posts() ) {
			echo $args['before_widget'];
			if ( ! empty( $instance['title'] ) ) {
				echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
			}
			echo "<ul>";
			while ( $query->have_posts() ) {
				$query->the_post();
				echo '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a>';
			}
			echo "</ul>";
			echo $args['after_widget'];
			wp_reset_query();
		}
	}

}
```

The `widget` method receives two parameters:

 - `$args` The arguments set up by the sidebar that will determine what HTML goes before and after the widget as well as the HTML that goes before and after the title.
 - `$instance` The user defined data for this instance of the widget. In this case, `num_candidates`, `party`, `state`, and `title`.

Building a query within WordPress is extremely easy. In this example we start with the base arguments of `post_type` and `posts_per_page`.
```php
$query_args = array(
			'post_type'  => 'politico_candidate',
			'num_posts'  => absint( $instance['num_candidates'] )
);
```
We then check to see if anything was saved for either of the filters with:

```php
if ( !empty( $instance['state'] ) ) {
	$query_args['meta_query'][] = array(
		'key'     => 'politico_candidate_state',
		'value'   => esc_attr( $instance['state'] )
	);
}
```

Finally, we execute the generated query with:
```php
$query = new \WP_Query( $query_args );
```

Note that we must use a backslash for class names that are in the global namespace. In PHP, class names **always** resolve to the current namespace whereas functions will fallback to the global definition.

###Register the widget
Now that we have built the widget, we need to register it with WordPress so that it shows up in the widget interface. To do so, simply drop the following into the `functions.php` file:

```php
/**
 * Widget
 */
function register_widget() {
	\register_widget( 'politico\widget\Candidate' );
}
add_action( 'widgets_init', 'politico\register_widget' );
```

Note that we use a backslash with our call to `\register_widget()` so that we're sure to reference the function that exists in the global namespace and not the current namespace since we just created a function with the same name.