<?php
	class Facetly_Widget extends WP_Widget {
		function Facetly_Widget() {
			$widget_ops = array( 'classname' => 'facetly_widget', 'description' => 'Facetly Facets Widget' ); 
			$control_ops = array( 'id_base' => 'Facetly_Widget' );
			$this->WP_Widget( 'Facetly_Widget', 'Facetly Facets', $widget_ops, $control_ops ); 
		}

		public function widget( $args, $instance ) {
			extract( $args );
			$title = "Facetly Search";
			$title2 = apply_filters( 'widget_title', $title );

			$search = facetly_search();
			echo $search->facets;
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
				$title = __( 'Facetly Search' );
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