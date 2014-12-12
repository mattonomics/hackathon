<?php

namespace politico\widget;

class Candidate extends \WP_Widget {
	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		parent::__construct(
			'candidate_widget', // Base ID
			__( 'List Candidates', 'politico' ), // Name
			array( 'description' => __( 'List candidates by Party or state', 'politico' ), ) // Args
		);
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
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

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
		$instance                   = array();
		$instance['num_candidates'] = absint( $new_instance['num_candidates'] );
		$instance['party']          = esc_attr( $new_instance['party'] );
		$instance['state']          = esc_attr( $new_instance['state'] );
		$instance['title']          = esc_html( $new_instance['title'] );
		return $instance;
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		// outputs the content of the widget
		$query_args = array(
			'post_type'       => 'politico_candidate',
			'posts_per_page'  => absint( $instance['num_candidates'] )
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