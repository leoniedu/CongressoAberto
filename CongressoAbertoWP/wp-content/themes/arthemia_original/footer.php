</div>  

<div id="front-popular" class="clearfloat">

<div id="recentpost" class="clearfloat">
<?php 	/* Widgetized sidebar, if you have the plugin installed. */ 					if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar(5) ) : ?> 		
<?php endif; ?></div> 		

<div id="mostcommented" class="clearfloat">
<h3>Most Commented</h3>
<ul><?php $result = $wpdb->get_results("SELECT comment_count,ID,post_title FROM $wpdb->posts ORDER BY comment_count DESC LIMIT 0 , 5"); 	foreach ($result as $topfive) { 	$postid = $topfive->ID; 	$title = $topfive->post_title; 	$commentcount = $topfive->comment_count;  	if ($commentcount != 0) { ?> 	<li><a href="<?php echo get_permalink($postid); ?>" title="<?php echo $title ?>"><?php echo $title ?></a></li> 	<?php } } ?>  
</ul>

<?php 	/* Widgetized sidebar, if you have the plugin installed. */ 					if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar(6) ) : ?> 
<?php endif; ?>
</div>

<div id="recent_comments" class="clearfloat">
<?php if (function_exists('get_most_viewed')): ?>
<h3>Most Viewed</h3>
<ul>
   <?php get_most_viewed('post',5); ?>
</ul>
<?php endif; ?>

<?php 	/* Widgetized sidebar, if you have the plugin installed. */ 					if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar(7) ) : ?> 
<?php endif; ?>
</div>
</div>

<div id="footer"> <?php wp_footer(); ?> Powered by <a href="http://wordpress.org/">WordPress</a> | <?php if ( is_user_logged_in() ) { ?> <?php wp_register('', ''); ?> | <?php } ?> <?php wp_loginout(); ?> | <a href="<?php bloginfo('rss2_url'); ?>">Entries (RSS)</a> | <a href="<?php bloginfo('comments_rss2_url'); ?>">Comments (RSS)</a> | <a href="http://michaelhutagalung.com/2008/05/arthemia-magazine-blog-wordpress-theme-released/" target="_blank">Arthemia</a> theme by <a href="http://michaelhutagalung.com" target="_blank">Michael Hutagalung</a>

<!-- <?php echo get_num_queries(); ?> queries. <?php timer_stop(1); ?> seconds. -->

</div>

</body>
</html>