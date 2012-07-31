
    var baseurlfile=facetly.baseurl+""+facetly.file;
    var isfacetlypage = false;
  
  if (jQuery('#facetly_facet').length && jQuery('#facetly_result').length) {
    isfacetlypage = true;
  }
  
  jQuery('input[facetly="on"]').each(function(index, elm) {
    var input = jQuery(this);
    var autosubmit = true;    
    var nocache = false;
    var gmap;
    var isctrl = false;      
    
    var delay = (function(){
      var timer = 0;
      return function(callback, ms){
        clearTimeout (timer);
        timer = setTimeout(callback, ms);
      };
    })(); 


    if (isfacetlypage) {
      jQuery(input).keydown(function(e) {   
        var keycode = e.which; 
        if (keycode >= 17 && keycode <= 18) {
          isctrl = true;
        }
        if (isctrl == false && !(keycode == 0 || keycode == 8 || keycode == 9 || keycode == 13 || (keycode >= 16 && keycode <= 20) || keycode == 27 || (keycode >= 33 && keycode <= 46) || (keycode >= 91 && keycode <= 93) || (keycode >= 112 && keycode <= 123) || (keycode >= 144 && keycode <= 145))) {
          jQuery('#facetly_result').html('<div class="facetly_loading">Loading Search Result .....</div>');
        }
      });
                                               
                                     
       jQuery(input).keyup(function(e) {   
        var keycode = e.which; 
        if (keycode >= 17 && keycode <= 18) {
          isctrl = false;
        }
        delay(function() {      
          jQuery(input).trigger('submit');
        }, 300);
      });
    }
    var serviceUrl= facetly.server+'/search/autocomplete';
    var params={
      "key" : facetly.key
    }
    jQuery(input).fautocomplete({
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
    if (isfacetlypage) {

    jQuery.address.state(facetly.baseurl).init(function() {
        // Initializes the plugin
        jQuery('#facetly_result .pager a, #facetly_facet a, form[facetly_form="on"]').address();
        
    }).change(function(event) {
        //console.log(event);
        // Selects the proper navigation link
        jQuery('#facetly_result .pager a').each(function() {
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
