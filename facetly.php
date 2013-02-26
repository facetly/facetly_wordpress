<?php
	/*
	Plugin Name: Facetly WP ECommerce
	Version: 0.1
	Description: Facetly Search Plugin for WP ECommerce.
	Author: Facetly
	Author URI: http://www.facetly.com
	Plugin URI: http://www.facetly.com
	*/
	require_once("facetly_common.php");
	require_once("facetly_admin.php");
	require_once("facetly_fields.php");
	require_once("facetly_reindex.php");
	require_once("facetly_template.php");
	require_once('facetly_widget.php');
	
	function facetly_activated(){
		add_option('facetly_fields');
		add_option('facetly_settings');
		add_option('facetly_tplpage');
		add_option('facetly_tplsearch');
		add_option('facetly_tplfacet');
		add_option('facetly_page_id');
		$facetly_page = wp_insert_post( array(
			'post_title' => 'Facetly Search',
			'post_type' => 'page',
			'post_name'	 => 'finds',
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'post_content' => '',
			'post_status' => 'publish',
			'post_author' => 1,
			'menu_order' => 0,
			'guid' => site_url() . "/finds",
		), true);
		
		$facetly_page_data = get_page_by_path('finds');
		$facetly_page_id = $facetly_page_data->ID;
		update_option('facetly_page_id', $facetly_page_id);  
	}

	register_activation_hook( __FILE__, function() {
	  	add_option('activated_plugin','facetly');
	});

	add_action('admin_init','load_plugin');
	function load_plugin() {
	    if(is_admin() && get_option('activated_plugin') == 'facetly') {
	     facetly_activated();
	     delete_option('activated_plugin');
	    }
	}
	
	add_action('activate_facetly', 'facetly_activated');

	function facetly_deactivated() {
		delete_option('facetly_fields');
		delete_option('facetly_settings');
		delete_option('facetly_tplpage');
		delete_option('facetly_tplsearch');
		delete_option('facetly_tplfacet');

		$unzipsource = TEMPLATEPATH. "/searchform.php.zip";
		$unzipdest = TEMPLATEPATH. "/";
		$unzip1 = unzipfile($unzipsource, $unzipdest);
		unlink($unzipsource);

		if ( is_writable(TEMPLATEPATH) ) {
			$facetly_searchtpl = TEMPLATEPATH. "/facetly-search-template.php";
			unlink($facetly_searchtpl);
			$facetly_searchtpl = TEMPLATEPATH. "/searchform.php";
			unlink($facetly_searchtpl);	
		}
		$facetly_page_id = get_option('facetly_page_id');
		wp_delete_post($facetly_page_id, true);
	}

	register_deactivation_hook( __FILE__, 'facetly_deactivated' );
	register_uninstall_hook( __FILE__, 'facetly_deactivated' );


	function facetly_admin_actions(){
		add_menu_page("Facetly Configuration", "Facetly Configuration", 'manage_options', "facetly-configuration", "facetly_admin");
		add_submenu_page("facetly-configuration", "Fields", "Fields", 'manage_options', "facetly-configuration-fields", "facetly_fields");
		add_submenu_page("facetly-configuration", "Reindex", "Reindex", 'manage_options', "facetly-configuration-reindex", "facetly_reindex");
		add_submenu_page("facetly-configuration", "Template", "Template", 'manage_options', "facetly-configuration-template", "facetly_template");
	}
	add_action('admin_menu', 'facetly_admin_actions');

	function facetly_style(){
		wp_register_style('facetly-search-style', plugins_url('css/facetly.css', __FILE__));
		wp_enqueue_style('facetly-search-style');
		wp_register_style('facetly-search-autocomplete-style', plugins_url('css/autocomplete.css', __FILE__));
		wp_enqueue_style('facetly-search-autocomplete-style');
		wp_register_style('facetly-search-progress-bar', plugins_url('css/progress-bar.css', __FILE__));
		wp_enqueue_style('facetly-search-progress-bar');
	};
	add_action ( 'wp_head', 'facetly_style');

	function facetly_js(){
		wp_register_script('facetly-search-jquery-address-js', plugins_url('js/jquery.address.js', __FILE__));
		wp_enqueue_script('facetly-search-jquery-address-js');
		wp_register_script('facetly-search-jquery-autocomplete-js', plugins_url('js/jquery.autocomplete.js', __FILE__));
		wp_enqueue_script('facetly-search-jquery-autocomplete-js');
		wp_register_script('facetly-search-facetly-js', plugins_url('js/facetly.js', __FILE__));
		wp_enqueue_script('facetly-search-facetly-js');
	};
	add_action ( 'wp_head', 'facetly_js');

	function facetly_search_title(){
	    global $post;
	    if ($post->post_title == 'Facetly Search') {
	    	if (!empty($_GET['query'])) {
				$query = stripslashes($_GET['query']);
				$query = htmlentities($query);
			} else {
				$query = '';
			}
			$title = "Search ". $query. ' | '; 
	   		return $title;
	    }
	    return;
	}
	add_filter('wp_title','facetly_search_title');

	function add_js_connection(){
		$common = get_option('facetly_settings');
		if (!empty($common)) {
			$key = $common['key'];
			$secret = $common['secret'];
			$server = $common['server'];
			$limit = $common['limit'];
			$add_variable = $common['add_variable'];
			$facetly_page_id = get_option('facetly_page_id');
			$facetly_page_data = get_page($facetly_page_id);
			$facetly_post_name = $facetly_page_data->post_name;

			echo '
			<script type="text/javascript">
				var facetly = {
				    "key" : "'. $key. '",
				    "server" : "'. $server. '",
				    "file" : "'. $facetly_post_name. '?'. $add_variable. '",
				    "baseurl" : "'. site_url(). '/",
				    "limit" : "'. $limit. '",
				}
				
			</script>';
		}
	}
	add_action('wp_head', 'add_js_connection');

	function facetly_admin_head_init(){
		wp_register_style('facetly-admin-style', plugins_url('css/facetly-admin.css', __FILE__));
		wp_enqueue_style('facetly-admin-style');
	}
	add_action( 'admin_head', 'facetly_admin_head_init' );

	function facetly_search(){
		static $var;
		if (empty($var)) {
			try {
				$facetly = facetly_api_init();
				$searchtype = "html";
				if (!empty($_GET['query'])) {
					$query = $_GET['query'];
					$query = stripslashes($_GET['query']);
				} else {
					$query = '';
				}

				$filter = $_GET;
				unset($filter['q']);
				unset($filter['query']);

				$common = get_option('facetly_settings');
				if (!empty($common)) {
					$limit = $common['limit'];
			        $add_variable = $common['add_variable'];
			    } else {
			        $limit = '';
			        $add_variable = '';
			    }
			    $facetly_page_id = get_option('facetly_page_id');
				$facetly_page_data = get_page($facetly_page_id);
				$facetly_post_name = $facetly_page_data->post_name;
			    $base_url = '/'.$facetly_post_name. '?'. $add_variable;

				$filter['limit'] = $limit;
				$filter['baseurl'] = $base_url;
				$var = $facetly->searchProduct($query, $filter, $searchtype);			
			} catch (Exception $e) {
				$var = new StdClass();
				$var->results = $e->getMessage();
				$var->facets = $e->getMessage();
				echo '<div class="error"><p><strong>'. $var->results. '</strong></p></div>';
			}
		}
		return $var;
	}

	function facetly_include_template($t) {
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
		$facetly_page_id = get_option('facetly_page_id');
		$facetly_page_data = get_page($facetly_page_id);
		$facetly_post_name = $facetly_page_data->post_name;
		if (strstr($_SERVER['REQUEST_URI'],'/'. $facetly_post_name)) {
			facetly_include_template(TEMPLATEPATH . '/facetly-search-template.php');
		}
	}
	//add_action('template_redirect', 'facetly_custom_template');

	function facetly_search_shortcode( $atts ) {
		extract( shortcode_atts( array(
			'output' => 'results',
		), $atts ) );
		$search = facetly_search();
		if ($output == 'results') {
			$return = $search->results;
		} else if ($output == 'facets') {
			$return = $search->facets;
		}
		return $return;
	} 
	add_shortcode( 'facetly_search', 'facetly_search_shortcode' );

	function facetly_progressbar( $atts ){
		extract( shortcode_atts( array(
			'percentage' => 0,
		), $atts ) );
		
		echo '<style type="text/css">  
			#progress-outer {
				background: #333;
				-webkit-border-radius: 13px;
				margin-left: auto;
				margin-right: auto;
				height: 30px;
				width: 1100px;
				padding: 3px;
			}
			#progress-text {
				width: 1100px;
				text-align: center;
				line-height: 30px;
				font-size: 14px;
				color:red;
			}

			#progress-inner {
				background: green;
				width: '. $percentage. '%;
				height: 100%;
				-webkit-border-radius: 9px;
			} 
			</style>';

			$progress_bar = '
				<div id="progress-outer">
				    <div id="progress-inner"> <div id="progress-text">'. $percentage. '%</div></div>
				</div>​';

			return $progress_bar;
	}
	add_shortcode( 'facetly_progress', 'facetly_progressbar' );
