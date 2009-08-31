<?php


class Widget_Snip
{
	function Widget_Snip ($name, $max = 1, $id = '', $args = '')
	{
		$this->name        = $name;
		$this->id          = $id;
		$this->widget_max  = $max;
		$this->args        = $args;
		
		if ($this->id == '')
			$this->id = strtolower (preg_replace ('/[^A-Za-z]/', '-', $this->name));

		$this->widget_available = 1;
		if ($this->widget_max > 1)
		{
			$this->widget_available = get_option ('widget_available_'.$this->id ());
			if ($this->widget_available === false)
				$this->widget_available = 1;
		}
		
		add_action ('init', array (&$this, 'initialize'));
	}
	
	function initialize ()
	{
		// Compatability functions for WP 2.1
		if (!function_exists ('wp_register_sidebar_widget'))
		{
			function wp_register_sidebar_widget ($id, $name, $output_callback, $classname = '')
			{
				register_sidebar_widget($name, $output_callback, $classname);
			}
		}

		if (!function_exists ('wp_register_widget_control'))
		{
			function wp_register_widget_control($name, $control_callback, $width = 300, $height = 200)
			{
				register_widget_control($name, $control_callback, $width, $height);
			}
		}
		
		if (function_exists ('wp_register_sidebar_widget'))
		{
			if ($this->widget_max > 1)
			{
				add_action ('sidebar_admin_setup', array (&$this, 'setup_save'));
				add_action ('sidebar_admin_page', array (&$this, 'setup_display'));
			}

			$this->load_widgets ();
		}
	}
	
	function load_widgets ()
	{
		for ($pos = 1; $pos <= $this->widget_max; $pos++)
		{
			wp_register_sidebar_widget ($this->id ($pos), $this->name ($pos), $pos <= $this->widget_available ? array (&$this, 'show_display') : '', $this->args (), $pos);
		
			if ($this->has_config ())
				wp_register_widget_control ($this->id ($pos), $this->name ($pos), $pos <= $this->widget_available ? array (&$this, 'show_config') : '', $this->args (), $pos);
		}
	}
	
	function args ()
	{
		if ($this->args)
			$args = $this->args;
		else
			$args = array ('classname' => '');

		if ($this->description ())
			$args['description'] = $this->description ();
		return $args;
	}

	function description () { return ''; }
	
	function name ($pos)
	{
		if ($this->widget_available > 1)
			return $this->name.' ('.$pos.')';
		return $this->name;
	}
	
	function id ($pos = 0)
	{
		if ($pos == 0)
			return $this->id;
		return $this->id.'-'.$pos;
	}
	
	function show_display ($args, $number = 1)
	{
		$config = get_option ('widget_config_'.$this->id ($number));
		if ($config === false)
			$config = array ();

		$this->load ($config);
		$this->display ($args);
	}
	
	function show_config ($position)
	{
		if (isset ($_POST['widget_config_save_'.$this->id ($position)]))
		{
			$data = $_POST[$this->id ()];
			if (count ($data) > 0)
			{
				$newdata = array ();
				foreach ($data AS $item => $values)
					$newdata[$item] = $values[$position];
				$data = $newdata;
			}
			
			update_option ('widget_config_'.$this->id ($position), $this->save ($data));
		}

		$options = get_option ('widget_config_'.$this->id ($position));
		if ($options === false)
			$options = array ();
			
		$this->config ($options, $position);
		echo '<input type="hidden" name="widget_config_save_'.$this->id ($position).'" value="1" />';
	}
	
	function has_config () { return false; }
	function save ($data)
	{
		return array ();
	}
	
	function setup_save ()
	{
		if (isset ($_POST['widget_setup_save_'.$this->id ()]))
		{
			$this->widget_available = intval ($_POST['widget_setup_count_'.$this->id ()]);
			if ($this->widget_available < 1)
				$this->widget_available = 1;
			else if ($this->widget_available > $this->widget_max)
				$this->widget_available = $this->widget_max;

			update_option ('widget_available_'.$this->id (), $this->widget_available);
			
			$this->load_widgets ();
		}
	}
	
	function config_name ($field, $pos)
	{
		return $this->id ().'['.$field.']['.$pos.']';
	}
	
	function setup_display ()
	{
		?>
		<div class="wrap">
			<form method="post">
				<h2><?php echo $this->name ?></h2>
				<p style="line-height: 30px;"><?php _e('How many widgets would you like?', $this->id); ?>
					<select name="widget_setup_count_<?php echo $this->id () ?>" value="<?php echo $options; ?>">
						<?php for ( $i = 1; $i <= $this->widget_max; ++$i ) : ?>
						 <option value="<?php echo $i ?>"<?php if ($this->widget_available == $i) echo ' selected="selected"' ?>><?php echo $i ?></option>
						<?php endfor; ?>
					</select>
					<span class="submit">
						<input type="submit" name="widget_setup_save_<?php echo $this->id () ?>" value="<?php echo attribute_escape(__('Save', $this->id)); ?>" />
					</span>
				</p>
			</form>
		</div>
		<?php
	}
}

class Sniplet_Widget extends Widget_Snip
{
	var $title  = '';
	var $name   = '';
	var $type   = '';
	
	function has_config () { return true; }
	
	function load ($config)
	{
		if (isset ($config['title']))
			$this->title = $config['title'];
			
		if (isset ($config['name']))
			$this->name = $config['name'];
			
		if (isset ($config['type']))
			$this->type = $config['type'];
	}
	
	function display ($args)
	{
		extract ($args);

		$content = '';
		if ($this->type == 'sniplet')
			$content = the_sniplet ($this->name);
		else if ($this->type == 'place')
			$content = the_sniplet_place ($this->name);

		if ($content)
		{
			echo $before_widget;
		
			if ($this->title)
				echo $before_title.$this->title.$after_title;
		
			echo $content.$after_widget;
		}
	}
	
	function config ($config, $pos)
	{
		?>
		<table>
			<tr>
				<th>Title:</th>
				<td><input type="text" name="<?php echo $this->config_name ('title', $pos) ?>" value="<?php echo htmlspecialchars ($config['title']) ?>"/></td>
			</tr>
			<tr>
				<th>Name:</th>
				<td><input type="text" name="<?php echo $this->config_name ('name', $pos) ?>" value="<?php echo htmlspecialchars ($config['name']) ?>"/></td>
			</tr>
			<tr>
				<th>Type:</th>
				<td>
					<select name="<?php echo $this->config_name ('type', $pos) ?>">
						<option value="sniplet"<?php if ($config['type'] == 'sniplet') echo ' selected="selected"' ?>>Sniplet</option>
						<option value="place"<?php if ($config['type'] == 'place') echo ' selected="selected"' ?>>Placement</option>
					</select>
				</td>
			</tr>
		</table>
		<?php
	}
	
	function save ($data)
	{
		return array ('title' => $data['title'], 'name' => $data['name'], 'type' => $data['type']);
	}
}

?>