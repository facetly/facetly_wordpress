<?php
	function facetly_admin(){

		if($_POST['facetly_settings_hidden'] == 'Y') {  
			$key = $_POST['facetly_key'];  
			$secret = $_POST['facetly_secret'];  
			$server = $_POST['facetly_server']; 
			$limit = $_POST['facetly_limit'];
			$add_variable = $_POST['facetly_add_variable']; 
			
			$settings = array(
				'key' => $key,
				'secret' => $secret,
				'server' => $server,
				'limit' => $limit,
				'add_variable' => $add_variable,
			);

			update_option('facetly_settings', $settings);  
			?>  
			<div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>  
			<?php  
		} else {  
			$common = get_option('facetly_settings');
			$key = $common['key'];
			$secret = $common['secret'];
			$server = $common['server'];
			$limit = $common['limit'];
			$add_variable = $common['add_variable'];
		}

		if($_POST['facetly_copy_hidden'] == 'Y') {
			if ( is_writable(TEMPLATEPATH) ) {
				$zipfilename = "searchform.php";
				$zipsource = TEMPLATEPATH. "/searchform.php";
				$zipdest = TEMPLATEPATH. "/";
				$backup = zipfile($zipfilename, $zipsource, $zipdest);
				unlink(TEMPLATEPATH. "/searchform.php");
				

				$unzipsource = WP_PLUGIN_DIR. "/facetly/facetly-search-template.zip";
				$unzipdest = TEMPLATEPATH. "/";  //folder directory must be ended with "/", example: c:/xampp/htdocs/wordpress/
				$unzip1 = unzipfile($unzipsource, $unzipdest);

				if ( $backup && $unzip1 && $unzip2 ) {
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
		<?php    echo "<h2>" . __( 'Facetly Settings' ) . "</h2>"; ?>  
		<?php echo "<h4>" . __( 'Facetly Common Settings' ) . "</h4>"; ?>  
		<form name="facetly_settings" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
			<input type="hidden" name="facetly_settings_hidden" value="Y">  
			<table>
				<tr>
					<td><?php _e("Consumer Key"); ?></td>
					<td><?php _e(":");?></td>
					<td><input type="text" name="facetly_key" value="<?php echo $key; ?>" size="50"><?php _e(" ex: qhduafdh" ); ?></td>
				</tr>
				<tr>
					<td><?php _e("Consumer Secret"); ?></td>
					<td><?php _e(":");?></td>
					<td><input type="text" name="facetly_secret" value="<?php echo $secret; ?>" size="50"><?php _e(" ex: q5yvmddqntukobeoszi6zuqmwvy9wwsv" ); ?></td>
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
				echo "File Already Exist";
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
?>