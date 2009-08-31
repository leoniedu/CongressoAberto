<?php
/*
 Plugin Name: WordPress Hashcash
 Plugin URI: http://wordpress-plugins.feifei.us/hashcash/
 Description: Client-side javascript blocks all spam bots.  XHTML 1.1 compliant.
 Author: Elliott Back
 Author URI: http://elliottback.com
 Version: 4.3

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 */

function wphc_option($save = false){
	if($save) {
		if( function_exists( 'update_site_option' ) ) {
			update_site_option('plugin_wp-hashcash', $save);
		} else {
			update_option('plugin_wp-hashcash', $save);
		}

		return $save;
	} else {
		if( function_exists( 'get_site_option' ) ) {
			$options = get_site_option('plugin_wp-hashcash');
		} else {
			$options = get_option('plugin_wp-hashcash');
		}

		if(!is_array($options))
			$options = array();

		return $options;
	}
}

/**
 * Install WP Hashcash
 */

function wphc_install () {
	// set our default options
	$options = wphc_option();
	$options['comments-spam'] = $options['comments-spam'] || 0;
	$options['comments-ham'] = $options['comments-ham'] || 0;
	$options['signups-spam'] = $options['signups-spam'] || 0;
	$options['signups-ham'] = $options['signups-ham'] || 0;
	$options['key'] = array();
	$options['key-date'] = 0;
	$options['refresh'] = 60 * 60 * 24 * 7;
	$options['signup_active'] = 1;
	$options['comments_active'] = 1;

	// akismet compat check
	if(function_exists('akismet_init')){
		$options['moderation'] = 'akismet';
	} else {
		$options['moderation'] = 'moderate';
	}
	
	// validate ip / url
	$options['validate-ip'] = true;
	$options['validate-url'] = true;

	// logging
	$options['logging'] = true;

	// update the key
	wphc_option($options);
	wphc_refresh();
}

add_action('activate_wp-hashcash/wp-hashcash.php', 'wphc_install');
add_action('activate_wp-hashcash.php', 'wphc_install');

/**
 * Update the key, if needed
 */

function wphc_refresh(){
	$options = wphc_option();

	if( !isset( $options[ 'signup_active' ] ) ) {
		wphc_install();
		return;
	}

	if(time() - $options['key-date'] > $options['refresh']) {
		if(count($options['key']) >= 5)
			array_shift($options['key']);

		array_push($options['key'], rand(21474836, 2126008810));

		$options['key-date'] = time();
		wphc_option($options);
	}
}

add_action('shutdown', 'wphc_refresh');

/**
 * Our plugin can also have a widget
 */

function get_spam_ratio( $ham, $spam ) {
	if($spam + $ham == 0)
		$ratio = 0;
	else
		$ratio = round(100 * ($spam/($ham+$spam)),2);

	return $ratio;
}

function widget_ratio($options){
	$signups_ham = (int)$options['signups-ham'];
	$signups_spam = (int)$options['signups-spam'];
	$ham = (int)$options['comments-ham'];
	$spam = (int)$options['comments-spam'];
	$ratio = get_spam_ratio( $hame, $spam );
	$signups_ratio = get_spam_ratio( $signups_ham, $signups_spam );

	$msg = "<p>$spam spam comments blocked out of $ham human comments.  " . $ratio ."% of your comments are spam!</p>";
	if( $signups_ham && $signups_spam )
		$msg = "<p>$signups_spam spam signups blocked out of $signups_ham human signups.  " . $signups_ratio ."% of your signups are spam!</p>";

	return $msg;
}

