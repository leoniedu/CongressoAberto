<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="options">
	<img src="<?php echo $this->url () ?>/resource/progress.gif" width="50" height="16" alt="Progress" style="display: none" class="sniplet-loading"/>
	
	<span id="snip_type_<?php echo $item->id ?>">
	<?php if ($item->placement) : ?>
		<?php $place = $item->automatic_types ($item->placement); echo substr ($place, 0, 30).(strlen ($place) > 30 ? '...' : '')?>
	<?php endif; ?>
	</span>
	
  <a class="sniplet-toggle" href="<?php echo $this->url () ?>/ajax.php?cmd=toggle&amp;id=<?php echo $item->id ?>&amp;_ajax_nonce=<?php echo wp_create_nonce ('sniplets-item_toggle')?>">
  <?php if ($item->status == 'enabled'): ?>
  	<img src="<?php echo $this->url () ?>/resource/disable.png" width="16" height="16" alt="Disable" title="<?php _e ('Disable sniplet', 'sniplet'); ?>"/>
  <?php else : ?>
    <img src="<?php echo $this->url () ?>/resource/enable.png" width="16" height="16" alt="Enable" title="<?php _e ('Enable sniplet', 'sniplet') ?>"/>
  <?php endif; ?>
  </a>

  <a class="nounder sniplet-delete" href="<?php echo $this->url () ?>/ajax.php?cmd=delete&amp;id=<?php echo $item->id ?>&amp;_ajax_nonce=<?php echo wp_create_nonce ('sniplets-item_delete')?>">
    <img src="<?php echo $this->url () ?>/resource/delete.png" alt="delete" width="16" height="16"/>
  </a>
</div>

<a href="<?php echo $this->url (); ?>/ajax.php?cmd=edit&amp;id=<?php echo $item->id ?>" class="sniplet-edit"><?php echo htmlspecialchars ($item->name) ?></a> -

<code><?php echo $item->content () ?></code>
