<?php

/* mcm_core.php - common functions, initialization */

$mcm = Array();

mcm_init();

function mcm_action($action, $params = '') {

  /* this is the true brain of the program */
  /* it calls the necessary scripts/functions based on $action */
  switch($action) {
  
    case 'html_header':
      include_once('mcm_html_header.php');
      session_start();
      mcm_html_header($params);
      break;
    case 'html_footer':
      include_once('mcm_html_footer.php');
      mcm_html_footer($params);
      break;
    case 'login':
      include_once('mcm_web_functions.php');
      return mcm_login();
      break;
    case 'login_form':
      include_once('mcm_web_functions.php');
      mcm_login_form();
      break;
    case 'print_table':
      include_once('mcm_web_functions.php');
      mcm_print_table($params);
      break;
    case 'record_selected':
      include_once('mcm_web_functions.php');
      mcm_record_selected();
      break;
    case 'finish_selection':
      include_once('mcm_web_functions.php');
      mcm_finish_selection();
      break;
    case 'read_dirlist':
      include_once('mcm_dirlist.php');
      return mcm_read_dirlist();
      break;
    case 'parse_dirlist':
      include_once('mcm_dirlist.php');
      return mcm_parse_dirlist($params);
      break;
    case 'lookup_last_rip':
      include_once('mcm_mysql.php');
      return mcm_lookup_last_rip();
      break;
    case 'lookup_rip':
      include_once('mcm_mysql.php');
      return mcm_lookup_rip($params);
      break;
    case 'lookup_prefs':
      include_once('mcm_mysql.php');
      return mcm_lookup_prefs($params);
      break;
    case 'lookup_all_users':
      include_once('mcm_mysql.php');
      return mcm_lookup_all_users();
      break;
    case 'lookup_reviewed':
      include_once('mcm_mysql.php');
      return mcm_lookup_reviewed($params);
      break;
    case 'lookup_reviewed_count':
      include_once('mcm_mysql.php');
      return mcm_lookup_reviewed_count($params);
      break;
    case 'create_cover_url':
      include_once('mcm_web_functions.php');
      return mcm_create_cover_url($params);
      break;
    case 'validate_login':
      include_once('mcm_user.php');
      return mcm_validate_login($params);
      break;
    case 'read_symlinks':
      include_once('mcm_symlinks.php');
      return mcm_read_symlinks($params);
      break;
  }

}

function mcm_init() {

  /* this function must be called every startup */
  global $mcm;

  require_once('mcm.ini.php');

  /* load settings to a global array */
  $mcm['self']     = $_SERVER['SCRIPT_NAME'];
  $mcm['dirname']  = (strlen(dirname($mcm['self'])) == 1) ? "" : dirname($mcm['self']);
  $mcm['path']     = dirname($_SERVER['SCRIPT_FILENAME']);
  $mcm['basedir']  = realpath($music_dir);
  $mcm['url_path'] = "http://" . $_SERVER['HTTP_HOST'] . $mcm['dirname'];
  
  $mcm['db_server']   = $server;
  $mcm['db_database'] = $database;
  $mcm['db_username'] = $username;
  $mcm['db_password'] = $password;
  
  $mcm['codepage']    = $codepage;
  
  $mcm['debug'] = $debug;
  
  require_once('mcm_mysql.php');
  mcm_open_db();
  
}

?>
