<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><div class="wrap">
	<?php $this->render_admin ('annoy'); ?>
	<?php screen_icon(); ?>
	<h2><?php _e ('Sniplets Options', 'sniplets'); ?></h2>

	<?php $this->render_admin ('submenu', array ('url' => $url)); ?>

	<form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post" accept-charset="utf-8" style="clear: both">
		<?php wp_nonce_field ('sniplets-options'); ?>
		<table class="form-table">
			<tr>
				<th><?php _e ('Sniplet content', 'sniplets'); ?>:</th>
				<td>
					<input type="checkbox" name="content"<?php if ($options['content']) echo ' checked="checked"' ?>/>
					<span class="sub"><?php _e ('Allow Sniplet content to be changed in a post (i.e. <code>[snip name]new content[/snip]</code>)', 'sniplets'); ?></span>
				</td>
			</tr>
			<tr>
				<th><?php _e ('Execute PHP in posts', 'sniplets'); ?>:</th>
				<td>
					<input type="checkbox" name="php"<?php if ($options['php']) echo ' checked="checked"' ?>/>
					<span class="sub"><?php _e ('Automatically execute any code between <code>&lt;?php ... ?&gt;</code>', 'sniplets'); ?></span>
				</td>
			</tr>
			<tr>
				<th valign="top"><?php _e ('Module search path', 'sniplets'); ?>:</th>
				<td>
					<input class="regular-text" type="text" name="search" value="<?php echo htmlspecialchars ($options['search']) ?>" size="50"/><br/>
					<span class="sub"><?php _e ('This will be used as an additional search path for custom modules', 'sniplets'); ?></span>
				</td>
			</tr>
			<tr>
				<th valign="top"><?php _e ('Exclude pages', 'sniplets'); ?>:</th>
				<td>
					<input class="regular-text" type="text"  size="50" name="exclude" value="<?php echo $options['exclude'] ?>" id="exclude"/><br/>
					<span class="sub"><?php _e ('Specify page IDs (comma separated) where only page-specific Sniplets will be displayed (i.e. no placements)', 'sniplets'); ?></span>
				</td>
			</tr>
			<tr>
				<th valign="top"><?php _e ('Custom places', 'sniplets'); ?>:</th>
				<td>
					<textarea name="custom_places" rows="4" cols="45"><?php echo htmlspecialchars ($options['custom_places']); ?></textarea><br/>
					<span class="sub"><?php _e ('Put each place on a new line', 'sniplets'); ?></span>
				</td>
			</tr>
			<tr>
				<th valign="top"><?php _e ('Custom templates', 'sniplets'); ?>:</th>
				<td>
					<textarea name="custom_templates" rows="4" cols="45"><?php echo htmlspecialchars ($options['custom_templates']); ?></textarea><br/>
					<span class="sub"><?php _e ('Put each template on a new line', 'sniplets'); ?></span>
				</td>
			</tr>
			<tr>
				<td></td>
				<td><input class="button-primary" type="submit" name="save" value="<?php _e ('Save', 'sniplets'); ?>" id="Save"/>
				</td>
			</tr>
		</table>
	</form>
</div>

<div class="wrap">
	<h2><?php _e ('Delete Sniplets', 'sniplets'); ?></h2>
	
	<p><?php _e ('This will delete all Sniplets data and remove the plugin.', 'sniplets'); ?></p>
	<form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post" accept-charset="utf-8">
		<?php wp_nonce_field ('sniplets-delete_plugin'); ?>
		<input class="button-primary" type="submit" name="delete" value="<?php _e ('Delete', 'sniplets'); ?>"/>
	</form>
</div>