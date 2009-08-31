<?php
/*
Plugin Name: Sniplets
Plugin URI: http://urbangiraffe.com/plugins/sniplets/
Description: Small 'sniplets' of information that can be embedded within posts
Author: John Godley
Version: 1.4.4
Author URI: http://urbangiraffe.com/
============================================================================================================
0.1    - Initial release
0.2    - Use correct table prefix
0.3    - Add display if logged in/out
0.3.1  - Add a template tag the_sniplet
1.0.0  - Big update.  Introduce arguments to sniplets, allow functions to be stacked.  Allow content in post
         to override sniplet
1.0.3  - Fix database creation routine
1.1    - Merge Sniplets with Page Notes, allow automatic placement and templates
1.1.1  - Fix PHP5 bugs, add inline loading graphic, fix TinyMCE problem, stop box appearing on new posts
1.1.2  - Fix missing module arguments
1.1.3  - Template tags return the code, not echo
1.1.4  - Fix the 'include file/URL' module for URLs
1.2    - Placement limits, quicktag, IE support, ordering
1.2.1  - Disable sniplets, sniplet exclusion, multiple placements, copy, move, nesting
1.2.2  - Make Sniplets work outside loop
1.2.3  - Security update
1.2.4  - Fix #26
1.2.5  - Fix #84, #107
1.3    - WordPress 2.5 edition
1.3.1  - Import/export
1.3.2  - WP 2.6
1.3.3  - Add 'all' placement
1.3.4  - Fix #289
1.3.5  - Fix to the_sniplet
1.3.6  - Add sniplet_transform_text function
1.3.7  - Bring back missing sniplets, fix limits
1.3.8  - Another fix to limits
1.3.9  - WP 2.7 metaboxes
1.3.10 - Fix menu in WP 2.7
1.4    - 2.7 styling, nonces, jQuery
1.4.1  - Add #347
1.4.2  - Fix issue with archive pages
1.4.3  - Update missing localisations
1.4.4  - WP 2.8 compat, fix various bugs and style issues
============================================================================================================
This software is provided "as is" and any express or implied warranties, including, but not limited to, the
implied warranties of merchantibility and fitness for a particular purpose are disclaimed. In no event shall
the copyright owner or contributors be liable for any direct, indirect, incidental, special, exemplary, or
consequential damages (including, but not limited to, procurement of substitute goods or services; loss of
use, data, or profits; or business interruption) however caused and on any theory of liability, whether in
contract, strict liability, or tort (including negligence or otherwise) arising in any way out of the use of
this software, even if advised of the possibility of such damage.
============================================================================================================
*/

include (dirname (__FILE__).'/plugin.php');
include (dirname (__FILE__).'/model/pager.php');
include (dirname (__FILE__).'/model/widget.php');

define ('SNIPLETS', true);

/**
 * Sniplets class provides the Sniplets plugin functionality
 *
 * @package Sniplets
 **/

class Sniplets extends Sniplets_Plugin
{
	/**
	 * Constructor registers all WordPress hooks
	 *
	 * @return void
	 **/

	var $sniplets = array ();
	var $widget   = null;
	var $loaded   = false;

