<?php
	/*
	Plugin Name: Facetly
	Version: 0.1
	Description: Facetly Search Plugin.
	Author: Andrew Junior
	Author URI: http://pionize.wordpress.com
	Plugin URI: http://pionize.wordpress.com
	*/

	//
	include('facetly_api.php');
	include('facetly_conn.php');

	include('facetly_reindex.php');
	include('facetly_fields.php');
	include('facetly_template.php');
	include('facetly_admin.php');
	include('facetly_widget.php');
	include('facetly_common.php');

	/*
	function facetly_autocomplete_suggestions(){
		// Query for suggestions
		$query = $_REQUEST['term'];
		/*$posts = get_posts( array(
			's' =>$_REQUEST['term'],
		) );
		
		$autocomplete_list = facetly_autocomplete($query);
		$suggests = $autocomplete_list->suggestions;

		// Initialise suggestions array
		$suggestions=array();

		//global $post;
		foreach ($suggests as $suggest): setup_postdata($post);
			// Initialise suggestion array
			$suggestion = array();
			$suggestion['label'] = $suggest;
			$suggestion['link'] = get_bloginfo('wpurl')."/facetly-search?query=".$suggest;

			// Add suggestion to suggestions array
			$suggestions[]= $suggestion;
		endforeach;

		// JSON encode and echo
		$response = $_GET["callback"] . "(" . json_encode($suggestions) . ")";
		echo $response;

		// Don't forget to exit!
		exit;
	}*/

	/*
	add_action( 'init', 'facetly_autocomplete_init' );
	function facetly_autocomplete_init() {
		// Register our jQuery UI style and our custom javascript file
		wp_register_style('facetly-jquery-ui', plugins_url('static/style/jquery-ui.css', __FILE__));
		wp_register_script( 'my_search', plugins_url('static/js/search.js', __FILE__), array('jquery','jquery-ui-autocomplete'), null,true);
		wp_localize_script( 'my_search', 'MySearch', array('url' => admin_url( 'admin-ajax.php' )));  
		// Function to fire whenever search form is displayed
		add_action( 'get_search_form', 'facetly_autocomplete_search_form' );

		// Functions to deal with the AJAX request - one for logged in users, the other for non-logged in users.
		add_action( 'wp_ajax_facetly_autocompletesearch', 'facetly_autocomplete_suggestions' );
		add_action( 'wp_ajax_nopriv_facetly_autocompletesearch', 'facetly_autocomplete_suggestions' );
	}
	
	function facetly_autocomplete_search_form(){
		wp_enqueue_script( 'my_search' );
		wp_enqueue_style( 'facetly-jquery-ui' );
	}
	*/


	function facetly_search(){
		include("facetly_conn.php");
		static $var;
		if (empty($var)) {
			$searchtype = "html";
			$query = $_GET['query'];
			$filter = $_GET;
			$common = get_option('facetly_settings');
			$limit = $common['limit'];
			$filter['limit'] = $limit;
			try {
				$var = $facetly->searchProduct($query, $filter, $searchtype);			
			} catch (Exception $e) {
				$var = new StdClass();
				$var->results = $e->getMessage();
				echo '<div class="error"><p><strong>'. $var. '</strong></p></div>';
				//echo $e->getMessage();
			}
		}
		//print_r($var);
		//print "joajsojodshfodshldsf";
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
		//wp_register_script('facetly-search-conn-js', plugins_url('static/js/facetly_conn.php', __FILE__));
		//wp_enqueue_script('facetly-search-conn-js');
		wp_register_script('facetly-search-facetly-js', plugins_url('static/js/facetly.js', __FILE__));
		wp_enqueue_script('facetly-search-facetly-js');
	};
	add_action ( 'wp_head', 'js');

	function add_js_connection(){
		$common = get_option('facetly_settings');
		$key = $common['key'];
		$secret = $common['secret'];
		$server = $common['server'];

		echo '
		<script type="text/javascript">
			var facetly = {
			    "key" : "'. $key. '",
			    "server" : "'. $server. '",
			    "file" : "",
			    "baseurl" : "/wp/facetly-search",
			}
		</script>';
	}
	add_action('wp_head', 'add_js_connection');

	function include_wordpress_template($t) {
		global $wp_query;
		if ($wp_query->is_404) {
			$wp_query->is_404 = false;
        	//$wp_query->is_page = true;
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
				//print $value;
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
			//print "category:". $items['category'];
			//strtotime($items['created']);
			$date = new DateTime($items['created']);
			$items['created'] = $date->getTimestamp() *1000;
			
			try {
				$facetly->productUpdate($items);
			} catch (Exception $e) {
				echo '<div class="error"><p><strong>'. $e->getMessage(). '</strong></p></div>';
			}
			
			//print_r(get_post_custom_values('specification', $post_id));
			//exit();
			//print_r($post);
			//print_r(wp_get_post_tags($post->ID));
			//print_r(wp_get_post_terms($post_id));
			//exit();

		} else if ( ($post->post_status == 'pending' || $post->post_status == 'draft' ) && $post_type === 'wpsc-product') {
			$facetly->productDelete;
			
		}
	}
	add_action('wp_insert_post', 'do_save_post');
	
	function facetly_admin_actions(){
		//add_options_page("Facetly Settings", "Facetly Settings", "manage_options", "facetly-settings", "facetly_admin");
		add_menu_page("Facetly Settings", "Facetly Settings", 1, "facetly-settings", "facetly_admin");
		add_submenu_page("facetly-settings", "Fields", "Fields", 1, "facetly-settings-fields", "facetly_fields");
		add_submenu_page("facetly-settings", "Reindex", "Reindex", 1, "facetly-settings-reindex", "facetly_reindex");
		add_submenu_page("facetly-settings", "Template", "Template", 1, "facetly-settings-template", "facetly_template");
	}
	add_action('admin_menu', 'facetly_admin_actions');
	

	function facetly_deactivated() {
    	
    	delete_option('facetly_fields');  // contain array of fields settings
	    delete_option('facetly_settings');  // contain array of facetly key, secret, and server settings
	    //delete_option('facetly_template');  //contain array of facetly template page, search, and facets settings
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
		//$source = plugins_url('facetly-search-template.php');
		
		//$dest = get_bloginfo('template_directory');
	//	copy($source, $dest);
		add_option('facetly_fields');
		add_option('facetly_settings');
		//add_option('facetly_template');
		add_option('facetly_tplpage', $tplpage);
		add_option('facetly_tplsearch', $tplsearch);
		add_option('facetly_tplfacet', $tplfacet);
	}

	register_activation_hook( __FILE__, 'facetly_activated' );