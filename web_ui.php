<?php

/*
 * web_ui.php - Web UI for the Music Management System
 *
 * $Header: /var/oldvar/cvsroot/jaquer/new-mcm/web_ui.php,v 1.2 2004/09/06 03:38:19 jaquer Exp $
 *
 */

require_once('mcm_defs.inc');

$user_name          = (isset($_POST['user_name'])) ? $_POST['user_name'] : "";
$cleartext_password = (isset($_POST['password'])) ? $_POST['password'] : "";
$encoded_password   = md5($cleartext_password);

include('html_header.inc');

if ( $user_name == "" ){

  require_login($user_name);

} elseif ( ! $user_id = validate_login($user_name, $encoded_password) ) {
?>
    <h3>Wrong username/password</h3>
<?php
  require_login($user_name);
} else {

  /* Proper login */

}

include('html_footer.inc');

/* Functions */
function require_login($username) {
?>
    <form method="post" action="<?= basename(__FILE__); ?>">
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