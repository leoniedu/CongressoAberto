jQuery(document).ready(function() {
	// Display initial link
	jQuery("#st-clicks-tags .inside").html('<a href="#st_click_tags" id="open_clicktags">'+stHelperClickTagsL10n.show_txt+'</a><a href="#st_click_tags" id="close_clicktags">'+stHelperClickTagsL10n.hide_txt+'</a><div class="container_clicktags"></div>');
	    		    		
	// Show click tags
	jQuery("a#open_clicktags").click(function() {				
		jQuery("#st-clicks-tags .container_clicktags")
			.fadeIn('slow')
			.load( stHelperClickTagsL10n.site_url + '/wp-admin/admin.php?st_ajax_action=click_tags', function() {
				jQuery("#st-clicks-tags .container_clicktags span").click(function() { 
					addTag(this.innerHTML);
				});
				jQuery("a#open_clicktags").hide();
				jQuery("a#close_clicktags").show();
			});	
								
		return false;
	});

	// Hide click tags
	jQuery("a#close_clicktags").click(function() {
		jQuery("#st-clicks-tags .container_clicktags").fadeOut('slow', function() {
			jQuery("a#open_clicktags").show();
			jQuery("a#close_clicktags").hide();
		});
		return false;
	});
});