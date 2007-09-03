<?php

/* mcm_symlinks.php - read, verify, create and delete symlinks */

function mcm_verify_symlinks_against_virtualfs($args) {

  global $mcm;
  
  error_reporting(E_ERROR);
  
  extract($args, EXTR_PREFIX_ALL, 'arg');
  /* 
   * required:
   *
   * $arg_path      - base dir
   * $arg_virtualfs - virtualfs listing
   *
   */
   
  $root = $mcm['basedir'];
  $dirs  = array();
  
  /* recurse through $arg_path, adding directories to $dirs[] array */
  $dirs[] = $arg_path;

  for ($index = 0; $index < count($dirs); $index++) {

    $current = $dirs[$index];
    $handle  = opendir($current);

    while ($entry = readdir($handle)) {

      if (($entry == ".") || ($entry == "..")) continue;

      $path = "${current}/${entry}";
      
      if ( is_dir($path) && is_readable($path)) {
        $dirs[] = $path;
        continue;
      }
      
      if ($target = readlink($path)) {      /* read a link's target, returns 'false' if $path is not a symlink */
        if (strpos($target, $root) === 0) { /* $target falls within mcm's root folder */
          if (! stat($path)) {              /* broken symlink */
            echo "      removing broken symlink: ${path}\n";
            unlink($path);
          }
          
          if (($location = array_search($target, $arg_virtualfs)) === FALSE) { /* uh? $target is within $root, but not in virtualfs... nuke it! */
            echo "      removing extra file: ${path}\n";
            unlink($path);
          } else { /* remove from virtualfs listing */
            unset($arg_virtualfs[$location]);
          }
        }
      }
      
    }
    
    closedir($handle);
    
  }
  
  return $arg_virtualfs;
  
}

function lookup_link($symlink) {

  /* TODO: this pattern *has* to be moved to a central location */
  $name_regexp = "/^\[(.+)\] \[(.+)\] \[(.+)\]$/";
  
  $target = readlink($symlink);
  
  /* the symlink could be targeting either a file inside a item's dir, or the dir itself */
  if (preg_match($name_regexp, basename(dirname($target)), $matches) || preg_match($name_regexp, basename($target), $matches)) {
  
    $params = array('artist_name' => $matches[1], 'album_name'  => $matches[2], 'item_quality' => $matches[3], 'update' => FALSE, 'insert' => FALSE);
    
    return mcm_action('lookup_item', $params);
    
  }
  
}


?>