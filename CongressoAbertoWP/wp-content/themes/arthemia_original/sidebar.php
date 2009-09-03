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

<h3>Blogroll</h3>
<ul>
<li><a href="http://colorlabsproject.com">Colorlabs Project</a></li>
<li><a href="http://michaelhutagalung.com">Michael Jubel</a></li>
<li><a href="http://www.hannasyalala.com">Hanna Siahaan</a></li>
<li><a href="http://majarimagazine.com">Majari Magazine</a></li>
<li><a href="http://bloggerzine.majarikanayakan.com">The Bloggerzine</a></li>
<li><a href="http://www.majarikanayakan.com">Majari Kanayakan</a></li>
</ul>
<?php endif; ?>
</div> 

</div>

<div id="sidebar-bottom"> 
<?php 	/* Widgetized sidebar, if you have the plugin installed. */ 					if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar(4) ) : ?> 		
<?php endif; ?> </div>   


</div>