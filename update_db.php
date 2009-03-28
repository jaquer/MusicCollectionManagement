#!/usr/bin/php
<?php

/*
 * update_db.php - updates the db... :/
 *
 */
 
if (strcmp(PHP_SAPI, 'cli') != 0) {
  die("this script can only be called from the command line - exiting\n");
}

require_once('mcm_core.php');

if (posix_geteuid() != 0) {
  if (! isset($mcm['userupdate'])) {
    die("only root is allowed to update the db - exiting\n");
  }
}

echo "db update starting\n";

echo "  reading directory list... ";
$dirlist  = mcm_action('read_dirlist');
echo count($dirlist) . " directories found\n";

echo "  parsing directory list... ";
$itemlist = mcm_action('parse_dirlist', $dirlist);
echo count($itemlist) . " valid entries found\n";

$last_item = mcm_action('lookup_last_item');

/* proceed with update */
foreach ($itemlist as $item) {

  $params = array('artist_name' => $item['artist_name'], 'album_name' => $item['album_name'], 'item_quality' => $item['item_quality'],
                  'pun' => $item['pun'], 'log' => $item['log'], 'bad' => $item['bad'], 'update' => TRUE, 'insert' => TRUE);
                  
  mcm_action('lookup_item', $params);
  
}

if (0) {
//if ($count = (mcm_action('lookup_last_item') - $last_item)) {

  echo "  " . $count . " new items found - notifying users\n";

  $users = mcm_action('lookup_all_users');
  
  foreach ($users as $user) {
  
    if (!empty($user['user_email'])) {
    
      $name = $user['user_name'];
      $email = $user['user_email'];
      
      $message = "Hello ${name}.\n\n";
      $message .= "This is a reminder that there";
      if ($count == 1) {
        $message .= " is one new album ";
      } else {
        $message .= " are " . $count . " new albums ";
      }
      $message .= "to be reviewed since the last time you visited the site.\n\n";
      $message .= "Please stop by http://lisa/music to decide if you want these new albums added to your music directory.\n\n";
      $message .= "-- \n";
      $message .= "The Management";
      
      mail($email, "Reminder", $message, "From: admin@izaram.net");
      
    }
    
  }
  
}

echo "db update completed\n";

?>
