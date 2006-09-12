#!/usr/bin/php
<?php

/*
 * update_db.php - Script to update the Music Content Management DB
 *
 * $Header: /var/oldvar/cvsroot/jaquer/new-mcm/update_db.php,v 1.5 2004/09/07 06:56:58 jaquer Exp $
 *
 */
 
require_once('mcm_defs.inc.php');

$name_regexp = "/^(\[.*\]) (\[.*\]) (\[.*\])$/";
$pun_regexp  = "/\.pun$/";
$log_regexp  = "/\.log$/";
$bad_regexp  = "/^--NOT COMPLIANT WITH STANDARD--\.txt$/";

$prev_last_rip = lookup_last_rip();

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
      $has_pun = FALSE;
      $has_log = FALSE;
      $has_bad = FALSE;
      
      $path_handle = opendir($path);
      
      /* Now recurse through files in each dir, and determine the rip's flags */
      while ( $file = readdir($path_handle) ) {

        $has_pun = (preg_match($pun_regexp, $file)) ? TRUE : $has_pun;
        $has_log = (preg_match($log_regexp, $file)) ? TRUE : $has_log;
        $has_bad = (preg_match($bad_regexp, $file)) ? TRUE : $has_bad;
        
      }
      closedir($path_handle);
      
      /* Lookup the corresponing IDs. Creating the rows if necessary. */
      $artist_id = lookup_artist_id($artist);
      $album_id  = lookup_album_id($album);
      
      /* No need to keep the rip_id, we're just insuring it exists. */
      lookup_rip_id($artist_id, $artist, $album_id, $album, $quality, $has_pun, $has_log, $has_bad);

    }

  }

  closedir($base_handle);

  $new_last_rip = lookup_last_rip();
  
  if ( $prev_last_rip != $new_last_rip ) {

    /* Notify users of updates */
    $query = "

    SELECT
      user_name,
      user_email
    FROM
      mdb_user
    WHERE
      user_email IS NOT NULL

    ";

    $result = do_query($query);

    while ( $row = get_row_r($result) ) {

      $user_name  = $row['user_name'];
      $user_email = $row['user_email'];

      $message = "Hello $user_name.\n\n";
      $message .= "This is a reminder that there are new albums to be reviewed since the last time you visited the site.\n\n";
      $message .= "Please stop by http://music.izaram.net to decide if you want these new albums added to your music directory.\n\n";
      $message .= "-- \n";
      $message .= "The Management";

      mail($user_email, "Reminder", $message, "From: admin@izaram.net");

    }

  }

}

function lookup_last_rip() {

  $query = "

  SELECT
    rip_id
  FROM
    mdb_rip
  ORDER BY
    rip_id DESC
  LIMIT
    1

  ";

  $row = get_row_q($query);

  return $row['rip_id'];

}



?>
