<?php

/* mcm_web_functions - functions used in web_ui */

include_once('player.php');

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
  
?>
<div id="login-form" class="center">
    <fieldset>
        <legend class="bold">Login</legend>
        <form action="#" method="post">
            <p>Username: <input type="text" name="user_name" size="15" value=""> <img src="images/user.png" alt="User"></p>
            <p>Password: <input type="password" name="password" size=15> <img src="images/password.png" alt="Password"></p>
            <p><input class="bold" type="submit" name="submit" value="Enter"></p>
            <p id="show-advanced"><a href="#" onclick="showAdvanced(); return false;">Advanced options</a></p>
            <p id="advanced">Review 
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
        </form>
    </fieldset>
</div>
<?php

}

function mcm_web_record_selections() {

  foreach($_POST as $key => $value)
    if (substr($key, 0, 2) == 'id')
      $_SESSION['status'][substr($key, 2)] = $value;

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
  
  $limit = 6; /* TODO: set this as a config option */
  $table_cols = 3;
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
<table id="list-and-nav" class="center">
    <tr>
        <td id="nav-left" class="nav">
            <?php print_navigation($start, $limit, $num_items, 'left'); ?>
        </td>
        
        <td>
            <table id="list">
            <input type="hidden" name="start" value="<?php echo $start; ?>">
            <input type="hidden" name="item_status" value="<?php echo $arg_item_status; ?>">
            <input type="hidden" name="item_type" value="<?php echo $arg_item_type; ?>">
<?php

  $row_number = 0;

  foreach ($items_list as $id => $row) {

    /* load current item status from db into session */
    if (!isset($_SESSION['status'][$id]))
      $_SESSION['status'][$id] = $arg_item_status;
  
    $row_number++;
  
    $album_dirname = "[${row['artist_name']}] [${row['album_name']}] [${row['item_quality']}]";
    $cover_url = mcm_action('create_cover_url', $album_dirname);
    $playlist_url = create_player($album_dirname);
    
    echo (($row_number - 1) % $table_cols == 0) ? "       <tr>\n" : "";
    
?>
                    <td class="item" id="id<?php echo $id; ?>-item">
                        <div id="id<?php echo $id; ?>-artist" class="artist"><?php echo $row['artist_name']; ?></div>
                        <div id="id<?php echo $id; ?>-album" class="album"><?php echo $row['album_name']; ?></div>
                        <div class="cover-container" onmouseover="showOverlay('id<?php echo $id; ?>')" onmouseout="hideOverlay('id<?php echo $id; ?>')">
                            <a href="#" onmousedown="loadPlaylist('<?php echo $playlist_url; ?>', '<?php echo $cover_url; ?>'); return false;">
                                <img id="id<?php echo $id; ?>-overlay" src="images/overlay.png" class="overlay" alt="">
                                <img id="id<?php echo $id; ?>-cover" src="<?php echo $cover_url; ?>" class="cover<?php  print_cover_class($id, $_SESSION['status'][$id]); ?>" alt="album cover" width="150" height="150">
                            </a>
                        </div>
                        <div class="choices">
                            <a href="#" onmousedown="clickRadio('id<?php echo $id; ?>', 'add'); return false;"><img id="id<?php echo $id; ?>-img-add" src="images/add<?php print_button($id, 'accepted'); ?>.png" alt="Add" title="Add"></a>
                            <input type="radio" id="id<?php echo $id; ?>-add" name="id<?php echo $id; ?>" value="accepted"<?php print_checkbox($id, 'accepted'); ?>>
                            <a href="#" onmousedown="clickRadio('id<?php echo $id; ?>', 'rem'); return false;"><img id="id<?php echo $id; ?>-img-rem" src="images/remove<?php print_button($id, 'rejected'); ?>.png" alt="Remove" title="Remove"></a>
                            <input type="radio" id="id<?php echo $id; ?>-rem" name="id<?php echo $id; ?>" value="rejected"<?php print_checkbox($id, 'rejected'); ?>>
                            <a href="#" onmousedown="clickRadio('id<?php echo $id; ?>', 'und'); return false;"><img id="id<?php echo $id; ?>-img-und" src="images/undecided<?php print_button($id, 'undefined'); ?>.png" alt="Undecided" title="Undecided"></a>
                            <input type="radio" id="id<?php echo $id; ?>-und" name="id<?php echo $id; ?>" value="undefined"<?php print_checkbox($id, 'undefined'); ?>>
                        </div>
                    </td>
<?php

    echo ($row_number % $table_cols == 0) ? "       </tr>\n" : "";
  
  }
  
  echo ($row_number % $table_cols != 0) ? "     </tr>\n" : "";
  
?>
                </tr>
            </table>
        </td>
    
        <td id="nav-right" class="nav">
            <?php print_navigation($start, $limit, $num_items, 'right'); ?>
        </td>
    </tr>
</table>

<div id="selected-artist"></div>
<div id="selected-album"></div>

<table id="player-table" class="center">
    <tr>
        <td id="player-cover-container">
            <img id="player-cover" src="images/player-cover.png" alt="Album Cover" width="100" height="100">
        </td>
        <td rowspan="2" id="player-meta">
        <div id="player-artist"></div>
        <div id="player-album"></div>
        <div id="player-title"></div>
        <div id="player-next">
            <span id="player-next-label"></span><span id="player-next-title"></span>
        </div>
        </td>
    </tr>
    <tr>
        <td id="player-controls">
            <a href="#" onmousedown="player.sendEvent('PREV'); return false;"><img src="images/rew.png" alt="Rewind" title="Previous Track"></a>
            <a href="#" onmousedown="player.sendEvent('PLAY'); return false;"><img src="images/play-pause.png" alt="Play/Pause" title="Play/Pause"></a>
            <a href="#" onmousedown="player.sendEvent('NEXT'); return false;"><img src="images/fwd.png" alt="Forward" title="Next Track"></a>
        </td>
    </tr>
</table>

<div id="toolbar">
    <a href="#" onclick="alert('Help not implemented. Yet.'); return false;"><img src="images/help.png" title="Help" alt="Help"></a> | 
    <input type="image" src="images/save-exit.png" name="submit" value="finish" title="Save and Exit"> |
    <input type="image" src="images/exit.png" name="submit" value="exit" title="Exit Without Saving" onclick="return confirm('You will lose all current selections!\n\nAre you sure you want to exit without saving?');">
</div>
<p id="player" style="text-align: center;">Media player requires Adobe Flash Player to be installed. <a href="http://www.adobe.com/go/getflashplayer">Download now</a>.</p>

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
<div id="signoff" class="center">
    <p class="bold">Process complete.</p>
    <p>The albums you have selected will be added or removed shortly.</p>
    <p>You can now close this window or return to the homepage.</p>
    <div id="home-link"><a href="">Return to Homepage <img src="images/home.png" alt="Home" title="Return to Homepage"></a></div>
</div>
<?php

  /* Completely wipe out the session */
  $_SESSION = array();
  session_destroy();
  
  /* signal an update */
  $update_signal = $mcm['path'] . '/_cache/update_signal';
  touch($update_signal);
  chmod($update_signal, 0666);

}

