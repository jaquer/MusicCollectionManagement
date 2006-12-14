<?php

/* mcm_web_functions - functions used in web_ui */

function mcm_login() {

  if (isset($_SESSION['user_name']) && isset($_SESSION['password'])) {
    $params = array('user_name' => $_SESSION['user_name'], 'password' =>$_SESSION['password']);
    return mcm_action('validate_login', $params);
  } elseif (isset($_POST['user_name']) && isset($_POST['password'])) {
    $params = array('user_name' => $_POST['user_name'], 'password' => md5($_POST['password']));
    return mcm_action('validate_login', $params);
  } else {
    return FALSE;
  }
  
}

function mcm_login_form($advanced) {

  global $mcm;
?>
    <form method="post" action="<?php echo $mcm['self']; ?>">
      <p>Username: <input type="text" name="user_name" size="15" value="<?php echo $mcm['user_name']; ?>"></p>
      <p>Password: <input type="password" name="password" size=15></p>
<?php

  if ($advanced) {

?>
      <p>Show
        <select name="reviewed">
          <option value="undecided" selected>unreviewed</option>
          <option value="accepted">accepted</option>
          <option value="rejected">rejected</option>
        </select>
        <select name="type">
          <option value="music" selected>music</option>
          <option value="audiobook">audio book</option>
          <option value="dupe">duplicate</option>
        </select>
        items.
      </p>
<?php

  }

?>
      <p><input type="submit" name="submit" value="Enter"></p>
    </form>
<?php
}

function mcm_record_selected() {

  foreach($_POST as $key => $value) {
  
    if ((substr($key, 0, 2) == 'id') && ($value != 'undecided')) {
      $_SESSION['reviewed'][substr($key, 2)] = $value;
    }
      
  }

}

function mcm_print_table($params) {

  global $mcm;
  
  $limit = 20; /* TODO: set this as a config option */
  $start = $params['start'];
  
  $start = ( $params['action'] == "next" ) ? $start + $limit : $start;
  $start = ( $params['action'] == "prev" ) ? $start - $limit : $start;
  
  /* determine number of rips available */
  $params = array('user_id' => $mcm['user_id'], 'reviewed' => $params['reviewed'], 'type' => $params['type']);
  $num_rips = mcm_action('lookup_reviewed_count', $params);
  
  $params = array('reviewed' => $params['reviewed'], 'user_id' => $mcm['user_id'], 'type' => $params['type'],
                  'order' => 'artist_name, album_name, rip_quality', 'limit' => "${start}, ${limit}");

  $to_review = mcm_action('lookup_reviewed', $params);
  
?>
    <form method="post" action="<?php echo $mcm['self']; ?>">
      <table width="98%">
        <input type="hidden" name="start" value="<?php echo $start; ?>">
        <input type="hidden" name="reviewed" value="<?php echo $params['reviewed'] ?>">
        <input type="hidden" name="type" value="<?php echo $params['type'] ?>">
<?php

  $row_number = 0;

  foreach ($to_review as $id => $row) {
  
    $row_number++;
  
    $album_dirname = "[${row['artist_name']}] [${row['album_name']}] [${row['rip_quality']}]";
    $image = mcm_action('create_cover_url', $album_dirname);
    
    echo (($row_number - 1) % 4 == 0) ? "       <tr>\n" : "";
    
?>
          <td class="item">
            <table class="item-table center no-pad">
              <tr>
                <td colspan="3" class="cover">
                  <a href="javascript:player('<?php echo base64_encode($album_dirname); ?>')"><img src="<?php echo $image ?>"></a>
                </td>
              </tr>
              <tr>
                <td colspan="3" class="artist"><?= htmlentities($row['artist_name']); ?></td>
              </tr>
              <tr>
                <td colspan="3" class="album"><?= htmlentities($row['album_name']); ?></td>
             </tr>
             <tr>
                <td class="choice accepted"><input class="accepted" id="id<?php echo $id; ?>-accepted" type="radio" name="id<?php echo $id; ?>" value="accepted"<?= print_checkbox($id, 'accepted'); ?>><label for="id<?php echo $id; ?>-accepted">add</label></td>
                <td class="choice rejected"><input class="rejected" id="id<?php echo $id; ?>-rejected" type="radio" name="id<?php echo $id; ?>" value="rejected"<?= print_checkbox($id, 'rejected'); ?>><label for="id<?php echo $id; ?>-rejected">remove</label></td>
                <td class="choice undecided"><input class="undecided" id="id<?php echo $id; ?>-undecided" type="radio" name="id<?php echo $id; ?>" value="undecided"<?= print_checkbox($id, 'undecided'); ?>><label for="id<?php echo $id; ?>-undecided">unsure</label></td>
             </tr>
            </table>
          </td>
<?php

    echo ($row_number % 4 == 0) ? "       </tr>\n" : "";
  
  }
  
  echo ($row_number % 4 != 0) ? "     </tr>\n" : "";
  
?>

      </table>
      <table id="navigation" class="center">
        <tr> 
          <td id="prev"><?= ($start - $limit >= 0) ? '<input type="submit" name="submit" value="Prev">' : ''; ?></td>
          <td id="finish"><input type="submit" name="submit" value="Finish"></td>
          <td id="next"><?= ($start + $limit <= $num_rips) ? '<input type="submit" name="submit" value="Next">' : '' ?></td>
        </tr>
      </table>
  </form>
<?php

}

