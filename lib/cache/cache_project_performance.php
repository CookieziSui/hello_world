<?php

require_once __DIR__ . '/../function/org_mgt.php';
require_once __DIR__ . '/../function/task_mgt.php';
require_once __DIR__ . '/../function/bug_mgt.php';
require_once __DIR__ . '/../function/date.php';
require_once __DIR__ . '/../function/check_data_existence.php';
// ==================================================================
//
// Author: CC
// Date: 11/13/2012
// Description: Calculate each valid project's effort
// Modified: 08/11/2013     CC
// Due to split org and project
//
// ------------------------------------------------------------------

/**
 * Effort for custom
 * 1. Get all valid org list
 */

if (isset($_SESSION['valid_projects'])) {
    $valid_projects = $_SESSION['valid_projects'];
} else {
    $valid_projects = valid_op("PROJECT");
}

/**
 * Get all valid iteration list
 */
$valid_iterations = valid_iteration();

$valid_project_iterations = array();
foreach ($valid_projects as $vo_key => $vo_val) {
    $valid_project_iterations[$vo_val['ID']] = isset($valid_iterations[$vo_val['ID']]) ? $valid_iterations[$vo_val['ID']] : array(array('ITERATION_ID' => '', 'FK_PROJECT_ID' => ''));
}

/**
 * Get task effort in dates
 */
if (isset($_SESSION['task_effort_temp'])) {
    $task_effort_temp = $_SESSION['task_effort_temp'];
} else {
    $task_effort_temp = tae_in_dates($last_saturday, $today);
}
//re-arrange task array, make the iteration id as the array KEY
foreach ($task_effort_temp as $tet_key => $tet_val) {
    $task_effort[$tet_val['ITERATION_ID']][] = $tet_val;
}

/**
 * Get bug info in dates
 */
if (isset($_SESSION['bug'])) {
    $bug_temp = $_SESSION['bug'];
} else {
    $bug_temp = bug_in_dates($last_saturday, $today);
}
//re-arrange bug array, make the iteration id as the array KEY
foreach ($bug_temp['bug'] as $bt_key => $bt_val) {
    foreach($bt_val as $bv_b_key => $bv_b_val){
        $bug[$bv_b_val['ITERATION_ID']][] = $bv_b_val;
    }
}

/**
 * Put the effort into the target array
 */
$project_effort = array();
foreach ($valid_project_iterations as $voi_key => $voi_val) {
    $project_effort[$voi_key]['raw_effort']['task_customer'] = array();
    $project_effort[$voi_key]['raw_effort']['bug_customer']  = array();
    $ex_total           = 0;
    $actual_total       = 0;
    $ex_setup           = 0;
    $ex_execution       = 0;
    $actual_setup       = 0;
    $actual_execution   = 0;
    $actual_investigate = 0;
    $bug_time           = 0;
    foreach ($voi_val as $vv_key => $vv_val) {
        //For task
        if (isset($task_effort[$vv_val['ITERATION_ID']])) {
            foreach ($task_effort[$vv_val['ITERATION_ID']] as $tevv_key => $tevv_val) {
                $project_effort[$voi_key]['raw_effort']['task_customer'][] = $tevv_val;
                $ex_setup           += $tevv_val['EX_SETUP_TIME'];
                $ex_execution       += $tevv_val['EX_EXECUTION_TIME'];
                $actual_setup       += $tevv_val['AC_SETUP_TIME'];
                $actual_execution   += $tevv_val['AC_EXECUTION_TIME'];
                $actual_investigate += $tevv_val['INVESTIGATE_TIME'];
            }
        } 
        
        $ex_total = $ex_setup + $ex_execution;
        $actual_total = $actual_setup + $actual_execution + $actual_investigate;
        $project_effort[$voi_key]['processed_effort']['task_customer']['actual_total']       = $actual_total;
        $project_effort[$voi_key]['processed_effort']['task_customer']['ex_setup']           = $ex_setup;
        $project_effort[$voi_key]['processed_effort']['task_customer']['ex_execution']       = $ex_execution;
        $project_effort[$voi_key]['processed_effort']['task_customer']['actual_setup']       = $actual_setup;
        $project_effort[$voi_key]['processed_effort']['task_customer']['actual_execution']   = $actual_execution;
        $project_effort[$voi_key]['processed_effort']['task_customer']['actual_investigate'] = $actual_investigate;

        //For bugs
        if (isset($bug[$vv_val['ITERATION_ID']])) {
            foreach ($bug[$vv_val['ITERATION_ID']] as $bvv_key => $bvv_val) {
                $project_effort[$voi_key]['raw_effort']['bug_customer'][] = $bvv_val;
                $bug_time += $bvv_val['EXECUTE_TIME'];
            }
        }
        $project_effort[$voi_key]['processed_effort']['bug_customer'] = $bug_time;
    }
}

