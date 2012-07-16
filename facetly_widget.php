<?php
	class Facetly_Widget extends WP_Widget {
		public function __construct() {
			parent::__construct(
		 		'facetly_widget', 
				'facetly_widget', 
				array( 'description' => __( 'A Facetly Facets Widget', 'text_domain' ), )
			);
		}

		public function widget( $args, $instance ) {
			extract( $args );
			$title = apply_filters( 'widget_title', $instance['title'] );

			echo $before_widget;
			if ( ! empty( $title ) )
				$search = facetly_search();
				echo $search->facets;
				
			echo $after_widget;
		}

		public function update( $new_instance, $old_instance ) {
			$instance = array();
			$instance['title'] = strip_tags( $new_instance['title'] );

			return $instance;
		}

		public function form( $instance ) {
			if ( isset( $instance[ 'title' ] ) ) {
				$title = $instance[ 'title' ];
			}
			else {
				$title = __( 'Facetly', 'text_domain' );
			}
			?>
			<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</p>
			<?php 
		}

	}
	add_action( 'widgets_init', create_function( '', 'register_widget( "facetly_widget" );' ) );
?>