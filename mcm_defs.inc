<?php

/*
 * mcm_defs.inc - Function/global definitions for the Music Content Management
 *
 * $Header: /var/oldvar/cvsroot/jaquer/new-mcm/mcm_defs.inc,v 1.2 2004/09/06 03:30:21 jaquer Exp $
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

function connect_db() {
  
  mysql_connect($db_host, $db_user, $db_pass) or die ("Unable to connect to db\n");
  mysql_select_db($db_name) or die ("Unable to open db\n");
  
}

/* IN: A MySQL query */
/* OUT: A MySQL result */
function do_query($query) {
  
  $result = mysql_query($query) or die("Query failed: " . mysql_error() . "\n<pre>$query</pre>");
  return $result;
  
}

/* IN: A MySQL query */
/* OUT: A MySQL_associated row */
function get_row_q($query, $remove_entities = TRUE) {
  
  $result = do_query($query);
  $row = get_row_r($result, $remove_entities);
  return $row;
  
}

/* IN: A MySQL result */
/* OUT: A MySQL associated row */
function get_row_r($result, $remove_entities = TRUE) {
  
  $row = mysql_fetch_array($result);
  if ($remove_entities)
    $row = row_to_htmlentities($row);
  return $row;
  
}

?>