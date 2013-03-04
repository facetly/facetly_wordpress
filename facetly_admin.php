<?php
	function facetly_admin() {
		if( !empty($_POST['facetly_settings_hidden']) && $_POST['facetly_settings_hidden'] == 'Y' ) {  
			$consumer_key = trim($_POST['facetly_key']);  
			$consumer_secret = trim($_POST['facetly_secret']);  
			$server = trim($_POST['facetly_server']); 
			$limit = trim($_POST['facetly_limit']);
			$add_variable = trim($_POST['facetly_add_variable']); 

			if (!is_numeric($limit)) {
				echo '<div class="error"><p><strong>Search limit setting must be numeric.</strong></p></div>';
			} else {
			    try {
					$facetly = facetly_api_init();
				    $facetly->setServer($server);
				    $facetly->setConsumer($consumer_key, $consumer_secret);
				    $fields = $facetly->fieldSelect();
				    
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
			    } catch (Exception $e) {
			    	$error = $e->getMessage();
			    }
			}
		} else {  
			$common = get_option('facetly_settings');
			if (!empty($common)) {
				$consumer_key = trim($common['key']);
				$consumer_secret = trim($common['secret']);
				$server = trim($common['server']);
				$limit = trim($common['limit']);
				$add_variable = trim($common['add_variable']);
			} else {
				$consumer_key = "";
				$consumer_secret = "";
				$server = "";
				$limit = "";
				$add_variable = "";
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
		</div> 
	<?php
	}
