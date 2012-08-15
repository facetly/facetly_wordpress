<?php
	function facetly_admin(){
		if( !empty($_POST['facetly_settings_hidden']) && $_POST['facetly_settings_hidden'] == 'Y' ) {  
			$consumer_key = $_POST['facetly_key'];  
			$consumer_secret = $_POST['facetly_secret'];  
			$server = $_POST['facetly_server']; 
			$limit = $_POST['facetly_limit'];
			$add_variable = $_POST['facetly_add_variable']; 

		    try {
				$facetly = facetly_api_init();
			    $facetly->setServer($server);
			    $facetly->setConsumer($consumer_key, $consumer_secret);
			    $fields = $facetly->fieldSelect();
		    } catch (Exception $e) {
		    	$error = $e->getMessage();
		    }

		    if (empty($fields)) {
		    	echo '<div class="error"><p><strong>Can not connect to server, please check your consumer API configuration or contact our support if problem persist.</strong></p></div>';
		    } else {
				$settings = array(
					'key' => $consumer_key,
					'secret' => $consumer_secret,
					'server' => $server,
					'limit' => $limit,
					'add_variable' => $add_variable,
				);

				update_option('facetly_settings', $settings);  
				?>  
				<div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>  
				<?php  
		    }
		} else {  
			$common = get_option('facetly_settings');
			$consumer_key = trim($common['key']);
			$consumer_secret = trim($common['secret']);
			$server = $common['server'];
			$limit = $common['limit'];
			$add_variable = $common['add_variable'];
		}

		if( !empty($_POST['facetly_copy_hidden']) && $_POST['facetly_copy_hidden'] == 'Y' ) {
			if ( is_writable(TEMPLATEPATH) ) {
				if( file_exists(TEMPLATEPATH."/searchform.php") ) {
					$zipfilename = "searchform.php";
					$zipsource = TEMPLATEPATH. "/searchform.php";
					$zipdest = TEMPLATEPATH. "/";
					$backup = zipfile($zipfilename, $zipsource, $zipdest);
					unlink(TEMPLATEPATH. "/searchform.php");
				} else {
					$backup = true;
				}

				$unzipsource = WP_PLUGIN_DIR. "/facetly-woocommerce/facetly-search-template.zip";
				$unzipdest = TEMPLATEPATH. "/";  //folder directory must be ended with "/", example: c:/xampp/htdocs/wordpress/
				$unzip1 = unzipfile($unzipsource, $unzipdest);
		
				if ( $backup && $unzip1 ) {
					$facetly_page = get_page_by_path('finds');;
					$facetly_page_id = $facetly_page->ID;
					update_post_meta($facetly_page_id, "_wp_page_template", "facetly-search-template.php");
					echo "<h4>" . __( 'Files Copy Success' ) . "</h4>";
				} else {
					echo "<h4>" . __( 'Files Copy Not Success' ) . "</h4>";
				}
			} else {
				echo "<h4>" . __( 'Theme Folder is Not Writable' ) . "</h4>";
			}
		}
	?> 

	<div class="wrap">  
		<?php    echo "<h2>" . __( 'Facetly Configuration' ) . "</h2>"; ?>  
		<?php echo "<h4>" . __( 'Facetly API Configuration' ) . "</h4>"; ?>  
		<form name="facetly_settings" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
			<input type="hidden" name="facetly_settings_hidden" value="Y">  
			<table>
				<tr>
					<td><?php _e("Consumer Key"); ?></td>
					<td><?php _e(":");?></td>
					<td><input type="text" name="facetly_key" value="<?php echo $consumer_key; ?>" size="50"><?php _e(" ex: qhduafdh" ); ?></td>
				</tr>
				<tr>
					<td><?php _e("Consumer Secret"); ?></td>
					<td><?php _e(":");?></td>
					<td><input type="text" name="facetly_secret" value="<?php echo $consumer_secret; ?>" size="50"><?php _e(" ex: q5yvmddqntukobeoszi6zuqmwvy9wwsv" ); ?></td>
				</tr>
				<tr>
					<td><?php _e("Server Name" ); ?></td>
					<td><?php _e(":");?></td>
					<td><input type="text" name="facetly_server" value="<?php echo $server; ?>" size="50"><?php _e(" ex: http://us1.beta.facetly.com/1" ); ?></td>
				</tr>
				<tr>
					<td><?php _e("Search Limit Setting" ); ?></td>
					<td><?php _e(":");?></td>
					<td><input type="text" name="facetly_limit" value="<?php echo $limit; ?>" size="50"><?php _e(" ex: 5" ); ?></td>
				</tr>

				<tr>
					<td><?php _e("Additional Variable" ); ?></td>
					<td><?php _e(":");?></td>
					<td><input type="text" name="facetly_add_variable" value="<?php echo $add_variable; ?>" size="50"><?php _e(" ex: _op[category]=or" ); ?></td>
				</tr>
				<tr>
					<td>
						<p class="submit">  
							<input type="submit" name="Submit" value="<?php _e('Submit Data' ) ?>" />  
						</p>  
					</td>
				</tr>
			</table>
		</form>  

		<?php
			if ( file_exists(TEMPLATEPATH."/facetly-search-template.php") && file_exists(TEMPLATEPATH."/searchform.php") ) {
				echo "<div class='custom_notice'><p><strong>File Already Exist</strong></p></div>";
			}
		?>
		<form name="facetly_copy" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
			<input type="hidden" name="facetly_copy_hidden" value="Y">  
			<table>
				<tr>
					<td><?php echo "<h4>" . __( 'Copy facetly_search_template.php and searchform.php to your current active theme' ) . "</h4>"; ?>  </td>
				</tr>
				<tr>
					<td>
						<p class="submit">  
							<input type="submit" name="Submit" value="<?php _e('Copy File' ) ?>" />  
						</p>  
					</td>
				</tr>
			</table>
		</form>  
	</div> 
<?php
	}
