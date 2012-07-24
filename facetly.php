<?php
	/*
	Plugin Name: Facetly
	Version: 0.1
	Description: Facetly Search Plugin.
	Author: Andrew Junior
	Author URI: http://pionize.wordpress.com
	Plugin URI: http://pionize.wordpress.com
	*/

	require_once('facetly_reindex.php');
	require_once('facetly_fields.php');
	require_once('facetly_template.php');
	require_once('facetly_admin.php');
	require_once('facetly_widget.php');
	require_once('facetly_common.php');

	function facetly_search(){
		static $var;
		if (empty($var)) {
			try {
				$facetly = facetly_api_init();
				$searchtype = "html";
				$query = $_GET['query'];
				$filter = $_GET;
				unset($filter['q']);
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

	function style(){
		wp_register_style('facetly-search-style', plugins_url('static/style/facetly.css', __FILE__));
		wp_enqueue_style('facetly-search-style');
		wp_register_style('facetly-search-progress-bar', plugins_url('static/style/progress-bar.css', __FILE__));
		wp_enqueue_style('facetly-search-progress-bar');
		wp_register_style('facetly-jquery-dynatree-style', plugins_url('static/style/ui.dynatree.css', __FILE__));
		wp_enqueue_style('facetly-jquery-dynatree-style');
	};
	add_action ( 'wp_head', 'style');

	function admin_head_init(){
		wp_register_style('facetly-admin-style', plugins_url('static/style/facetly-admin.css', __FILE__));
		wp_enqueue_style('facetly-admin-style');
	}
	add_action( 'admin_head', 'admin_head_init' );

	function js(){
		wp_register_script('facetly-search-jquery-address-js', plugins_url('static/js/jquery.address.js', __FILE__));
		wp_enqueue_script('facetly-search-jquery-address-js');
		wp_register_script('facetly-search-jquery-autocomplete-js', plugins_url('static/js/jquery.autocomplete.js', __FILE__));
		wp_enqueue_script('facetly-search-jquery-autocomplete-js');
		wp_register_script('facetly-search-facetly-js', plugins_url('static/js/facetly.js', __FILE__));
		wp_enqueue_script('facetly-search-facetly-js');
		wp_register_script('facetly-jquery-ui-custom-js', plugins_url('static/js/jquery-ui.custom.js', __FILE__));
		wp_enqueue_script('facetly-jquery-ui-custom-js');
		wp_register_script('facetly-jquery-dynatree-js', plugins_url('static/js/jquery.dynatree.js', __FILE__));
		wp_enqueue_script('facetly-jquery-dynatree-js');
		wp_register_script('facetly-jquery-dynatree-init-js', plugins_url('static/js/jquery.dynatree.init.js', __FILE__));
		wp_enqueue_script('facetly-jquery-dynatree-init-js');
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
			    "file" : "finds",
			    "baseurl" : "/",
			    "limit": "'. $limit. '",
			}
			
		</script>';
	}
	add_action('wp_head', 'add_js_connection');

	function include_facetly_template($t) {
		global $wp_query;
		if ($wp_query->is_404) {
			$wp_query->is_404 = false;
        	$wp_query->is_archieve = true;
			header("HTTP/1.1 200 OK");
			include($t);
			exit();
		}
	}
	function facetly_custom_template() {
		if (strstr($_SERVER['REQUEST_URI'],'/finds')) {
			include_facetly_template(TEMPLATEPATH . '/facetly-search-template.php');
		}
	}
	add_action('template_redirect', 'facetly_custom_template');

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

	function facetly_save_post($post_id) {
		$post = get_post($post_id);
		$post_type = $post->post_type;
		$post_status = $post->post_status;
		echo $post_type;
		if($post_status == 'trash' || $post_status == 'auto-draft' || $post_type != 'wpsc-product'){
			return $post_id;
		} else if ($post_status == 'publish' && $post_type == 'wpsc-product') {
			try {
				facetly_insert_product($post_id);
			} catch (Exception $e) {
				echo '<div class="error"><p><strong>'. $e->getMessage(). '</strong></p></div>';
			}
		} else if ( ($post_status == 'pending' || $post_status == 'draft' ) && $post_type == 'wpsc-product') {
			$facetly->productDelete($post_id);
		}
	}
	add_action('wp_insert_post', 'facetly_save_post');

	function facetly_insert_product($post_id){
		global $wpdb;

		$post = get_post($post_id);
		$url = $post->guid;
		$image = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full-size' );
		$imageurl = $image['0'];
		
		$category = custom_taxonomies_terms_links($post_id);
		$imageurl = facetly_get_product_imageurl($post_id);
		
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
		$item['url'] = wpsc_product_url($post_id);
		$item['imageurl'] = $imageurl;
		$item['category'] = $category;
		$date = new DateTime($item['created']);
		$item['created'] = $date->getTimestamp() *1000;

		$facetly = facetly_api_init();
		$facetly->productUpdate($item);
	}
	
	function facetly_admin_actions(){
		add_menu_page("Facetly Settings", "Facetly Settings", 1, "facetly-settings", "facetly_admin");
		add_submenu_page("facetly-settings", "Fields", "Fields", 1, "facetly-settings-fields", "facetly_fields");
		add_submenu_page("facetly-settings", "Reindex", "Reindex", 1, "facetly-settings-reindex", "facetly_reindex");
		add_submenu_page("facetly-settings", "Template", "Template", 1, "facetly-settings-template", "facetly_template");
	}
	add_action('admin_menu', 'facetly_admin_actions');
	

	function facetly_deactivated() {
    	delete_option('facetly_fields');  
	    delete_option('facetly_settings');  
	    delete_option('facetly_tplpage');
		delete_option('facetly_tplsearch');
		delete_option('facetly_tplfacet');
    	
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
