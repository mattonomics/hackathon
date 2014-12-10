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