/**
 * Effort for internal
 * 1. Query all valid employee list of this week. 
 * DB table: weekly_org_ahc
 * The data in this table were calculated by valid orgs, 
 * so we can query all orgs' member through $weekly_org_ahc
 * after get the two array, $weekly_org_ahc and weekly_employee_effort, 
 * re-arrange them and get the last part of final array $project_effort
 */

$qry_weekly_project_ahc  = "SELECT FK_PROJECT_ID,AHC,DATA FROM daily_project_ahc WHERE YEAR='$year'AND MONTH='$month' AND WEEK='$week_number';";
$rst_project_ahc         = $GLOBALS['db']->query($qry_weekly_project_ahc);
$weekly_project_ahc_temp = $rst_project_ahc->fetch_all(MYSQLI_ASSOC);

/**
 * unserialize DATA, and re-arrange this array
 * Format:
 *  [OID] => Array
 *       (
 *           [EMPLOYEE_LIST] => Array
 *               (
 *                   [UID] => Array
 *                       (
 *                           [FK_USER_ID] => 59
 *                           [FK_ORG_ID] => 1
 *                           [FK_GROUP_ID] => 1
 *                           [GRP_NAME] => PMS_Admin_Admin
 *                           [GRP_TYPE] => 1
 *                       )
 *
 *               )
 *
 *           [AHC] => 1
 *       )
 */
foreach ($weekly_project_ahc_temp as $woat_key => $woat_val) {
    $employee_list= json_decode($woat_val['DATA'],TRUE);
    foreach($employee_list as $el_key => $el_val){
        $weekly_project_ahc[$woat_val['FK_PROJECT_ID']]['EMPLOYEE_LIST'][$el_val['FK_USER_ID']] = $el_val;
    }
    $weekly_project_ahc[$woat_val['FK_PROJECT_ID']]['AHC'] = $woat_val['AHC'];
}

/**
 * 2. Query all valid employee's effort of this week
 */
$qry_weekly_employee_effort  = "SELECT FK_UID,RAW_DATA,PROCESSED_DATA FROM cache_employee_performance WHERE YEAR='$year'AND MONTH='$month' AND WEEK='$week_number';";
$rst_weekly_employee_effort  = $GLOBALS['db']->query($qry_weekly_employee_effort);
$weekly_employee_effort_temp = $rst_weekly_employee_effort->fetch_all(MYSQLI_ASSOC);

foreach ($weekly_employee_effort_temp as $weet_key => $weet_val) {
    $weekly_employee_effort[$weet_val['FK_UID']] = $weet_val;
}

/**
 * Filter all effort to the correspanding org
 * Format:
 * Array
 *(
 *   [OID] => Array
 *       (
 *           [0] => Array
 *               (
 *                   [FK_UID] => 59
 *                   [RAW_DATA] => 
 *                   [PROCESSED_DATA] => 
 *               )
 *
 *       )
 *)
 */
foreach ($weekly_project_ahc as $woa_key => $woa_val) {
    $employee_list = isset($woa_val['EMPLOYEE_LIST'])?$woa_val['EMPLOYEE_LIST']:array();
    foreach ($employee_list as $el_key => $el_val) {
        $project_employee_effort_temp[$woa_key][] = $weekly_employee_effort[$el_val['FK_USER_ID']];
    }
}

/**
 * Push the raw effort for internal into $project_effort
 */
