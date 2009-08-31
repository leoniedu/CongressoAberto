<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<th <?php if (count ($sniplet->modules) > 1) echo 'valign="top"' ?>><?php _e ('Modules', 'sniplets'); ?>:</th>
<td>
	<?php if (!empty ($sniplet->modules)) : ?>
	<ol class="modules">
		<?php foreach ((array)$sniplet->modules AS $pos => $module) : ?>
			<li id="<?php printf ('snip_%d_func_%d', $sniplet->id, $pos);  ?>">
				<div class="options">
					<a class="nounder sniplet-delete-function" href="<?php echo $this->url () ?>/ajax.php?cmd=delete_function&amp;id=<?php echo $sniplet->id ?>&amp;pos=<?php echo $pos; ?>&amp;_ajax_nonce=<?php echo wp_create_nonce ('sniplets-function_delete')?>">
					  <img src="<?php echo $this->url () ?>/resource/delete.png" alt="delete" width="16" height="16"/>
					</a>
				</div>

				<a class="sniplet-edit-function" href="<?php echo $this->url (); ?>/ajax.php?cmd=edit_function&amp;id=<?php echo $sniplet->id ?>&amp;pos=<?php echo $pos ?>"><?php if (isset($types[$module['function']])) echo $types[$module['function']]; ?>
					<?php if ($module['args'] != '') : ?>
						(<?php echo htmlspecialchars ($module['args']); ?>)
					<?php endif;?>
				</a>
			</li>
		<?php endforeach; ?>
	</ol>
	<?php endif; ?>
</td>

