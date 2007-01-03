<?php

/* mcm_symlinks.php /* read & create symlinks */

function mcm_read_symlinks($path) {

  $dirs  = array();
  $paths  = array();
  
  /* recurse through $path, adding directories to $dirs[] array */
  $dirs[] = $path;

  for ($index = 0; $index < count($dirs); $index++) {

    $current = $dirs[$index];
    $handle  = opendir($current);

    while ($entry = readdir($handle)) {

      if ( ($entry == ".") || ($entry == "..") ) continue;

      $path = "${current}/${entry}";
      
      if ( is_dir($path) && is_readable($path)) {
        $dirs[] = $path;
      }
      
      if ( is_link($path) ) {
      
        if ($item = lookup_link($path)) {
        
          $paths[$item['item_id']] = dirname($path);
          continue;
          
        }
        
      }
      
    }
    
    closedir($handle);
    
  }
  
  return $paths;
  
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