foreach ($project_employee_effort_temp as $oeet_key => $oeet_val) {
    $project_effort[$oeet_key]['raw_effort']['task_internal'] = array();
    $project_effort[$oeet_key]['raw_effort']['bug_internal']  = array();
    foreach ($oeet_val as $ov_key => $ov_val) {
        $raw_project_employee_data = json_decode($ov_val['RAW_DATA'],TRUE);
        foreach ($raw_project_employee_data['task'] as $roed_t_key => $roed_t_val) {
            $project_effort[$oeet_key]['raw_effort']['task_internal'][] = $roed_t_val;
        }
        foreach ($raw_project_employee_data['bug'] as $roed_b_key => $roed_b_val) {
            if (!empty($roed_b_val)) {
                $project_effort[$oeet_key]['raw_effort']['bug_internal'][] = $roed_b_val;
            }else{
                $project_effort[$oeet_key]['raw_effort']['bug_internal'] = array();
            }
        }
    }
}

/**
 * Process the raw_data and push them into $project_effort
 */
foreach ($project_effort as $oe_key => $oe_val) {
    $ex_total_internal           = 0;
    $actual_total_internal       = 0;
    $ex_setup_internal           = 0;
    $ex_execution_internal       = 0;
    $actual_setup_internal       = 0;
    $actual_execution_internal   = 0;
    $actual_investigate_internal = 0;
    if(isset($oe_val['raw_effort']['task_internal'])){
        foreach ($oe_val['raw_effort']['task_internal'] as $ov_t_key => $ov_t_val) {
            $ex_setup_internal           += isset($ov_t_val['EX_SETUP_TIME']) ? $ov_t_val['EX_SETUP_TIME'] : 0;
            $ex_execution_internal       += isset($ov_t_val['EX_EXECUTION_TIME']) ? $ov_t_val['EX_EXECUTION_TIME'] : 0;
            $actual_setup_internal       += isset($ov_t_val['AC_SETUP_TIME']) ? $ov_t_val['AC_SETUP_TIME'] : 0;
            $actual_execution_internal   += isset($ov_t_val['AC_EXECUTION_TIME']) ? $ov_t_val['AC_EXECUTION_TIME'] : 0;
            $actual_investigate_internal += isset($ov_t_val['INVESTIGATE_TIME']) ? $ov_t_val['INVESTIGATE_TIME'] : 0;
        }   
    }
    
    $ex_total_internal     = $ex_setup_internal + $ex_execution_internal;
    $actual_total_internal = $actual_setup_internal + $actual_execution_internal + $actual_investigate_internal;
    $project_effort[$oe_key]['processed_effort']['task_internal']['ex_total']           = $ex_total_internal;
    $project_effort[$oe_key]['processed_effort']['task_internal']['actual_total']       = $actual_total_internal;
    $project_effort[$oe_key]['processed_effort']['task_internal']['ex_setup']           = $ex_setup_internal;
    $project_effort[$oe_key]['processed_effort']['task_internal']['ex_execution']       = $ex_execution_internal;
    $project_effort[$oe_key]['processed_effort']['task_internal']['actual_setup']       = $actual_setup_internal;
    $project_effort[$oe_key]['processed_effort']['task_internal']['actual_execution']   = $actual_execution_internal;
    $project_effort[$oe_key]['processed_effort']['task_internal']['actual_investigate'] = $actual_investigate_internal;

    /**
     * For bug effort
     */
    $bug_time = 0;
    // 1. For bugs
    if(isset($oe_val['raw_effort']['bug_internal'])){
        foreach ($oe_val['raw_effort']['bug_internal'] as $ov_b_key => $ov_b_val) {
            $bug_time += isset($ov_b_val['EXECUTE_TIME']) ? $ov_b_val['EXECUTE_TIME'] : 0;
        }
    }
    $project_effort[$oe_key]['processed_effort']['bug_internal'] = $bug_time;
}

/**
 * Push this array into DB
 */
check_cache_effort_data_existence('cache_project_performance', 'YEAR', 'MONTH', 'WEEK', $year, $month, $week_number);   //1. check the target data existence

//2. insert data into DB
foreach ($project_effort as $db_oe_key => $db_oe_val) {
    $raw_data       = json_encode($db_oe_val['raw_effort']);
    $processed_data = json_encode($db_oe_val['processed_effort']);
    $cpe[] = "('$db_oe_key','$year','$month','$week_number','$raw_data','$processed_data')";
}
$qry_data = "INSERT INTO cache_project_performance (FK_PROJECT_ID,YEAR,MONTH,WEEK,RAW_DATA,PROCESSED_DATA) VALUES ".implode(',', $cpe).";";
$rst_raw_data = $GLOBALS['db']->query($qry_data);
?>
