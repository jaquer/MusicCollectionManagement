<?php

/*
 * web_ui.php - Web UI for the Music Management System
 *
 * $Header: /var/oldvar/cvsroot/jaquer/new-mcm/web_ui.php,v 1.2 2004/09/06 03:38:19 jaquer Exp $
 *
 */

require_once('mcm_defs.inc');
session_start();

$user_name          = (isset($_POST['user_name'])) ? $_POST['user_name'] : "";
$cleartext_password = (isset($_POST['password'])) ? $_POST['password'] : "";
$encoded_password   = (isset($_SESSION['encoded_password'])) ? $_SESSION['encoded_password'] : md5($cleartext_password);

/* Rips per page */
$limit = 30;

$start  = (isset($_POST['start'])) ? $_POST['start'] : "0";
$status = (isset($_POST['submit'])) ? $_POST['submit'] : "";

include('html_header.inc');

if ( $user_name == "" ) {

  require_login($user_name);

} elseif ( ! $user_id = validate_login($user_name, $encoded_password) ) {
?>
    <h3>Wrong username/password</h3>
<?php
  require_login($user_name);
} else {

  /* Proper login */
  if ( ! isset($_SESSION['encoded_password']) ) $_SESSION['encoded_password'] = $encoded_password;
  unset($_POST['password']);

  switch($status) {
    case "Finish":
      print_confirmation($user_id);
      break;
    default:
      print_rips_list($user_id, $start, $status);
      break;
  }

}

include('html_footer.inc');

/* Functions */

function print_rips_list($user_id, $start, $status) {

  $limit = 30;
  $user = lookup_user($user_id);

  $start = ( $status == "Next" ) ? $start + $limit : $start;
  $start = ( $status == "Prev" ) ? $start - $limit : $start;
  $_POST['start'] = $start;
?>
    <p style="font-size: 120%; font-weight: bold;">Welcome <?= $user['user_name']; ?>. Your last visit was on: <?= $user['user_last_visit']; ?></p>

    <form method="post" action="<?= $_SERVER['PHP_SELF']; ?>">
      <!-- Previous page data -->
<?php

  foreach ( $_POST as $key => $value ) {
?>
      <input type="hidden" name="<?= $key; ?>" value="<?= $value; ?>">
<?php
  }
?>
      <!-- End previous page data -->
      <table>
        <tr>
          <th>Artist</th>
          <th>Album</th>
          <th>Yes</th>
          <th>No</th>
          <th>Maybe</th>
          <th>Quality</th>
          <th>Flags</th>
          <th>Added</th>
          <th>Play</th>
        </tr>
<?php

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
    mdb_reviewed.user_id = $user_id
  WHERE 
    mdb_reviewed.rip_id IS NULL
  
  ";

  $row = get_row_q($query);
  $num_rips = $row['num_rips'];

  $query = "

  SELECT
    mdb_rip.rip_id,
    mdb_rip.artist_id,
    mdb_rip.album_id,
    mdb_artist.artist_name,
    mdb_album.album_name,
    mdb_rip.rip_quality,
    mdb_rip.rip_flags,
    mdb_rip.rip_added
  FROM
    mdb_rip,
    mdb_artist,
    mdb_album
  LEFT JOIN
    mdb_reviewed
  ON
    mdb_rip.rip_id = mdb_reviewed.rip_id
  AND
    mdb_reviewed.user_id = $user_id
  WHERE
    mdb_rip.artist_id = mdb_artist.artist_id
  AND
    mdb_rip.album_id = mdb_album.album_id
  AND
    mdb_reviewed.rip_id IS NULL
  ORDER BY
    artist_name,
    album_name,
    rip_quality
  LIMIT 
    $start, $limit

  ";


  $result = do_query($query);
  $row_number = 0;

  while ( $row = get_row_r($result) ) {
    $row_number++;
?>
        <tr<?= ($row_number % 2) ? ' class="highlight"' : ""; ?>>
          <!-- ID: <?= $row['rip_id']; ?> -->
          <td><?= htmlentities($row['artist_name']); ?></td>
          <td><?= htmlentities($row['album_name']); ?></td>
          <td><input type="radio" name="id<?php echo $row['rip_id']; ?>" value="accepted"<?= print_checkbox($row['rip_id'], "accepted"); ?>></td>
          <td><input type="radio" name="id<?= $row['rip_id']; ?>" value="rejected"<?= print_checkbox($row['rip_id'], "rejected"); ?>></td>
          <td><input type="radio" name="id<?= $row['rip_id']; ?>" value="maybe"<?= print_checkbox($row['rip_id'], "maybe"); ?>></td>
          <td<?= ($row['rip_quality'] == "APX") ? ' class="flag"' : ""; ?>><?= $row['rip_quality']; ?></td>
          <td><?= ( $row['rip_flags'] != "" ) ? $row['rip_flags'] : "&nbsp;"; ?></td>
          <td><?= $row['rip_added']; ?></td>
          <td><a href="<?= "/zina/index.php?p=[" . $row['artist_name'] . "] [" . $row['album_name'] . "] [" . $row['rip_quality'] . "]&l=8&m=0"; ?>">Play</a></td>
        </tr>
<?php
  }
?>
      </table>
      <p><?= ($start - $limit >= 0) ? '<input type="submit" name="submit" value="Prev">' : ''; ?> <?= ($start + $limit <= $num_rips) ? '<input type="submit" name="submit" value="Next">' : '' ?></p>
      <p><input type="submit" name="submit" value="Finish"></p>
<?php
}

function print_confirmation($user_id) {

  foreach ( $_POST as $key => $value ) {
    
    $rip_id = str_replace("id", "", $key);
    $query = "";
    if ( $value == "accepted" ) {
      $query = "INSERT INTO mdb_reviewed (user_id, rip_id, rip_status) VALUES ($user_id, $rip_id, 1)";
      echo "    <!-- id $rip_id was added -->\n";
    } elseif ( $value == "rejected" ) {
      $query = "INSERT INTO mdb_reviewed (user_id, rip_id, rip_status) VALUES ($user_id, $rip_id, 0)";
      echo "    <!-- id $rip_id was removed -->\n";
    }

    if ( $query !== "") do_query($query);

  }
  
  /* Completely wipe out the session */
  $_SESSION = array();
  if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
  }
  session_destroy();

?>
    <p>The albums you have selected have been queued. They will appear on your collection shortly.</p>
    <form method="post" action="<?= $_SERVER['PHP_SELF'] ?>">
      <input type="submit" name="finished" value="Click here to finish process">
    </form>
<?php
  
}

function print_checkbox($id, $value) {
  
  $id = "id" . $id;
  
  if ( (isset($_POST[$id])) && ($_POST[$id] == $value) ) {
    return " checked";
  }

  if ( ( ! isset($_POST[$id]) && ($value == "maybe" ) )) {
    return " checked";
  }
  
}


function require_login($username) {
?>
    <form method="post" action="<?= $_SERVER['PHP_SELF']; ?>">
      <p>Username: <input type="text" name="user_name" size="15" value="<?= $user_name; ?>"></p>
      <p>Password: <input type="password" name="password" size=15></p>
      <p><input type="submit" name="submit" value="Enter"></p>
    </form>
<?php
}

function validate_login($user_name, $encoded_password) {

  $query = "SELECT user_id, user_password FROM mdb_user WHERE user_name = '$user_name'";

  $row = get_row_q($query, FALSE);

  if ( $encoded_password == $row['user_password'] ) return $row['user_id'];

}


?>