function print_checkbox($id, $value) {

  if ($_SESSION['status'][$id] == $value)
    echo " checked";

}

function print_cover_class($id) {

  $value = $_SESSION['status'][$id];

  if ($value == "accepted")
    echo " cover-add";
  else if ($value == "rejected")
    echo " cover-rem";
  else if ($value == "undefined")
    echo " cover-und";
  
}

function print_button($id, $value) {

  if ($_SESSION['status'][$id] != $value)
    echo "-off";

}


function print_navigation($start, $limit, $num_items, $direction) {

  if ($direction == 'left') {
    if ($start - $limit >= 0) {
?>
            <!-- input type="image" src="images/first.png" title="First Page" -->
            <input type="image" src="images/prev.png" name="submit" value="prev" title="Previous Page">
<?php
    }
  }

  if ($direction == 'right') {
    if ($start + $limit <= $num_items) {
?>
            <!-- input type="image" src="images/last.png" title="First Page" -->
            <input type="image" src="images/next.png" name="submit" value="next" title="Next Page">
<?php
    }
  }
}

function mcm_create_cover_url($album_dirname) {

  global $mcm;

  $cwd = getcwd();
  
  $checksum = md5($album_dirname);
  $cache    = "_cache/cover/${checksum}.jpg";
  $cache_path = "${mcm['path']}/${cache}";
  $cache_url  = "${mcm['url_path']}/${cache}";
  
  if (file_exists($cache_path)) return $cache_url;
  
  chdir("${mcm['basedir']}/${album_dirname}");
  $image = glob("*.jpg");
  
  chdir(dirname($cache_path));
  if (count($image))
    symlink("${mcm['basedir']}/${album_dirname}/" . basename($image[0]), basename($cache_path));
  else
    $cache_url = "${mcm['url_path']}/images/no-cover.png";

  chdir($cwd);
    
  return $cache_url;
  
}

?>
