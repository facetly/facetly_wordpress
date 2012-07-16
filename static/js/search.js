jQuery(document).ready(function ($){
	var acs_action = 'facetly_autocompletesearch';
	jQuery("#s").autocomplete({
		source: function(req, response){
			jQuery.getJSON(MySearch.url+'?callback=?&action='+acs_action, req, response);
		},
		select: function(event, ui) {
			window.location.href=ui.item.link;
		},
		minLength: 2,
	});
});
