<?php

/* web_ui.php - web user interface for choosing albums */
/*              this can be renamed/symlinked to index.php */

require_once('mcm_core.php');

mcm_action('html_header');

if (!mcm_action('login')) {
  mcm_action('login_form');
  mcm_action('html_footer');
  exit;
}

$action = (isset($_POST['submit'])) ? strtolower($_POST['submit']) : NULL;
$start  = (isset($_POST['start'])) ? abs(intval($_POST['start'])) : "0";
$type   = (isset($_POST['type'])) ? $_POST['type'] : 'MUSIC';

switch($action) {

  case 'finish':
    mcm_action('record_selected');
    mcm_action('finish_selection');
    break;
  case 'enter':
  case 'next':
  case 'prev':
    mcm_action('record_selected');
    mcm_action('print_table', array('action' => $action, 'start' => $start, 'type' => $type));
    break;
}

mcm_action('html_footer');

?>