<?php

/*
Plugin Name: tags4page
Plugin URI: http://www.michelem.org/wordpress-plugin-tags4page/
Description: Now you can add tags to pages too (native Worpdress 2.3 tagging support) - Works ONLY with Wordpress >= 2.3
Version: 1.1
Author: Michele Marcucci
Author URI: http://www.michelem.org/

Copyright (c) 2007 Michele Marcucci
Released under the GNU General Public License (GPL)
http://www.gnu.org/licenses/gpl.txt
*/

add_action('edit_page_form', 'addthetags');
add_filter('posts_where', 'addpagetowhere' );

function addthetags() {
	global $post_ID;
	$tags = get_tags_to_edit( $post_ID );
	print '
	<fieldset id="tagdiv">
	<legend>'._e("Tags (separate multiple tags with commas: cats, pet food, dogs)").'</legend>
	<div><input type="text" name="tags_input" class="tags-input" id="tags-input" size="30" tabindex="3" value="'.$tags.'" /></div>
	</fieldset><p>&nbsp;</p>';
}

function addpagetowhere( $where ) {
  if( is_tag() ) {
    $where = preg_replace(
       "/ ([0-9a-zA-Z_]*\.?)post_type = 'post'/",
       "(${1}post_type = 'post' OR ${1}post_type = 'page')", $where );
   }

  return $where;
}

?>
