<?php

/* mcm_web_functions - functions used in web_ui */

include_once('mcm_web_player.php');

function mcm_web_login() {

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

function mcm_web_login_form($args) {

  global $mcm;
  extract($args, EXTR_PREFIX_ALL, 'arg');
  /* arg_advanced - flag to output advanced login options */
  
?>
    <form method="post" action="<?php echo $mcm['self']; ?>">
      <p>Username: <input type="text" name="user_name" size="15" value="<?php echo $mcm['user_name']; ?>"></p>
      <p>Password: <input type="password" name="password" size=15></p>
<?php

  if ($arg_advanced) {

?>
      <p>Review 
        <select name="item_status">
          <option value="undefined" selected>undefined</option>
          <option value="accepted">accepted</option>
          <option value="rejected">rejected</option>
        </select>
        <select name="item_type">
          <option value="music" selected>music</option>
          <option value="audiobook">audio book</option>
          <option value="dupe">duplicate</option>
        </select>
        items.
      </p>
<?php

  }

?>
      <p><input type="submit" name="submit" value="Enter"><?php if (!$arg_advanced) echo ' <a style="font-size: 80%;" href="?advanced=true">advanced</a>'; ?></p>
    </form>
<?php

}

function mcm_web_record_selections() {

  foreach($_POST as $key => $value) {
  
    if (substr($key, 0, 2) == 'id') {
      if (($value == 'accepted') || ($value == 'rejected')) {
        $_SESSION['status'][substr($key, 2)] = $value;
      } elseif (($value == 'undefined') && (isset($_SESSION['status'][substr($key, 2)]))) {
        unset($_SESSION['status'][substr($key, 2)]);
      }
    }
      
  }

}

function mcm_web_print_table($args) {

  global $mcm;
  
  extract($args, EXTR_PREFIX_ALL, 'arg');
  /* 
   * required:
   *
   * $arg_action      - {prev,next} order
   * $arg_start       - query start offset
   * $arg_item_status - {accepted,rejected,undefined}
   * $arg_item_type   - user-defined item type
   *
   */
  
  $limit = 12; /* TODO: set this as a config option */
  $start = $arg_start;
  
  $start = ($arg_action == 'next') ? $start + $limit : $start;
  $start = ($arg_action == 'prev') ? $start - $limit : $start;
  
  /* determine number of items available */
  $params = array('user_id' => $mcm['user_id'], 'item_status' => $arg_item_status, 'item_type' => $arg_item_type,
                  'order' => 'artist_name, album_name, item_quality', 'limit' => "${start}, ${limit}");
                  
  
  $items_list = mcm_action('lookup_itemlist', $params);
  $num_items  = mcm_action('lookup_itemlist_count', $params);
  
  
?>
    <form method="post" action="<?php echo $mcm['self']; ?>">
<?php print_navigation($start, $limit, $num_items); ?>
      <table id="list" class="center">
        <input type="hidden" name="start" value="<?php echo $start; ?>">
        <input type="hidden" name="item_status" value="<?php echo $arg_item_status; ?>">
        <input type="hidden" name="item_type" value="<?php echo $arg_item_type; ?>">
<?php

  $row_number = 0;

  foreach ($items_list as $id => $row) {

    /* load current item status from db into session */
    if (($arg_item_status != 'undefined') && (! isset($_SESSION['status'][$id]))) {
      $_SESSION['status'][$id] = $arg_item_status;
    }
  
    $row_number++;
  
    $album_dirname = "[${row['artist_name']}] [${row['album_name']}] [${row['item_quality']}]";
    $cover_url = mcm_action('create_cover_url', $album_dirname);
    
    echo (($row_number - 1) % 4 == 0) ? "       <tr>\n" : "";
    
?>
          <td class="item">
            <table class="item-table center no-pad">
              <tr>
                <td colspan="3" class="cover">
                  <?php create_player($album_dirname); ?>
                </td>
              </tr>
              <tr>
                <td colspan="3" class="artist"><?php echo htmlentities($row['artist_name'], ENT_COMPAT, 'UTF-8'); ?></td>
              </tr>
              <tr>
                <td colspan="3" class="album"><?php echo htmlentities($row['album_name'], ENT_COMPAT, 'UTF-8'); ?></td>
             </tr>
             <tr>
                <td class="choice accepted"><input class="accepted" id="id<?php echo $id; ?>-accepted" type="radio" name="id<?php echo $id; ?>" value="accepted"<?= print_checkbox($id, 'accepted'); ?>><label for="id<?php echo $id; ?>-accepted">add</label></td>
                <td class="choice rejected"><input class="rejected" id="id<?php echo $id; ?>-rejected" type="radio" name="id<?php echo $id; ?>" value="rejected"<?= print_checkbox($id, 'rejected'); ?>><label for="id<?php echo $id; ?>-rejected">remove</label></td>
                <td class="choice undefined"><input class="undefined" id="id<?php echo $id; ?>-undefined" type="radio" name="id<?php echo $id; ?>" value="undefined"<?= print_checkbox($id, 'undefined'); ?>><label for="id<?php echo $id; ?>-undefined">unsure</label></td>
             </tr>
            </table>
          </td>
<?php

    echo ($row_number % 4 == 0) ? "       </tr>\n" : "";
  
  }
  
  echo ($row_number % 4 != 0) ? "     </tr>\n" : "";
  
?>

      </table>
<?php print_navigation($start, $limit, $num_items); ?>
  </form>
<?php

}

function mcm_web_finish_selection() {

  global $mcm;
  
  $_SESSION['status'] = (is_array($_SESSION['status'])) ? $_SESSION['status'] : array();
  
  foreach ($_SESSION['status'] as $item_id => $item_status) {
  
    do_query("DELETE FROM mdb_status WHERE user_id = ${mcm['user_id']} AND item_id = ${item_id}");
    
    $query = NULL;
    
    switch ($item_status) {
    
      case 'accepted':
        $query = "INSERT INTO mdb_status (user_id, item_id, item_status) VALUES (${mcm['user_id']}, ${item_id}, 1)";
        break;
      case 'rejected':
        $query = "INSERT INTO mdb_status (user_id, item_id, item_status) VALUES (${mcm['user_id']}, ${item_id}, 0)";
        break;
    }
    
    if ($query) do_query($query);
    
  }
  
  $query = "UPDATE mdb_user SET user_last_visit = NOW() WHERE user_id = ${mcm['user_id']}";
  do_query($query);
  
?>
    <p>The items you have selected have been queued. They will appear on your collection shortly.</p>
    <form method="post" action="<?= $mcm['self'] ?>">
      <input type="submit" name="finished" value="Click here to finish process">
    </form>
<?php

  /* Completely wipe out the session */
  $_SESSION = array();
  session_destroy();
  
  /* signal an update */
  $update_signal = $mcm['path'] . '/_cache/update_signal';
  touch($update_signal);
  chmod($update_signal, 666);

}

function print_checkbox($id, $value) {
  
  if ((isset($_SESSION['status'][$id])) && ($_SESSION['status'][$id] == $value)) {
    return " checked";
  }

  if ((!isset($_SESSION['status'][$id]) && ($value == "undefined" ))) {
    return " checked";
  }
  
}

function print_navigation($start, $limit, $num_items) {
?>
      <table class="navigation center">
        <tr> 
          <td class="prev"><?= ($start - $limit >= 0) ? '<input type="image" src="images/prev.png" name="submit" value="prev">' : ''; ?></td>
          <td class="finish"><input type="submit" name="submit" value="Finish"></td>
          <td class="next"><?= ($start + $limit <= $num_items) ? '<input type="image" src="images/next.png" name="submit" value="next">' : '' ?></td>
        </tr>
      </table>
<?php
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

?>
