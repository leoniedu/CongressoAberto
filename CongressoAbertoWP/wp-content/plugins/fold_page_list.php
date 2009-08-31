<?php

/*
	Plugin Name: Fold Page List
	Version: 1.9
	Plugin URI: http://www.webspaceworks.com/resources/wordpress/30/
	Description: Provides PHP functions to display a folding page tree
	Author: Rob Schumann
	Author URI: http://www.webspaceworks.com/
*/
/*
	v1.9:  Compatibility (All in One SEO & Page menu Editor plugins) [28 December, 2008]
	        Modifications to extraction of metatags to prevent All-in-one-SEO titles appearing in navigation menus and to 
	        provide support for Page Menu Editor functionality
	v1.8:  New Feature [29 June, 2008]
	        Added breadcrumbs capability and a new template tag 'wswwpx_page_breadcrumbs'
	v1.76: Bugfix [02 May, 2008]
		 	Fix bug that affected section-wise unfolding of page hierarchy (introduced in v1.75)...
		 	Fix bug that affected Fold Page List when used with SEO plugins...
		 	Amended contact email address in header
	v1.75: Bugfix [28 February, 2008]
		 	Correction to _wswwpx_get_ancestor_ids to facilitate root-relative page hierarchy...
	v1.74: Bugfix [21 January, 2007]
		 	Full fix for the array handling issues...
	v1.73: Bugfix [21 January, 2007]
		 	Temporary solution to array warning messages from in_array and array_merge
	v1.72: Feature/bugfix [19 January, 2007]
		 	Added trap for common ancestry to allow entire subtrees to stay open when siblings are clicked.
	v1.71: WP2.1 compatibility [18 January, 2007]
		 	Amend _wswwpx_page_get_child_ids internal function to work with WP version 2.1 and full unfolding
	v1.7: Bugfix [17 January, 2007: not publicly released] 
		 	Fix for unfolding problem for deeply nested pages.. tested to 7 levels of pages, but essentially infinite.
	v1.6: New features [27 December, 2006]
			Extends the use of the 'fullunfold' argument to take a numeric value of 2, to bypass all folding and duplicate the wp_list_pages behaviour, but with full styling of the whole hierarchy.
			Adds preliminary support for additional custom tags (wswpg_linkrel) to place 'rel' or 'rev' attributes into links.
			Changed link in the header
	v1.5: Bugfix [25 November, 2006]
			Fix such that page children are pages, not anything else. Resolves cases where attached images are seen as children, wrongly causing 'page_folder' class to be attached to a page menu item if no child pages exist.
	v1.4: Compatibility fix [11 August, 2006]
			Added new css class (page_folder) that is applied to links in the tree that have children (displayed or not), allowing a style rules to apply different bullets depending on whether there are/aren't child pages.
	v1.3: Compatibility fix [25 April, 2006]
			Compatibility with Subscribe2 plugin <www.skippy.net> (Hack around an conflict between polyglot &amp; subscribe2 apparently conflicting use of 'the_title' filter on subscribe2 confirmation pages only).
	v1.2: New features/bugfix [14 February, 2006] - not released
			Brought up to date with current wp_list_pages capability in support of filters applied to the page list (suggested by Timm Severin)
	v1.1: New features/bugfix [11 December, 2005]
			Added new css class (current_page_ancestor) to allow for separate identification of current item from its ancestor trail.
			Adds new argument (true/false) to enable full unfolding (to a limit of 'depth') of a section. Default FALSE.
			Bugfix to internal function _wswwpx_page_get_child_ids to correctly return array of child ids
	v1.0.1: Compatibility fix [10 November, 2005]
			Added minor fix for compatibility with Polyglot plugin. No new functionality
	v1.0 (final): First release [28 September, 2005]
			Final release. No additional features or fixes, just a change to the header
	v1.0b6 (rc3): New feature [14 Aug, 2005]
			Added support for custom title attribute on anchor tags. Added thru 'wswpg_linkttl' custom field
	v1.0b5 (rc2): Bug fix [23 May, 2005]
			Fixed problem of sub-pages being duplicated when non-zero 'child_of' argument given
	v1.0b4 (rc1): Bug fix [4 May, 2005]
			broken xhtml validation fixed (bad placement of </li> tag in _wswwpx_tree_sublevels_out)
	v1.0b3: Bug fix [26 April, 2005]
			Resolve issue when no $queried_obj->ID available, causing problems on determining ancestors.
			_wswwpx_page_get_ancestor_ids now returns a one element array with a value of 0 (zero) in these circumstances.
	v1.0b2: Bug fix [24 April, 2005]
			Reference to __('pages') in wswwpx_fold_page_list fixed.
	v1.0b1: First public release [22 April, 2005]
	Copyright (c) 2005 - 2006  Rob Schumann  (email : robs@webspaceworks.com)
	Released under the GPL license
	http://www.gnu.org/licenses/gpl.txt

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/



/*
 * page_get_parent_id
 *  - $child is the ID of a page
 *  - returns the ID of the parent of the given page
 */
