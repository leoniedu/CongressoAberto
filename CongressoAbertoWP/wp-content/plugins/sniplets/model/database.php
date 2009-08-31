<?php

class SN_Database
{
	function install ()
	{
		global $wpdb;
		
		// Does a sniplets table exist?
		if ($wpdb->get_results ("SHOW TABLES LIKE '{$wpdb->prefix}sniplets'") > 0)
			$this->upgrade ();

		$wpdb->query ("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}sniplets (
		  `id` int(11) NOT NULL auto_increment,
	  	`post_id` int(10) unsigned default NULL,
			`position` int(10) unsigned NOT NULL,
		  `name` varchar(50) NOT NULL default '',
		  `contents` text NOT NULL,
		  `modules` mediumtext,
		  `placement` mediumtext,
		  `template` varchar(20) default NULL,
		  `status` enum('enabled','disabled') NOT NULL default 'enabled',
		  PRIMARY KEY  (`id`),
		  KEY `name` (`name`),
		  KEY `post_id` (`post_id`)
		)");
	}
	
	function remove ($plugin)
	{
		global $wpdb;
		
		$wpdb->query ("DROP TABLE {$wpdb->prefix}sniplets");
		
		delete_option ('sniplets_version');
		delete_option ('sniplets');
		delete_option ('widgets_config_sniplet-1');
		delete_option ('widgets_config_sniplet-2');
		delete_option ('widgets_config_sniplet-3');
		delete_option ('widgets_config_sniplet-4');
		delete_option ('widgets_config_sniplet-5');
		
		// Deactivate the plugin
		$current = get_option('active_plugins');
		array_splice ($current, array_search (basename (dirname ($plugin)).'/'.basename ($plugin), $current), 1 );
		update_option('active_plugins', $current);
	}
	
	function upgrade ($current, $desired)
	{
		if (get_option ('sniplets') === false)
			$this->upgrade_from_0 ();
		else
		{
			if ($current === false)
			{
				$this->upgrade_from_1 ();
				$this->upgrade_from_2 ();
				$this->upgrade_from_3 ();
			}
			else if ($current == 2)
			{
				$this->upgrade_from_2 ();
				$this->upgrade_from_3 ();
			}
			else if ($current == 3)
				$this->upgrade_from_3 ();
		}
		
		update_option ('sniplets_version', $desired);
	}
	
	function upgrade_from_0 ()
	{
		global $wpdb;
		
		$wpdb->query ("ALTER TABLE {$wpdb->prefix}sniplets DROP cache_expiry;");
		$wpdb->query ("ALTER TABLE {$wpdb->prefix}sniplets DROP cache_updated;");
		$wpdb->query ("ALTER TABLE {$wpdb->prefix}sniplets DROP cache;");
		$wpdb->query ("ALTER TABLE {$wpdb->prefix}sniplets DROP processor;");
		$wpdb->query ("ALTER TABLE {$wpdb->prefix}sniplets ADD `functions` mediumtext;");
		$wpdb->query ("ALTER TABLE {$wpdb->prefix}sniplets ADD `post_id` int UNSIGNED DEFAULT NULL ;");
		$wpdb->query ("ALTER TABLE {$wpdb->prefix}sniplets ADD INDEX  (`post_id`);");
	}
	
	function upgrade_from_1 ()
	{
		global $wpdb;
		
		$wpdb->query ("ALTER TABLE {$wpdb->prefix}sniplets ADD `position` int UNSIGNED NOT NULL;");
		$wpdb->query ("ALTER TABLE {$wpdb->prefix}sniplets ADD `placement` VARCHAR(20) DEFAULT NULL;");
		$wpdb->query ("ALTER TABLE {$wpdb->prefix}sniplets CHANGE `functions` `modules` mediumtext;");
		$wpdb->query ("ALTER TABLE {$wpdb->prefix}sniplets ADD `template` VARCHAR(20) DEFAULT NULL;");
	}
	
	function upgrade_from_2 ()
	{
		global $wpdb;
		
		$wpdb->query ("ALTER TABLE `{$wpdb->prefix}sniplets` ADD `status` enum('enabled','disabled') NOT NULL DEFAULT 'enabled'");
		$wpdb->query ("ALTER TABLE `{$wpdb->prefix}sniplets` CHANGE `placement` `placement` mediumtext DEFAULT NULL ;");
	}
	
	function upgrade_from_3 ()
	{
		// Change placements
		global $wpdb;
		
		foreach (array ('root', 'beforeall', 'afterall', 'beforefirst', 'afterfirst', 'comment', 'header', 'footer', 'root', 'more') AS $thing)
			$wpdb->query ("UPDATE {$wpdb->prefix}sniplets SET placement=REPLACE(placement,'$thing','post/$thing')");
	}
}
?>