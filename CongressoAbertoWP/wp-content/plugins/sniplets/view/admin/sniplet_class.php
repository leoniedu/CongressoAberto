<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<script type="text/javascript" charset="utf-8">
	jQuery('#snip_<?php echo $id ?>').removeClass ();
	
	<?php if ($klass) : ?>
	jQuery('#snip_<?php echo $id ?>').addClass ('<?php echo $klass ?>');
	<?php endif; ?>
</script>