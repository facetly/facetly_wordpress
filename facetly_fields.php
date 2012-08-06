<?php
	function facetly_fields(){
		global $wpdb;
		$facetly = facetly_api_init();
		$fields = $facetly->fieldSelect();
		if (empty($fields)) {
		   	echo '<div class="error"><p><strong>Can not connect to server, please check your consumer API configuration or contact our support if problem persist.</strong></p></div>';
		} else {
			$default_field = array("-- Not Selected --");

			$default_fields = array();
			$default_fields[] = "guid";
			$default_fields[] = "post_content";
			$default_fields[] = "post_date";
			$default_fields[] = "post_title";

			$custom_fields = $wpdb->get_results($wpdb->prepare("SELECT meta_key, meta_value, post_id FROM $wpdb->postmeta JOIN $wpdb->posts ON post_id = ID WHERE post_type = 'wpsc-product' AND meta_value <> ''  GROUP BY meta_key"));
			foreach( $custom_fields as $custom_value ) {
				if ( @unserialize( $custom_value->meta_value ) === true ) continue;
				if ( strpos( $custom_value->meta_key, "hide_on_screen" ) || strpos( $custom_value->meta_key, "layout" ) || strpos($custom_value->meta_key, "meta" ) || strpos( $custom_value->meta_key, "position" ) ) {
						continue;
					}
				$default_fields[] = $custom_value->meta_key;
			}
			sort($default_fields);
			$wp_fields = array_merge($default_field, $default_fields);
			
			$defined_fields = array();
			try {
					$fields_data = $facetly->fieldSelect();	
				} catch (Exception $e) {
					echo '<div class="error"><p><strong>'. $e->getMessage(). '</strong></p></div>';
				}
			foreach( $fields_data->field as $fields_value ) {
				if ( $fields_value->name == 'url' || $fields_value->name == 'imageurl' || $fields_value->name == 'category' ) continue;
				$defined_fields[] = $fields_value->name;
			}
			sort($defined_fields);
			$save_field = array();
			
			if( !empty($_POST['facetly_fields_hidden']) && $_POST['facetly_fields_hidden'] == 'Y' ) {  
				foreach( $defined_fields as $defined_value ) {
					$save_field[$defined_value] = $_POST[$defined_value];
				}
				update_option('facetly_fields', $save_field);  
				?>  
				<div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>  
				<?php  
			} else {  
				$facetly_fields = get_option('facetly_fields');
				if ( !empty($facetly_fields) ) {
					foreach( $facetly_fields as $facetly_key => $facetly_value ) {
						$save_field[$facetly_key] = $facetly_value;
					}
				}
			}  
			
		?> 

			<div class="wrap">  
				<?php    echo "<h2>" . __( 'Facetly Configuration' ) . "</h2>"; ?>  
				<?php echo "<h4>" . __( 'Facetly Fields' ) . "</h4>"; ?>  
				<form name="facetly_fields" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
					<input type="hidden" name="facetly_fields_hidden" value="Y">  
					<table>
					<?php
						foreach( $defined_fields as $def_value ):
							$def_field_name = '.field.'.$def_value;
							
								?> 
							<tr>
								<td> <?php echo $def_field_name; ?></td>
							</tr>
							<tr>
								<td>
									<select name="<?php echo $def_value; ?>">
								<?php
									$selected = "";
									foreach( $wp_fields as $wp_value ):
										if ( $wp_value === $save_field[$def_value]) {
											$selected = "selected";
										}
										if ( $selected == "" ) {
											if ( strstr($def_value, "body") ) {
												if ( strstr($wp_value, "content") ) {
													$selected = "selected";
												}
											} else if ( strstr($def_value, "title") ) {
												if ( strstr($wp_value, "title") ) {
													$selected = "selected";
												}
											} else if ( strstr($def_value, "created") ) {
												if ( strstr($wp_value, "date") ) {
													$selected = "selected";
												}
											} else if ( strstr($def_value, "price") ) {
												if ( strstr($wp_value, "_wpsc_price") ) {
													$selected = "selected";
												}
											}
										}
								?>
										<option value="<?php echo $wp_value; ?>" <?php echo $selected; ?> ><?php echo $wp_value; ?></option>
								<?php
										$selected = "";
									endforeach;
								?>
									</select>
								</td>
							</tr>
						<?php
						endforeach;
					?>
						<tr>
							<td>
								<p class="submit">  
									<input type="submit" name="Submit" value="<?php _e('Submit Fields' ) ?>" />  
								</p>  
							</td>
						</tr>
					</table>
				</form>  
			</div> 	
	<?php	
			}
		}
