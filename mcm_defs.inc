<?php

/*
 * mcm_defs.inc - Function/global definitions for the Music Content Management
 *
 * $Header: /var/oldvar/cvsroot/jaquer/new-mcm/mcm_defs.inc,v 1.4 2004/09/07 06:55:22 jaquer Exp $
 *
 */

/* Error reporting/debugging */
ini_set("display_errors", "1");

/* DB globals */
$db_host = "localhost";
$db_name = "musicdb";
$db_user = "musicdb";
$db_pass = "musiiza";

/* Main music dir */
$music_dir = "/home/data/music";

mysql_connect($db_host, $db_user, $db_pass) or die ("Unable to connect to db\n");
mysql_select_db($db_name) or die ("Unable to open db\n");


/* IN: A MySQL query */
/* OUT: A MySQL result */
function do_query($query) {
  
  $result = mysql_query($query) or die("Query failed: " . mysql_error() . "\n<pre>$query</pre>");
  return $result;
  
}

/* IN: A MySQL query */
/* OUT: A MySQL_associated row */
function get_row_q($query) {
  
  $result = do_query($query);
  $row = get_row_r($result);
  return $row;
  
}

/* IN: A MySQL result */
/* OUT: A MySQL associated row */
function get_row_r($result) {
  
  $row = mysql_fetch_array($result);
  return $row;
  
}

function lookup_artist_id($artist, $debug = FALSE) {
  
  $query = "SELECT artist_id FROM mdb_artist WHERE artist_name = \"$artist\"";
  
  if ( $row = get_row_q($query) ) {
    
    $artist_id = $row['artist_id'];
    if ($debug) echo "!!";
    
  }
  else {
    
    $query = "INSERT INTO mdb_artist (artist_name) VALUES (\"$artist\")";
    $result = do_query($query);
    $artist_id = mysql_insert_id();
    if ($debug) echo "++";
    
  }
  
  if ($debug) echo ">> $artist ($artist_id)\n";
  
  return $artist_id;
  
}

function lookup_album_id($album, $debug = FALSE) {
  
  $query = "SELECT album_id FROM mdb_album WHERE album_name = \"$album\"";
  
  if ( $row = get_row_q($query) ) {
    
    $album_id = $row['album_id'];
    if ($debug) echo "!!";
    
  }
  else {
    
    $query = "INSERT INTO mdb_album (album_name) VALUES (\"$album\")";
    $result = do_query($query);
    $album_id = mysql_insert_id();
    if ($debug) echo "++";
    
  }
  
  if ($debug) echo ">> $album ($album_id)\n";
  
  return $album_id;
  
}

function lookup_rip_id($artist_id, $artist_name, $album_id, $album_name, $rip_quality, $has_pun = FALSE, $has_log = FALSE, $has_bad = FALSE, $debug = FALSE) {
  
  $rip_flags = "";
  $rip_flags .= ($has_pun) ? "P" : "";
  $rip_flags .= ($has_log) ? "L" : "";
  $rip_flags .= ($has_bad) ? "X" : "";

  $query = "SELECT rip_id FROM mdb_rip WHERE artist_name = \"$artist_name\" AND album_name = \"$album_name\" AND rip_quality = \"$rip_quality\"";
  
  if ( $row = get_row_q($query) ) {
    
    $rip_id = $row['rip_id'];

    $query = "UPDATE mdb_rip SET rip_flags = \"$rip_flags\" WHERE rip_id = $rip_id";
    do_query($query);
    if ($debug) echo "!!";
    
  }
  else {
    
    
    $query = "INSERT INTO mdb_rip (artist_id, artist_name, album_id, album_name, rip_quality, rip_flags, rip_added) VALUES($artist_id, \"$artist_name\", $album_id, \"$album_name\", \"$rip_quality\", \"$rip_flags\", NOW())";
    $result = do_query($query);
    $rip_id = mysql_insert_id();
    if ($debug) echo "++";
    
  }
  
  if ($debug) echo ">> rip ($rip_id)\n";
  
  return $rip_id;
  
}

function get_user_id($user_name) {
  
  $query = "SELECT user_id FROM mdb_user WHERE user_name = '$user_name'";
  $row = get_row_q($query);
  return $row['user_id'];
  
}

function lookup_user($user_id) {

  $query = "SELECT * FROM mdb_user WHERE user_id = $user_id";

  $row = get_row_q($query);

  return $row;

}

?>
