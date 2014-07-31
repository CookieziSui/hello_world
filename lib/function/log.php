<?php
// ==================================================================
//
// AUTHOR: CC
// Date: 07/15/2013
// Description:
// 	All the functions about logging will be defined in this page
//
// ------------------------------------------------------------------


/**
 * [log_login Login users' login info]
 * @param  [type] $user_id         [USER ID]
 * @param  [type] $user_agent      [Browser and OS info]
 * @param  [type] $ip              [IP]
 * @param  [type] $login_timestamp [Timestamp]
 * @return [type]                  [login id. Used for login user's db operations later]
 */
function log_login($user_id,$user_agent,$ip,$login_timestamp){
    $new_log     = "INSERT INTO log_login (USER_ID,USER_AGENT,IP,LOGIN) VALUES ('$user_id','$user_agent','$ip','$login_timestamp');";
    $rst_new_log = $GLOBALS['db']->query($new_log);
    $login_id    = $GLOBALS['db']->insert_id;
    return $login_id;
}

function log_operation($user_id, $login_id, $module, $operation_type, $action){
    $log_operation = "INSERT INTO log_db (USER_ID,LOGIN_ID,OPERATE_TIMESTAMP,MODULE,OPERATION,ACTION) VALUES ('$user_id','$login_id','".date("Y-m-d H:i:s")."','$module','$operation_type','".json_encode($action)."');";
    $rst_log_operation = $GLOBALS['db'] ->query($log_operation);
}
?>