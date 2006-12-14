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

function mcm_lookup_rip($params) {

  /* this function should check is all required params have been passed */
  
  extract($params);

  $flags = Array();
  if ($log) $flags[] = "LOG";
  if ($pun) $flags[] = "PUN";
  if ($bad) $flags[] = "BAD";

  $flags = implode(",", $flags);

  $lookup_query = "SELECT * FROM mdb_rip WHERE artist_name = \"${artist}\" AND album_name = \"${album}\" AND rip_quality = \"${quality}\"";
  
  $row = get_row_q($lookup_query);
  
  if ($row && $update) {
    
    $query = "UPDATE mdb_rip SET rip_flags = \"${flags}\" WHERE rip_id = ${row['rip_id']}";
    do_query($query);
    
  }
  elseif (!$row && $insert) {
    
    $insert_query = "INSERT INTO mdb_rip (artist_name, album_name, rip_quality, rip_flags, rip_added) 
                     VALUES(\"${artist}\", \"${album}\", \"${quality}\", \"${flags}\", NOW())";
    do_query($insert_query);
    
    /* recursive call time! */
    $row = get_row_q($lookup_query);
    
  }
  
  return $row;
  
}

function mcm_lookup_reviewed_count($params) {

  extract($params); /* $reviewed = {accepted,rejected,undecided}
                       $user_id
                       $type = {MUSIC,AUDIOBOOK,DUPE}
                       $order (optional)
                       $limit (optional)
                    */
                    
  $query = "";

  if ($reviewed == 'undecided') {
  
    $query = "
    
    SELECT 
      COUNT(*) AS num_rips
    FROM 
      mdb_rip 
    LEFT JOIN
      mdb_reviewed 
    ON 
      mdb_rip.rip_id = mdb_reviewed.rip_id 
    AND 
      mdb_reviewed.user_id = ${user_id} 
    WHERE 
      mdb_rip.rip_type = '${type}' 
    AND 
      mdb_reviewed.rip_id IS NULL
    ";

  } else {
  
    $query = "
    
    SELECT 
      COUNT(*) AS num_rips
    FROM
      mdb_rip,
      mdb_reviewed
    WHERE
      mdb_rip.rip_id = mdb_reviewed.rip_id
    AND
      mdb_rip.rip_type = '${type}' 
    AND
      mdb_reviewed.user_id = ${user_id}
    AND
    ";
    
    if ($reviewed == 'accepted')
      $query .= "    mdb_reviewed.rip_status = 1";
    elseif ($reviewed == 'rejected')
      $query .= "    mdb_reviewed.rip_status = 0";
      
  }
  

  $row = get_row_q($query);
  
  return $row['num_rips'];
  
}

function mcm_lookup_reviewed($params) {

  /* this function should check if all required params have been passed */
  
  extract($params); /* $reviewed = {accepted,rejected,undecided}
                       $user_id
                       $type = {MUSIC,AUDIOBOOK,DUPE}
                       $order (optional)
                       $limit (optional)
                    */
                    
  $reviewed = strtolower($reviewed);
  $query = "";
  $return = array();
  
  if ($reviewed == 'undecided') {
  
    $query = "
    
    SELECT 
      mdb_rip.*
    FROM 
      mdb_rip 
    LEFT JOIN
      mdb_reviewed 
    ON 
      mdb_rip.rip_id = mdb_reviewed.rip_id 
    AND 
      mdb_reviewed.user_id = ${user_id} 
    WHERE 
      mdb_rip.rip_type = '${type}' 
    AND 
      mdb_reviewed.rip_id IS NULL
    ";
  } else {
  
    $query = "
    
    SELECT 
      mdb_rip.*
    FROM
      mdb_rip,
      mdb_reviewed
    WHERE
      mdb_rip.rip_type = '${type}' 
    AND 
      mdb_rip.rip_id = mdb_reviewed.rip_id
    AND
      mdb_reviewed.user_id = ${user_id}
    AND
    ";
    
    if ($reviewed == 'accepted')
      $query .= "    mdb_reviewed.rip_status = 1";
    elseif ($reviewed == 'rejected')
      $query .= "    mdb_reviewed.rip_status = 0";
      
  }
  
  if (isset($order))
    $query .= " ORDER BY " . $order;
    
  if (isset($limit))
    $query .= " LIMIT " . $limit;

  $result = do_query($query);
  
  while ($row = get_row_r($result))
    $return[$row['rip_id']] = $row;
    
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

function mcm_lookup_last_rip() {

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
