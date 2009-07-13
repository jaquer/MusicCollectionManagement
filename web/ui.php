<?php

/* web_ui.php - web user interface for choosing albums */
/*              this can be renamed/symlinked to index.php */

require_once('../core/init.php');

mcm_action('html_header');

$advanced = (isset($_GET['advanced'])) ? (bool) $_GET['advanced'] : FALSE;

if (!mcm_action('web_login')) {
  mcm_action('web_login_form', array('advanced' => $advanced));
  mcm_action('html_footer');
  exit;
}

$action   = (isset($_POST['submit'])) ? strtolower($_POST['submit']) : NULL;
$start    = (isset($_POST['start'])) ? abs(intval($_POST['start'])) : "0";
$item_type   = (isset($_POST['item_type'])) ? strtoupper($_POST['item_type']) : 'MUSIC';
$item_status = (isset($_POST['item_status'])) ? strtolower($_POST['item_status']) : 'undefined';

switch($action) {

  case 'finish':
    mcm_action('web_record_selections');
    mcm_action('web_finish_selection');
    break;
  case 'exit':
    mcm_action('web_exit_without_saving');
    break;
  case 'enter':
  case 'next':
  case 'prev':
  default:
    mcm_action('web_record_selections');
    mcm_action('web_print_table', array('action' => $action, 'start' => $start, 'item_status' => $item_status, 'item_type' => $item_type));
    break;
}

mcm_action('html_footer');

?>
