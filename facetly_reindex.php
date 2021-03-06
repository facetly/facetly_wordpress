<?php
	global $percentage;

	function facetly_reindex_display_error($error_message = "") {
		return new WP_Error('product_insert', $error_message);
	}

	function facetly_reindex(){
		$facetly = facetly_api_init();

		global $wpdb;
		global $total_all;

	    $limit = 25;
		if ( !empty($_GET['reindex']) && $_GET['reindex'] == "y" ) {
			$facetly_fields = get_option('facetly_fields');
			$node_types = $facetly_fields['node_type'];
			$node_type = array();
			foreach ($node_types as $key => $value) {
				$node_type[] = "pt.post_type = ". "'". $value. "'";
			}
			$post_type = implode(' OR ', $node_type);

			$query_product = " select pt.ID, pt.post_title from ". $wpdb->prefix. "posts As pt  ";
			$query_product .= " LEFT JOIN ". $wpdb->prefix. "postmeta As mt ON pt.ID=mt.post_id WHERE ";
			$query_product .= "(". $post_type.")";
			$query_product .= " AND pt.post_status = 'publish' ";
			$query_product .= " GROUP BY mt.post_id ";
			$query_product .= " ORDER BY pt.ID ";
			$query = $wpdb->query($query_product);
			$total_all = $wpdb->num_rows;
			
			$counter = $_GET['counter'];
			$next = $counter+1;
			
			$start = $counter*$limit;
			if ($counter == 0 ) {
				$start = 0;
				$facetly->productTruncate();
			}
			$percentage = $start/$total_all*100;
			if ($percentage < 100) {
				$query_product .= "LIMIT ". $start. ",". $limit;
				
				$query = $wpdb->query($query_product);
				$total = $wpdb->num_rows;
				
				$myrows = $wpdb->get_results($query_product);
				$reindex = "y";
				
				foreach( $myrows as $row ) {
					$post_id = $row->ID;
					$post = get_post($post_id);
					try {
						facetly_insert_product($post);
					} 
					catch (Exception $e) {
						$reindex = "n";
						$header = '&reindex='.$reindex;
						
						$error_message = $e->getMessage();
						update_option('facetly_error', $error_message);
					}
				}
				$header = '&reindex='.$reindex.'&counter='.$next;
			} else {
				$reindex = "n";
				$header = '&reindex='.$reindex;
				$percentage = 100;
				$completed = true;
			}
			$error_message = get_option('facetly_error');
			if (!empty($error_message)) {
				$reindex = "n";
				$header = '&reindex='.$reindex;
				$percentage = 100;
				$completed = true;
			}
			
			echo '<meta http-equiv="refresh" content="2;admin.php?page=facetly-configuration-reindex'. $header. '"/>';
			echo '<meta http-equiv="cache-control" content="NO-CACHE"/>';
			echo "<h2>" . __( 'Reindexing All WP e-Commerce Data' ) . "</h2>";
			echo "<h4>" . __( 'Please Wait Until Finish' ) . "</h4>";
			echo do_shortcode("[facetly_progress percentage=". $percentage. "]");
		}
		if ( !isset($_GET['reindex']) || $_GET['reindex'] == "n" ) {
			if ( !empty($_GET['reindex']) && $_GET['reindex'] == "n" && empty($percentage) ) {
				$error_message = get_option('facetly_error');
				$error = facetly_reindex_display_error($error_message);
				delete_option('facetly_error');
				if (!empty($error_message)) {
					if ( is_wp_error($error) ) echo '<div class="error"><p><strong>'. $error->get_error_message(). '</strong></p></div>';
				} else {
					echo "<div class='custom_notice'><p><strong>" . __( 'Reindex Completed' ) . "</strong></p></div>";
				}
			}
			$get = $_GET;
			$get['counter'] = 0;
			$get['reindex'] = 'y';
			
			$url_query = http_build_query($get,'','&');
			try {
				$fields = $facetly->fieldSelect();	
				if (!empty($fields)) {
				?>
					<div class="wrap">
						<?php echo "<h2>" . __( 'Facetly Configuration' ) . "</h2>"; ?>  
						<?php echo "<h4>" . __( 'Facetly Reindex' ) . "</h4>"; ?>  
						<form name="facetly_settings" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['PHP_SELF']). "?". $url_query; ?>">  	
							<p class="submit">  
								<input type="submit" name="Submit" value="<?php _e('Start Reindex' ) ?>" />  
							</p>  
						</form>  
					</div> 
			<?php
				}
			} catch (Exception $e) {
				echo '<div class="error"><p><strong>'. $e->getMessage(). '</strong></p></div>';
				if (empty($fields)) {
					echo '<div class="error"><p><strong>Can not connect to server, please check your consumer API configuration or contact our support if problem persist.</strong></p></div>';
				}
			}
		} 	
	}
