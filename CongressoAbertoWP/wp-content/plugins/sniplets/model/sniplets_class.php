<?php

class Snip extends Sniplets_Plugin
{
  var $id;
  var $name;
  var $contents;
	var $modules = array ();
	var $placement;
	var $post_id;

  function Snip ($arr = '')
  {
		$this->register_plugin ('sniplets', dirname (__FILE__));

		$this->status = 'enabled';
    if (is_array ($arr))
    {
			foreach ($arr AS $key => $value)
				$this->$key = $value;

			$this->name    = preg_replace ('/[\[\]<\/\\>]/', '', $this->name);
			if (!is_array ($this->modules) && !empty($this->modules) && $this->modules != 'NULL')
				$this->modules = unserialize ($this->modules);

			if (!is_array ($this->modules))
			 	$this->modules = array();

			if ($this->placement != '')
			{
				$this->placement = array_filter (explode ('|', str_replace ('-', '', $this->placement)));
				sort ($this->placement);
			}
			else
				$this->placement = array ();

			if ($this->template == '-')
				$this->template = '';
		}
  }

	function anonymous ($module, $args = '')
	{
		$module = explode (' ', $module);
		$module = $module[0];

		$snip = new Snip;
		$snip->name      = 'anonymous';
		$snip->contents  = '';
		$snip->modules[] = array ('function' => $module.'.php', 'args' => $args);

		return array ($snip);
	}

	function toggle_status ()
	{
		if ($this->status == 'enabled')
			$this->status = 'disabled';
		else
			$this->status = 'enabled';

		global $wpdb;
		$wpdb->query ("UPDATE {$wpdb->prefix}sniplets SET status='{$this->status}' WHERE id='{$this->id}'");
	}

  function create ($post_id = '')
  {
		if (strlen ($this->name) > 0)
		{
	    global $wpdb;

			$placement = '';
			if (!empty ($this->placement) > 0)
				$placement = $wpdb->escape (implode ('|', $this->placement));

	    $name      = $wpdb->escape ($this->name);
	    $contents  = $wpdb->escape ($this->contents);
			$template  = $wpdb->escape ($this->template);

			if (count ($this->modules) > 0)
				$modules = $wpdb->escape (serialize ($this->modules));
			else
				$modules = 'NULL';

			if ($this->post_id > 0)
				$post_id = $this->post_id;

			if ($post_id == '' || $post_id == 0)
				$post_id = 'NULL';

			$position = $wpdb->get_var ("SELECT COUNT(*) FROM {$wpdb->prefix}sniplets WHERE post_id".($post_id == 'NULL' ? ' IS NULL' : '='.$post_id));
	    $wpdb->query ("INSERT INTO {$wpdb->prefix}sniplets (name,contents,post_id,position,placement,template,modules) VALUES ('$name','$contents',$post_id,$position,'$placement','$template','$modules')") !== false;
			$this->id = $wpdb->insert_id;
			return true;
		}

		return false;
  }

	function update ($id)
	{
		$this->id = $id;
		if (strlen ($this->name) > 0)
		{
		    global $wpdb;

		    $name      = $wpdb->escape ($this->name);
		    $contents  = $wpdb->escape ($this->contents);
			$template  = $wpdb->escape ($this->template);

			$post_id = intval ($this->post_id);
			if ($post_id == '' || $post_id == 0)
				$post_id = 'NULL';

		    return $wpdb->query ("UPDATE {$wpdb->prefix}sniplets SET name='$name', contents='$contents', template='$template', post_id=$post_id WHERE id='$id'") !== false;
		}

		return false;
	}

	function delete ($id)
	{
		global $wpdb;

		$post = $wpdb->get_var ("SELECT post_id FROM {$wpdb->prefix}sniplets WHERE id='$id'");

		$wpdb->query ("DELETE FROM {$wpdb->prefix}sniplets WHERE id=$id");

		// Update positions
		if ($post == 0)
			$post_id = 'IS NULL';
		else
			$post_id = '='.$post;

		$snips = $wpdb->get_results ("SELECT id FROM {$wpdb->prefix}sniplets WHERE post_id $post_id ORDER BY position");
		if (count ($snips) > 0)
		{
			foreach ($snips AS $pos => $snip)
				$wpdb->query ("UPDATE {$wpdb->prefix}sniplets SET position='$pos' WHERE id='{$snip->id}'");
		}
	}