  function Sniplets ()
  {
		require_once (dirname (__FILE__).'/model/sniplets_class.php');

		$this->register_plugin ('sniplets', __FILE__);

	  if (is_admin ())
	  {
			$this->register_activation (__FILE__);

			$this->add_action( 'wp_print_scripts' );
			$this->add_action( 'admin_head', 'wp_print_styles' );
			$this->add_action( 'admin_print_styles', 'wp_print_styles' );
			$this->add_filter( 'contextual_help', 'contextual_help', 10, 2 );
			$this->add_action( 'admin_menu' );
			$this->add_action( 'admin_footer' );
			
			$this->register_plugin_settings( __FILE__ );
	  }
		else
		{
			$this->add_filter ('the_posts');
			$this->add_action ('the_sniplet');
			$this->add_action ('the_sniplet_place', 'the_sniplet_place', 1, 3);

			// Do we need to process PHP
			$options = $this->get_options ();
			if ($options['php'])
			{
				$this->add_filter ('the_content', 'run_php');
				$this->add_filter ('the_excerpt', 'run_php');
			}

			$this->add_filter ('the_real_content', 'the_real_content', 10, 2);
		}

		$this->widget = new Sniplet_Widget ('Sniplet', 10);
  }

	
	function plugin_settings ($links)	{
		$settings_link = '<a href="tools.php?page='.basename( __FILE__ ).'">'.__('Settings', 'sniplets').'</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	function version ()
	{
		$plugin_data = implode ('', file (__FILE__));
		
		if (preg_match ('|Version:(.*)|i', $plugin_data, $version))
			return trim ($version[1]);
		return '';
	}

	function the_posts ($posts)
	{
		if ($this->loaded == false)
		{
			if ((is_single () || is_page ()) && !is_404 ())
				$sniplets = Snip::get_for (array ($posts[0]), 'post', true);
			else if (is_front_page ())
				$sniplets = Snip::get_for ($posts, 'home', true);
			else if (is_archive ())
			 	$sniplets = Snip::get_for ($posts, 'archive', true);
			else if (is_feed ())
			 	$sniplets = Snip::get_for ($posts, 'rss', true);
			else
				$sniplets = Snip::get_shared ();

			if (count ($sniplets) > 0)
			{
				$options = $this->get_options ();

				// Run through the sniplets and decide what needs to be done
				foreach ($sniplets AS $snip)
				{
					if ($snip->placement)
					{
						foreach ($snip->placement AS $place)
						{
							$parts = explode ('/', $place);

							if (count ($parts) == 2)
							{
								if ($parts[1] == 'root')
									echo $snip->process ($options['search']);   // do it now
								else if ($parts[1])
								{
									$this->sniplets[$parts[1]][] = $snip;
									$this->sniplets[$place][] = $snip;
								}

								$this->place = $parts[0];
							}
						}
					}

					$this->sniplets['manual'][$snip->name][] = $snip;
				}

				// Setup any WP hooks
				if (isset ($this->sniplets['header']))
					$this->add_action ('wp_head');

				if (isset ($this->sniplets['footer']))
					$this->add_action ('wp_footer');

				if (isset ($this->sniplets['comment']))
					$this->add_action ('comment_form');

				if (is_feed ())
				{
					$this->add_action ('the_content_rss', 'the_content', 1);
					$this->add_action ('the_excerpt_rss', 'the_content', 1);
				}
				else
				{
					$this->add_action ('the_content', 'the_content', 1);
					$this->add_action ('the_excerpt', 'the_content', 1);
				}
			}

			$this->loaded = true;
		}

		return $posts;
	}

	function is_excluded ($sniplet)
	{
		if (is_single () || is_page () || is_feed () || is_archive ())
		{
			global $post;

			if ($sniplet->post_id > 0 && $post->ID != $sniplet->post_id)
				return true;
				
			$options = $this->get_options ();
			$parts = explode (',', $options['exclude']);

			if (count ($parts) > 0)
			{
				if (in_array ($post->ID, $parts))
					return true;
			}
		}

		return false;
	}

	/**
	 * Called when the plugin is activated, and installs database tables
	 *
	 * @return void
	 **/

	function activate ()
	{
		include (dirname (__FILE__).'/model/database.php');

		$db = new SN_Database;

		$db->upgrade (2);
		$db->install ();

		$options = $this->get_options ();
		update_option ('sniplets', $options);
	}


	function upgrade ()
	{
		if (get_option ('sniplets_version') != 4)
		{
			include (dirname (__FILE__).'/model/database.php');

			$db = new SN_Database;
			$db->upgrade (get_option ('sniplets_version'), 4);
		}
	}


	/**
	 * Inserts JS & CSS into administration pages
	 *
	 * @return void
	 **/

	function contextual_help ($help, $screen)
	{
		if ($screen == 'tools_page_sniplets')
		{
			$help .= '<h5>' . __('Sniplets Help') . '</h5><div class="metabox-prefs">';
			$help .= '<a href="http://urbangiraffe.com/plugins/sniplets/">'.__ ('Sniplets Documentation', 'sniplets').'</a><br/>';
			$help .= '<a href="http://urbangiraffe.com/support/forum/sniplets">'.__ ('Sniplets Support Forum', 'sniplets').'</a><br/>';
			$help .= '<a href="http://urbangiraffe.com/tracker/projects/sniplets/issues?set_filter=1&tracker_id=1">'.__ ('Sniplets Bug Tracker', 'sniplets').'</a><br/>';
			$help .= '<a href="http://urbangiraffe.com/plugins/sniplets/faq/">'.__ ('Sniplets FAQ', 'sniplets').'</a><br/>';
			$help .= __ ('Please read the documentation and FAQ, and check the bug tracker, before asking a question.', 'sniplets');
			$help .= '</div>';
		}
		
		return $help;
	}
	
	function wp_print_scripts() {
		if ( (isset($_GET['page']) && $_GET['page'] == 'sniplets.php') || strpos ($_SERVER['REQUEST_URI'], 'page.php') || strpos ($_SERVER['REQUEST_URI'], 'post.php')) {
			wp_enqueue_script ('sniplets', $this->url ().'/resource/admin.js', array ('jquery', 'jquery-ui-sortable'), $this->version ());
			
			$this->render_admin( 'head' );
		}
	}
	
	function wp_print_styles() {
		if ( (isset($_GET['page']) && $_GET['page'] == 'sniplets.php') || strpos ($_SERVER['REQUEST_URI'], 'page.php') || strpos ($_SERVER['REQUEST_URI'], 'post.php')) {
			echo '<link rel="stylesheet" href="'.$this->url ().'/resource/admin.css" type="text/css" media="screen" title="no title" charset="utf-8"/>';

			if (!function_exists ('wp_enqueue_style'))
				echo '<style type="text/css" media="screen">
				.subsubsub {
					list-style: none;
					margin: 8px 0 5px;
					padding: 0;
					white-space: nowrap;
					font-size: 11px;
					float: left;
				}
				.subsubsub li {
					display: inline;
					margin: 0;
					padding: 0;
				}
				</style>';
		}
	}


	/**
	 * Injects a Sniplets menu into administration pages
	 *
	 * @return void
	 **/

  function admin_menu ()
	{
  	add_management_page (__ ("Sniplets", 'sniplets'), __ ("Sniplets", 'sniplets'), "administrator", basename (__FILE__), array ($this, "admin_screen"));

		add_meta_box ('snipletstuff', __ ('Sniplets', 'headspace'), array (&$this, 'metabox'), 'post', 'normal', 'high');
		add_meta_box ('snipletstuff', __ ('Sniplets', 'headspace'), array (&$this, 'metabox'), 'page', 'normal', 'high');
	}

	/**
	 * Displays the nice animated support logo
	 *
	 * @return void
	 **/
	function admin_footer() {
		if ( isset($_GET['page']) && $_GET['page'] == basename( __FILE__ ) ) {
			$options = $this->get_options();

			if ( !$options['support'] ) {
?>
<script type="text/javascript" charset="utf-8">
	jQuery(function() {
		jQuery('#support-annoy').animate( { opacity: 0.2, backgroundColor: 'red' } ).animate( { opacity: 1, backgroundColor: 'yellow' });
	});
</script>
<?php
			}
		}
	}
		
	/**
	 * Displays the appropriate administration pages
	 *
	 * @return void
	 **/

	function admin_screen ()
	{
		$this->upgrade ();

		$sub = isset ($_GET['sub']) ? $_GET['sub'] : '';
	  $url = explode ('&', $_SERVER['REQUEST_URI']);
	  $url = $url[0];
	
		if (isset($_GET['sub'])) {
			if ($_GET['sub'] == 'options')
				return $this->admin_options ($url);
			else if ($_GET['sub'] == 'limits')
				return $this->admin_limits ($url);
			else if ($_GET['sub'] == 'export')
				return $this->admin_export ($url);
			else if ($_GET['sub'] == 'support')
				$this->render_admin('support', array( 'url' => $url ) );
		}
	  else
			return $this->admin_sniplets ($url);
	}

	function admin_export ($url)
	{
		global $wpdb;

		if (isset ($_POST['import']) && is_uploaded_file ($_FILES['importer']['tmp_name']) && check_admin_referer ('sniplets-import'))
		{
			// Import the XML file
			$xml = simplexml_load_file ($_FILES['importer']['tmp_name']);
			if ($xml)
			{
				if (count ($xml->sniplet) > 0)
				{
					foreach ($xml->sniplet AS $sniplet)
					{
							$snip = new Snip;

							$snip->name      = (string)$sniplet['name'];
							$snip->placement = explode ('|', (string)$sniplet->placement[0]);
							$snip->template  = (string)$sniplet['template'];
							$snip->modules   = unserialize ((string)$sniplet->modules[0]);
							$snip->contents  = (string)$sniplet->content[0];

							$snip->create (intval ($_POST['post_id']));
					}
				}
			}
			else
				$this->render_error ('Sorry, but that file could not be loaded');
		}

		// Get list of posts with sniplets
		$snipposts = $wpdb->get_results ("select {$wpdb->posts}.id,{$wpdb->posts}.post_title from {$wpdb->prefix}sniplets LEFT JOIN {$wpdb->posts} ON {$wpdb->posts}.ID={$wpdb->prefix}sniplets.post_id GROUP BY {$wpdb->prefix}sniplets.post_id ORDER BY {$wpdb->posts}.post_name");
		$this->render_admin ('export', array ('posts' => $snipposts, 'url' => $url));
	}

	function admin_limits ($url)
	{
		if (isset ($_POST['save']) && check_admin_referer ('sniplets-limits'))
		{
			$_POST = stripslashes_deep ($_POST);

			$options = $this->get_options ();

			$options['limits'] = $_POST['limits'];
			$options['rand']   = $_POST['rand'];

			update_option ('sniplets', $options);
			$this->render_message (__ ('Your limits were successfully saved', 'sniplets'));
		}

		$places = Snip::automatic_types ();
		if (!is_array ($places))
			$places = array ();
		array_shift ($places);
		$this->render_admin ('limits', array ('places' => $places, 'options' => $this->get_options (), 'url' => $url));
	}


	/**
	 * Options page
	 *
	 * @return void
	 **/

	function admin_options ($url)
	{
		if (isset ($_POST['save']) && check_admin_referer ('sniplets-options'))
		{
			$_POST = stripslashes_deep ($_POST);

			$options = $this->get_options ();
			$options['php']              = isset ($_POST['php']) ? true : false;
			$options['content']          = isset ($_POST['content']) ? true : false;
			$options['search']           = trim (rtrim ($_POST['search'], DIRECTORY_SEPARATOR));
			$options['custom_places']    = trim ($_POST['custom_places']);
			$options['custom_templates'] = trim ($_POST['custom_templates']);
			$options['exclude']          = trim (preg_replace ('/[^0-9,]/', '', $_POST['exclude']));

			update_option ('sniplets', $options);
			$this->render_message (__ ('Your options have been saved', 'sniplets'));
		}
		else if (isset ($_POST['delete']) && check_admin_referer ('sniplets-delete_plugin'))
		{
			include (dirname (__FILE__).'/model/database.php');

			$db = new SN_Database;
			$db->remove (__FILE__);

			$this->render_message (__ ('Sniplets has been successfully removed', 'sniplets'));
		}

		$this->render_admin ('options', array ('options' => $this->get_options (), 'url' => $url));
	}


	/**
	 * Get options, setting values to defaults if not present
	 *
	 * @return array Options
	 **/

	function get_options ()
	{
		$options = get_option ('sniplets');
		if (!is_array ($options))
			$options = array ();

		$defaults = array
		(
			'php'     => false,
			'content' => false,
			'support' => false,
			'exclude' => '',
			'limits'  => array(),
			'rand'    => array(),
			'searchpath' => ''
		);
		
		return array_merge( $defaults, $options );
	}


	/**
	 * Displays the main sniplets page
	 *
	 * @return void
	 **/

	function admin_sniplets ($url)
	{
		if (isset ($_POST['add']) && check_admin_referer ('sniplets-add_sniplet'))
		{
			$snip = new Snip ($_POST);
			if ($snip->create ())
				$this->render_message (__ ('Your Sniplet was successfully added', 'sniplets'));
		  else
				$this->render_error (__ ('Failed to add Sniplet - please give a valid name', 'sniplets'));
		}

		$pager = new SNI_Pager ($_GET, $_SERVER['REQUEST_URI'], 'position', 'ASC');

		$this->render_admin ('sniplets', array ('snips' => Snip::get_all_shared ($pager), 'pager' => $pager, 'url' => $url));
	}


	/**
	 * Replaces sniplets
	 *
	 * @param array $string Array of matches from regex
	 * @return string Replaced text
	 **/

  function replace_sniplets ($matches)
  {
		if (isset ($this->sniplets['manual'][$matches[1]]))
			return $this->get_display ($this->sniplets['manual'][$matches[1]], 'manual');
		else
			return sprintf (__ ('No sniplet called <strong>%s</strong>', 'sniplets'), htmlspecialchars ($matches[1]));
  }


	/**
	 * Replaces content sniplets
	 *
	 * @param array $string Array of matches from regex
	 * @return string Replaced text
	 **/

	function replace_content ($matches)
	{
		if (isset ($this->sniplets['manual'][$matches[1]]))
			return $this->get_display ($this->sniplets['manual'][$matches[1]], 'manual', trim (preg_replace ('@<br />$@', '', $matches[2])));
		else if (substr ($matches[1], 0, 7) == 'module:')
		{
			if (preg_match ('/args="(.*?)"/', $matches[1], $args) > 0)
				$args = $args[1];
			else
				$args = '';

			return $this->get_display (Snip::anonymous (substr ($matches[1], 7), $args), 'manual', trim (preg_replace ('@<br />$@', '', $matches[2])));
		}
	}

	function wp_head ()
	{
		echo $this->get_display ($this->sniplets['header'], 'header');
	}

	function wp_footer ()
	{
		echo $this->get_display ($this->sniplets['footer'], 'footer');
	}

	function comment_form ($id)
	{
		echo $this->get_display ($this->sniplets['comment'], 'comment');
	}


	/**
	 * Replaces PHP
	 *
	 * @param array $string Array of matches from regex
	 * @return string Replaced text
	 **/

	function execute_php ($matches)
	{
		ob_start ();
		eval ($matches[1]);
		$output = ob_get_contents ();
		ob_end_clean ();
		return $output;
	}

	function get_display ($sniplets, $place, $content = '')
	{
		$text = '';
		if (count ($sniplets) > 0)
		{
			$options = $this->get_options ();

			if (isset ($options['rand']) && isset ($place, $options['rand']))
				shuffle ($sniplets);

			$count = 0;
			foreach ($sniplets AS $snip)
			{
				if (!$this->is_excluded ($snip))
				{
					$text .= $snip->process ($options['searchpath'], $content);
					$count++;
					
					if (isset ($options['limits'][$place]) && $options['limits'][$place] > 0 && $count >= $options['limits'][$place])
						break;
				}
			}
		}

		if (strpos ($text, '[snip') !== false)
			$text = $this->the_content ($text);

		return $text;
	}

	/**
	 * Filter that replaces all sniplets in post data
	 *
	 * @param array $string Array of matches from regex
	 * @return string Replaced text
	 **/

  function the_content ($text)
  {
		global $post;

		if (is_single () || is_page () || is_feed ())
		{
			global $post, $multipage, $page, $numpages;

			if (($multipage && $page == 1) || !$multipage)
				$before = $this->get_display (isset($this->sniplets['beforefirst']) ? $this->sniplets['beforefirst'] : array(), 'beforefirst');

			if (($multipage && $page == $numpages) || !$multipage)
				$after = $this->get_display (isset($this->sniplets['afterfirst']) ? $this->sniplets['afterfirst'] : array(), 'afterfirst');

			if (isset ($this->sniplets['more']))
				$text = preg_replace ('@<span id="more-\d*"></span>@', $this->get_display ($this->sniplets['more'], 'more'), $text);

			$text = $before.$this->get_display (isset($this->sniplets['beforeall']) ? $this->sniplets['beforeall'] : array(), 'beforeall').$text.$this->get_display (isset($this->sniplets['afterall']) ? $this->sniplets['afterall'] : array(), 'afterall').$after;
		}

		// Now process manual sniplets
		if (isset ($this->sniplets['manual']))
		{
			$options = $this->get_options ();

			// Replace [snip NAME]something[/snip]
			if ($options['content'])
				$text = preg_replace_callback ('/\[snip\s+(.*?)\](.*?)\[\/snip\]/s', array ($this, 'replace_content'), $text);

			// Replace [sniplet NAME]
			$text = preg_replace_callback ('/\[sniplet\s+(.*?)\]/', array ($this, 'replace_sniplets'), $text);
		}

		return $text;
  }

	function run_php ($text)
	{
		// Replace any PHP functions
		return preg_replace_callback ('@<\?php(.*)\?>@', array ($this, 'execute_php'), $text);
	}


	function metabox ($post)
	{
		global $wp_meta_boxes;
		if (isset ($wp_meta_boxes['post']['normal']['sorted']['headspacestuff']))
			unset ($wp_meta_boxes['post']['normal']['sorted']['headspacestuff']);
			
		if (current_user_can ('administrator'))
		{
			global $post;

			$snips = Snip::get_for (array ($post), '', false, true);
			$this->render_admin ('post-form-inside', array ('sniplets' => $snips));
		}
	}

	function post_form ()
	{
		if (current_user_can ('administrator'))
		{
			global $post;

			$snips = Snip::get_for (array ($post), '', false, true);
			$this->render_admin ('post-form', array ('sniplets' => $snips));
		}
	}

	function the_sniplet ($sniplet)
	{
		if (!$this->loaded)
			$this->the_posts (array ());

		if (isset ($this->sniplets['manual'][$sniplet]))
			echo $this->get_display ($this->sniplets['manual'][$sniplet], 'manual');
	}

	function the_sniplet_place ($place, $before = '', $after = '')
	{
		if (!$this->loaded)
			$this->the_posts (array ());

		if (isset ($this->sniplets[$place]))
			echo $before.$this->get_display ($this->sniplets[$place], $place).$after;
	}

	function the_real_content ($text, $post)
	{
		$sniplets = Snip::get_for (array ($post->ID), 'post', true);
		if (count ($sniplets) > 0)
		{
			$options = $this->get_options ();

			foreach ($sniplets AS $snip)
				$this->sniplets['manual'][$snip->name][] = $snip;
		}

		return $this->the_content ($text);
	}
}


/**
 * Template tag to allow a sniplet to be displayed in a template
 *
 * @param  string Sniplet name
 * @return string Text
 **/

function the_sniplet ($sniplet)
{
	do_action ('the_sniplet', $sniplet);
}

function the_sniplet_place ($place, $before = '', $after = '')
{
	do_action ('the_sniplet_place', $place, $before, $after);
}

function have_sniplet ($sniplet)
{
	global $sniplets;
	return isset ($sniplets->sniplets['manual'][$sniplet]);
}

function have_sniplet_place ($place)
{
	global $sniplets;
	return isset ($sniplets->sniplets[$place]);
}

function sniplet_transform_text ($text)
{
	global $sniplets;
	return $sniplets->the_content ($text);
}

/**
 * The Sniplet plugin object
 *
 * @global
 **/

$sniplets = new Sniplets;
