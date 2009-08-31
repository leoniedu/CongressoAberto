<div id="sidebar">

<div id="sidebar-ads">
<img src="<?php echo get_option('home'); ?>/wp-content/themes/arthemia/images/banners/square.jpg" alt="" width="300px" height="250px" />

</div>
  
<div id="sidebar-top"> 
<?php 	/* Widgetized sidebar, if you have the plugin installed. */ 					if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar(1) ) : ?>

<?php endif; ?>
</div>


<div id="sidebar-middle" class="clearfloat"> 
<div id="sidebar-left">
<?php 	/* Widgetized sidebar, if you have the plugin installed. */ 					if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar(2) ) : ?> 		
<?php endif; ?> 
<ul><?php wp_list_bookmarks('categorize=0&category=17&title_li=0&show_images=0&show_description=0&orderby=name'); ?></ul>
</div>  

<div id="sidebar-right">
<?php 	/* Widgetized sidebar, if you have the plugin installed. */ 					if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar(3) ) : ?> 		

<?php endif; ?>
</div> 

</div>

<div id="sidebar-bottom"> 
<?php 	/* Widgetized sidebar, if you have the plugin installed. */ 					if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar(4) ) : ?> 		
<?php endif; ?> </div>   


</div>