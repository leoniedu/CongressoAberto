<?php
/*
Plugin Name: Fix Rss Feeds
Plugin URI: http://www.flyaga.info/en/wordpress/plugins/fix-rss-feed-error-wordpress-plugins.htm
Description: fix wordpress rss feed error "Error on line 2: The processing instruction target matching "[xX][mM][lL]" is not allowed." while you burn wordpress rss feed from http://www.feedburner.com, also fix error "XML or text declaration not at start of entity" in firefox, and fix error "XML declaration not at beginning of document" in opera.
Author: flyaga li
Version: 1.03
date:2009-5-24
Author URI: http://www.flyaga.info/
Change log:
2008-12-30 release v1.0
2009-02-04 release v1.01, fixed some errors, add create backup files before change php files, thanks for Willem Kossen's advice.
2009-02-16 release v1.02, fixed some errors
2009-05-24 release v1.03, add "check wordpress rss feed error" button, thanks for Wanda's advice.
*/

load_plugin_textdomain('fixrssfeed', PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)));

/*option menu*/
if(!function_exists("fixrssfeed_reg_admin")) {
	/**
	* Add the options page in the admin menu
	*/
	function fixrssfeed_reg_admin() {
		if (function_exists('add_options_page')) {
			add_options_page('Fix Rss Feed', 'Fix Rss Feed',8, basename(__FILE__), 'fixrssfeedOption');
			//add_options_page($page_title, $menu_title, $access_level, $file).
		}
	}
}


add_action('admin_menu', 'fixrssfeed_reg_admin');

if(!function_exists("fixrssfeedOption")) {
  function fixrssfeedOption(){
	do_fixrssfeed_action();
?>
	<div class="wrap" style="padding:10px 0 0 10px;text-align:left">
	<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
	<p><h3><?php _e("Click the button bellow to fix wordpress rss feed error","fixrssfeed");?></h3></p>
	<p><?php _e('It will fix wordpress rss feed error "<a href="http://www.flyaga.info/wordpress/fix-wordpress-rss-feed-error.htm" target=_blank>Error on line 2: The processing instruction target matching "[xX][mM][lL]" is not allowed.</a>" while you burn rss feed from http://www.feedburner.com, also fix error "<a href="http://www.flyaga.info/wordpress/fix-wordpress-rss-feed-error.htm" target=_blank>XML or text declaration not at start of entity</a>" in firefox, and fix error "<a href="http://www.flyaga.info/wordpress/fix-wordpress-rss-feed-error.htm" target=_blank>XML declaration not at beginning of document</a>" in opera</a>.');?></p>
  <p><b>Please select a way to support my fix rss feed plugin, help me to  
  continue support and development of this free software!</b></p> 
  <blockquote>
  <p><input type="radio" value="link" name="supportaction" checked>Bookmark <a href="http://www.flyaga.info">Fix rss feed</a> to your links<br>   
  <input type="radio" value="order" name="supportaction">Donate $4.99&nbsp;   
  using palpay <a href="http://www.flyaga.info/go/fix-rss-feed-donations1.php" target="_blank"><img border="0" src="http://www.flyaga.info/image/donate.gif" width="62" height="31"></a><br>
  <input type="radio" value="none" name="supportaction">None</p>
  </blockquote>
	<p><input type="submit" value="<?php _e("Check wordpress rss feed error","fixrssfeed");?>" id="checkrssfeedDelbt" name="checkrssfeedDelbt" onClick="return fixrssfeedinput(0); " /> <input type="submit" value="<?php _e("Fix wordpress rss feed error","fixrssfeed");?>" id="fixrssfeedDelbt" name="fixrssfeedDelbt" onClick="return fixrssfeedinput(1); " /></p>
	</form>
	<p><?php //_e("Note:this will not delete data from your databases, it is only delete head and tail blank line in your php files","fixrssfeed");?></p>
  <br><h3>Thanks for using this plugin!</h3>
  <p>If you are satisfied with the results, isn't it worth at least $4.99? <a href="http://www.flyaga.info/go/fix-rss-feed-donations1.php" target="_blank"><img border="0" src="http://www.flyaga.info/image/donate.gif" width="62" height="31"></a> 
  help me to continue support and development of this free software!</p> 
  <h3>Informations and support</h3>
  <p>Check <a href="http://www.flyaga.info/en/wordpress/plugins/fix-rss-feed-error-wordpress-plugins.htm" target="_blank">http://www.flyaga.info/en/wordpress/plugins/fix-rss-feed-error-wordpress-plugins.htm</a> 
  for updates and comment there if you have any problems / questions / 
  suggestions.</p>
	</div>

	<SCRIPT LANGUAGE="JavaScript">
	<!--
	function fixrssfeedinput(isfix)
    {
		if(isfix)
			document.getElementById('fixrssfeedDelbt').value ='<?php _e("Please Wait...","fixrssfeed");?>';
		else
			document.getElementById('checkrssfeedDelbt').value ='<?php _e("Please Wait...","fixrssfeed");?>';

        if(document.getElementById('supportaction').value=='order')
          window.open('http://www.flyaga.info/go/fix-rss-feed-donations1.php','');
		return true;
	}
	//-->
	</SCRIPT>
<?php
	}
}

