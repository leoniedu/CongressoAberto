<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap">
	<?php $this->render_admin ('annoy'); ?>
	<?php screen_icon(); ?>
	
	<h2><?php _e ('Export', 'sniplets'); ?></h2>
	<?php $this->render_admin ('submenu', array ('url' => $url)); ?>
	<p style="clear: both"><?php _e ('This allows you to export Sniplets as XML files and then import them on another website', 'sniplets'); ?>.</p>
	
	<form action="<?php echo $this->url (); ?>/xml.php" method="post">
		<select name="post">
			<option value="0"><?php _e ('Global Sniplets', 'sniplets'); ?></option>
			
			<?php if (count ($posts) > 0) : ?>
			<?php foreach ($posts AS $post) : ?>
				<?php if ($post->id > 0) : ?>
				<option value="<?php echo $post->id ?>"><?php echo htmlspecialchars ($post->post_title); ?></option>
				<?php endif; ?>
			<?php endforeach; ?>
			<?php endif; ?>
		</select>
		<input class="button-primary" type="submit" name="export" value="Export" id="export"/>
	</form>
</div>

<div class="wrap">
	<h2><?php _e ('Import', 'sniplets')?></h2>
	<form action="<?php echo $this->url ($_SERVER['REQUEST_URI']); ?>" method="post" enctype="multipart/form-data">
		<?php wp_nonce_field ('sniplets-import'); ?>
		<table class="form-table">
			<tr>
				<th><?php _e ('Import to post', 'sniplets') ?>:</th>
				<td><input class="small-text" type="text" size="5" name="post_id" value="0"/> (<?php _e ('enter 0 for global sniplets', 'sniplets') ?>)</td>
			</tr>
			<tr>
				<th><?php _e ('File to import')?>:</th>
				<td><input type="file" name="importer" value="importer" id="importer"/></td>
			</tr>
			<tr>
				<th></th>
				<td><input class="button-primary" type="submit" name="import" value="Import" id="import"/></td>
			</tr>
		</table>
	</form>
</div>