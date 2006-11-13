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

    while ($item = readdir($handle)) {

      if ( ($item == ".") || ($item == "..") ) continue;

      $item_path = "${current}/${item}";
      
      if ( is_dir($item_path) && is_readable($item_path)) {
        $dirs[] = $item_path;
      }
      
      if ( is_link($item_path) ) {
      
        if ($rip  = lookup_link($item_path)) {
        
          $paths[$rip['rip_id']] = dirname($item_path);
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
  
  /* the symlink coul be targeting either a file inside a rip's dir, or the dir itself */
  if (preg_match($name_regexp, basename(dirname($target)), $matches) || preg_match($name_regexp, basename($target), $matches)) {
  
    $params = array('artist' => $matches[1], 'album'  => $matches[2], 'quality' => $matches[3], 'update' => FALSE, 'insert' => FALSE);
    
    return mcm_action('lookup_rip', $params);
    
  }
  
}


?>