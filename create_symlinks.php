#!/usr/bin/php
<?php

/*
 * create_symlinks.php - Script that creates symlinks for each user
 *
 * $Header: /var/oldvar/cvsroot/jaquer/new-mcm/create_symlinks.php,v 1.2 2004/09/06 03:38:19 jaquer Exp $
 *
 */

require_once('mcm_defs.inc');

if ( $argc < 3 || $argc > 3 ) die("Usage: " . $argv[0] . " username dest_dir\n");

$user_name = $argv[1];
$dest_dir  = $argv[2];

if ( ! is_dir(realpath($dest_dir)) ) die("Directory '$dest_dir' does not exist.\n");
if ( ! is_writable($dest_dir) ) die("Cannot write to '$dest_dir'\n");

$dest_dir = realpath($destdir);

if ( ! $user_id = get_user_id($user_name) )
  die("User: '$user_name' does not exist.\n");

$existing_links = get_existing_links($dest_dir);


function get_existing_links($base_dir) {

  $dirs = array();
  $links = array();
  
  /* Get a list of all dirs */
  $dirs[] = $base_dir;

  for ($index = 0; $index < count($dirs); $index++) {

    $current_dir = $dirs[$index];
    $dir_handle = opendir($current_dir);

    while ($item = readdir($dir_handle)) {

      if ( ($item == ".") || ($item == "..") ) continue;

      $item_path = $current_dir . "/" . $item;
      
      if ( is_dir($item_path) ) {
        $dirs[] = $item_path;
      }
      
      if ( is_link($item_path) ) {
        $links['path'][] = $item_path;
        $links['rip_id'][] = lookup_link($item_path);
      } 

    }

    closedir($dir_handle);

  }
  
  return $links;

}

function lookup_link($item_path) {
  
  $name_regexp = "/^(\[.*\]) (\[.*\]) (\[.*\])$/";
  
  $target = readlink($item_path);
  
  if ( preg_match($name_regexp, $target, $matches) ) {
    /* Remove the leading/trailing brackets from the names */
    foreach ($matches as $key => $value)
      $matches[$key] = trim($value, '[]');

    $artist      = $matches[1];
    $album       = $matches[2];
    $rip_quality = $matches[3];
    
    $artist_id = lookup_artist_id($artist);
    $album_id  = lookup_album_id($album);
    
    $query = "SELECT rip_id FROM mdb_rip WHERE artist_id = $artist_id AND album_id = $album_id AND rip_quality = '$rip_quality'";

    $row = get_row_q($query);

    return $row['rip_id'];
    
  }
  
}

function get_user_id($user_name) {
  
  $query = "SELECT user_id FROM mdb_user WHERE user_name = '$user_name'";
  $row = get_row_q($query);
  return $row['user_id'];
  
}

?>
