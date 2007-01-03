<?php

/*
 * mcm_dirlist.php - functions dealing with reading the musicdir
 *
 */
 
function mcm_read_dirlist() {

  global $mcm;
  
  $cwd = getcwd();
  $dirlist = Array();
  
  chdir($mcm['basedir']);
  
  foreach (glob("*") as $entry) {
  
    if (is_dir($entry)) 
      $dirlist[] = $entry;
    
  }
  
  chdir($cwd);
  return $dirlist;
  
}

function mcm_parse_dirlist($dirlist) {

  global $mcm;
  
  $name_regexp = "/^\[(.+)\] \[(.+)\] \[(.+)\]$/";
  $pun_regexp  = "/\.pun$/";
  $log_regexp  = "/\.log$/";
  $bad_regexp  = "/^--NOT COMPLIANT WITH STANDARD--\.txt$/";
  
  $cwd = getcwd();
  $itemlist = Array();
  
  foreach ($dirlist as $directory) {
  
    if (preg_match($name_regexp, $directory, $matches)) {
    
      $artist_name  = $matches[1];
      $album_name   = $matches[2];
      $item_quality = $matches[3];
      $pun = FALSE;
      $log = FALSE;
      $bad = FALSE;
      
      chdir($mcm['basedir'] . '/' . $directory);
      
      foreach (glob("*") as $file) {
      
        if (preg_match($pun_regexp, $file))
          $pun = TRUE;
        if (preg_match($log_regexp, $file))
          $log = TRUE;
        if (preg_match($bad_regexp, $file))
          $bad = TRUE;
          
      }
      
      $itemlist[] = array('artist_name' => $artist_name, 'album_name' => $album_name, 'item_quality' => $item_quality,
                          'pun' => $pun, 'log' => $log, 'bad' => $bad);
        
    }
    
  }
  
  chdir($cwd);
  return $itemlist;
  
}

?>