<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><div class="wrap">
	<?php $this->render_admin ('annoy'); ?>
	<?php screen_icon(); ?>
	<h2><?php _e ('Placement Limits', 'sniplets'); ?></h2>
	
	<?php $this->render_admin ('submenu', array ('url' => $url)); ?>
	
	<p style="clear: both"><?php _e ('Placement limits allow you to create a limit to the number of sniplets that can appear in a particular place.', 'sniplets'); ?></p>
	
	<form action="<?php echo $this->url ($_SERVER['REQUEST_URI']) ?>" method="post" accept-charset="utf-8">
		<?php wp_nonce_field ('sniplets-limits'); ?>
		<table class="form-table">
			<?php if (is_array ($places)) : ?>
			<?php foreach ($places as $key => $value): ?>
				<?php foreach ($value AS $sub => $area) : ?>
				<tr>
					<th><?php echo $key.'/'.$area ?>:</th>
					<td>
						<input class="small-text" size="4" type="text" name="limits[<?php echo strtolower ($sub) ?>]" value="<?php echo isset($options['limits'][strtolower ($sub)]) ? $options['limits'][strtolower ($sub)] : ''?>"/>
						<label><span class="sub"><?php _e ('random', 'sniplets'); ?></span> <input type="checkbox" name="rand[<?php echo strtolower ($sub)?>]" value="<?php echo $sub ?>"<?php $this->checked ($options['rand'], strtolower ($sub)) ?>/></label>
					</td>
				</tr>
				<?php endforeach; ?>
			<?php endforeach ?>
			<?php endif; ?>
		</table>
		<input class="button-primary" type="submit" name="save" value="Save"/>
	</form>
</div>