<?php
require_once __DIR__ . '/../function/org_mgt.php';
require_once __DIR__ . '/../function/task_mgt.php';
require_once __DIR__ . '/../function/bug_mgt.php';
require_once __DIR__ . '/../function/date.php';
require_once __DIR__ . '/../function/check_data_existence.php';

// ==================================================================
//
// Author: CC
// Date: 2012-11-09
// Description: Calculate each valid employee's effort
// Modified: 08/11/2013
// Due to split org and project. Make the changes that org from project
//
// ------------------------------------------------------------------

/**
 * 1. Get all valid employess list
 */
$valid_project_employee = valid_project_employee();

/*
 * 2. Get the task effort,bug effort, support effort of all valid employees
 */

//Query all the task effort between the two date
$task_effort_temp = tae_in_dates($last_saturday, $today);
$_SESSION['task_effort_temp'] = $task_effort_temp;

//re-arrange task array, make the uid as the array KEY
foreach ($task_effort_temp as $tet_key => $tet_val) {
    $task_effort[$tet_val['TESTER']][] = $tet_val;
}

//Query all the task effort between the two date
$bug             = bug_in_dates($last_saturday, $today);
$_SESSION['bug'] = $bug;

//Query all the task effort between the two date
$support = support_in_dates($last_saturday, $today);

/*
 * Array for store personal effort
 * For more details, refer to DB structure in 'PMS_Performance_Improvement'
 * RAW_DATA(blob):
 * array(
    'task' =>
        array(
            [0] =>
                array(
                    [NO] => 54908
                    [ITERATION_ID] => 906
                    [TASK_TYPE] => 1
                    [TASK_ID] =>
                    [TASK_TITLE] =>
                        ......
                    [SEQUENCE_ID] =>
                    )
            )
    'bug'=>
        array(
            [0]=>
                array(
                    )
            ),
    'support'=>
        array(
            [0]=>
                array(
                    )
            )
  )

  PROCESSED_DATA(blob):
    'processed_effort' => array(
        task => array(
            'ex_total'=>,
            'actual_total'=>,
            'estimated setup'=>,
            'estimated execution'=>,
            'actual setup'=>,
            'actual execution'=>,
            'actual investigate'=>
            ),
    'bug' => '',
    'support' => ''
    )
 */

$personal_effort = array();
foreach ($valid_project_employee as $ve_key => $ve_val) {
    /*
     * For task effort
     */
     $personal_effort[$ve_val['FK_USER_ID']]['raw_effort']['task'] = isset($task_effort[$ve_val['FK_USER_ID']]) ? $task_effort[$ve_val['FK_USER_ID']] : array(array());
     
     /*
     * For bug effort
     */
     // 1. For bugs
     $personal_effort[$ve_val['FK_USER_ID']]['raw_effort']['bug'] = isset($bug['bug'][$ve_val['FK_USER_ID']]) ? $bug['bug'][$ve_val['FK_USER_ID']] : array(array());
     
     // 2. For bug notes
     $personal_effort[$ve_val['FK_USER_ID']]['raw_effort']['bug_note'] = isset($bug['bug_note'][$ve_val['FK_USER_ID']]) ? $bug['bug_note'][$ve_val['FK_USER_ID']] : array(array());
     
     /*
     * For support effort
     */
     $personal_effort[$ve_val['FK_USER_ID']]['raw_effort']['support'] = isset($support[$ve_val['FK_USER_ID']]) ? $support[$ve_val['FK_USER_ID']] : array(array());
}

/**
 * Purpose: make the data as the following array structure
 * For more details, refer to DB structure in 'PMS_Performance_Improvement'
 */
foreach ($personal_effort as $rpe_key => $rpe_val) {
    /**
     * For task effort
     */
    $ex_total           = 0;
    $actual_total       = 0;
    $ex_setup           = 0;
    $ex_execution       = 0;
    $actual_setup       = 0;
    $actual_execution   = 0;
    $actual_investigate = 0;
    foreach ($rpe_val['raw_effort']['task'] as $rv_key => $rv_val) {
        $ex_setup           += isset($rv_val['EX_SETUP_TIME']) ? $rv_val['EX_SETUP_TIME'] : 0;
        $ex_execution       += isset($rv_val['EX_EXECUTION_TIME']) ? $rv_val['EX_EXECUTION_TIME'] : 0;
        $actual_setup       += isset($rv_val['AC_SETUP_TIME']) ? $rv_val['AC_SETUP_TIME'] : 0;
        $actual_execution   += isset($rv_val['AC_EXECUTION_TIME']) ? $rv_val['AC_EXECUTION_TIME'] : 0;
        $actual_investigate += isset($rv_val['INVESTIGATE_TIME']) ? $rv_val['INVESTIGATE_TIME'] : 0;
    }
    $ex_total     = $ex_setup + $ex_execution;
    $actual_total = $actual_setup + $actual_execution + $actual_investigate;
    $personal_effort[$rpe_key]['processed_effort']['task']['ex_total']           = $ex_total;
    $personal_effort[$rpe_key]['processed_effort']['task']['actual_total']       = $actual_total;
    $personal_effort[$rpe_key]['processed_effort']['task']['ex_setup']           = $ex_setup;
    $personal_effort[$rpe_key]['processed_effort']['task']['ex_execution']       = $ex_execution;
    $personal_effort[$rpe_key]['processed_effort']['task']['actual_setup']       = $actual_setup;
    $personal_effort[$rpe_key]['processed_effort']['task']['actual_execution']   = $actual_execution;
    $personal_effort[$rpe_key]['processed_effort']['task']['actual_investigate'] = $actual_investigate;

    /**
     * For bug effort
     */
    $bug_time      = 0;
    $bug_note_time = 0;
    // 1. For bugs
    foreach ($rpe_val['raw_effort']['bug'] as $rvb_key => $rvb_val) {
        $bug_time += isset($rvb_val['EXECUTE_TIME']) ? $rvb_val['EXECUTE_TIME'] : 0;
    }
    // For bug notes
    foreach ($rpe_val['raw_effort']['bug_note'] as $rvbn_key => $rvbn_val) {
        $bug_note_time += isset($rvbn_val['EXECUTE_TIME']) ? $rvbn_val['EXECUTE_TIME'] : 0;
    }
    $personal_effort[$rpe_key]['processed_effort']['bug']      = $bug_time;
    $personal_effort[$rpe_key]['processed_effort']['bug_note'] = $bug_note_time;

    /**
     * For support effort
     */
    $support_time = 0;
    foreach ($rpe_val['raw_effort']['support'] as $rvs_key => $rvs_val) {
        $support_time += isset($rvs_val['EXECUTE_TIME']) ? $rvs_val['EXECUTE_TIME'] : 0;
    }
    $personal_effort[$rpe_key]['processed_effort']['support'] = $support_time;
}

/**
 * Push this array into DB
 */
check_cache_effort_data_existence('cache_employee_performance', 'YEAR', 'MONTH', 'WEEK', $year, $month, $week_number);  //1. check the target data existence

//2. insert data into DB
foreach ($personal_effort as $db_pe_key => $db_pe_val) {
    $raw_data       = json_encode($db_pe_val['raw_effort']);
    $processed_data = json_encode($db_pe_val['processed_effort']);
    $cep_array[]    = "('$db_pe_key','$year','$month','$week_number','$raw_data','$processed_data')";
}

$qry_data     = "INSERT INTO cache_employee_performance (FK_UID,YEAR,MONTH,WEEK,RAW_DATA,PROCESSED_DATA) VALUES ".implode(',', $cep_array).";";
$rst_raw_data = $GLOBALS['db']->query($qry_data);
?>
