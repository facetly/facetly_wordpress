<?php

	$facetly = new Facetly;
	$common = get_option('facetly_settings');
	$consumer_key = $common['key'];
	$consumer_secret = $common['secret'];
	$server = $common['server'];
	$facetly->setConsumer($consumer_key, $consumer_secret); 
	$facetly->setServer($server);
	$facetly->setBaseUrl("/finds");



?>
