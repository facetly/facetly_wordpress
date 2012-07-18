jQuery(document).ready(function() {
  jQuery('input[facetly="on"]').each(function(index, elm) {
    var input = jQuery(this);
    var autosubmit;    
    var nocache;
    var gmap;
    /*if (input.attr('autosubmit') == 'no') {
      autosubmit = false;
    } else {
      autosubmit = true;
    }
    
    if (input.attr('nocache') == 'yes') {
      nocache = true;
    } else {
      nocache = false;
    }
    
    if (input.attr('gmap') == 'yes') {
      gmap = true;
    } else {
      gmap = false;
    } */
    
    //var xhr;          
    
    var facetly_delay = (function(){
      var timer = 0;
      return function(callback, ms){
        clearTimeout (timer);
        timer = setTimeout(callback, ms);
      };
    })();

                                             
    jQuery(input).keyup(function() {   
      facetly_delay(function() {      
        params = {};
        facetly_server = facetly.server;
        params.key = facetly.key;
        params.baseurl = facetly.baseurl+"/"+facetly.file;
        params.searchtype = "html";
        if (jQuery(input).val() != "") {
          params.query = jQuery(input).val();
        }
        
        jQuery.ajax({
          url: facetly_server + "/search/product",
          dataType: "jsonp",
          type: "GET",
          data : params,
          success: function(data) {		
            handler(data);
          }
        });
      }, 300);
    });
    var serviceUrl= facetly.server+'/search/autocomplete';
    var params={
      "key" : facetly.key
    }
    jQuery(input).autocomplete({
      autoSubmit: autosubmit,
      noCache: nocache,
      gmap: gmap,
      params: params, 
      serviceUrl:serviceUrl,  
      jsonp: true,  
    });                        
  });
  




    var init = true, 
        state = window.history.pushState !== undefined;
    
    // Handles response
    var handler = function(data) {
       // jQuery('title').html(jQuery('title', 'test test').html());       
        jQuery('#facetly_result').html(data.results);
        jQuery('#facetly_result').show();
        if (data.total > 0) {
          jQuery('#facetly_facet').html(data.facets);
          jQuery('#facetly_facet').show();
          
        } 
        jQuery(document).trigger("facetly_loaded");       
    };	
    
    //console.log(Drupal.settings.facetly_state);
    //alert(Drupal.settings.facetly_baseurl);
    var baseurlfile=facetly.baseurl+""+facetly.file;
    console.log(baseurlfile);
    if (jQuery('.pager a[href*="'+baseurlfile+'"], #facetly_facet a[href*="'+baseurlfile+'"]')) {
    jQuery.address.state(facetly.baseurl).init(function() {
        // Initializes the plugin
        jQuery('.pager a[href*="'+baseurlfile+'"], #facetly_facet a[href*="'+baseurlfile+'"]').address();
        
    }).change(function(event) {
        //console.log(event);
        // Selects the proper navigation link
        jQuery('.pager a[href*="'+baseurlfile+'"]').each(function() {
            if (jQuery(this).attr('href') == (jQuery.address.state() + event.path)) {
        	        jQuery(this).parent().addClass('pager-current').focus();
            } else {
                jQuery(this).parent().removeClass('pager-current');
            }
        });
        
        if (state && init) {
        
            init = false;
            jQuery(document).trigger("facetly_loaded");
        
        } else {
           //console.log(event)           
                    
           params = {};
           // fix bug [], replace %5B AND %5D
           for (var i = 0; i < event.parameterNames.length; i++) {
             var key = event.parameterNames[i];
             var newkey = decodeURIComponent(key);
             //console.log("newkey " + newkey);
             //console.log("values " + event.parameters[key]);
             //console.log("type " + typeof event.parameters[key]);
             if (typeof event.parameters[key] == "string") {             	
             	params[newkey] = decodeURIComponent(event.parameters[key]).replace(/\+/g, ' ');
             } else {
             	var values = [];
             	var value_temp = event.parameters[key];
             	for (var j = 0; j < value_temp.length; j++) {             		
             		values[j] = decodeURIComponent(value_temp[j]).replace(/\+/g, ' ');
             	}
             	params[newkey] = values;
             }
           }
           
           facetly_server = facetly.server;
           params["key"] = facetly.key;
           params["baseurl"] = baseurlfile;
           params["searchtype"] = "html";
           
           jQuery.ajax({
             url: facetly_server + "/search/product",
             dataType: "jsonp",
             type: "GET",
             data : params,
             success: function(data, textStatus, XMLHttpRequest) {		
              handler(data);
              jQuery(document).trigger("facetly_loaded");
             }
           });
        }

    });
    } 
})	

