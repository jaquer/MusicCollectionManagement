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
$dest_dir  = realpath($argv[2]);

if ( ! is_dir($dest_dir) ) die("Directory '$dest_dir' does not exist.\n");
if ( ! touch($dest_dir . "/test.tmp") )
  die("Cannot write to '$dest_dir'\n");
else
  unlink($dest_dir . "/test.tmp");

if ( ! $user_id = get_user_id($user_name) )
  die("User: '$user_name' does not exist.\n");



function get_user_id($user_name) {
  
  $query = "SELECT user_id FROM mdb_user WHERE user_name = '$user_name'";
  $row = get_row_q($query);
  return $row['user_id'];
  
}

?>