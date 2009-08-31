<?php
if ( !isset(ABSPATH) || !isset(WP_UNINSTALL_PLUGIN) ) return;

delete_option('widget_wppp');
delete_option('wppp_cache');

?>
