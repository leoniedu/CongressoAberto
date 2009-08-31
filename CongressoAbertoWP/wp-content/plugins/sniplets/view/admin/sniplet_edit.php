<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<table width="100%">
 	<tr>
		<th width="100"><?php _e ('Name', 'sniplets'); ?>:</th>
		<td><input type="text" name="name" value="<?php echo $sniplet->name ?>" id="snip_name_<?php echo $sniplet->id ?>"  style="width: 98%"/></td>
 	</tr>
	<tr>
		<th valign="top"><?php _e ('Contents', 'sniplets'); ?>:</th>
		<td><textarea id="snip_contents_<?php echo $sniplet->id ?>" name="contents" rows="8" style="width: 98%"><?php echo htmlspecialchars ($sniplet->contents) ?></textarea></td>
	</tr>
	<tr>
		<th><?php _e ('Template', 'sniplets')?>:</th>
		<td>
			<div class="options">
			<?php _e ('Post ID', 'sniplets'); ?>: <input size="5" id="snip_post_<?php echo $sniplet->id ?>" type="text" name="post_id" value="<?php echo $sniplet->post_id ? $sniplet->post_id : 0 ?>"/>
			</div>

			<select name="template" id="snip_template_<?php echo $sniplet->id ?>">
				<?php echo $this->select ($sniplet->templates (), $sniplet->template); ?>
			</select>
		</td>
	</tr>

	<tr id="functions_<?php echo $sniplet->id ?>"<?php if (empty ($sniplet->modules)) echo ' style="display: none"' ?>>
		<?php $this->render_admin ('function_list', array ('sniplet' => $sniplet, 'types' => SnipletFunction::get ()))?>
	</tr>
	
	<tr id="placements_<?php echo $sniplet->id ?>"<?php if (empty ($sniplet->placement)) echo ' style="display: none"' ?>>
		<?php $this->render_admin ('placement_list', array ('sniplet' => $sniplet, 'types' => $sniplet->automatic_types ()))?>
	</tr>
	
	<tr>
		<th></th>
		<td>
			<div class="options">
				<?php _e ('Advanced', 'sniplets'); ?>:
				<select id="advanced_<?php echo $sniplet->id ?>">
					<?php echo $this->select (array ('module' => 'Add module', 'placement' => 'Add placement', 'duplicate' => 'Duplicate Sniplet')); ?>
				</select>

				<?php $types = SnipletFunction::get (); $first = current (array_keys ($types)); ?>

				<input type="hidden" name="function_<?php echo $sniplet->id ?>" value="<?php echo $first ?>" id="function_<?php echo $sniplet->id ?>"/>
				<input type="hidden" name="arguments_<?php echo $sniplet->id ?>" value="" id="arguments_<?php echo $sniplet->id ?>"/>
				
				<input class="button-secondary sniplet-advanced" type="submit" name="go" value="Go"/>
			</div>

			<input class="button-primary sniplet-save" type="submit" value="<?php _e ('Save', 'sniplets'); ?>"/>
			<input class="button-secondary sniplet-cancel" type="submit" value="<?php _e ('Cancel', 'sniplets'); ?>"/>

			<img src="<?php echo $this->url () ?>/resource/progress.gif" width="50" height="16" alt="Progress" style="display: none" class="sniplet-loading"/>
		</td>
	</tr>
</table>

<div style="clear: both"></div>
<script type="text/javascript" charset="utf-8">
	setupEditSniplet ('<?php echo $this->url () ?>/ajax.php', <?php echo $sniplet->id ?>, '<?php echo wp_create_nonce ('sniplets-item_save')?>');
</script>
