<?php

include ('../../../wp-config.php');
@header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));

class Sniplets_AJAX extends Sniplets_Plugin
{
	function Sniplets_AJAX ($id, $command)
	{
		if (!current_user_can ('administrator'))
			die ('<p style="color: red">You are not allowed access to this resource</p>');
		
		$_POST = stripslashes_deep ($_POST);
		$this->register_plugin ('sniplets', __FILE__);
		if (method_exists ($this, $command))
			$this->$command ($id);
		else
			die ('<p style="color: red">That function is not defined</p>');
	}
	
	function edit ($id)
	{
	  $sniplet = Snip::get_by_id ($id);

		$this->render_admin ('sniplet_edit', array ('sniplet' => $sniplet));
	}
	
	function save ($id)
	{
		if (check_ajax_referer ('sniplets-item_save'))
		{
			$sniplet = new Snip ($_POST);
			$sniplet->update ($id);
		
			$sniplet = Snip::get_by_id ($id);
		
			$this->render_admin ('sniplet_item', array ('item' => $sniplet));
			$this->render_admin ('sniplet_class', array ('id' => $id, 'klass' => $sniplet->klass ()));
		}
	}
	
	function show ($id)
	{
	  $sniplet = Snip::get_by_id ($id);

		$this->render_admin ('sniplet_item', array ('item' => $sniplet));
	}
	
	function toggle ($id)
	{
		if (check_ajax_referer ('sniplets-item_toggle'))
		{
		  $sniplet = Snip::get_by_id ($id);
			$sniplet->toggle_status ();

			$this->render_admin ('sniplet_item', array ('item' => $sniplet));
		}
	}
	
	function delete ($id)
	{
		if (check_ajax_referer ('sniplets-item_delete'))
			Snip::delete ($id);
	}
	
	
	function add_placement ($id)
	{
		if (check_ajax_referer ('sniplets-item_save'))
		{
		  $sniplet = Snip::get_by_id ($id);
			$sniplet->add_placement ();
		
			$this->render_admin ('placement_list', array ('sniplet' => $sniplet, 'types' => $sniplet->automatic_types ()));
		}
	}
	
	function delete_placement ($id)
	{
		if (check_ajax_referer ('sniplets-placement_delete'))
		{
		  $sniplet = Snip::get_by_id ($id);
			$sniplet->delete_placement (intval ($_GET['pos']));

			$this->render_admin ('placement_list', array ('sniplet' => $sniplet, 'types' => $sniplet->automatic_types ()));
		}
	}
	
	function edit_placement ($id)
	{
	  $sniplet = Snip::get_by_id ($id);
		$pos     = intval ($_GET['pos']);
	
		$places  = $sniplet->automatic_types ();
		unset ($places['-']);
		$current = $sniplet->placement[$pos];
		
		$this->render_admin ('placement_edit', array ('places' => $places, 'current' => $current, 'id' => $id, 'pos' => $pos));
	}
	
	function cancel_placement ($id)
	{
	  $sniplet = Snip::get_by_id ($id);
		
		$this->render_admin ('placement_list', array ('sniplet' => $sniplet, 'types' => $sniplet->automatic_types ()));
	}
	
	function save_placement ($id)
	{
		if (check_ajax_referer ('sniplets-placement_save'))
		{
			$sniplet = Snip::get_by_id ($id);
			$sniplet->update_placement (intval ($_GET['pos']), $_POST['placement']);
		
			$this->render_admin ('placement_list', array ('sniplet' => $sniplet, 'types' => $sniplet->automatic_types ()));
		}
	}

	function add_function ($id)
	{
		if (check_ajax_referer ('sniplets-item_save'))
		{
		  $sniplet = Snip::get_by_id ($id);
			$sniplet->add_function ('encode.php', $_POST['arguments']);
		
			$this->render_admin ('function_list', array ('sniplet' => $sniplet, 'types' => SnipletFunction::get ()));
		}
	}
	
	function delete_function ($id)
	{
		if (check_ajax_referer ('sniplets-function_delete'))
		{
		  $sniplet = Snip::get_by_id ($id);
			$sniplet->delete_function (intval ($_GET['pos']));

			$this->render_admin ('function_list', array ('sniplet' => $sniplet, 'types' => SnipletFunction::get ()));
		}
	}
	
	function edit_function ($id)
	{
	  $sniplet = Snip::get_by_id ($id);
	
		$this->render_admin ('function_edit', array ('function' => $sniplet->modules[intval ($_GET['pos'])], 'id' => $id, 'pos' => intval ($_GET['pos'])));
	}
	
	function save_function ($id)
	{
		if (check_ajax_referer ('sniplets-function_save'))
		{
		  $sniplet = Snip::get_by_id ($id);
			$pos = intval ($_GET['pos']);
		
			$sniplet->update_function ($pos, $_POST['module'], $_POST['arguments']);
			$this->render_admin ('function_list', array ('sniplet' => $sniplet, 'types' => SnipletFunction::get ()));
		}
	}
	
	function cancel_function ($id)
	{
	  $sniplet = Snip::get_by_id ($id);
		
		$this->render_admin ('function_list', array ('sniplet' => $sniplet, 'types' => SnipletFunction::get ()));
	}
	
	
	function add_sniplet ($id)
	{
		$snip = new Snip ($_POST);
		$snip->create ($id);
	  ?>
<li id="snip_<?php echo $snip->id ?>"<?php if ($snip->klass ()) echo ' class="snip_'.$snip->klass ().'"' ?>><?php $this->render_admin ('sniplet_item', array ('item' => $snip)) ?></li>
	  <?php
	}
	
	function dup_sniplet ($id)
	{
		$snip = Snip::get_by_id ($id);
		$snip->name .= ' copy';
		$snip->create ();
	  ?>
<li id="snip_<?php echo $snip->id ?>"<?php if ($snip->klass ()) echo ' class="snip_'.$snip->klass ().'"' ?>><?php $this->render_admin ('sniplet_item', array ('item' => $snip)) ?></li>
	  <?php
	}
	
	function save_order ($id)
	{
		if (check_ajax_referer ('sniplets-save_sort'))
			Snip::save_order ($_POST['snip']);
	}
}

$id  = $_GET['id'];
$cmd = $_GET['cmd'];

$obj = new Sniplets_AJAX ($id, $cmd);
?>