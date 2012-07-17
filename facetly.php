<?php
	/*
	Plugin Name: Facetly
	Version: 0.1
	Description: Facetly Search Plugin.
	Author: Facetly
	Author URI: http://www.facetly.com
	Plugin URI: http://www.facetly.com
	*/

	include('facetly_api.php');
	include('facetly_conn.php');

	include('facetly_reindex.php');
	include('facetly_fields.php');
	include('facetly_template.php');
	include('facetly_admin.php');
	include('facetly_widget.php');
	include('facetly_common.php');

	function facetly_search(){
		include("facetly_conn.php");
		static $var;
		if (empty($var)) {
			try {
				$searchtype = "html";
				$query = $_GET['query'];
				$filter = $_GET;
				$common = get_option('facetly_settings');
				$limit = $common['limit'];
				$filter['limit'] = $limit;
				$var = $facetly->searchProduct($query, $filter, $searchtype);			
			} catch (Exception $e) {
				$var = new StdClass();
				$var->results = $e->getMessage();
				echo '<div class="error"><p><strong>'. $var->results. '</strong></p></div>';
			}
		}
		return $var;
	}

	function facetly_autocomplete($query){
		include("facetly_conn.php");
		$autocomplete_list = $facetly->searchAutoComplete($query);

		return $autocomplete_list;
	}

	function style(){
		wp_register_style('facetly-search-style', plugins_url('static/style/facetly.css', __FILE__));
		wp_enqueue_style('facetly-search-style');
		wp_register_style('facetly-search-progress-bar', plugins_url('static/style/progress-bar.css', __FILE__));
		wp_enqueue_style('facetly-search-progress-bar');
	};
	add_action ( 'wp_head', 'style');

	function js(){
		wp_register_script('facetly-search-jquery-address-js', plugins_url('static/js/jquery.address.js', __FILE__));
		wp_enqueue_script('facetly-search-jquery-address-js');
		wp_register_script('facetly-search-jquery-autocomplete-js', plugins_url('static/js/jquery.autocomplete.js', __FILE__));
		wp_enqueue_script('facetly-search-jquery-autocomplete-js');
		wp_register_script('facetly-search-facetly-js', plugins_url('static/js/facetly.js', __FILE__));
		wp_enqueue_script('facetly-search-facetly-js');
	};
	add_action ( 'wp_head', 'js');

	function add_js_connection(){
		$common = get_option('facetly_settings');
		$key = $common['key'];
		$secret = $common['secret'];
		$server = $common['server'];
		$limit = $common['limit'];
		
		echo '
		<script type="text/javascript">
			var facetly = {
			    "key" : "'. $key. '",
			    "server" : "'. $server. '",
			    "file" : "",
			    "limit": '. $limit. ',
			    "baseurl" : "'. get_bloginfo('wpurl'). '/facetly-search",
			}
		</script>';
	}
	add_action('wp_head', 'add_js_connection');

	function include_wordpress_template($t) {
		global $wp_query;
		if ($wp_query->is_404) {
			$wp_query->is_404 = false;
        	$wp_query->is_archieve = true;
			header("HTTP/1.1 200 OK");
			include($t);
			exit();
		}
	}
	function my_template() {
		if (strstr($_SERVER['REQUEST_URI'],'/facetly-search')) {
			include_wordpress_template(TEMPLATEPATH . '/facetly-search-template.php');
		}
	}
	add_action('template_redirect', 'my_template');

	function do_del_post($post_id) {
		include('facetly_conn.php');
		$post = get_post($post_id);
		$post_type = $post->post_type;
		if($post->post_status == 'trash' or $post->post_status == 'auto-draft' or $post_type != 'wpsc-product'){
			return $post_id;
		} else if ($post_type === 'wpsc-product') {
			try {
				$facetly->productDelete($post_id);
			} catch (Exception $e) {
				echo '<div class="error"><p><strong>'. $e->getMessage(). '</strong></p></div>';
			}
		}
	}
	add_action('wp_trash_post', 'do_del_post');

	function do_save_post($post_id){
		global $wpdb;

		include('facetly_conn.php');
		
		$post = get_post($post_id);
		$post_type = $post->post_type;
		
		if($post->post_status == 'trash' or $post->post_status == 'auto-draft' or $post_type != 'wpsc-product'){
			return $post_id;
		} else if ($post->post_status == 'publish' && $post_type === 'wpsc-product') {
			$category = custom_taxonomies_terms_links($post_id);
			$imageurl = facetly_get_product_imageurl($post_id);
			
			$items = array();
			$meta = get_post_meta($post_id, '');
			$facetly_fields = get_option('facetly_fields');
			foreach( $facetly_fields as $key => $value ) {
				if ( get_post_custom_values($value, $post_id) != "" ){
					$items[$key] = get_post_custom_values($value, $post_id);
				} else if ( strstr($value, 'post') ) {
					$items[$key] = $post->$value;
				} else if (isset($meta[$value])) {
					$items[$key] = $meta[$value][0];
				}
			}
			$items['id'] = $post_id;
			$items['url'] = wpsc_product_url($post_id);
			$items['imageurl'] = $imageurl;
			$items['category'] = $category;
			$date = new DateTime($items['created']);
			$items['created'] = $date->getTimestamp() *1000;
			
			try {
				$facetly->productUpdate($items);
			} catch (Exception $e) {
				echo '<div class="error"><p><strong>'. $e->getMessage(). '</strong></p></div>';
			}
			
		} else if ( ($post->post_status == 'pending' || $post->post_status == 'draft' ) && $post_type === 'wpsc-product') {
			$facetly->productDelete;	
		}
	}
	add_action('wp_insert_post', 'do_save_post');
	
	function facetly_admin_actions(){
		add_menu_page("Facetly Settings", "Facetly Settings", 1, "facetly-settings", "facetly_admin");
		add_submenu_page("facetly-settings", "Fields", "Fields", 1, "facetly-settings-fields", "facetly_fields");
		add_submenu_page("facetly-settings", "Reindex", "Reindex", 1, "facetly-settings-reindex", "facetly_reindex");
		add_submenu_page("facetly-settings", "Template", "Template", 1, "facetly-settings-template", "facetly_template");
	}
	add_action('admin_menu', 'facetly_admin_actions');
	

	function facetly_deactivated() {
    	delete_option('facetly_fields');  // contain array of fields settings
	    delete_option('facetly_settings');  // contain array of facetly key, secret, and server settings
	    delete_option('facetly_tplpage', $tplpage);
		delete_option('facetly_tplsearch', $tplsearch);
		delete_option('facetly_tplfacet', $tplfacet);
    	
		$unzipsource = TEMPLATEPATH. "/searchform-def-backup.zip";
		$unzipdest = TEMPLATEPATH. "/";  //folder directory must be ended with "/", example: c:/xampp/htdocs/wordpress/
		$unzip1 = unzipfile($unzipsource, $unzipdest);

    	if ( is_writable(TEMPLATEPATH) ) {
			$facetly_searchtpl = TEMPLATEPATH. "/facetly-search-template.php";
			unlink($facetly_searchtpl);
			$facetly_searchtpl = TEMPLATEPATH. "/searchform.php";
			unlink($facetly_searchtpl);
				
		}
	}

	register_deactivation_hook( __FILE__, 'facetly_deactivated' );
	register_uninstall_hook( __FILE__, 'facetly_deactivated' );

	function facetly_activated(){
		add_option('facetly_fields');
		add_option('facetly_settings');
		add_option('facetly_tplpage', $tplpage);
		add_option('facetly_tplsearch', $tplsearch);
		add_option('facetly_tplfacet', $tplfacet);
	}

	register_activation_hook( __FILE__, 'facetly_activated' );