  function get_by_id ($id)
  {
    global $wpdb;
    $snip = $wpdb->get_row ("SELECT * FROM {$wpdb->prefix}sniplets WHERE id='$id'", ARRAY_A);

    if ($snip)
			return new Snip ($snip);
    return false;
  }

	function get_for ($posts, $area, $shared = false, $disabled = false)
	{
		global $wpdb;

		$ids = '';
		if (count ($posts) > 0)
		{
			foreach ($posts AS $item) {
				$ids[] = $item->ID;
			}

			$ids = array_filter ($ids);
			if (!empty ($ids))
			{
				$ids = implode (',', $ids);

				if ($shared)
					$shared = "(post_id IN ($ids) OR post_id IS NULL)";
				else
					$shared = "post_id IN ($ids)";

				$status = "AND status='enabled'";
				if ($disabled)
					$status = '';

				$placement = '';
	//			$placement = "AND (post_id IS NULL OR (placement LIKE '%$area/%' OR placement LIKE '%all/%'))";
	//			$placement .= ' OR placement IS NULL OR placement=""';
	//			$placement .= ")";
				$items = array ();
		    $results = $wpdb->get_results ("SELECT * FROM {$wpdb->prefix}sniplets WHERE $shared $status $placement ORDER BY position", ARRAY_A);

			  if (count ($results) > 0)
			  {
				   foreach ($results AS $item)
			       $items[] = new Snip ($item);
			  }
			}
		}

   	return $items;
	}

	function get_shared ()
	{
    global $wpdb;

    $results = $wpdb->get_results ("SELECT * FROM {$wpdb->prefix}sniplets WHERE post_id IS NULL ORDER BY position", ARRAY_A);

    $items = array ();
    if (count ($results) > 0)
    {
      foreach ($results AS $item)
        $items[] = new Snip ($item);
    }

    return $items;
	}

  function get_all_shared (&$pager)
  {
    global $wpdb;

    $results = $wpdb->get_results ("SELECT SQL_CALC_FOUND_ROWS * FROM {$wpdb->prefix}sniplets ".$pager->to_limits ('post_id IS NULL', array ('name', 'contents')), ARRAY_A);
		$pager->set_total ($wpdb->get_var ("SELECT FOUND_ROWS()"));

    $items = array ();
    if (count ($results) > 0)
    {
      foreach ($results AS $item)
        $items[] = new Snip ($item);
    }

    return $items;
  }

	function add_placement ()
	{
		global $wpdb;

		$this->placement[] = 'post/beforeall';
		$placement = $wpdb->escape (implode ('|', $this->placement));
		$wpdb->query ("UPDATE {$wpdb->prefix}sniplets SET placement='$placement' WHERE id='{$this->id}'");
	}

	function update_placement ($pos, $placement)
	{
		global $wpdb;

		unset ($this->placement[$pos]);

		$this->placement[] = $placement;
		$this->placement = array_unique ($this->placement);
		sort ($this->placement);
		$placement = implode ('|', $this->placement);

		$wpdb->query ("UPDATE {$wpdb->prefix}sniplets SET placement='$placement' WHERE id='{$this->id}'");
	}

	function add_function ($function, $arguments)
	{
		global $wpdb;

		$this->modules[] = array ('function' => $function, 'args' => $arguments);

		$data = $wpdb->escape (serialize ($this->modules));

		$wpdb->query ("UPDATE {$wpdb->prefix}sniplets SET modules='$data' WHERE id='{$this->id}'");
	}

	function delete_placement ($pos)
	{
		global $wpdb;

		unset ($this->placement[$pos]);
		$data = $wpdb->escape (implode ('|', $this->placement));

		$wpdb->query ("UPDATE {$wpdb->prefix}sniplets SET placement='$data' WHERE id='{$this->id}'");
	}

