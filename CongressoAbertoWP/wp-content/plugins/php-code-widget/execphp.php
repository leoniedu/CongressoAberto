<?php
/*
Plugin Name: Executable PHP widget
Description: Like the Text widget, but it will take PHP code as well. Heavily derived from the Text widget code in WordPress.
Author: Otto
Version: 1.2
Author URI: http://ottodestruct.com
Plugin URI: http://wordpress.org/extend/plugins/php-code-widget/
*/
/*
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2, 
    as published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
*/

function widget_execphp($args, $widget_args = 1) {
	extract( $args, EXTR_SKIP );
	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );

	$options = get_option('widget_execphp');
	if ( !isset($options[$number]) )
		return;

	$title = $options[$number]['title'];
	$text = apply_filters( 'widget_execphp', $options[$number]['text'] );
?>
		<?php echo $before_widget; ?>
			<?php if ( !empty( $title ) ) { echo $before_title . $title . $after_title; } ?>
			<div class="execphpwidget"><?php eval('?>'.$text); ?></div>
		<?php echo $after_widget; ?>
<?php
}

function widget_execphp_control($widget_args) {
	global $wp_registered_widgets;
	static $updated = false;

	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );

	$options = get_option('widget_execphp');
	if ( !is_array($options) )
		$options = array();

	if ( !$updated && !empty($_POST['sidebar']) ) {
		$sidebar = (string) $_POST['sidebar'];

		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) )
			$this_sidebar =& $sidebars_widgets[$sidebar];
		else
			$this_sidebar = array();

		foreach ( $this_sidebar as $_widget_id ) {
			if ( 'widget_execphp' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
				$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
				if ( !in_array( "execphp-$widget_number", $_POST['widget-id'] ) ) unset($options[$widget_number]);
			}
		}

		foreach ( (array) $_POST['widget-execphp'] as $widget_number => $widget_text ) {
			$title = strip_tags(stripslashes($widget_text['title']));
			if ( current_user_can('unfiltered_html') )
				$text = stripslashes( $widget_text['text'] );
			else
				$text = stripslashes(wp_filter_post_kses( $widget_text['text'] ));
			$options[$widget_number] = compact( 'title', 'text' );
		}

		update_option('widget_execphp', $options);
		$updated = true;
	}

	if ( -1 == $number ) {
		$title = '';
		$text = '';
		$number = '%i%';
	} else {
		$title = attribute_escape($options[$number]['title']);
		$text = format_to_edit($options[$number]['text']);
	}
?>
		<p>
			<input class="widefat" id="execphp-title-<?php echo $number; ?>" name="widget-execphp[<?php echo $number; ?>][title]" type="text" value="<?php echo $title; ?>" />
			<p>PHP Code (MUST be enclosed in &lt;?php and ?&gt; tags!):</p>
			<textarea class="widefat" rows="16" cols="20" id="execphp-text-<?php echo $number; ?>" name="widget-execphp[<?php echo $number; ?>][text]"><?php echo $text; ?></textarea>
			<input type="hidden" id="execphp-submit-<?php echo $number; ?>" name="execphp-submit-<?php echo $number; ?>" value="1" />
		</p>
<?php
}

function widget_execphp_register() {

	// Check for the required API functions
	if ( !function_exists('wp_register_sidebar_widget') || !function_exists('wp_register_widget_control') )
		return;

	if ( !$options = get_option('widget_execphp') )
		$options = array();
	$widget_ops = array('classname' => 'widget_execphp', 'description' => __('Arbitrary text, HTML, or PHP code'));
	$control_ops = array('width' => 460, 'height' => 350, 'id_base' => 'execphp');
	$name = __('PHP Code');

	$id = false;
	foreach ( array_keys($options) as $o ) {
		// Old widgets can have null values for some reason
		if ( !isset($options[$o]['title']) || !isset($options[$o]['text']) )
			continue;
		$id = "execphp-$o"; // Never never never translate an id
		wp_register_sidebar_widget($id, $name, 'widget_execphp', $widget_ops, array( 'number' => $o ));
		wp_register_widget_control($id, $name, 'widget_execphp_control', $control_ops, array( 'number' => $o ));
	}
	
	// If there are none, we register the widget's existance with a generic template
	if ( !$id ) {
		wp_register_sidebar_widget( 'execphp-1', $name, 'widget_execphp', $widget_ops, array( 'number' => -1 ) );
		wp_register_widget_control( 'execphp-1', $name, 'widget_execphp_control', $control_ops, array( 'number' => -1 ) );
	}
	
}

add_action( 'widgets_init', 'widget_execphp_register' );

?>