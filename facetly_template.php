<?php 
	function facetly_template(){
		
		if($_POST['facetly_settings_hidden'] == 'Y') {  
			
			$tplsearch = stripslashes($_POST['tplsearch']);  
			$tplfacet = stripslashes($_POST['tplfacet']);  
			try {
				$facetly = facetly_api_init();
				$response = $facetly->templateUpdate($tplsearch,$tplfacet);
			} catch (Exception $e) {
				$error = $e->getMessage();
			}

			update_option('facetly_tplsearch', $tplsearch);
			update_option('facetly_tplfacet', $tplfacet);

			if (!empty($error)) {
				echo '<div class="error"><p><strong>'. $error. '</strong></p></div>';
			}
			if (!empty($response)) {
				echo '<div class="updated"><p><strong>'. $response. '</strong></p></div>';
			}
		} else {  
			$tplsearch = get_option('facetly_tplsearch');
			$tplfacet = get_option('facetly_tplfacet');
		}  
	?> 

	<div class="wrap">  
		<?php    echo "<h2>" . __( 'Facetly Settings' ) . "</h2>"; ?>  
		<?php echo "<h4>" . __( 'Facetly Template Settings' ) . "</h4>"; ?>  
		<form name="facetly_settings" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
			<input type="hidden" name="facetly_settings_hidden" value="Y">  
			<table>
				<tr>
					<td><?php _e("Search Template:"); ?></td>
					<td><textarea name="tplsearch" cols="150" rows="20"><?php echo $tplsearch ?></textarea></td>
				</tr>
				<tr>
					<td><?php _e("Facet Template:"); ?></td>
					<td><textarea name="tplfacet" cols="150" rows="20"><?php echo $tplfacet ?></textarea></td>
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
?>