	function delete_function ($pos)
	{
		global $wpdb;

		unset ($this->modules[$pos]);
		$data = $wpdb->escape (serialize ($this->modules));

		$wpdb->query ("UPDATE {$wpdb->prefix}sniplets SET modules='$data' WHERE id='{$this->id}'");
	}

	function update_function ($pos, $type, $args)
	{
		global $wpdb;

		$this->modules[$pos]['function'] = $type;
		$this->modules[$pos]['args']     = $args;

		$data = $wpdb->escape (serialize ($this->modules));
		$wpdb->query ("UPDATE {$wpdb->prefix}sniplets SET modules='$data' WHERE id='{$this->id}'");
	}

  function process ($searchpath, $content = '')
  {
		$libpath = dirname (__FILE__).'/../lib';

		// Run the contents through each module
		$text = $this->contents;
		if ($content != '')
			$text = $content;

		if (is_array ($this->modules) && count ($this->modules) > 0)
		{
			foreach ($this->modules AS $module)
			{
				$file = '';
				if (file_exists ($searchpath.'/'.$module['function']))
					$file = $searchpath.'/'.$module['function'];
				else if (file_exists (dirname (__FILE__).'/../modules/'.$module['function']))
					$file = dirname (__FILE__).'/../modules/'.$module['function'];

				if ($file)
				{
					// Parse args
					$args  = array ();
					$parts = explode (',', $module['args']);

					if (count ($parts) > 0)
					{
						foreach ($parts AS $part)
						{
							$tmp = explode ('=', $part);
							if (count ($tmp) == 2)
							{
								if ($tmp[1] == 'true')
									$tmp[1] = true;
								else if ($tmp[1] == 'false')
									$tmp[1] = false;

								$args[$tmp[0]] = $tmp[1];
							}
							else
								$args[$tmp[0]] = true;
						}

						ob_start ();
				    include ($file);
				    $text = trim (ob_get_contents ());
				    ob_end_clean ();
					}
				}
			}
		}

		// Add any template
		if ($this->template && $text)
			$text = $this->capture ($this->template, array ('text' => $text));
    return $text;
  }

	function content ()
	{
    $contents = $this->contents;
    if (strlen ($contents) > 60)
      $contents = substr ($this->contents, 0, 60).'...';

		if ($contents)
			return "'".htmlspecialchars ($contents)."'";
		return '';
	}

	function klass ()
	{
		$klass = '';
		if (in_array ($this->template, array ('inset', 'notice', 'warning')))
			$klass = $this->template;

		if ($this->status == 'disabled')
			$klass .= ' disabled';
		return $klass;
	}