function wphc_widget_init () {
	if(!function_exists('register_sidebar_widget'))
		return;

	function widget_wphc($args) {
		extract($args);
		$options = wphc_option();

		echo $before_widget . $before_title . '<a href="http://wordpress-plugins.feifei.us/hashcash/">WP Hashcash</a>' . $after_title;
		echo '<ul>';
		echo '<li><small>By <a href="http://elliottback.com">Elliott Back</a></small></li>';
		echo "<li>".widget_ratio($options)."</li>";
		echo '</ul>';
		echo $after_widget;
	}
	
	register_sidebar_widget(array('WP Hashcash', 'widgets'), 'widget_wphc');
}

add_action('widgets_init', 'wphc_widget_init');

/**
 * Admin Options
 */

add_action('admin_menu', 'wphc_add_options_to_admin');

function wphc_add_options_to_admin() {
	if( function_exists( 'is_site_admin' ) && !is_site_admin() )
		return;

	if (function_exists('add_options_page')) {
		if( function_exists( 'is_site_admin' ) ) {
			add_submenu_page('wpmu-admin.php', __('WordPress Hashcash'), __('WordPress Hashcash'), 'manage_options', 'wphc_admin', 'wphc_admin_options');
		} else {
			add_options_page('Wordpress Hashcash', 'Wordpress Hashcash', 8, basename(__FILE__), 'wphc_admin_options');
		}
	}
}

