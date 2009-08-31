<?php if ( function_exists('register_sidebar') ) 
{     
register_sidebar(array('name' => 'Sidebar Top','before_widget' => '','after_widget' => '','before_title' => '<h3>','after_title' => '</h3>'));     
register_sidebar(array('name' => 'Sidebar Left','before_widget' => '','after_widget' => '','before_title' => '<h3>','after_title' => '</h3>')); 
register_sidebar(array('name' => 'Sidebar Right','before_widget' => '','after_widget' => '','before_title' => '<h3>','after_title' => '</h3>'));   
register_sidebar(array('name' => 'Sidebar Bottom','before_widget' => '','after_widget' => '','before_title' => '<h3>','after_title' => '</h3>'));    
register_sidebar(array('name' => 'Footer Left','before_widget' => '','after_widget' => '','before_title' => '<h3>','after_title' => '</h3>'));     
register_sidebar(array('name' => 'Footer Center','before_widget' => '','after_widget' => '','before_title' => '<h3>','after_title' => '</h3>'));     
register_sidebar(array('name' => 'Footer Right','before_widget' => '','after_widget' => '','before_title' => '<h3>','after_title' => '</h3>')); 
} 


remove_filter('get_the_excerpt', 'wp_trim_excerpt');
add_filter('get_the_excerpt', 'custom_trim_excerpt');

remove_filter('get_the_excerpt', 'wp_trim_excerpt');
add_filter('get_the_excerpt', 'custom_trim_excerpt');

function custom_trim_excerpt($text) { // Fakes an excerpt if needed
global $post;
	if ( '' == $text ) {
		$text = get_the_content('');

		$text = strip_shortcodes( $text );

		$text = apply_filters('the_content', $text);
		$text = str_replace(']]>', ']]&gt;', $text);
		$text = strip_tags($text);
		$excerpt_length = apply_filters('excerpt_length', 90);
		$words = explode(' ', $text, $excerpt_length + 1);
		if (count($words) > $excerpt_length) {
			array_pop($words);
			array_push($words, '...');
			$text = implode(' ', $words);
		}
	}
	return $text;
}

function publish_later_on_feed($where) {
	global $wpdb;

	if ( is_feed() ) {
		// timestamp in WP-format
		$now = gmdate('Y-m-d H:i:s');

		// value for wait; + device
		$wait = '60'; // integer

		// http://dev.mysql.com/doc/refman/5.0/en/date-and-time-functions.html#function_timestampdiff
		$device = 'MINUTE'; //MINUTE, HOUR, DAY, WEEK, MONTH, YEAR

		// add SQL-sytax to default $where
		$where .= " AND TIMESTAMPDIFF($device, $wpdb->posts.post_date_gmt, '$now') > $wait ";
	}
	return $where;
}

add_filter('posts_where', 'publish_later_on_feed');

function list_dep_state()  {
  global $id;
  $opts = 'sort_column=post_title&child_of='.$id.'&depth=2&title_li=';
  echo wp_list_pages( $opts );
}

?>