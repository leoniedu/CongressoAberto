<?php get_header(); ?>

	<div id="content">

	<?php if (have_posts()) : ?>
	
	<!-- <span class="breadcrumbs"><a href="<?php echo get_option('home'); ?>/">Home</a> &raquo; Search</span> -->

	<h2 class="title">Resultado da  <strong>Pesquisa</strong></h2>

	<div id="archive">	

	<?php while (have_posts()) : the_post(); ?>

	<div class="clearfloat">
	<!-- <h3 class="cat_title"><?php the_category(', '); ?> &raquo;</h3> -->
	<div class="title"><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></div>
	<div class="meta">[<?php the_time('j M Y') ?> | <?php comments_popup_link('No Comment', 'One Comment', '% Comments');?> | <?php if(function_exists('the_views')) { the_views(); } ?>]</div>	
	<div class="spoiler">

	<?php	$values = get_post_custom_values("Image");
	 if (isset($values[0])) { ?> 
	<a href="<?php the_permalink() ?>" rel="bookmark" title=""> 
	 <img src="/php/timthumb.php?src=/<?php
$values = get_post_custom_values("Image"); echo $values[0]; ?>&w=150&h=0&zc=0&q=100"
alt="" class="left"  width="150px"  /></a> 
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

	<?php else : ?>
	

	<!-- <span class="breadcrumbs"><a href="<?php echo get_option('home'); ?>/">Home</a> &raquo; Not Found</span> -->
	<h2 class="title">Sua pesquisa não encontrou nenhum documento correspondente.</h2>
	

	<?php endif; ?>

	</div>




<?php get_sidebar(); ?>
<?php get_footer(); ?>