function wphc_admin_options() {
	if( function_exists( 'is_site_admin' ) && !is_site_admin() )
		return;

	$options = wphc_option();

	if( !isset( $options[ 'signup_active' ] ) ) {
		wphc_install(); // MU has no activation hook
		$options = wphc_option();
	}

	// POST HANDLER
	if($_POST['wphc-submit']){
		check_admin_referer( 'wphc-options' );
		if ( function_exists('current_user_can') && !current_user_can('manage_options') )
			die('Current user not authorized to managed options');

		$options['refresh'] = strip_tags(stripslashes($_POST['wphc-refresh']));
		$options['moderation'] = strip_tags(stripslashes($_POST['wphc-moderation']));
		$options['validate-ip'] = strip_tags(stripslashes($_POST['wphc-validate-ip']));
		$options['validate-url'] = strip_tags(stripslashes($_POST['wphc-validate-url']));
		$options['logging'] = strip_tags(stripslashes($_POST['wphc-logging']));
		$options['signup_active'] = (int)$_POST['signup_active'];
		$options['comments_active'] = (int)$_POST['comments_active'];
		wphc_option($options);
	}
	
	// MAIN FORM
	echo '<style type="text/css">
		.wrap h3 { color: black; background-color: #e5f3ff; padding: 4px 8px; }

		.sidebar {
			border-right: 2px solid #e5f3ff;
			width: 200px;
			float: left;
			padding: 0px 20px 0px 10px;
			margin: 0px 20px 0px 0px;
		}

		.sidebar input {
			background-color: #FFF;
			border: none;
		}

		.main {
			float: left;
			width: 600px;
		}

		.clear { clear: both; }
	</style>';

	echo '<div class="wrap">';

	echo '<div class="sidebar">';
	echo '<h3>Plugin</h3>';
	echo '<ul>
	<li><a href="http://wordpress-plugins.feifei.us/hashcash/">Plugin\'s Homepage</a></li>';
	if( function_exists( 'is_site_admin' ) && is_site_admin() ) {
		echo '<li><a href="http://mu.wordpress.org/forums/">WordPress MU Forums</a></li>';
	}
	echo '<li><a href="http://wordpress.org/tags/wp-hashcash">Plugin Support Forum</a></li>';
	echo '</ul>';		
	echo '<h3>Donation</h3>';
	echo '<center><form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input style="border:none;" type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----"></form></center>';
	echo '<p>Any small donation would be highly appreciated.</p>';
	echo '<h3>Miscellaneous</h3>';
	echo '<ul>
	<li><a href="http://wordpress-plugins.feifei.us/">Elliott\'s WP Plugins</a></li>
	<li><a href="http://ocaoimh.ie/wordpress-plugins/">Donncha\'s WP Plugins</a></li>
	</ul>';
	echo '<h3>Statistics</h3>';
	echo '<p>'.widget_ratio($options).'</p>';
	echo '</div>';

	echo '<div class="main">';
	echo '<h2>WordPress Hashcash</h2>';
	echo '<p>This is an antispam plugin that eradicates spam signups on WordPress sites. It works because your visitors must use obfuscated
	javascript to submit a proof-of-work that indicates they opened your website in a web browser, not a robot.  You can read more about it on the 
	<a href="http://wordpress-plugins.feifei.us/hashcash/">WordPress Hashcash plugin page</a> of my site.</p>';

	echo '<h3>Standard Options</h3>';
	echo '<form method="POST" action="?page=' . $_GET[ 'page' ] . '&updated=true">';
	wp_nonce_field('wphc-options');
	if( function_exists( 'is_site_admin' ) ) { // MU only
		$signup_active = (int)$options[ 'signup_active' ];
		$comments_active = (int)$options[ 'comments_active' ];
		echo "<p><label>Signup protection enabled: <input type='checkbox' name='signup_active' value='1' " . ( $signup_active == '1' ? ' checked' : '' ) . " /></label></p>";
		echo "<p><label>Comments protection enabled: <input type='checkbox' name='comments_active' value='1' " . ( $comments_active == '1' ? ' checked' : '' ) . " /></label></p>";
	}
	// moderation options
	$moderate = htmlspecialchars($options['moderation'], ENT_QUOTES);
	echo '<p><label for="wphc-moderation">' . __('Moderation:', 'wp-hashcash') . '</label>';
	echo '<select id="wphc-moderation" name="wphc-moderation">';
	echo '<option value="moderate"'.($moderate=='moderate'?' selected':'').'>Moderate</option>';
	echo '<option value="akismet"'.($moderate=='akismet'?' selected':'').'>Akismet</option>';
	echo '<option value="delete"'.($moderate=='delete'?' selected':'').'>Delete</option>';
	echo '</select>';
	echo '<br/><span style="color: grey; font-size: 90%;">The default is to place spam comments into the
	akismet/moderation queue. Otherwise, the delete option will immediately discard spam comments.</span>';
	echo '</p>';

	// refresh interval
	$refresh = htmlspecialchars($options['refresh'], ENT_QUOTES);
	echo '<p><label for="wphc-refresh">' . __('Key Expiry:', 'wp-hashcash').'</label>
		<input style="width: 200px;" id="wphc-refresh" name="wphc-refresh" type="text" value="'.$refresh.'" />
		<br/><span style="color: grey; font-size: 90%;">Default is one week, or <strong>604800</strong> seconds.</p>';

	// current key
	echo '<p>Your current key is <strong>' . $options['key'][count($options['key']) - 1] . '</strong>.';
	if(count($options['key']) > 1)
		echo ' Previously you had keys '. join(', ', array_reverse(array_slice($options['key'], 0, count($options['key']) - 1))).'.';
	echo '</p>';

	// additional options
	echo '<h3>Additional options:</h3>';

	$validate_ip = htmlspecialchars($options['validate-ip'], ENT_QUOTES);
	echo '<p><label for="wphc-validate-ip">Validate IP Address</label>
		<input name="wphc-validate-ip" type="checkbox" id="wphc-validate-ip"'.($validate_ip?' checked':'').'/> 
		<br /><span style="color: grey; font-size: 90%;">
		Checks if the IP address of the trackback sender is equal to the IP address of the webserver the trackback URL is referring to.</span></p>';

	$validate_url = htmlspecialchars($options['validate-url'], ENT_QUOTES);
	echo '<p><label for="wphc-validate-url">Validate URL</label>
		<input name="wphc-validate-url" type="checkbox" id="wphc-validate-url"'.($validate_url?' checked':'').'/> 
		<br /><span style="color: grey; font-size: 90%;">Retrieves the web page located at the URL included 
		in the trackback to check if it contains a link to your blog.  If it does not, it is spam!</span></p>';

	// logging options
	echo '<h3>Logging:</h3>';

	$logging = htmlspecialchars($options['logging'], ENT_QUOTES);
	echo '<p><label for="wphc-logging">Logging</label>
		<input name="wphc-logging" type="checkbox" id="wphc-logging"'.($logging?' checked':'').'/> 
		<br /><span style="color: grey; font-size: 90%;">Logs the reason why a given comment failed the spam
		check into the comment body.  Works only if moderation / akismet mode is enabled.</span></p>';

	echo '<input type="hidden" id="wphc-submit" name="wphc-submit" value="1" />';
	echo '<input type="submit" id="wphc-submit-override" name="wphc-submit-override" value="Save WP Hashcash Settings"/>';
	echo '</form>';
	echo '</div>';

	echo '<div class="clear">';
	echo '<p style="text-align: center; font-size: .85em;">&copy; Copyright '.date('Y').' <a href="http://elliottback.com">Elliott B&auml;ck</a></p>';
	echo '</div>';

	echo '</div>';
}

/**
 * Add JS to the header
 */
function wphc_posthead() {
	if( function_exists( 'is_site_admin' ) ) {
		$options = wphc_option();
		if( !$options['comments_active'] )
			return;
	}
	if((is_single() || is_page()))
		wphc_addhead();
}
add_action('wp_head', 'wphc_posthead');

function wphc_signuphead() {
	if( function_exists( 'is_site_admin' ) ) {
		$options = wphc_option();
		if( !$options['signup_active'] )
			return;
	}
	wphc_addhead();
}
add_action('signup_header', 'wphc_signuphead');

function wphc_addhead() {
	echo "<script type=\"text/javascript\"><!--\n";
	echo 'function addLoadEvent(func) {
  var oldonload = window.onload;
  if (typeof window.onload != \'function\') {
    window.onload = func;
  } else {
    window.onload = function() {
      if (oldonload) {
        oldonload();
      }
      func();
    }
  }
}
';
	echo  wphc_getjs() . "\n";
	echo "addLoadEvent(function(){document.getElementById('wphc_value').value=wphc();});\n";
	echo "//--></script>\n";
}

