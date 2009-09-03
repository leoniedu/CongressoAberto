<?php get_header(); ?>
	
	<?php if(!is_paged()) { ?>

	<div id="top" class="clearfloat">
	
		<div id="headline">
		<img src="<?php echo get_option('home'); ?>/wp-content/themes/arthemia/images/headline.png" width="75px" height="21px" alt="" />
		<?php query_posts("showposts=1&category_name=Headline"); ?>
		<?php while (have_posts()) : the_post(); ?>	
	
	<div class="title"><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></div>
	<div class="meta">[<?php the_time('j M Y') ?> | <?php comments_popup_link('No Comment', 'One Comment', '% Comments');?> | <?php if(function_exists('the_views')) { the_views(); } ?>]</div>	
	<?php $values = get_post_custom_values("Headline");?>
 	<a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title(); ?>">
<img src="<?php echo bloginfo('template_url'); ?>/scripts/timthumb.php?src=/<?php
$values = get_post_custom_values("Image"); echo $values[0]; ?>&w=300&h=275&zc=1&q=100"
alt="<?php the_title(); ?>" class="left" width="300px" height="275px"  /></a>
	<?php the_excerpt(); ?>
	<a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title(); ?>">Read the full story &raquo;</a>
	<?php endwhile; ?>
		</div>
		
	<div id="featured">
	
	<img src="<?php echo get_option('home'); ?>/wp-content/themes/arthemia/images/featured.png" width="72px" height="17px" alt="" />

	<?php query_posts("showposts=4&category_name=Featured"); $i = 1; ?>

		
      	<?php while (have_posts()) : the_post(); ?>
	<div class="clearfloat">
	<?php $values = get_post_custom_values("Image");
	if (isset($values[0])) { ?>
      <a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title(); ?>">
	<img src="<?php echo bloginfo('template_url'); ?>/scripts/timthumb.php?src=/<?php
$values = get_post_custom_values("Image"); echo $values[0]; ?>&w=100&h=65&zc=1&q=100"
alt="<?php the_title(); ?>" class="left" width="100px" height="65px"  /></a>
      <?php } ?>
	<div class="info"><a href="<?php the_permalink() ?>" rel="bookmark" class="title"><?php the_title(); ?></a>
<div class="meta">[<?php the_time('j M Y') ?> | <?php comments_popup_link('No Comment', 'One Comment', '% Comments');?> | <?php if(function_exists('the_views')) { the_views(); } ?>]</div>	
	
</div>
    	</div>
	
      <?php endwhile; ?>

	</div>

</div>	

	<div id="middle" class="clearfloat">
	<img src="<?php echo get_option('home'); ?>/wp-content/themes/arthemia/images/category.png" class="clearfloat" alt="" />
	<?php $display_categories = array(5,6,7,8,11); $i = 1;
	foreach ($display_categories as $category) { ?>

	<div id="cat-<?php echo $i; ?>" class="category">
		<?php query_posts("showposts=1&cat=$category")?>
		<span class="cat_title"><a href="<?php echo get_category_link($category);?>"><?php single_cat_title(); ?></a></span>
		<a href="<?php echo get_category_link($category);?>"><?php echo category_description($category); ?></a>
	</div>

	<?php $i++; ?>
    	<?php } ?>

	</div>

	<?php } ?>

	<div id="bottom" class="clearfloat">
		
	<div id="front-list">	
	
	<?php
      $page = (get_query_var('paged')) ? get_query_var('paged') : 1;
      query_posts("cat=-27,-28&paged=$page&posts_per_page=5"); ?>
	
	<?php while (have_posts()) : the_post(); ?>		

	<div class="clearfloat">
	<h3 class=cat_title><?php the_category(', '); ?> &raquo</h3>
	<div class="title"><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></div>
	<div class="meta">[<?php the_time('j M Y') ?> | <?php comments_popup_link('No Comment', 'One Comment', '% Comments');?> | <?php if(function_exists('the_views')) { the_views(); } ?>]</div>	
	
	<div class="spoiler">
	<?php	$values = get_post_custom_values("Image");
	if (isset($values[0])) { ?>
      <a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title(); ?>">
	<img src="<?php echo bloginfo('template_url'); ?>/scripts/timthumb.php?src=/<?php
$values = get_post_custom_values("Image"); echo $values[0]; ?>&w=150&h=150&zc=1&q=100"
alt="<?php the_title(); ?>" class="left" width="150px" height="150px"  /></a>
      <?php } ?>

	<?php the_excerpt(); ?>
	</div>

	</div>
		
      <?php endwhile; ?>

	<div class="navigation">
		<?php if(function_exists('wp_pagenavi')) { wp_pagenavi(); } 
			else { ?>

			<div class="right"><?php next_posts_link('Next Page &raquo;') ?></div>
			<div class="left"><?php previous_posts_link('&laquo; Previous Page') ?></div>
			<?php } ?>

	</div>
	
	</div>
		

	<?php get_sidebar(); ?>

	</div>	

<?php get_footer(); ?>
