<?php

	$facetly = new Facetly;
	$consumer_secret = get_option('facetly_secret');
	$consumer_key = get_option('facetly_key');
	$server = get_option('facetly_server');
	$facetly->setConsumer($consumer_key, $consumer_secret); 
	$facetly->setServer($server);
	$facetly->setBaseUrl(get_bloginfo('wpurl')."/facetly-search");



?>