<?php

namespace politico\candidate;

/**
 * Register our custom post type for candidate
 */
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

/**
 * Setup our CPT meta boxes
 */
function add_meta_boxes() {
	add_meta_box( 'politico_candidate_information', esc_html__( 'Candidate Information', 'politico' ), 'politico\candidate\output_candidate_information', 'politico_candidate', 'normal', 'core' );
}
add_action( 'add_meta_boxes', 'politico\candidate\add_meta_boxes' );

/**
 * Output our candidate information meta box
 *
 * @param object $post
 */
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
			<option value="democrat"><?php esc_html_e( 'Democrat', 'politico' ); ?></option>
			<option <?php selected( $party, 'republican' ); ?> value="republican"><?php esc_html_e( 'Republican', 'politico' ); ?></option>
			<option <?php selected( $party, 'independent' ); ?>  value="independent"><?php esc_html_e( 'Independent', 'politico' ); ?></option>
		</select>
	</p>

	<p>
		<label for="politico_candidate_state">
			<?php esc_html_e( 'Home State:', 'politico' ); ?>
		</label><br>
		<select name="politico_candidate_state" class="postform">
			<option value="0">--------</option>
			<?php
			foreach ( \politico\get_states() as $state_short => $state_long ) {
				echo '<option ' . selected( $state_long, $state, false ) . ' value="' . esc_attr( $state_long ) . '">' . esc_attr( $state_long ) . "</option>\n";
			}
			?>
		</select>
	</p>

	<?php
}

/**
 * Save meta information for candidate CPT
 *
 * @param int $post_id
 */
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

/**
 * Add new columns to candidate table
 *
 * @param array $columns
 * @return array
 */
function filter_columns( $columns ) {
	$columns['politico_state']  = esc_html__( 'Home State', 'politico' );
	$columns['politico_party']  = esc_html__( 'Party', 'politico' );

	unset( $columns['date'] );
	$columns['date']  = esc_html__( 'Date' );

	return $columns;
}
add_filter( 'manage_politico_candidate_posts_columns', 'politico\candidate\filter_columns' );

/**
 * Output candidate columns
 *
 * @param string $column_name
 * @param id $post_id
 */
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