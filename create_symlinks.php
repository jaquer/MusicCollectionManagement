#!/usr/bin/php
<?php

/* symlinks_update.php - script to update user's symlinks */

if (strcmp(PHP_SAPI, 'cli') != 0)
  die("this script can only be called from the command line - exiting\n");

require_once('mcm_core.php');

if (posix_geteuid() == 0) {
  update_all_users();
  exit();
}

$user = read_input();

if (! mcm_action('validate_login', $user))
  die("invalid username/password\n");
  
update_user($user);

die();

function update_user($user) {

  global $mcm;
  
  /* if we ran validate_login(),then the $user_id hasn't been set... */
  if (!isset($user['user_id']))
    $user['user_id'] = $mcm['user_id'];
  
  $prefs = mcm_action('lookup_prefs', $user['user_id']);
  
  if (isset($user['forced_target']))
    $prefs['pref_target'] = $user['forced_target'];
    
  echo "updating symlinks for user '${user['user_name']}' on directory '${prefs['pref_target']}'\n";
  
  if (! $prefs = validate_prefs($prefs)) return;
  
  echo "  - current symlinks: ";
  $current = mcm_action('read_symlinks', $prefs['pref_target']);
  echo count($current) . " found\n";
  
  echo "  - accepted items: ";
  $params = array('user_id' => $user['user_id'], 'item_status' => 'accepted', 'item_type' => 'MUSIC');
  $accepted = mcm_action('lookup_itemlist', $params);
  echo count($accepted) . "\n";
  
  echo "  - rejected items: ";
  $params['item_status'] = 'rejected';
  $rejected = mcm_action('lookup_itemlist', $params);
  echo count($rejected) . "\n";
  
  /* sorting (hopefully?) makes diff/intersect faster */
  ksort($current);
  ksort($accepted);
  ksort($rejected);
  
  $create = key_diff($accepted, $current);
  $remove = key_intersect($rejected, $current);
  
  $mcmnew_dir = "${prefs['pref_target']}/_mcmnew";
  
  if (! make_mcmnew_dir($mcmnew_dir))
    return;
  
  $create_list = generate_create_list($create, $accepted, $prefs['pref_extensions']);
  
  chdir($mcmnew_dir);
  
  foreach ($create_list as $item) {
  
    $dirname = $item['dirname'];
    $path    = $item['path'];
    $files   = $item['files'];
    
    mkdir($dirname);
    
    foreach ($files as $file)
      symlink("${path}/${file}", "${dirname}/${file}");
      
    if ($prefs['pref_codepage'] != $mcm['codepage'])
      system("/usr/bin/convmv --notest -r -f ${mcm['codepage']} -t ${prefs['pref_codepage']} --exec \"mv #1 #2\" \"${dirname}\" >/dev/null");
    
  }
  
}

function generate_create_list($create, $accepted, $extensions) {

  global $mcm;
  
  $list = array();


  $pwd  = getcwd();
  $root = $mcm['basedir'];
  $pattern = "{,.}*.{" .$extensions . "}";
  
  foreach ($create as $id) {
  
    $item = $accepted[$id];
    
    $dirname = "[${item['artist_name']}] [${item['album_name']}] [${item['item_quality']}]";
    $path    = "${root}/${dirname}";
    
    chdir($path);
    
    $files = glob($pattern, GLOB_BRACE);
    
    $list[] = array('dirname' => $dirname, 'path' => $path, 'files' => $files);
    
  }
  
  chdir($pwd);
  
  return $list;
  
}

function make_mcmnew_dir($mcmnew_dir) {

  if (! is_dir($mcmnew_dir)) {
    if (! mkdir($mcmnew_dir)) {
      echo "  error: unable to create mcm subdir under target directory\n";
      return FALSE;
    }
  }
  
  return TRUE;
  
}


function update_all_users() {

  $users = mcm_action('lookup_all_users');
  
  echo "superuser mode - updating symlinks for all users with default settings\n";
  
  foreach ($users as $user) {
    $user['seteuid'] = TRUE; /* attempt to change uid to the sysname of the user */
    update_user($user);
  }

}

function validate_prefs($prefs) {

  $sysinfo = posix_getpwnam($prefs['pref_sysname']);
  $prefs['pref_sysid'] = $sysinfo['uid']; /* will be empty if  sysname is invalid */
  
  $target = $prefs['pref_target'];
  if (! is_dir($target)) {
    echo "  target directory '${target}' does no exist\n";
    return FALSE;
  }
  if (! is_writeable($target)) {
    echo "  cannot write to target directory '${target}'\n";
    return FALSE;
  }
  
  return $prefs;
  
}

function read_input() {

  $input = getopt("u:p:t:");
  
  if (!isset($input['u']))
    die("usage: ${_SERVER['PHP_SELF']} -u mcm_username [-p password] [-t target] - exiting\n");
  
  $user_name = $input['u'];
  
  if (isset($input['p']))
    $password = $input['p'];
  else
    $password = read_password($user_name);
    
  if (isset($input['t']))
    $forced_target = $input['t'];
    
  return array('user_name' => $user_name, 'password' => md5($password), 'forced_target' => $forced_target);
  
}

function read_password($user_name) {

  echo "enter password for user '${user_name}': ";
  system("stty -echo");
  $password = fgets(STDIN);
  system("stty echo");
  echo "\n";
  
  return trim($password);
  
}

function key_diff($array_one, $array_two) {

  $keys_one = array_keys($array_one);
  $keys_two = array_keys($array_two);
  
  return array_diff($keys_one, $keys_two);
  
}

function key_intersect($array_one, $array_two) {

  $keys_one = array_keys($array_one);
  $keys_two = array_keys($array_two);
  
  return array_intersect($keys_one, $keys_two);
  
}

?>
