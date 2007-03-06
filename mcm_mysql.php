<?php

/* mcm_mysql.php - handles mysql connection, requests, etc */

function mcm_open_db() {

  global $mcm;
  
  mysql_connect($mcm['db_server'], $mcm['db_username'], $mcm['db_password']) or die ("Unable to connect to database server.\n");
  mysql_select_db($mcm['db_database']) or die ("Unable to open database.\n");
  
}


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
  
  $row = mysql_fetch_assoc($result);
  return $row;
  
}

function mcm_lookup_item($args) {

  /* this function should check is all required params have been passed */
  extract($args, EXTR_PREFIX_ALL, 'arg');

  $flags = Array();
  if ($arg_log) $flags[] = "LOG";
  if ($arg_pun) $flags[] = "PUN";
  if ($arg_bad) $flags[] = "BAD";

  $flags = implode(",", $flags);

  $lookup_query = "SELECT * FROM mdb_item WHERE artist_name = \"${arg_artist_name}\" AND album_name = \"${arg_album_name}\" AND item_quality = \"${arg_item_quality}\"";
  
  $row = get_row_q($lookup_query);
  
  if ($row && $arg_update) {
    
    $query = "UPDATE mdb_item SET item_flags = \"${flags}\" WHERE item_id = ${row['item_id']}";
    do_query($query);
    
  }
  elseif (!$row && $arg_insert) {
    
    $insert_query = "INSERT INTO mdb_item (artist_name, album_name, item_quality, item_flags, item_added) 
                     VALUES(\"${arg_artist_name}\", \"${arg_album_name}\", \"${arg_item_quality}\", \"${flags}\", NOW())";
    do_query($insert_query);
    
    /* recursive call time! */
    $row = get_row_q($lookup_query);
    
  }
  
  return $row;
  
}

function mcm_lookup_itemlist_count($args) {

  extract($args, EXTR_PREFIX_ALL, 'arg');
  /* required:
   *
   * $arg_item_status  - {accepted,rejected,undefined}
   * $arg_user_id      - user_id to limit results to
   * $arg_item_type    - user-defined item type
   *
   */
                    
  $query = "";

  if ($arg_item_status == 'undefined') {
  
    $query = "
    
    SELECT 
      COUNT(*) AS num_items 
    FROM 
      mdb_item 
    LEFT JOIN 
      mdb_status 
    ON 
      mdb_item.item_id = mdb_status.item_id 
    AND 
      mdb_status.user_id = ${arg_user_id} 
    WHERE 
      mdb_item.item_type = '${arg_item_type}' 
    AND 
      mdb_status.item_id IS NULL
    ";

  } else {
  
    $query = "
    
    SELECT 
      COUNT(*) AS num_items 
    FROM 
      mdb_item, 
      mdb_status 
    WHERE 
      mdb_item.item_id = mdb_status.item_id
    AND
      mdb_item.item_type = '${arg_item_type}' 
    AND
      mdb_status.user_id = ${arg_user_id}
    AND
    ";
    
    if ($arg_item_status == 'accepted')
      $query .= "    mdb_status.item_status = 1";
    elseif ($arg_item_status == 'rejected')
      $query .= "    mdb_status.item_status = 0";
      
  }
  
  $row = get_row_q($query);
  
  return $row['num_items'];
  
}

function mcm_lookup_itemlist($args) {

  extract($args, EXTR_PREFIX_ALL, 'arg');
  /* required:
   *
   * $arg_item_status - {accepted,rejected,undefined}
   * $arg_item_type   - user-defined item type
   * $arg_user_id     - user_id to limit results to
   *
   * optional:
   *
   * $order
   * $limit
   *
   */
                    
  $query = "";
  $return = array();
  
  if ($arg_item_status == 'undefined') {
  
    $query = "
    
    SELECT 
      mdb_item.* 
    FROM 
      mdb_item 
    LEFT JOIN 
      mdb_status 
    ON 
      mdb_item.item_id = mdb_status.item_id 
    AND 
      mdb_status.user_id = ${arg_user_id} 
    WHERE 
      mdb_item.item_type = '${arg_item_type}' 
    AND 
      mdb_status.item_id IS NULL
    ";
  } else {
  
    $query = "
    
    SELECT 
      mdb_item.* 
    FROM 
      mdb_item, 
      mdb_status 
    WHERE 
      mdb_item.item_type = '${arg_item_type}' 
    AND 
      mdb_item.item_id = mdb_status.item_id 
    AND 
      mdb_status.user_id = ${arg_user_id}
    AND
    ";
    
    if ($arg_item_status == 'accepted')
      $query .= "    mdb_status.item_status = 1";
    elseif ($arg_item_status == 'rejected')
      $query .= "    mdb_status.item_status = 0";
      
  }
  
  if (isset($arg_order))
    $query .= " ORDER BY " . $arg_order;
    
  if (isset($arg_limit))
    $query .= " LIMIT " . $arg_limit;

  $result = do_query($query);
  
  while ($row = get_row_r($result))
    $return[$row['item_id']] = $row;
    
  return $return;
  
}

function get_user_id($user_name) {
  
  $query = "SELECT user_id FROM mdb_user WHERE user_name = '$user_name'";
  $row = get_row_q($query);
  return $row['user_id'];
  
}

function mcm_lookup_all_users() {

  $users = array();
  
  $query = "SELECT * FROM mdb_user";
  
  $result = do_query($query);
  
  while ($row = get_row_r($result))
    $users[] = $row;
    
  return $users;

}


function mcm_lookup_prefs($user_id) {

  $query = "SELECT * FROM mdb_user_prefs where user_id = ${user_id}";
  
  return get_row_q($query);
  
}

function lookup_user($user_id) {

  $query = "SELECT * FROM mdb_user WHERE user_id = $user_id";

  $row = get_row_q($query);

  return $row;

}

function mcm_lookup_last_item() {

  $query = "

  SELECT
    item_id
  FROM
    mdb_item
  ORDER BY
    item_id DESC
  LIMIT
    1

  ";

  $row = get_row_q($query);

  return $row['item_id'];
  
}

?>
