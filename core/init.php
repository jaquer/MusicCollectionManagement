<?php

/* mcm_core.php - common functions, initialization */

$mcm = Array();

mcm_init();

function mcm_action($action, $params = '') {

  /* this is the true brain of the program */
  /* it calls the necessary scripts/functions based on $action */
  switch($action) {
  
    case 'html_header':
      include_once('../web/header.php');
      session_start();
      mcm_html_header($params);
      break;
    case 'html_footer':
      include_once('../web/footer.php');
      mcm_html_footer($params);
      break;
    case 'web_login':
      include_once('../web/functions.php');
      return mcm_web_login();
      break;
    case 'web_login_form':
      include_once('../web/functions.php');
      mcm_web_login_form($params);
      break;
    case 'web_print_table':
      include_once('../web/functions.php');
      mcm_web_print_table($params);
      break;
    case 'web_record_selections':
      include_once('../web/functions.php');
      mcm_web_record_selections();
      break;
    case 'web_finish_selection':
      include_once('../web/functions.php');
      mcm_web_finish_selection();
      break;
    case 'read_dirlist':
      include_once('../fs/dirlist.php');
      return mcm_read_dirlist();
      break;
    case 'parse_dirlist':
      include_once('../fs/dirlist.php');
      return mcm_parse_dirlist($params);
      break;
    case 'lookup_last_item':
      include_once('../db/mysql.php');
      return mcm_lookup_last_item();
      break;
    case 'lookup_item':
      include_once('../db/mysql.php');
      return mcm_lookup_item($params);
      break;
    case 'lookup_prefs':
      include_once('../db/mysql.php');
      return mcm_lookup_prefs($params);
      break;
    case 'lookup_all_users':
      include_once('../db/mysql.php');
      return mcm_lookup_all_users();
      break;
    case 'lookup_itemlist':
      include_once('../db/mysql.php');
      return mcm_lookup_itemlist($params);
      break;
    case 'lookup_itemlist_count':
      include_once('../db/mysql.php');
      return mcm_lookup_itemlist_count($params);
      break;
    case 'create_cover_url':
      include_once('../web/functions.php');
      return mcm_create_cover_url($params);
      break;
    case 'validate_login':
      include_once('user.php');
      return mcm_validate_login($params);
      break;
    case 'verify_symlinks_against_virtualfs':
      include_once('../fs/symlinks.php');
      return mcm_verify_symlinks_against_virtualfs($params);
      break;
    default:
      echo "unknown core action: ${action}\n";
      break;
  }

}

function mcm_init() {

  /* this function must be called every startup */
  global $mcm;

  require_once('../mcm.ini.php');

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
  
  require_once('../db/mysql.php');
  mcm_open_db();
  
}

?>
