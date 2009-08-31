<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>

<select name="placement[]" id="placement_item_<?php printf ('%d_%d', $id, $pos) ?>">
	<?php echo $this->select ($places, $current); ?>
</select>

<input class="button-primary sniplet-save-placement" type="submit" value="<?php _e ('Save', 'sniplets'); ?>"/>
<input class="button-secondary sniplet-cancel-placement" type="submit" value="<?php _e ('Cancel', 'sniplets'); ?>" />

<script type="text/javascript" charset="utf-8">
	jQuery('#place_<?php echo $id ?>_<?php echo $pos ?> .sniplet-save-placement').click (function (item)
	{
		jQuery('#snip_<?php echo $id ?> .sniplet-loading').show ();
		jQuery('#placements_<?php echo $id ?>').load ('<?php echo $this->url () ?>/ajax.php?cmd=save_placement&id=<?php echo $id ?>&pos=<?php echo $pos ?>&_ajax_nonce=<?php echo wp_create_nonce ('sniplets-placement_save')?>',
			{
				placement: jQuery('#placement_item_<?php echo $id ?>_<?php echo $pos ?>').val()
			}, function ()
			{
				jQuery('#snip_<?php echo $id ?> .sniplet-loading').hide ();
				setupEditFunctions (<?php echo $id ?>);
			});
		return false;
	});

	jQuery('#place_<?php echo $id ?>_<?php echo $pos ?> .sniplet-cancel-placement').click (function (item)
	{
		jQuery('#snip_<?php echo $id ?> .sniplet-loading').show ();
		jQuery('#placements_<?php echo $id ?>').load ('<?php echo $this->url () ?>/ajax.php?cmd=cancel_placement&id=<?php echo $id ?>&pos=<?php echo $pos ?>', {}, function ()
			{
				jQuery('#snip_<?php echo $id ?> .sniplet-loading').hide ();
				setupEditPlacements (<?php echo $id ?>);
			});
		return false;
	});
</script>