<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<?php 
global $post;
if ($post && isset ($post->ID) && $post->ID > 0) : ?>

<div class="meta-box-sortables ui-sortable" style="position: relative;">
	<div class="postbox snipbox">
		<h3><?php _e ('Sniplets', 'sniplets') ?></h3>
		
		<?php $this->render_admin ('post-form-inside', array ('sniplets' => $sniplets)); ?>
	</div>
</div>

<?php endif; ?>