<?php get_header(); ?>

	<div id="content">
	
	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

	<div class="post" id="post-<?php the_ID(); ?>">
	
	<span class="breadcrumbs"><a href="<?php echo get_option('home'); ?>/">Home</a> &raquo; <?php the_category(', ') ?></span>
	
	<h2 class="title"><?php the_title(); ?></h2>
	
	<div id="stats">
<span><?php the_time('j F Y') ?></span>
<span><?php if(function_exists('the_views')) { the_views(); } ?></span>
<span><?php comments_number('No Comment', 'One Comment', '% Comments' );?></span></div>


	<div class="entry clearfloat">
	
	<?php the_content('Read the rest of this entry &raquo;'); ?>

	<?php wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
	
	</div>

	<div id="tools">
<div style="float:left;"><a href=" http://digg.com/submit?phase=2&url= <?php the_permalink();?>&title=<?php the_title();?>" target="_blank"><img src="<?php echo get_option('home'); ?>/wp-content/themes/arthemia/images/sociable/digg.png" title="Digg this!"></a><a href=" http://del.icio.us/post?v=4&noui&jump=close
&url=<?php the_permalink();?>
&title=<?php the_title();?>" target="_blank"><img src="<?php echo get_option('home'); ?>/wp-content/themes/arthemia/images/sociable/delicious.png" title="Add to del.icio.us!"></a><a href="http://www.stumbleupon.com/submit?url=<?php the_permalink(); ?>&title=<?php the_title(); ?>" target="_blank"><img src="<?php echo get_option('home'); ?>/wp-content/themes/arthemia/images/sociable/stumbleupon.png" title="Stumble this!"></a><a href=" http://technorati.com/faves?add=<?php echo get_option('home'); ?>" target="_blank"><img src="<?php echo get_option('home'); ?>/wp-content/themes/arthemia/images/sociable/technorati.png" title="Add to Techorati!"></a><a href="http://www.facebook.com/sharer.php?u=<?php the_permalink();?>&t=<?php the_title();?>" target="_blank"><img src="<?php echo get_option('home'); ?>/wp-content/themes/arthemia/images/sociable/facebook.png" title="Share on Facebook!"></a><a href=" http://www.newsvine.com/_tools/seed&save? u=<?php the_permalink();?>&h=<?php the_title();?>" target="_blank"><img src="<?php echo get_option('home'); ?>/wp-content/themes/arthemia/images/sociable/newsvine.png" title="Seed Newsvine!"></a><a href=" http://reddit.com/submit?url=
<?php the_permalink();?>&title=<?php the_title();?>" target="_blank"><img src="<?php echo get_option('home'); ?>/wp-content/themes/arthemia/images/sociable/reddit.png" title="Reddit!"></a><a href=" http://myweb.yahoo.com/myresults/bookmarklet? t=<?php the_title();?>&u=<?php the_permalink();?>&ei=UTF" target="_blank"><img src="<?php echo get_option('home'); ?>/wp-content/themes/arthemia/images/sociable/yahoomyweb.png" title="Add to Yahoo!"></a>
</div>

	<div style="float:right;display:block;"><?php if(function_exists('the_ratings')) { the_ratings(); } ?></div>
	</div>

	</div>
	
	<div id="comments">
	<?php comments_template(); ?>
	</div>

	<?php endwhile; else: ?>

	<p>Sorry, no posts matched your criteria.</p>

	<?php endif; ?>

	</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>