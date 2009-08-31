jQuery(document).ready( function() {
	jQuery('#printOptions').tabs({
		cookie: { expires: 30 },
		fx: { opacity: 'toggle' } 
	});
	
	jQuery('input#cloud_max_color')
		.ready(function(){cloudMaxColor()})
		.click(function(){cloudMaxColor()})
		.blur(function(){cloudMaxColor()})
		.change(function(){cloudMaxColor()})
		.focus(function(){cloudMaxColor()});
	
	jQuery('input#cloud_min_color')
		.ready(function(){cloudMinColor()})
		.click(function(){cloudMinColor()})
		.blur(function(){cloudMinColor()})
		.change(function(){cloudMinColor()})
		.focus(function(){cloudMinColor()});			
});

function cloudMaxColor() {
	jQuery('div.cloud_max_color').css({
		backgroundColor: jQuery('input#cloud_max_color').val()
	});
}
						
function cloudMinColor() {
	jQuery('div.cloud_min_color').css({
		backgroundColor: jQuery('input#cloud_min_color').val()
	});
}	