function mcm_finish_selection() {

  global $mcm;
  
  foreach ($_SESSION['reviewed'] as $rip_id => $value) {
  
    $query = NULL;
    
    switch ($value) {
    
      case 'accepted':
        $query = "INSERT INTO mdb_reviewed (user_id, rip_id, rip_status) VALUES (${mcm['user_id']}, ${rip_id}, 1)";
        break;
      case 'rejected':
        $query = "INSERT INTO mdb_reviewed (user_id, rip_id, rip_status) VALUES (${mcm['user_id']}, ${rip_id}, 0)";
        break;
    }
    
    if ($query) do_query($query);
    
  }
  
  $query = "UPDATE mdb_user SET user_last_visit = NOW() WHERE user_id = ${mcm['user_id']}";
  do_query($query);
  
?>

    <p>The albums you have selected have been queued. They will appear on your collection shortly.</p>
    <form method="post" action="<?= $mcm['self'] ?>">
      <input type="submit" name="finished" value="Click here to finish process">
    </form>
<?php

  /* Completely wipe out the session */
  $_SESSION = array();
  session_destroy();

}

function print_checkbox($id, $value) {
  
  if ( (isset($_SESSION['reviewed'][$id])) && ($_SESSION['reviewed'][$id] == $value) ) {
    return " checked";
  }

  if ( ( ! isset($_SESSION['reviewed'][$id]) && ($value == "undecided" ) )) {
    return " checked";
  }
  
}

function mcm_create_cover_url($album_dirname) {

  global $mcm;
  
  $checksum = md5($album_dirname);
  $cache    = "/_cache/cover/${checksum}.jpg";
  $cache_path = "${mcm['path']}/${cache}";
  $cache_url  = "${mcm['url_path']}/${cache}";
  
  if (file_exists($cache_path)) return $cache_url;
  
  chdir("${mcm['basedir']}/${album_dirname}");
  $image = glob("*.jpg");
  
  chdir(dirname($cache_path));
  if (count($image))
    symlink("${mcm['basedir']}/${album_dirname}/" . basename($image[0]), basename($cache_path));
  else
    $cache_url = "${mcm['url_path']}/images/no_cover.jpg";
    
  return $cache_url;
  
}

function validate_login($user_name, $password) {

  global $mcm;
  
  $mcm['user_name'] = $user_name;

  $query = "SELECT user_id, user_password FROM mdb_user WHERE user_name = '${user_name}'";
  
  $row = get_row_q($query);
  
  if ($password == $row['user_password']) {
  
    $_SESSION['user_name'] = $user_name;
    $_SESSION['password'] = $password;
    
    $mcm['user_id'] = $row['user_id'];
    
    return TRUE;
    
  } else {
  
    return FALSE;
    
  }
}

?>
