<?php

/* web_ui.php - web user interface for choosing albums */
/*              this can be renamed/symlinked to index.php */

require_once('mcm_core.php');

mcm_action('html_header');

$advanced = (isset($_GET['advanced'])) ? $_GET['advanced'] : FALSE;

if (!mcm_action('login')) {
  mcm_action('login_form', $advanced);
  mcm_action('html_footer');
  exit;
}

$action   = (isset($_POST['submit'])) ? strtolower($_POST['submit']) : NULL;
$start    = (isset($_POST['start'])) ? abs(intval($_POST['start'])) : "0";
$type     = (isset($_POST['type'])) ? strtoupper($_POST['type']) : 'MUSIC';
$reviewed = (isset($_POST['reviewed'])) ? strtolower($_POST['reviewed']) : 'undecided';

switch($action) {

  case 'finish':
    mcm_action('record_selected');
    mcm_action('finish_selection');
    break;
  case 'enter':
  case 'next':
  case 'prev':
    mcm_action('record_selected');
    mcm_action('print_table', array('action' => $action, 'start' => $start, 'reviewed' => $reviewed, 'type' => $type));
    break;
}

mcm_action('html_footer');

?>
