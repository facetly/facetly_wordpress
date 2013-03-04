<?php
    class FacetlyWidget extends WP_Widget {
        function FacetlyWidget() {
            $widget_ops  = array(
                'classname' => 'FacetlyWidget',
                'description' => 'Facetly Facets Widget'
            );

            $this->WP_Widget( 'FacetlyWidget', 'Facetly Facets Widget', $widget_ops); 
        }

        public function widget($args, $instance) {
            extract($args);

            $title = apply_filters('widget_title', $instance['title'] );
            $name = $instance['name'];
            $show_info = isset( $instance['show_info'] ) ? $instance['show_info'] : false;

            $searchform = do_shortcode("[facetly_searchform]");
            echo $searchform;
            if (!empty($title))
            echo $before_title . $title . $after_title;

            $search = do_shortcode("[facetly_search output=facets]");
            echo $search;

        }

        public function update( $new_instance, $old_instance ) {
            $instance = array();
            $instance['title'] = strip_tags( $new_instance['title'] );

            return $instance;
        }

        public function form( $instance ) {
            if ( isset( $instance[ 'title' ] ) ) {
                $title = $instance[ 'title' ];
            } else {
                $title = __( 'Facetly Search' );
            }
            if (!empty($_GET['query'])) {
                $query = stripslashes($_GET['query']);
                $query = htmlentities($query);
            } else {
                $query = '';
            }

            ?>
            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
                <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
            </p>
            <?php 
        }

    }
    add_action('widgets_init', create_function('', 'return register_widget("FacetlyWidget");'));  