function _wswwpx_page_get_parent_id ( $child = 0 ) {
	global $wpdb;
	// Make sure there is a child ID to process
	if ( $child > 0 ) {
		$result = $wpdb->get_var("
									SELECT post_parent
										FROM $wpdb->posts
										WHERE ID = $child");
	} else {
		// ... or set a zero result.
		$result = 0;
	}
	//
	return $result;
}
/*
 * page_get_child_ids
 *  - $parent the ID of the parent page
 *  - returns an array containing the IDs of the children
 *    of the parent page
 */
function _wswwpx_page_get_child_ids ( $parent = 0, $_status='static' ) {
	global $wpdb;
	if ( $parent > 0 ) {
		// Get the ID of the parent.
		if (strlen($_status)>0) {
			$andPostStatus = " AND post_status = '$_status'";
			$andPostType   = '';
			if (($wp_version = get_bloginfo('version')) >= 2.1) {
				$andPostStatus = " AND post_status IN ('$_status', 'publish')";
				$andPostType   = " AND post_type = 'page'";
			}
		} else {
			$andPostStatus = '';
			$andPostType   = '';
		}
		$results = $wpdb->get_results("
	    							SELECT ID
	     								 FROM $wpdb->posts
	     								 WHERE post_parent = $parent$andPostStatus$andPostType", ARRAY_N );
 		if ($results) {
			foreach ($results AS $r) {
			 	foreach ($r AS $v) {
			 		$result[] = $v;
			 	}
			 }
		} else {
			$result = false;
		}
	} else {
		// ... or set a zero result.
		$pages = get_pages();
		foreach ($pages AS $page) {
			$result[]=$page->ID;
		}
//		$result = 0;
	}
	//
	return $result;
}

/*
 * page_get_ancestor_ids
 *  - $child is the ID of a page
 *  - returns an array of IDs of all ancestors of the requested page
 *  - default sort order is top down.
 */
function _wswwpx_page_get_ancestor_ids ( $child = 0, $inclusive=true, $topdown=true ) {
 	if ( $child && $inclusive ) $ancestors[] = $child;
 	while ($parent = _wswwpx_page_get_parent_id ( $child ) ) {
 		$ancestors[] = $parent;
 		$child = $parent;
 	}
 	$ancestors[] = 0;
 	//	If there are ancestors, test for resorting, and apply
 	if ($ancestors && $topdown) $ancestors = array_reverse($ancestors);
//	if ( !$ancestors ) $ancestors[] = 0;
 	//
 	return $ancestors;
 }

 /*
  * page_get_descendant_ids
  *  - $parent is the ID of a page
  *  - $inclusive is a switch determining whether the parent ID is included in the returned array. Defaults TRUE
  *  - returns an array of IDs of all descendents of the requested page
  */
function _wswwpx_page_get_descendant_ids ( $parent = 0, $inclusive=true ) {
 	if ( $parent && $inclusive ) $descendants[] = $parent;
 	if ( $offspring = _wswwpx_page_get_child_ids ( $parent ) ) {
//		if (is_array($descendants) ) $descendants = @array_merge($descendants, $offspring);
		if (is_array($offspring) && is_array($descendants) ) {
			$descendants = array_merge($descendants, $offspring);
		} else  if (is_array($offspring)){
			$descendants = $offspring;
		}
 		foreach ( $offspring as $child ) {
 			$grandchildren = _wswwpx_page_get_descendant_ids ( $child, false );
	 		if (is_array($grandchildren) && is_array($descendants)) {
	 			$descendants = @array_merge($descendants, $grandchildren);
	 		} else if (is_array($grandchildren)) {
	 			$descendants = $grandchildren;
	 		}
 		}
 	}
 	//
 	return $descendants;
 }

/*
 * page_get_custom_linktitle
 *  - $child is the ID of a page
 *  - returns the custom link title for the given page
 */
function _wswwpx_page_get_custom_linktitle ( $page = 0 ) {
	global $wpdb;
	// Make sure there is a page ID to process
	if ( $page > 0 ) {
		$result = $wpdb->get_var("
									SELECT meta_value
										FROM $wpdb->postmeta
										WHERE post_id = $page AND meta_key = 'wswpg_linkttl'");
	} else {
		// ... or set a zero result.
		$result = false;
	}
	//
	return $result;
}

/*
 * page_get_custom_tags
 *  - $child is the ID of a page
 *  - returns all custom tags for the given page
 */
function _wswwpx_page_get_custom_tags ( $page = 0 ) {
	global $wpdb;
	// Make sure there is a page ID to process
	if ( $page > 0 ) {
		$results = $wpdb->get_results("
									SELECT meta_key, meta_value
										FROM $wpdb->postmeta
										WHERE post_id = $page");
		if ($results) {
			foreach ($results AS $r) {
				$result[$r->meta_key] = $r->meta_value;
			 }
		} else {
			$result = false;
		}
	} else {
		// ... or set a zero result.
		$result = false;
	}
	//
	return $result;
}

/*
 *
 *	The following are taken from WP itself, and modified.
 * Modifed versions of:
 *		wp_list_pages:   tree_list_pages
 *		_page_level_out: _tree_sublevels_out
 *
 *	Original comments from WP are left in place
 *
 */
function wswwpx_fold_page_list ($args = '', $fullunfold=false) {
	parse_str($args, $r);
	if (!isset($r['depth'])) $r['depth'] = 0;
	if (!isset($r['show_date'])) $r['show_date'] = '';
	if (!isset($r['child_of'])) $r['child_of'] = 0;
	if ( !isset($r['title_li']) ) $r['title_li'] = __('Pages');
	if ( !isset($r['echo']) ) $r['echo'] = 1;

	$output = '';

	// Query pages.
	$pages = get_Pages($args);
	if ( $pages ) :

	if ( $r['title_li'] )
		$output .= '<li id="pagenav">' . $r['title_li'] . '<ul>';
	// Now loop over all pages that were selected
	$page_tree = Array();
	foreach($pages as $page) {
		// set the title for the current page
//
// 09-11-2005: Replaced to fix polyglot plugin by adding apply_filters
//	  		$page_tree[$page->ID]['title'] = $page->post_title;
// 25-Apr-2006: Hack around a problem with the_title filter that is introduced by the Subscribe2 subscription plugin.
//		if confirming a subscription, DON't apply the filter... it creates an empty title.
// 28-Dec-2008: Changed to use 'mtitle' instead of 'title' to avoid conflict with All-in-One-SEO-Pack use of a 'title' metatag.
//

		if (!isset($_GET['s2']) && !class_exists('wpSEO')) {
			$page_tree[$page->ID]['mtitle'] = apply_filters('the_title', $page->post_title);
		} else {
			//
			//	Will fail if used with polyglot as well!!
			//
			$page_tree[$page->ID]['mtitle'] = $page->post_title;
		}
		$page_tree[$page->ID]['name'] = $page->post_name;
		//
		//	Get custom link title for anchor 'title' attribute - Added 14 August, 2005
		//	- Changes to support Page Menu Editor plugin (added 28-Dec-2008 to FPL v1.9)
		//
		if ( $tags = _wswwpx_page_get_custom_tags ( $page->ID ) ) {
			foreach ($tags AS $key=>$value) {
				if (!empty($value)) $page_tree[$page->ID][$key] = $value;
				if ($key=='menulabel') $page_tree[$page->ID]['mtitle'] = $value;
				if ($key=='wswpg_linkttl' && !isset($page_tree[$page->ID]['wswpg_linkttl'])) $page_tree[$page->ID][$key] = $value;
				if ($key=='title_attrib') $page_tree[$page->ID]['wswpg_linkttl'] = $value;
			}
		}

		// set the selected date for the current page
		// depending on the query arguments this is either
		// the creation date or the modification date
		// as a unix timestamp. It will also always be in the
		// ts field.
		if (! empty($r['show_date'])) {
			if ('modified' == $r['show_date'])
				$page_tree[$page->ID]['ts'] = $page->time_modified;
			else
				$page_tree[$page->ID]['ts'] = $page->time_created;
		}

		// The tricky bit!!
		// Using the parent ID of the current page as the
		// array index we set the current page as a child of that page.
		// We can now start looping over the $page_tree array
		// with any ID which will output the page links from that ID downwards.
		$page_tree[$page->post_parent]['children'][] = $page->ID;
	}
	// Output of the pages starting with child_of as the root ID.
	// child_of defaults to 0 if not supplied in the query.
	//	** Modified: Will only expand to show sub-pages for the current page
	// Do NOT echo directly to the screen., but save to variable for applying filters to output.
//echo "DEPTH: {$r['depth']}<br />\n";
	$output .= _wswwpx_tree_sublevels_out($r['child_of'],$page_tree, $r, 0, $fullunfold, false);
	if ( $r['title_li'] )
		$output .=  '</ul></li>';
	endif;
	$output = apply_filters('wp_list_pages', $output);

	if ( $r['echo'] )
		echo $output;
	else 
		return $output;
}

function _wswwpx_tree_sublevels_out($parent, $page_tree, $args, $depth = 0, $fullunfold=false, $echo=true) {
	global $wp_query;
	

	$queried_obj = $wp_query->get_queried_object();
	
	$output = '';
	
	if($depth) $indent = str_repeat("\t", $depth);
	$all_ancestors = _wswwpx_page_get_ancestor_ids($queried_obj->ID);
	$n = count($all_ancestors);
	if ($n>1) $all_ancestors = array_slice($all_ancestors, 1);
	$all_children  = _wswwpx_page_get_descendant_ids($all_ancestors[0]);	// Get all children of the non-zero root parent of current page
	foreach($page_tree[$parent]['children'] as $page_id) {
		$withChild = _wswwpx_page_get_child_ids ( $page_id );
		$cur_page = $page_tree[$page_id];
		$page_ancestors = _wswwpx_page_get_ancestor_ids($page_id);
		$title = $cur_page['mtitle'];
		//	14-August-2005
		//	Add check for linkttl, and revert to default behaviour if not available...
		// 
		if ( isset($cur_page['wswpg_linkttl'] ) ) {
			$linkttl = $cur_page['wswpg_linkttl'];
		} else {
			$linkttl = $title;
		}
		$queried_page = $queried_obj->ID;
		$css_class = 'page_item';
		if ( $page_id == $queried_obj->ID ) {
			$css_class .= ' current_page_item';
		} else if ( in_array($page_id, $all_ancestors) ) {
			$css_class .= ' current_page_ancestor';
		}
		if ( $withChild ) $css_class .= ' page_folder';
// 14-Aug-2005: Add custom title attribute on anchor tag.
		$output .= $indent . '<li class="' . $css_class . '"><a href="' . get_page_link($page_id) . '" title="' . wp_specialchars($linkttl) . '"' . $linkrel . '>' . $title . '</a>';

		if(isset($cur_page['ts'])) {
			$format = get_settings('date_format');
			if(isset($args['date_format'])) $format = $args['date_format'];
			$output .= " " . gmdate($format,$cur_page['ts']);
		}
		$output .= "\n";
		/*
		//
		// Fullunfold = 2 emulates exactly wp_list_pages...
		//
		*/
		if (is_array($all_children)) {
			$among_children = in_array($page_id, $all_children);
		} else {
			$among_children = false;
		}
		$common_ancestors = array_intersect($page_ancestors, $all_ancestors);
		if ($fullunfold===2 || @in_array($page_id, $all_ancestors) || ($common_ancestors && $fullunfold && $queried_page != 0) || ($among_children && $fullunfold && $queried_page != 0 ) ) {
			if(isset($cur_page['children']) && is_array($cur_page['children'])) {
				$new_depth = $depth + 1;
				if(!$args['depth'] ||  $depth < ($args['depth']-1)) {
					$output .= "$indent<ul>\n";
					$output .= _wswwpx_tree_sublevels_out($page_id, $page_tree, $args, $new_depth, $fullunfold, $echo);
					$output .= "$indent</ul>\n";
				}
			}
		}
		$output .= "$indent</li>\n";
	}
	if ( $echo )
		echo $output;
	else
		return $output;
}
/*
//
//	Breadcrumbs
//
*/
function wswwpx_page_breadcrumbs ($_root=array('label'=>'Home', 'title'=>'Homepage', 'link'=>'/'), $_sep='&nbsp;&raquo;&nbsp;', $_echo=true) {
	global $wp_query;

	$queried_obj = $wp_query->get_queried_object();
	$pages = get_Pages();
	$all_ancestors = _wswwpx_page_get_ancestor_ids($queried_obj->ID);
	if ($pages) {
		foreach ($pages AS $page) {
			if ( in_array($page->ID, $all_ancestors) ) $ancestors[$page->ID]  = $page;
		}
		$breadcrumbs = $s = '';
		foreach ($all_ancestors AS $aid) {
			if ($aid == 0) {
				if (is_array($_root)) {
					$breadcrumbs .= "<a href='" . $_root['link'] . "' title='" . $_root['title'] . "'>" . $_root['label'] . "</a>";
					$ss = $_sep;
				}
			} else if ($aid != $queried_obj->ID) {
				$breadcrumbs .= $ss . "<a href='" . get_page_link($ancestors[$aid]->ID) . "' title='" . $ancestors[$aid]->post_title . "'>" . $ancestors[$aid]->post_title . "</a>";
				$ss = $_sep;
			} else {
				$breadcrumbs .= $ss . $ancestors[$aid]->post_title;
			}
		}
	}
	//
	if ( $_echo )
		echo $breadcrumbs;
	else
		return $breadcrumbs;
}
?>
