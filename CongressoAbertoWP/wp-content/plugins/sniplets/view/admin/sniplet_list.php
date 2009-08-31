<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<ul class="sniplets" id="sniplets">
	<?php foreach ($sniplets as $item) : ?>
  	<li id="snip_<?php echo $item->id ?>"<?php if ($item->klass ()) echo ' class="'.$item->klass ().'"' ?>><?php $this->render_admin ('sniplet_item', array ('item' => $item)) ?></li>
	<?php endforeach; ?>
</ul>

<div class="sort_sniplet"<?php if (count ($sniplets) <= 1) echo ' style="display: none"'?> id="sort_sniplet">
	<img src="<?php echo $this->url () ?>/resource/sort.png" width="16" height="16" alt="Sort"/>
	
	<a id="toggle_sort_on" onclick="return toggle_sort (true,false);" href="#">sort</a>
	<a style="display: none" id="toggle_sort_off" onclick="return toggle_sort (false,'<?php echo wp_create_nonce ('sniplets-save_sort')?>');" href="#">save order</a>
</div>

<div style="clear: both"></div>
