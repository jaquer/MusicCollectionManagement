<?php

/* mcm_user.php - user functions */

function mcm_validate_login($params) {

  global $mcm;
  
  extract($params); /* gives us $user_name & $password */
  
  $mcm['user_name'] = $user_name;

  $query = "SELECT user_id, user_password FROM mdb_user WHERE user_name = '${user_name}'";
  
  $row = get_row_q($query);
  
  if ($password == $row['user_password']) {
  
    $_SESSION['user_name'] = $user_name;
    $_SESSION['password']  = $password;
    
    $mcm['user_id'] = $row['user_id'];
    
    return TRUE;
    
  } else {
  
    return FALSE;
    
  }
  
}

?>