/*
end of feed fix
*/
// deal with feed fix
function do_fixrssfeed_action(){
  if( (!empty($_POST['fixrssfeedDelbt']))||(!empty($_POST['checkrssfeedDelbt'])) ){
    include("findfile.php");
    $page_root = ABSPATH;
    debugwrite(__("Scanning ","fixrssfeed").$page_root);
    $phplist = getFiles($page_root);
    $foundfilesarray=array();
    foreach ($phplist as $file) {
      if (!$data = @file($file))
      {
        if(filesize($file)!=0)
          errorwrite(__("Error opening ","fixrssfeed").$file);
        continue;
      }

      $isfound=false;
      $content = implode("",$data);

      //search whether include blank lines at begin
      if(preg_match("/^\s/",$content))
      {
        $isfound=true;
        array_push($foundfilesarray,$file);

        errorwrite($file.__(" has head blank lines","fixrssfeed"),"#0000FF");
      }

      //search whether include blank lines at tail
      if(preg_match("/(\s)$/",$content))
      {
        if(!$isfound)
          array_push($foundfilesarray,$file);

        errorwrite($file.__(" has tail blank lines","fixrssfeed"),"#0000FF");
      }
    }

    $issuccess=true;
    if((count($foundfilesarray)>0)&&(!empty($_POST['fixrssfeedDelbt'])))
    {
      debugwrite('');
      debugwrite(count($foundfilesarray).__(" php files are found head and tail blank lines, now fix them...","fixrssfeed"));
      foreach ($foundfilesarray as $file)
      {
        //create backup files before change php files
        if (!copy($file, $file.'.bak'))
        {
          errorwrite($file.'.bak'.__(" can not be created, please check file permission","fixrssfeed"));
          continue;
        }

        $data = @file($file);
        $content = implode("",$data);
        //replace head blank lines
        $content=preg_replace("/^(\s)*/","",$content);
        //replace tail blank lines
        $content=preg_replace("/(\s)*$/","",$content);

        //save
        if(WriteTextFile($file,$content))
          debugwrite(__("Success, fix file=","fixrssfeed").$file);
        else
        {
          errorwrite($file.__(" can not be writed, please check file permission","fixrssfeed"));
          $issuccess=false;
        }
      }

      if($issuccess)
        $msg = __("Now your wordpress rss feed is fixed successfully, please check your wordpress rss feed from ","fixrssfeed")."<a href='".get_option('home')."/feed' target=_blank>".get_option('home')."/feed</a>";
      else
        $msg = __("There is some errors, please try it again after you check them!","fixrssfeed");
    }
    else
    {
      if(!empty($_POST['checkrssfeedDelbt']))
      {
        if(count($foundfilesarray)>0)
          $msg = __("There is ".count($foundfilesarray)." errors, you can click 'fix wordpress rss feed error' button to fix these errors or manual fix these errors","fixrssfeed");
        else
          $msg = __("No found blank lines from your wordpress php files, now your feed is ok!","fixrssfeed");
      }
      else
        $msg = __("No found blank lines from your wordpress php files, now your feed is ok!","fixrssfeed");
    }

    if($_POST['supportaction']=='link')
      if(count(get_bookmarks(array('search' => 'flyaga')))<1)
        wp_insert_link(array('link_name'=>'Fix Rss Feed','link_url'=>'http://www.flyaga.info','link_description'=>'wordpress plugin that fix rss feed error "Error on line 2: The processing instruction target matching "[xX][mM][lL]" is not allowed." while burn feed from feedburner.com','link_target'=>'_blank'));
  }
  if($msg)
	echo '<div class="updated"><strong><p>'.$msg.'</p></strong></div>';
}
?>