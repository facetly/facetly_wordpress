<?php

function facetly_api_init() {
    static $facetly;

    if ( empty( $facetly ) ) {
        require_once("facetly_api.php");

        $facetly = new facetly_api();
        $common = get_option('facetly_settings');
		$consumer_key = $common['key'];
		$consumer_secret = $common['secret'];
		$server = $common['server'];
		$add_variable = $common['add_variable'];
		$base_url = "/finds?". $add_variable;

		$facetly->setConsumer($consumer_key, $consumer_secret); 
		$facetly->setServer($server);
		$facetly->setBaseUrl($base_url);
    }
    return $facetly;
}

function custom_get_child($parent_id, $terms_childs, $tax) {
	foreach ($terms_childs as $key => $value) {
		if($value->parent == $parent_id) {
			$parent_id = $value->term_id;
			$name = $value->name;
			$tax[] = $name;
			unset($terms_childs[$parent_id]);
			$taxo = custom_get_child($parent_id, $terms_childs, $tax);
			
			return $taxo;
		}
	}
	return $tax;
}

function custom_taxonomies_terms_links($post_id) {
	//generate only taxonomy related to post
	$terms = wp_get_object_terms( $post_id, 'wpsc_product_category', array('orderby' => 'parent', 'order' => 'DESC', 'fields' => 'all') );

	foreach ($terms as $key => $value) {
		$terms_childs[$value->term_id] = $value;
	}

	foreach ($terms as $key => $value) {
		$terms_parents[$value->parent][$value->term_id] = $value;
	}

	$parents = $terms_parents[0];
	foreach ($parents as $key => $value) {
		$parent_id = $value->term_id;
		unset($terms_childs[$parent_id]);
	}
	foreach ($parents as $key => $value) {
		$parent_id = $value->term_id;
		$name = $value->name;
		$tax = array();
		$tax[] = $name;
		unset($terms_childs[$parent_id]);

		$taxonomy[] = custom_get_child($parent_id, $terms_childs, $tax);
	}

	if (!empty($taxonomy)) {
		foreach ($taxonomy as $key => $value) {
			$cat[] = join(';', $value);
		}
	}

	return $cat;
}

function zipfile($filename, $pathsource, $pathdestination){
	$pathsource = str_replace("\\", "/", $pathsource);
	$pathdestination = str_replace("\\", "/", $pathdestination);

	$fp = fopen($pathsource, 'r');
	$filecontent = fread($fp, filesize($pathsource));
	fclose($fp);

	$zip = new ZipArchive();
	$filezip =  $filename.".zip";

	$compress = $zip->open($pathdestination. $filezip, ZIPARCHIVE::CREATE);
	if ($compress) {
	   $zip->addFromString( $filename, $filecontent);
	   $zip->close();

	   return true;
	} else {
		return false;
	}
}

function unzipfile($pathsource, $pathdestination){
	$pathsource = str_replace("\\", "/", $pathsource);
	$pathdestination = str_replace("\\", "/", $pathdestination);

	$zip = zip_open($pathsource);
	if ($zip) {
		while ($zip_entry = zip_read($zip)) {
			$fp = fopen($pathdestination. zip_entry_name($zip_entry), "w");
			if (zip_entry_open($zip, $zip_entry, "r")) {
		  		$buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
		  		fwrite($fp,"$buf");
				
		 		zip_entry_close($zip_entry);
				fclose($fp);
			}
	  	}
		zip_close($zip);
		return true;
	} else {
		return false;
	}
}

function facetly_save_post($post_id) {
	$post = get_post($post_id);
	$post_type = $post->post_type;
	$post_status = $post->post_status;
	
	if($post_status == 'trash' || $post_status == 'auto-draft' || $post_type != 'wpsc-product'){
		return $post_id;
	} else if ($post_status == 'publish' && $post_type == 'wpsc-product') {
		try {
			facetly_insert_product($post_id);
		} catch (Exception $e) {
			echo '
				<tr>
					<td colspan="11" class="custom_error">'. $e->getMessage(). '</td>
				</tr>
			';
		}
	} else if ( ($post_status == 'pending' || $post_status == 'draft' ) && $post_type == 'wpsc-product') {
		$facetly->productDelete($post_id);
	}
}

function facetly_insert_product($post_id){
	global $wpdb;

	$post = get_post($post_id);
	$url = get_permalink($post_id);
	$image = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full-size' );
	$imageurl = $image['0'];
	
	$category = custom_taxonomies_terms_links($post_id);
	$item = array();
	$meta = get_post_meta($post_id, '');

	$facetly_fields = get_option('facetly_fields');
	foreach( $facetly_fields as $key => $value ) {
		if ( strstr($value, 'post') ) {	
			$item[$key] = $post->$value;
		} else if (isset($meta[$value])) {
			$item[$key] = $meta[$value][0];
		}
	}
	$item['id'] = $post_id;
	$item['url'] = $url;
	$item['imageurl'] = $imageurl;
	$item['category'] = $category;
	$date = new DateTime($item['created']);
	$item['created'] = $date->getTimestamp() *1000;
	
	$facetly = facetly_api_init();
	$facetly->productUpdate($item);
}
add_action('wp_insert_post', 'facetly_save_post');

function facetly_delete_product($post_id) {
	$post = get_post($post_id);
	$post_type = $post->post_type;
	if($post->post_status == 'trash' or $post->post_status == 'auto-draft' or $post_type != 'product'){
		return $post_id;
	} else if ($post_type == 'product') {
		try {
			$facetly = facetly_api_init();
			$facetly->productDelete($post_id);
		} catch (Exception $e) {
			echo '<div class="error"><p><strong>'. $e->getMessage(). '</strong></p></div>';
		}
	}
}
add_action('wp_trash_post', 'facetly_delete_product');
