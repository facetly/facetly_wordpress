<?php
	global $percentage;

	function display_error($error_message = "") {
	  return  new WP_Error('product_insert', $error_message);
	}

	function facetly_reindex(){
		$myErrors = new WP_Error();
		$facetly = facetly_api_init();

		global $wpdb;
		global $total_all;

	    $limit = 2;
		
	    if ( $_GET['reindex'] == "y" ) {
			$query_product = "select pt.ID, pt.post_title from ". $wpdb->prefix. "posts As pt  ";
			$query_product .= "LEFT JOIN ". $wpdb->prefix. "postmeta As mt ON pt.ID=mt.post_id  ";
			$query_product .= "WHERE pt.post_type = 'wpsc-product' ";
			$query_product .= "AND pt.post_status = 'publish' ";
			$query_product .= "GROUP BY mt.post_id ";
			$query_product .= "ORDER BY pt.post_title ";
			if( empty($total_all) ) {
				$query = $wpdb->query($query_product);
				$total_all = $wpdb->num_rows;
			}
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
					try {
						facetly_insert_product($post_id);
					} catch (Exception $e) {
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
			
			echo '<meta http-equiv="refresh" content="2;admin.php?page=facetly-settings-reindex'. $header. '"/>';
			echo '<meta http-equiv="cache-control" content="NO-CACHE"/>';
			echo "<h2>" . __( 'Reindexing All WP e-Commerce Data' ) . "</h2>";
			echo "<h4>" . __( 'Please Wait Until Finish' ) . "</h4>";
			echo '
				<div id="progress-outer">
				    <div id="progress-inner"></div>
				</div>​';
		}
		if ( !isset($_GET['reindex']) || $_GET['reindex'] == "n" ) {
			if ( $_GET['reindex'] == "n" && empty($percentage) ) {
				$error_message = get_option('facetly_error');
				$error = display_error($error_message);
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
			?>

			<div class="wrap">
				<?php    echo "<h2>" . __( 'Facetly Settings' ) . "</h2>"; ?>  
				<?php echo "<h4>" . __( 'Facetly Reindex' ) . "</h4>"; 

				?>  
				<form name="facetly_settings" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['PHP_SELF']). "?". $url_query; ?>">  	
					<input type="hidden" name="facetly_settings_hidden" value="Y">  
					<p class="submit">  
						<input type="submit" name="Submit" value="<?php _e('Start Reindex' ) ?>" />  
					</p>  
				</form>  
			</div> 
<?php
		} 
	}

	function custom_progress_bar($percentage) {
		if (strstr($_SERVER['REQUEST_URI'], 'page=facetly-settings-reindex') && strstr($_SERVER['REQUEST_URI'], 'reindex=y')) {	
			global $wpdb;
			global $total_all;
			
			$limit = 2;
			$counter = $_GET['counter'];
			$start = $counter*$limit;
			
			if ($counter == 0 ) {
				$start = 0;
			}

			$query_product = "select pt.ID, pt.post_title from ". $wpdb->prefix. "posts As pt  ";
			$query_product .= "LEFT JOIN ". $wpdb->prefix. "postmeta As mt ON pt.ID=mt.post_id  ";
			$query_product .= "WHERE pt.post_type = 'wpsc-product' ";
			$query_product .= "AND pt.post_status = 'publish' ";
			$query_product .= "GROUP BY mt.post_id ";
			$query_product .= "ORDER BY pt.post_title ";
			$query = $wpdb->query($query_product);
			$total_all = $wpdb->num_rows;
			$percentage = $start/$total_all*100;

			if (empty($percentage)) {
				$percentage = 0;
			}
			if ($percentage >= 100) {
				$percentage = 100;
			}
			// this is where we'll style our box  
			
			echo '<style type="text/css">  
				#progress-outer {
					background: #333;
					-webkit-border-radius: 13px;
					margin-left: auto;
					margin-right: auto;
					height: 30px;
					width: 1100px;
					padding: 3px;
				}

				#progress-inner {
					background: green;
					width: '. $percentage. '%;
					height: 100%;
					-webkit-border-radius: 9px;
				} 
				</style>';
		}
	}
	add_action('admin_head', 'custom_progress_bar');