	function automatic_types ($mytype = '')
	{
		$types = array
		(
			'' => __ ('Manual', 'sniplets'),

			'Post' => array
			(
					'post/beforeall'    => __ ('Before post (all pages)', 'sniplets'),
					'post/beforefirst'  => __ ('Before post (first page)', 'sniplets'),
					'post/afterall'     => __ ('After post (all pages)', 'sniplets'),
					'post/afterlast'    => __ ('After post (last page)', 'sniplets'),
					'post/comment'      => __ ('Comment form', 'sniplets'),
					'post/header'       => __ ('Header', 'sniplets'),
					'post/footer'       => __ ('Footer', 'sniplets'),
					'post/root'         => __ ('Initialisation', 'sniplets'),
			),

			'Home' => array
			(
					'home/beforeall'    => __ ('Before post (all pages)', 'sniplets'),
					'home/beforefirst'  => __ ('Before post (first page)', 'sniplets'),
					'home/afterall'     => __ ('After post (all pages)', 'sniplets'),
					'home/afterlast'    => __ ('After post (last page)', 'sniplets'),
					'home/header'       => __ ('Header', 'sniplets'),
					'home/footer'       => __ ('Footer', 'sniplets'),
					'home/root'         => __ ('Initialisation', 'sniplets'),
					'home/more'         => __ ('More tag', 'sniplets'),
			),

			'Archive' => array
			(
					'archive/beforeall'    => __ ('Before post (all pages)', 'sniplets'),
					'archive/beforefirst'  => __ ('Before post (first page)', 'sniplets'),
					'archive/afterall'     => __ ('After post (all pages)', 'sniplets'),
					'archive/afterlast'    => __ ('After post (last page)', 'sniplets'),
					'archive/header'       => __ ('Header', 'sniplets'),
					'archive/footer'       => __ ('Footer', 'sniplets'),
					'archive/root'         => __ ('Initialisation', 'sniplets'),
					'archive/more'         => __ ('More tag', 'sniplets'),
			),
			
			'All' => array
			(
				'all/beforeall'    => __ ('Before post', 'sniplets'),
				'all/afterall'     => __ ('After post', 'sniplets'),
				'all/header'       => __ ('Header', 'sniplets'),
				'all/footer'       => __ ('Footer', 'sniplets'),
				'all/root'         => __ ('Initialisation', 'sniplets'),
			),

			'RSS' => array
			(
					'rss/beforeall'    => __ ('Before post (all pages)', 'sniplets'),
					'rss/beforefirst'  => __ ('Before post (first page)', 'sniplets'),
					'rss/afterall'     => __ ('After post (all pages)', 'sniplets'),
					'rss/afterlast'    => __ ('After post (last page)', 'sniplets'),
					'rss/root'         => __ ('Initialisation', 'sniplets'),
					'rss/more'         => __ ('More tag', 'sniplets'),
			)
		);

		if ($mytype)
		{
			foreach ($mytype AS $type)
				$tmp[] = $this->get_placement ($type);

			return implode (', ', $tmp);
		}

		// Add custom types
		global $sniplets;
		$options = $sniplets->get_options ();
		if ($options['custom_places'])
		{
			$places = array_filter (preg_split ("/[\r\n]/", $options['custom_places']));
			if (count ($places) > 0)
			{
				foreach ($places AS $place)
					$types['Custom']['custom/'.$place] = ucfirst ($place);
			}
		}

		$extra = apply_filters ('sniplet_places', true);
		if (!is_array ($extra))
			$extra = array ();
		return array_merge ($types, $extra);
	}

	function get_placement ($place)
	{
		$types = $this->automatic_types ();
		foreach ($types AS $area_name => $area)
		{
			if (is_array ($area))
			{
				foreach ($area AS $area_type => $name)
				{
					if ($area_type == $place)
						return $area_name.'/'.$name;
				}
			}
			else if ($area == $place)
				return $area_name;
		}

		return 'Unknown';
	}

	function templates ()
	{
		$templates = array
		(
			'-'       => __ ('No template', 'sniplets'),
			'inset'   => __ ('Inset', 'sniplets'),
			'notice'  => __ ('Notice', 'sniplets'),
			'warning' => __ ('Warning', 'sniplets'),
		);

		// Add custom templates
		global $sniplets;
		$options = $sniplets->get_options ();
		if ($options['custom_templates'])
		{
			$places = array_filter (preg_split ('/[\s,]+/', $options['custom_templates']));
			if (count ($places) > 0)
			{
				foreach ($places AS $place)
					$templates[$place] = ucfirst ($place);
			}
		}

		return $templates;
	}

	function save_order ($order)
	{
		global $wpdb;

		foreach ($order AS $position => $id)
			$wpdb->query ("UPDATE {$wpdb->prefix}sniplets SET position=$position WHERE id=$id");
	}
}

class SnipletFunction
{
  var $functions;

	function get_functions ($path)
	{
		$functions = array ();

	  $files = glob ($path);
	  if (count ($files) > 0)
	  {
	    foreach ($files as $file)
	    {
	      $contents = @file_get_contents ($file);
	      if (preg_match ('/Name: (.*)/', $contents, $matches) > 0)
	        $functions[basename ($file)] = trim (str_replace ('*/', '', $matches[1]));
	      else
	        $functions[basename ($file)] = str_replace ('.php', '', basename ($file));
	    }
	  }

		return $functions;
	}

	function get ()
	{
		global $sniplets;

		$options = $sniplets->get_options ();

		$functions = SnipletFunction::get_functions (dirname (__FILE__).'/../modules/*.php');

		if ($options['search'] && file_exists ($options['search']))
			$functions = array_merge ($functions, SnipletFunction::get_functions ($options['search'].'/*.php'));
		return $functions;
	}

}


?>
