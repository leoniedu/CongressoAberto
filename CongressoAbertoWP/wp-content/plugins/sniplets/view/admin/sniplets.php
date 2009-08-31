<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><div class="wrap">
	<?php $this->render_admin ('annoy'); ?>
	<?php screen_icon(); ?>
  <h2><?php _e ('Shared Sniplets', 'sniplets'); ?></h2>

	<?php $this->render_admin ('submenu', array ('url' => $url)); ?>

	<form method="get" action="<?php echo $this->url ($pager->url) ?>">
		<p class="search-box">
			<input type="hidden" name="page" value="<?php echo $_GET['page'] ?>"/>
			<input type="hidden" name="curpage" value="<?php echo $pager->current_page () ?>"/>

			<label for="post-search-input" class="hidden"><?php _e ('Search') ?>:</label>

			<input class="search-input" type="text" name="search" value="<?php echo htmlspecialchars (isset($_GET['search']) ? $_GET['search'] : '') ?>"/>
			<input class="button-secondary" type="submit" name="go" value="<?php _e ('Search', 'drain-hole') ?>"/>
		</p>
			
		<div id="pager" class="tablenav">
			<div class="alignleft actions">
				<?php $pager->per_page ('sniplets'); ?>

				<input type="submit" value="<?php _e('Filter'); ?>" class="button-secondary" />

				<br class="clear" />
			</div>
		
			<div class="tablenav-pages">
				<?php echo $pager->page_links (); ?>
			</div>
		</div>
	</form>

	<?php $this->render_admin ('sniplet_list', array ('sniplets' => $snips)); ?>

	<div style="clear: both"></div>
	<img id="sniplet_loading" src="<?php echo $this->url () ?>/resource/loading.gif" width="32" height="32" alt="Loading" style="display: none"/>
</div>

<div class="wrap">
  <h2><?php _e ('Add Sniplet', 'sniplets') ?></h2>
	<p><?php _e ('A Sniplet is a piece of data that is defined outside of a post.  You can insert a sniplet into a post, and assign various types of processing to it.', 'sniplets'); ?></p>
	
  <form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post" onsubmit="return add_page_sniplet(0);">
		<?php wp_nonce_field ('sniplets-add_sniplet'); ?>
  	<table width="100%" class="form-table">
  		<tr>
				<th width="100"><?php _e ('Name', 'sniplets') ?>:</th>
				<td><input class="regular-text" type="text" name="name" value="" id="sniplet_name"/></td>
			</tr>
  		<tr>
				<th valign="top"><?php _e ('Contents', 'sniplets') ?>:</th>
				<td><textarea name="contents" rows="4" style="width: 98%" id="sniplet_content"></textarea></td>
			</tr>
			<tr>
				<th><?php _e ('Placement', 'sniplets')?>:</th>
				<td>
					<select name="placement" id="sniplet_place">
						<?php echo $this->select (Snip::automatic_types ()); ?>
					</select>

					<strong><?php _e ('Template', 'sniplets')?>:</strong>
					<select name="template" id="sniplet_template">
						<?php echo $this->select (Snip::templates ()); ?>
					</select>
				</td>
			</tr>
			<tr><td></td><td><input class="button-primary" type="submit" name="add" value="<?php _e ('Add Sniplet', 'sniplets') ?>" id="add" /></td></tr>
  	</table>
  </form>
</div>

<script type="text/javascript" charset="utf-8">
	jQuery(document).ready(function()
	{ 
		setupSniplets ();
	});
</script>
