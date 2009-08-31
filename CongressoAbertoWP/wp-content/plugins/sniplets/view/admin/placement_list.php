<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<th <?php if (count ($sniplet->placement) > 1) echo 'valign="top"' ?>><?php _e ('Placements', 'sniplets')?>:</th>
<td>
	<ol class="modules">
		<?php if ($sniplet->placement) : ?>
			<?php foreach ($sniplet->placement AS $pos => $place) : ?>
			<li id="place_<?php printf ('%d_%d', $sniplet->id, $pos) ?>">
				<div class="options">
					<a class="sniplet-delete-placement" href="<?php echo $this->url () ?>/ajax.php?cmd=delete_placement&amp;id=<?php echo $sniplet->id ?>&amp;pos=<?php echo $pos; ?>&amp;_ajax_nonce=<?php echo wp_create_nonce ('sniplets-placement_delete')?>">
						<img src="<?php echo $this->url () ?>/resource/delete.png" width="16" height="16" alt="Delete"/>
					</a>
				</div>

				<a class="sniplet-edit-placement" href="<?php echo $this->url () ?>/ajax.php?cmd=edit_placement&amp;id=<?php echo $sniplet->id ?>&amp;pos=<?php echo $pos; ?>">
					<?php echo $sniplet->get_placement ($place); ?>
				</a>
			</li>
			<?php endforeach; ?>
		<?php endif; ?>
	</ol>
</td>