function wphc_getjs(){
	$options = wphc_option();
	$val = $options['key'][count($options['key']) - 1];
	$js = 'function wphc_compute(){';

	switch(rand(0, 3)){
		/* Addition of n times of field value / n, + modulus:
		 Time guarantee:  100 iterations or less */
		case 0:
			$inc = rand($val / 100, $val - 1);
			$n = floor($val / $inc);
			$r = $val % $inc;
		
			$js .= "var wphc_eax = $inc; ";
			for($i = 0; $i < $n - 1; $i++){
				$js .= "wphc_eax += $inc; ";
			}
			
			$js .= "wphc_eax += $r; ";
			$js .= 'return wphc_eax; ';
			break;

			/* Conversion from binary:
		Time guarantee:  log(n) iterations or less */
		case 1:
			$binval = strrev(base_convert($val, 10, 2));
			$js .= "var wphc_eax = \"$binval\"; ";
			$js .= 'var wphc_ebx = 0; ';
			$js .= 'var wphc_ecx = 0; ';
			$js .= 'while(wphc_ecx < wphc_eax.length){ ';
			$js .= 'if(wphc_eax.charAt(wphc_ecx) == "1") { ';
			$js .= 'wphc_ebx += Math.pow(2, wphc_ecx); ';
			$js .= '} ';
			$js .= 'wphc_ecx++; ';
			$js .= '} ';
			$js .= 'return wphc_ebx;';
			
		break;
		
		/* Multiplication of square roots:
		Time guarantee:  constant time */
		case 2:
			$sqrt = floor(sqrt($val));
			$r = $val - ($sqrt * $sqrt);
			$js .= "return $sqrt * $sqrt + $r; ";
		break;
		
		/* Sum of random numbers to the final value:
		Time guarantee:  log(n) expected value */
		case 3:
			$js .= 'return ';
	
			$i = 0;
			while($val > 0){
				if($i++ > 0)
					$js .= '+';
				
				$temp = rand(1, $val);
				$val -= $temp;
				$js .= $temp;
			}
	
			$js .= ';';
		break;
	}
		
	$js .= '} wphc_compute();';
	
	// pack bytes
	if( !function_exists( 'strToLongs' ) ) {
	function strToLongs($s) {
		$l = array();
	    
		// pad $s to some multiple of 4
		$s = preg_split('//', $s, -1, PREG_SPLIT_NO_EMPTY);
	    
		while(count($s) % 4 != 0){
			$s [] = ' ';
		}
	
		for ($i = 0; $i < ceil(count($s)/4); $i++) {
			$l[$i] = ord($s[$i*4]) + (ord($s[$i*4+1]) << 8) + (ord($s[$i*4+2]) << 16) + (ord($s[$i*4+3]) << 24);
	    	}
	
		return $l;
	}
	}
	
	// xor all the bytes with a random key
	$key = rand(21474836, 2126008810);
	$js = strToLongs($js);
	
	for($i = 0; $i < count($js); $i++){
		$js[$i] = $js[$i] ^ $key;
	}
	
	// libs function encapsulation
	$libs = "function wphc(){\n";
	
	// write bytes to javascript, xor with key
	$libs .= "\tvar wphc_data = [".join(',',$js)."]; \n";
	
	// do the xor with key
	$libs .= "\n\tfor (var i=0; i<wphc_data.length; i++){\n";
	$libs .= "\t\twphc_data[i]=wphc_data[i]^$key;\n";
	$libs .= "\t}\n";
	
	// convert bytes back to string
	$libs .= "\n\tvar a = new Array(wphc_data.length); \n";
	$libs .= "\tfor (var i=0; i<wphc_data.length; i++) { \n";
	$libs .= "\t\ta[i] = String.fromCharCode(wphc_data[i] & 0xFF, wphc_data[i]>>>8 & 0xFF, ";
	$libs .= "wphc_data[i]>>>16 & 0xFF, wphc_data[i]>>>24 & 0xFF);\n";
	$libs .= "\t}\n";
	
	$libs .= "\n\treturn eval(a.join('')); \n";
	
	// call libs function
	$libs .= "}";
	
	// return code
	return $libs;
}

