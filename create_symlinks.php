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

$dest_dir = realpath($dest_dir);

if ( ! $user_id = get_user_id($user_name) )
  die("User: '$user_name' does not exist.\n");

$existing_links = get_existing_links($dest_dir);
$accepted_rips  = get_accepted_rips($user_id, $music_dir);
$rejected_rips  = get_rejected_rips($user_id);

if ( ! $accepted_rips )
  die("No rips have been reviewed by user '$user_name'\n");

update_symlinks($existing_links, $accepted_rips, $rejected_rips, $dest_dir);

function get_accepted_rips($user_id, $music_dir) {

  $query = "

  SELECT 
    mdb_rip.rip_id,
    mdb_artist.artist_name,
    mdb_album.album_name,
    mdb_rip.rip_quality
  FROM
    mdb_rip,
    mdb_artist,
    mdb_album,
    mdb_reviewed
  WHERE
    mdb_rip.rip_id = mdb_reviewed.rip_id
  AND
    mdb_rip.artist_id = mdb_artist.artist_id
  AND
    mdb_rip.album_id = mdb_album.album_id
  AND
    mdb_reviewed.user_id = $user_id
  AND
    mdb_reviewed.rip_status = 1

  ";

  $result = do_query($query);

  while ( $row = get_row_r($result) ) {
    $accepted['path'][] = $music_dir . "/[" . $row['artist_name'] . "] [" . $row['album_name'] . "] [" . $row['rip_quality'] . "]";
    $accepted['rip_id'][] = $row['rip_id'];
  }

  return $accepted;
  
}

function get_existing_links($base_dir) {

  $dirs = array();
  $links = array();
  $links['path']   = array();
  $links['rip_id'] = array();
  
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
        if ( ! in_array(dirname($item_path), $links['path']) ) {
          if ( $rip_id = lookup_link($item_path) ) {
            if ( ! in_array($rip_id, $links['rip_id']) ) {
              $links['path'][] = dirname($item_path);
              $links['rip_id'][] = $rip_id;
            }
          }
        }
      }

    }

    closedir($dir_handle);

  }

  return $links;

}

function lookup_link($item_path) {
  
  $name_regexp = "/^.*(\[.*\]) (\[.*\]) (\[.*\])$/";
  
  $target = readlink($item_path);
  
  if ( preg_match($name_regexp, dirname($target), $matches) || preg_match($name_regexp, $target, $matches) ) {
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

function get_rejected_rips($user_id) {

  $query = "

  SELECT 
    rip_id
  FROM
    mdb_reviewed
  WHERE
    mdb_reviewed.user_id = $user_id
  AND
    mdb_reviewed.rip_status = 0

  ";

  $result = do_query($query);

  while ( $row = get_row_r($result, FALSE) ) {
    $rejected['rip_id'][] = $row['rip_id'];
  }
  
  return $rejected;
  
}

function update_symlinks($existing_links, $accepted_rips, $rejected_rips, $dest_dir) {

  $to_create = ( isset($existing_links['rip_id']) ) ? array_diff($accepted_rips['rip_id'], $existing_links['rip_id']) : $accepted_rips['rip_id'];

  $base_dest = $dest_dir . "/_new";
  if ( ! is_dir($base_dest) )
    mkdir($base_dest);

  foreach ($to_create as $key => $value) {

    $source   = $accepted_rips['path'][$key];
    $dest_dir = $base_dest . "/" . basename($source);
    mkdir($dest_dir);
    chdir($dest_dir);

    $handle = opendir($source);

    while ( $file = readdir($handle) ) {
      if ( preg_match("/^.+\.(mp3|jpg|m3u)$/", $file) ) {
        $command = "ln -s \"$source/$file\"";
        exec($command);
      }
    }
    closedir($handle);

  }

}


?>
