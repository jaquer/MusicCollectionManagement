#!/usr/bin/php
<?php

/*
 * update_db.php - Script to update the Music Content Management DB
 *
 * $Header: /var/oldvar/cvsroot/jaquer/new-mcm/update_db.php,v 1.4 2004/09/06 04:01:46 jaquer Exp $
 *
 */
 
require_once('mcm_defs.inc');

$name_regexp = "/^(\[.*\]) (\[.*\]) (\[.*\])$/";
$pun_regexp  = "/\.pun$/";
$log_regexp  = "/\.log$/";
$bad_regexp  = "/^--NOT COMPLIANT WITH STANDARD--\.txt$/";

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
      
      
      $path_handle = opendir($path);
      
      /* Now recurse through files in each dir, and determine the rip's flags */
      while ( $file = readdir($path_handle) ) {

        $has_pun = (preg_match($pun_regexp, $file)) ? TRUE : $has_pun;
        $has_log = (preg_match($log_regexp, $file)) ? TRUE : $has_log;
        $has_bad = (preg_match($bad_regexp, $file)) ? TRUE : $has_bad;
        
      }
      closedir($path_handle);
      
    }
    closedir($base_handle);
    
  }
  
}

?>