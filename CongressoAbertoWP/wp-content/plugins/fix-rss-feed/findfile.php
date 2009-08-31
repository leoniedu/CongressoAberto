<?php
set_time_limit(0);

// list of file filters

global $filefilters,$disallow_dir,$disallow_file;

$filefilters[]=".php";
//$filefilters[]=".htm";

// list of allowed directories
//$allow_dir[]=".";
//$allow_dir[]="plugins";
//$allow_dir[]="themes";

// list of disallowed directories 
$disallow_dir[] = "";
//$disallow_dir[] = "wp-admin";
//$disallow_dir[] = "wp-includes";

// list of disallowed file types
$disallow_file[] = ".bak";

// simple compare function: equals 
function ar_contains($key, $array) {
  if(count($array)>0)
  foreach ($array as $val) {
    if ($key == $val) {
        return true;
    }
  }
  return false;
}

// better compare function: contains 
function fl_contains($key, $array) {
  if(count($array)>0)
  foreach ($array as $val) {
//  echo $key.'-------'.$val.'<br>';
    if($val=='')
      continue;
    $pos = strpos($key, $val);
    if ($pos == FALSE) continue;
    return true;
  }

  return false;
}

// this function changes a substring($old_offset) of each array element to $offset 
function changeOffset($array, $old_offset, $offset) {
  $res = array();
  foreach ($array as $val) {
    $res[] = str_replace($old_offset, $offset, $val);
  }
  return $res;
}

// this walks recursivly through all directories starting at page_root and
//   adds all files that fits the filter criterias 
// taken from Lasse Dalegaard, http://php.net/opendir
function getFiles($directory, $directory_orig = "", $directory_offset="") 
{
  global $disallow_dir, $disallow_file,$filefilters,$allow_dir;
  if ($directory_orig == "") $directory_orig = $directory;

  if($dir = opendir($directory)) 
  {
    // Create an array for all files found
    $tmp = Array();

    // Add the files
    while($file = readdir($dir)) 
    {
      // Make sure the file exists
      if($file != "." && $file != ".." && $file[0] != '.' ) {
        // If it's a directiry, list all files within it
        if(strpos($directory . "/" . $file,"//")===false)
          $delimit="/";
        else
          $delimit="";
        if(is_dir($directory . $delimit . $file)) {
          $disallowed_abs = fl_contains($directory.$delimit.$file, $disallow_dir); // handle directories with pathes
          $disallowed = ar_contains($file, $disallow_dir); // handle directories only without pathes
          if ($disallowed || $disallowed_abs) continue;

          $tmp2 = getFiles($directory . $delimit . $file, $directory_orig, $directory_offset);//changeOffset(getFiles($directory . "/" . $file, $directory_orig, $directory_offset), $directory_orig, $directory_offset);
          if(is_array($tmp2)) {
            $tmp = array_merge($tmp, $tmp2);
          }
        } else {  // files
          if (fl_contains($file, $filefilters)==false) continue;
          if (fl_contains($file, $disallow_file)) continue;
          //array_push($tmp, str_replace($directory_orig, $directory_offset, $directory."/".$file));
          array_push($tmp, $directory.$delimit.$file);
        }
      }
    }

    // Finish off the function
    closedir($dir);
    return $tmp;
  }
}

function WriteTextFile($filename,$somecontent)
{
  if (is_writable($filename)) {
    if (!$handle = fopen($filename, 'w')) {
         errorwrite("Cannot open file '$filename'");
        return false;
    }

    // Write $somecontent to our opened file.
    if (fwrite($handle, $somecontent) === FALSE) {
        errorwrite("Cannot write to file '$filename'");
        return false;
    }

    fclose($handle);

  } else {
    errorwrite("The file '$filename' is not writable");
    return false;
  }
  return true;
}

 function debugwrite($text)
 {
   echo $text."<br>";
 }

 function debugwriteend($text)
 {
   die($text."<br>");
 }

 function errorwrite($text,$color="#FF0000")
 {
   debugwrite("<font color='$color'>".$text."</font>");
 }

 function errorwriteend($text,$color="#FF0000")
 {
   die("<font color='$color'>".$text."</font><br>");
 }
?>