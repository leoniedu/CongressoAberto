<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><?php $modules = SnipletFunction::get (); ?>

<select name="function" id="function_<?php printf ('%d_%d', $id, $pos) ?>">
<?php foreach ($modules AS $file => $name) : ?>
 <option value="<?php echo $file ?>"<?php if ($function['function'] == $file) echo ' selected="selected"' ?>><?php echo htmlspecialchars ($name) ?></option>
<?php endforeach; ?>
 </select>

<input style="width: 50%" type="text" name="arg_<?php printf ('%d_%d', $id, $pos) ?>" id="arg_<?php printf ('%d_%d', $id, $pos) ?>" value="<?php echo $function['args'] ?>"/>

<input class="button-primary sniplet-save-function" type="submit" value="<?php _e ('Save', 'sniplets'); ?>"/>
<input class="button-secondary sniplet-cancel-function" type="submit" value="<?php _e ('Cancel', 'sniplets'); ?>" />

<script type="text/javascript" charset="utf-8">
	jQuery('#snip_<?php echo $id ?>_func_<?php echo $pos ?> .sniplet-save-function').click (function (item)
	{
		jQuery('#snip_<?php echo $id ?> .sniplet-loading').show ();
		jQuery('#functions_<?php echo $id ?>').load ('<?php echo $this->url () ?>/ajax.php?cmd=save_function&id=<?php echo $id ?>&pos=<?php echo $pos ?>&_ajax_nonce=<?php echo wp_create_nonce ('sniplets-function_save')?>',
			{
				module: jQuery('#function_<?php echo $id ?>_<?php echo $pos ?>').val(),
				arguments: jQuery('#arg_<?php echo $id ?>_<?php echo $pos ?>').val()
			}, function ()
			{
				jQuery('#snip_<?php echo $id ?> .sniplet-loading').hide ();
				setupEditFunctions (<?php echo $id ?>);
			});
		return false;
	});

	jQuery('#snip_<?php echo $id ?>_func_<?php echo $pos ?> .sniplet-cancel-function').click (function (item)
	{
		jQuery('#snip_<?php echo $id ?> .sniplet-loading').show ();
		jQuery('#functions_<?php echo $id ?>').load ('<?php echo $this->url () ?>/ajax.php?cmd=cancel_function&id=<?php echo $id ?>&pos=<?php echo $pos ?>', {}, function ()
			{
				jQuery('#snip_<?php echo $id ?> .sniplet-loading').hide ();
				setupEditFunctions (<?php echo $id ?>);
			});
		return false;
	});
</script>