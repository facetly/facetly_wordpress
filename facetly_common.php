<?php

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

	function facetly_get_product_imageurl($post_id){
		global $wpdb;
		$retval = '';  
		$query_product = "select ID, post_title from ". $wpdb->prefix. "posts  ";
		$query_product .= "WHERE post_type = 'wpsc-product' ";
		$query_product .= "AND post_status = 'publish' AND ID =".$post_id;
		$product = mysql_query($query_product);
		while($get_product = mysql_fetch_object($product)){
			$id = $get_product->ID;
			$title = $get_product->post_title;
			$query_product_meta = "select wm.meta_value, wm.meta_key from ". $wpdb->prefix. "postmeta wm LEFT JOIN ". $wpdb->prefix. "posts wp ON wm.post_id = wp.id";
			$query_product_meta .= " where wm.post_id=".$id. " and (wm.meta_key='_wpsc_price' or wm.meta_key='_thumbnail_id')";
			$product_meta = mysql_query($query_product_meta);
			while($get_product_meta = mysql_fetch_object($product_meta)){
				$meta_key = $get_product_meta->meta_key;
				if ( $meta_key == '_thumbnail_id' ) {
					$meta_id = $get_product_meta->meta_value;
					$query_img = "select guid from ". $wpdb->prefix. "posts where ID=". $meta_id;
					$img = mysql_query($query_img);
					while($get_img = mysql_fetch_object($img)){
						$imageurl = $get_img->guid;
					}
				}
			}
		}
		return $imageurl;
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

		while (!empty($terms_childs)) {
			$i++;
			if ($i > 20) {
				break;
			}
			foreach ($terms_childs as $key => $value) {
				if (empty($terms_parents[$key])) {
					$new_terms[$key][] = $value->name;
					$parent = $value->parent;
					$new_child = $key;
					while ($parent != 0) {
						$j++;
						if ($j > 20) {
							exit();
						}
						foreach ($terms_parents as $key2 => $value2) {
							if (!empty($value2[$new_child])) {
								$new_terms[$key][] = $terms_childs[$key2]->name;
								$parent = $terms_childs[$key2]->parent;
								if (empty($terms_parents[$key2])) {
									unset($terms_childs[$key2]);
								}
								$new_child = $key2;
								if ($parent==0) {
									break;

								}
							}
						}
					}
					unset($terms_childs[$key]);
				}

			}
		}
		if (!empty($new_terms)) {
			foreach ($new_terms as $key => $value) {
				krsort($value);
				$cat[] = join(';', $value);
			}
		}

		return $cat;
	}

