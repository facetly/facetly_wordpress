jQuery(function(){
	jQuery(document).bind("facetly_loaded",function(){
		jQuery(".wrappertree:eq(0)").dynatree({
		checkbox: true,
		});
	});
	jQuery(".dynatree-checkbox").live("click",function(){
		var classname = jQuery(this).parent().find('a').attr('href');
		jQuery("a[href='"+classname+"']").trigger("click");		
	});
});
