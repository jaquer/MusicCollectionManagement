function player(path) {
  id = 'player';
  eval("page" + id + " = window.open('web_player.php?path=' + path, '" + id + "', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=415,height=185');");
}
