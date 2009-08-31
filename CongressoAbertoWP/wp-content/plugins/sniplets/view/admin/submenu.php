<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<ul class="subsubsub">
  <li><a <?php if (!isset($_GET['sub'])) echo 'class="current"'; ?>href="<?php echo $url ?>"><?php _e ('Shared Sniplets', 'sniplets'); ?></a> |</li>
  <li><a <?php if (isset($_GET['sub']) && $_GET['sub'] == 'options') echo 'class="current"'; ?>href="<?php echo $url ?>&amp;sub=options"><?php _e ('Options', 'sniplets'); ?></a> |</li>
  <li><a <?php if (isset($_GET['sub']) && $_GET['sub'] == 'limits') echo 'class="current"'; ?>href="<?php echo $url ?>&amp;sub=limits"><?php _e ('Limits', 'sniplets'); ?></a> |</li>
  <li><a <?php if (isset($_GET['sub']) && $_GET['sub'] == 'export') echo 'class="current"'; ?>href="<?php echo $url ?>&amp;sub=export"><?php _e ('Import/Export', 'sniplets'); ?></a> |</li>
  <li><a <?php if (isset($_GET['sub']) && $_GET['sub'] == 'support') echo 'class="current"'; ?>href="<?php echo $url ?>&amp;sub=support"><?php _e ('Support', 'sniplets'); ?></a></li>
</ul>