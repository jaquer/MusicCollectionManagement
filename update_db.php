#!/usr/bin/php
<?php

/*
 * update_db.php - Script to update the Music Content Management DB
 *
 * $Header: /var/oldvar/cvsroot/jaquer/new-mcm/update_db.php,v 1.3 2004/09/06 03:47:49 jaquer Exp $
 *
 */
 
require_once('mcm_defs.inc');

$name_regexp = "/^(\[.*\]) (\[.*\]) (\[.*\])$/";

if (is_dir($music_dir)) {

  $base_handle = opendir($music_dir);
  
  /* Loop through each subdir */
  while ($dir = readdir($base_handle)) {
    
    $path = $music_dir . "/" . $dir;
    
    if (is_dir($path) && preg_match($name_regexp, $dir, $matches)) {
      
      /* Remove the leading/trailing brackets from the names */
      foreach ($matches as $key => $value) {
        $matches[$key] = trim($value, '[]');
      }
      $artist  = $matches[1];
      $album   = $matches[2];
      $quality = $matches[3];
      
    }
    
    closedir($base_handle);
    
  }
  
}

?>