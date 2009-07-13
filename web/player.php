<?php

/* mcm_web_player.php - creates flash mp3 player code */

function create_player($album_dirname) {

  global $mcm;

  $cwd = getcwd();

  $full_path = "${mcm['basedir']}/${album_dirname}";

  $stream_base = "/_cache/stream/" . session_id() . "/" . md5($album_dirname);

  $stream_path   = $mcm['path'] . $stream_base;
  $stream_url    = $mcm['url_path'] . $stream_base;
  $playlist_path = $stream_path . ".xspf";
  $playlist_url  = $stream_url . ".xspf";

  if (!file_exists($playlist_path) || !is_dir($stream_path)) {

    @mkdir($mcm['path'] . "/_cache/stream/" . session_id());
  
    $playlist = "";
    $playlist .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    $playlist .= "<playlist version=\"0\" xmlns = \"http://xspf.org/ns/0/\">\n";
    $playlist .= "<title>" . utf8_encode($album_dirname) . "</title>\n";
  
    $img = mcm_action('create_cover_url', $album_dirname);
    chdir($full_path);
    $mp3s = glob("*.mp3");
  
    mkdir($stream_path);
    chdir($stream_path);

    $playlist .= "<image>${img}</image>\n";

    $playlist .= "  <trackList>\n";
  
    $index = 0;

    foreach ($mp3s as $mp3) {
  
      $index++;
      $symlink = str_pad($index, 2, "0", STR_PAD_LEFT) . ".mp3";
      symlink("${full_path}/${mp3}", $symlink);

      $playlist .= "    <track>\n";
      $playlist .= "      <location>${stream_url}/${symlink}</location>\n";
      $playlist .= "      <title>" . htmlentities(substr(substr($mp3, 5), 0, -4), ENT_COMPAT, 'UTF-8') . "</title>\n";
      $playlist .= "      <image>${img}</image>\n";
      $playlist .= "    </track>\n";
     
    }
    
    $playlist .= "  </trackList>\n";
    $playlist .= "</playlist>\n";
    
    $hnd = fopen($playlist_path, 'w');
    fwrite($hnd, $playlist);
    fclose($hnd);
    
  }

  chdir($cwd);

  return $playlist_url;
  
}

?>

