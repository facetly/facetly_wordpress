<?php

	
	function facetly_search_result($query, $filter = array(), $searchtype){
		static $var;
		if (empty($var)){
			print
			$url = "/facetly-search/";
			$consumer_secret = get_option('facetly_secret');
			$consumer_key = get_option('facetly_key');
			$post = array(
					"key" => $consumer_key,
	//				"secret" => $consumer_secret,
	//				"query" => $query,
					"limit" => 3,
					"searchtype" => $searchtype,
					"baseurl" => $url,
			);

			if (!empty($query)) {
			  $post['query'] = $query;
			}

			$post = array_merge($post, $filter);
			$header = http_build_query($post,'','&');			

			$path_server = get_option('facetly_server'). "/search/product?".$header;
			$Curl_Session = curl_init($path_server);
			//curl_setopt ($Curl_Session, CURLOPT_POST, 1);
			//curl_setopt ($Curl_Session, CURLOPT_POSTFIELDS, $post);
			curl_setopt ($Curl_Session, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($Curl_Session,CURLOPT_ENCODING, 1);
			curl_setopt ($Curl_Session, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt ($Curl_Session, CURLOPT_HTTPHEADER, array("Accept-Encoding: gzip"));
			$output = curl_exec ($Curl_Session);
			curl_close ($Curl_Session);
			$var = json_decode($output);
		}
		return $var;

	}

?>