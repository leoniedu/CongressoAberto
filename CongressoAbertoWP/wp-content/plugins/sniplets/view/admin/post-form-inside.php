<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<?php 
global $post;
if ($post && isset ($post->ID) && $post->ID > 0) : ?>

	<div class="inside" id="headspacestuff">
		<?php $this->render_admin ('sniplet_list', array ('sniplets' => $sniplets)); ?>
		
		<?php _e ('Name', 'sniplets'); ?>: <input type="text" name="sniplet_name" size="30" value="" id="sniplet_name"/>
		<input type="hidden" name="sniplet_content" value="" id="sniplet_content"/>
		<input type="hidden" name="sniplet_template" value="" id="sniplet_template"/>
		
		<?php _e ('Placement', 'sniplets'); ?>:
		<select name="placement" id="sniplet_place">
			<?php echo $this->select (Snip::automatic_types ()); ?>
		</select>

		<input class="button-secondary" type="submit" name="add_note" value="Add Sniplet" id="add_sniplet" onclick="return add_page_sniplet(<?php echo $post->ID ?>);"/>
				
		<img src="<?php echo $this->url () ?>/resource/small.gif" width="16" height="16" alt="Loading" id="sniplet_loading" style="display: none"/>
	</div>
		
<script type="text/javascript" charset="utf-8">
	jQuery(document).ready(function()
	{ 
		setupSniplets ();
		jQuery('#sniplet_name').keypress (function(event)
		{
			if (event.which == 13)
			{
				jQuery('#add_sniplet').trigger ('click');
				return false;
			}
		})
	});
</script>
<br/>
<?php endif; ?>