/**
 * Hook into the signups form
 */
function wphc_add_signupform(){
	echo '<input type="hidden" id="wphc_value" name="wphc_value" value=""/>';
}
add_action( 'signup_hidden_fields', 'wphc_add_signupform' );

function wphc_add_commentform(){
	$options = wphc_option();
	
	switch($options['moderation']){
		case 'delete':
			$verb = 'deleted';
			break;
		case 'akismet':
			$verb = 'queued in Akismet';
			break;
		case 'moderate':
		default:
			$verb = 'placed in moderation';
			break;
	}
	
	echo '<div><input type="hidden" id="wphc_value" name="wphc_value" value=""/></div>';
	echo '<p>' . __('Powered by', 'wp-hashcash') . ' <a href="http://wordpress-plugins.feifei.us/hashcash/">WP Hashcash</a></p>';
	echo '<noscript><div><small>Wordpress Hashcash needs javascript to work, but your browser has javascript disabled. Your comment will be '.$verb.'!</small></div></noscript>';
}

add_action('comment_form', 'wphc_add_commentform');

/**
 * Validate our tag
 */

function wphc_check_signup_hidden_tag( $result ) {
	// get our options
	$options = wphc_option();
	$spam = false;
	if( !strpos( $_SERVER[ 'PHP_SELF' ], 'wp-signup.php' ) )
		return $result;

	// Check the wphc values against the last five keys
	$spam = !in_array($_POST["wphc_value"], $options['key']);
	
	if($spam){
		$options['signups-spam'] = ((int) $options['signups-spam']) + 1;
		wphc_option($options);
		$result['errors']->add( 'blogname', __('You did not pass a spam check. Please enable JavaScript in your browser.') );
	} else {
		$options['signups-ham'] = ((int) $options['signups-ham']) + 1;
		wphc_option($options);
	}
	
	return $result;
}
add_filter( 'wpmu_validate_blog_signup', 'wphc_check_signup_hidden_tag' );
add_filter( 'wpmu_validate_user_signup', 'wphc_check_signup_hidden_tag' );

function wphc_check_hidden_tag($comment) {
	// get our options
	$type = $comment['comment_type'];
	$options = wphc_option();
	$spam = false;

	if($type == "trackback" || $type == "pingback"){
		// check the website's IP against the url it's sending as a trackback
		if($options['validate-ip']){
			$server_ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
			$web_ip = gethostbyname(parse_url($comment['comment_author_url'], PHP_URL_HOST));
			$ipv = $server_ip != $web_ip;
			$spam = $spam || ($ipv);
			
			if($options['logging'] && $ipv) $comment['comment_content'] .= "\n\n[WORDPRESS HASHCASH] The comment's server IP (".$server_ip.") doesn't match the"
				. " comment's URL host IP (".$web_ip.") and so is spam.";
		}

		// look for our link in the page itself
		if(!$spam && $options['validate-url']){
			if(!class_exists('Snoopy'))
				require_once( ABSPATH . WPINC . '/class-snoopy.php' );
				
			$permalink = get_permalink($comment['comment_post_ID']);
			$permalink = preg_replace('/\/$/', '', $permalink);
			$snoop = new Snoopy;
			
			if (@$snoop->fetchlinks($comment['comment_author_url'])){
				$found = false;
				
				if( !empty( $snoop->results ) )
				{
					foreach($snoop->results as $url){
						$url = preg_replace('/(\/|\/trackback|\/trackback\/)$/', '', $url);
						if($url == $permalink)
							$found = true;	
					}
				}
				
				if($options['logging'] && !$found) 
					$comment['comment_content'] .= "\n\n[WORDPRESS HASHCASH] The comment's actual post text did not contain your blog url (".$permalink.") and so is spam.";
				
				$spam = $spam || !$found;
			} else {
				$spam = true;
				if($options['logging']) 
					$comment['comment_content'] .= "\n\n[WORDPRESS HASHCASH] Snoopy failed to fetch results for the comment blog url (".$comment['comment_author_url'].") with error '".$snoop->error."' and so is spam.";
			}
		}
	} else {
		// Check the wphc values against the last five keys
		$spam = !in_array($_POST["wphc_value"], $options['key']);
		if($options['logging'] && $spam)
			$comment['comment_content'] .= "\n\n[WORDPRESS HASHCASH] The poster sent us '".intval($_POST["wphc_value"])." which is not a hashcash value.";
	}
	
	if($spam){
		$options['comments-spam'] = ((int) $options['comments-spam']) + 1;
		wphc_option($options);
			
		switch($options['moderation']){
			case 'delete':
				add_filter('comment_post', create_function('$id', 'wp_delete_comment($id); die(\'This comment has been deleted by WP Hashcash\');'));
				break;
			case 'akismet':
				add_filter('pre_comment_approved', create_function('$a', 'return \'spam\';'));
				break;
			case 'moderate':
			default:
				add_filter('pre_comment_approved', create_function('$a', 'return 0;'));
				break;
		}
	} else {
		$options['comments-ham'] = ((int) $options['comments-ham']) + 1;
		wphc_option($options);
	}
	
	return $comment;
}

add_filter('preprocess_comment', 'wphc_check_hidden_tag');
?>
