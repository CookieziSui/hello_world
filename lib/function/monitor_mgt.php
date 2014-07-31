<?php
require_once __DIR__ . '/../../lib/function/access_control.php';
require_once __DIR__ . '/../../lib/function/org_mgt.php';
require_once __DIR__ . '/../../lib/function/bug_mgt.php';
require_once __DIR__ . '/../../lib/inc/constant_task.php';
require_once __DIR__ . '/../../lib/inc/constant_bug.php';
require_once __DIR__ . '/../../lib/function/task_mgt.php';
require_once __DIR__ . '/../../lib/function/error.php';

/*
 * Caculate the month between two date
 */

function caculate_month($date_from, $date_to) {
    $month_from_temp = date('Y-m', strtotime($date_from));
    $month_to_temp = date('Y-m', strtotime($date_to));
    $month_from = strtotime($month_from_temp);
    $month_to = strtotime($month_to_temp);
    $f = '';

    while ($month_from < $month_to) {
        $month_from = strtotime((date('Y-m-d', $month_from) . ' +15days'));
        if (date('Y-F', $month_from) != $f) {
            $f = date('Y-F', $month_from);
            if (date('Y-m', $month_from) != $month_to && ($month_from < $month_to))
                $months[] = array(
                    'Y-F' => date('Y-F', $month_from),
                    'Y-m' => date('Y-m', $month_from));
        }
    }

    $months[] = array(
        'Y-F' => date('Y-F', $month_from),
        'Y-m' => date('Y-m', $month_from));
    return $months;
}

/*
 * Part I: For caculate the task effirt (time)
 */

function caculate_tasks_effort($org_task_effort) {
    $ex_setup_time        = 0;
    $ex_execution_time    = 0;
    $ac_setup_time        = 0;
    $ac_execution_time    = 0;
    $investigate_time     = 0;
    $total_actual_time    = 0;
    $total_expected_time  = 0;
    $org_task_time_effort = 0;

    foreach ($org_task_effort as $ote_key => $ote_val) {
        $ex_setup_time     += $ote_val['EX_SETUP_TIME'];
        $ex_execution_time += $ote_val['EX_EXECUTION_TIME'];
        $ac_setup_time     += $ote_val['AC_SETUP_TIME'];
        $ac_execution_time += $ote_val['AC_EXECUTION_TIME'];
        $investigate_time  += $ote_val['INVESTIGATE_TIME'];
    }
    $total_expected_time = $ex_setup_time + $ex_execution_time;
    $total_actual_time = $ac_setup_time + $ac_execution_time + $investigate_time;

    $org_task_time_effort = array(
        'ex_setup_time'       => round($ex_setup_time / 60, 2),
        'ex_execution_time'   => round($ex_execution_time / 60, 2),
        'ac_setup_time'       => round($ac_setup_time / 60, 2),
        'ac_execution_time'   => round($ac_execution_time / 60, 2),
        'investigate_time'    => round($investigate_time / 60, 2),
        'expected_total_time' => round($total_expected_time / 60, 2),
        'actual_total_time'   => round($total_actual_time / 60, 2),
    );
    return $org_task_time_effort;
}

/**
 * [caculate_effort Calculate the effort of the org
 * Cal each type of effort time: Estimated, Actual, [Bug]
 * $bug_flag: 0: exclude bug time [For internal view]
 *            1: include bug time [For external view]
 * ]
 * Author: CC
 * Date: 2013-06-24
 * Last Modified: 2013-06-24
 * @param  [array] $org_effort    [all org effort]
 * @param  [int] $bug_flag        [0: exclude bug time, 1: include bug time]
 * @return [array]                  [calculated effort]
 * Modified:08/28/2013  CC 
 *     if bug_flag is 0, set total_effort to total_expected_time
 */
function caculate_effort($org_effort, $bug_effort, $bug_flag) {
    $ex_setup_time       = 0;
    $ex_execution_time   = 0;
    $ac_setup_time       = 0;
    $ac_execution_time   = 0;
    $investigate_time    = 0;
    $total_actual_time   = 0;
    $total_expected_time = 0;
    $total_bug_time      = 0;
    $total_effort        = 0;
    $general_org_effort  = 0;

    /**
     * Loop the org effort, add each time
     */
    foreach ($org_effort as $ote_key => $ote_val) {
        $ex_setup_time     += $ote_val['EX_SETUP_TIME'];
        $ex_execution_time += $ote_val['EX_EXECUTION_TIME'];
        $ac_setup_time     += $ote_val['AC_SETUP_TIME'];
        $ac_execution_time += $ote_val['AC_EXECUTION_TIME'];
        $investigate_time  += $ote_val['INVESTIGATE_TIME'];
    }

    /**
     * Loop the bug effort, add each bug time (including bug note time)
     */
    
    foreach ($bug_effort as $be_key => $be_val) {
        $total_bug_time += $be_val['EXECUTE_TIME'];
    }    

    $total_expected_time = $ex_setup_time + $ex_execution_time;
    $total_actual_time   = $ac_setup_time + $ac_execution_time + $investigate_time;

    if($bug_flag == 1){
        $total_effort = $total_actual_time + $total_bug_time;
    } else{
        $total_effort = $total_expected_time + 0;
    }
    $general_org_effort = array(
        'ex_setup_time'       => round($ex_setup_time / 60, 2),
        'ex_execution_time'   => round($ex_execution_time / 60, 2),
        'ac_setup_time'       => round($ac_setup_time / 60, 2),
        'ac_execution_time'   => round($ac_execution_time / 60, 2),
        'investigate_time'    => round($investigate_time / 60, 2),
        'bug_time'            => round($total_bug_time / 60, 2),
        'expected_total_time' => round($total_expected_time / 60, 2),
        'actual_total_time'   => round($total_actual_time / 60, 2),
        'total_effort'        => round($total_effort / 60, 2)
    );
    
    return $general_org_effort;
}

/**
 * [task_effort Calculte tasks within the date scope]
 * @param  [int] $father_id [father org id]
 * @param  [array] $sub_org   [sub orgs]
 * @param  [string] $date_from [date from]
 * @param  [string] $date_to   [date to]
 * @return [type]            [
 * [OID] => Array
 *     (
 *         [ORG_NAME]
 *         [LIST] = Array
 *             (
 *                 [FINISHED]
 *                 [UNFINISHED]
 *             )
 *         [COUNT] = Array
 *             (
 *                 [TOTAL]
 *                 [UNFINISHED]
 *                 [FINISHED]
 *                 [FINISHED_RATE]
 *                 [AHC]
 *             )   
 *         [TIME_EFFORT] = Array
 *             (
 *                 [UNFINISHED] => Array
 *                     (
 *                          [ex_setup_time] => 0
 *                          [ex_execution_time] => 0
 *                          [ac_setup_time] => 0
 *                          [ac_execution_time] => 0
 *                          [investigate_time] => 0
 *                          [expected_total_time] => 0
 *                          [actual_total_time] => 0
 *                     )
 *                 [FINISHED] => Array
 *                     (
 *                          [ex_setup_time] => 0
 *                          [ex_execution_time] => 0
 *                          [ac_setup_time] => 0
 *                          [ac_execution_time] => 0
 *                          [investigate_time] => 0
 *                          [expected_total_time] => 0
 *                          [actual_total_time] => 0
 *                     )
 *             )
 *     )
 * 
 * ]
 */
function task_effort($projects, $date_from, $date_to) {
    $i = 0;
    $j = 0;
    $k = 0;
    /**TODO
    * March 01
    */
    // ==================================================================
    //
    // Author: CC
    // Date:
    // Last Modified: 2013-06-18
    // 
    // If current timestamp is larger than the deadline that pre-defined, MP will calculate data including today's data,
    // if not, check whethe the $day_of_week is monday or not,
    //      Yes: do not minus one day, just list the data of this week
    //      No: minus one day, set the month_day as yesterday
    //
    // Modified: 08/09/2013 CC
    // Simplify the loop. Just calc project
    // ------------------------------------------------------------------
    
    if(strtotime(date("H:i:s")) >= strtotime($GLOBALS['DATA_TAKEN_TIME_DL'])){
        $day_of_week = $GLOBALS['day_of_week'];
        $month_day   = $GLOBALS['month_today'];
    }else{
        if($GLOBALS['day_of_week'] == 1){
            $day_of_week = 1;
            $month_day   = $GLOBALS['month_today'];
        }else{
            $day_of_week = $GLOBALS['day_of_week']-1;
            $month_day   = $GLOBALS['month_yesterday'];
        }
    }

    $general_sub_project_task            = array();
    $direct_sub_project_string           = op_string($projects);
    $list_unfinished_project_task        = list_unfinished_project_task($direct_sub_project_string);
    $list_finished_project_task_withdate = list_finished_project_task_withdate($direct_sub_project_string, $date_from, $date_to);
    
    /**
     * [$bug_info_withdate Buf info within a date scope]
     * @var [array]
     */
    $bug_note_withdate = bugs_of_org_external($direct_sub_project_string, $date_from, $date_to);

    /**
     * [$ds_org_md get org ahc with date scope]
     * the ahc info of a org between the given date scope
     * @var [type]
     */
    if($_SESSION['type'] == 4){
        $ds_op_md = ds_op_md_bhc($direct_sub_project_string,$date_from, $date_to, "PROJECT");
    }else{
        $ds_op_md = ds_op_md($direct_sub_project_string,$date_from, $date_to, "PROJECT");
    }
    
    $today_explode = explode("-",$GLOBALS['today']);

    /**
     * [$last_month_end The last day of last month]
     * @var [date]
     */
    $last_month_end   = $GLOBALS['last_month']."-31";
    /**
     * [$last_month_month The month number of last month]
     * @var [char] e.g. 02
     */
    $last_month_month = $GLOBALS['last_month_month'];

    /**
     * [$last_month_year The year number of last month]
     * @var [chart] e.g. 2013
     */
    $last_month_year  = $GLOBALS['last_month_year'];
    
    /**
     * Rearrange this org ahc array. Set Org ID as the first deep key. 
     * And the day of last month as the second deep key
     */
    if(isset($ds_op_md[$last_month_year][$last_month_month])){
        foreach($ds_op_md[$last_month_year][$last_month_month] as $doa_key => $doa_val){
            foreach($doa_val as $dv_key => $dv_val){
                $last_month_day = $last_month_month."-".$doa_key;
                $ds_org_md_by_project_month[$dv_key][$last_month_day] = $dv_val['MD'];
                $ds_org_wd_by_project_month[$dv_key][$last_month_day] = $dv_val['WD'];
            }
        }
    }

    /**
     * [$last_week_day_monday last monday of last week]
     * @var [date]
     */
    $last_week_day_monday = $GLOBALS['last_monday'];
    /**
     * [$last_week_day Minus one day]
     * @var [date]
     * This is used for the following loop. Once we minus one day, the loop can calculate ahc from Monday as expected 
     */
    $last_week_day = date("Y-m-d",strtotime($last_week_day_monday." -1 day"));
    for($i=0;$i<=6;$i++){
        $last_week_day         = date("Y-m-d",strtotime($last_week_day." +1 day"));
        $last_week_day_month   = date("m/d",strtotime($last_week_day));
        $last_week_day_explode = explode("-",$last_week_day);
        if(isset($ds_op_md[$last_week_day_explode[0]][$last_week_day_explode[1]][$last_week_day_explode[2]])){
            foreach($ds_op_md[$last_week_day_explode[0]][$last_week_day_explode[1]][$last_week_day_explode[2]] as $doad_key => $doad_val){
                $ds_org_md_by_project_day[$doad_key][$last_week_day_month] = $doad_val['MD'];
                $ds_org_wd_by_project_day[$doad_key][$last_week_day_month] = $doad_val['WD'];
            }
        }
    }    
    
    /**
     * [$this_monday This Monday of this week]
     * @var [date]
     */
    $this_monday = $GLOBALS['this_monday'];

    /**
     * [$this_week_day Minus one day]
     * @var [date]
     * This is used for the following loop. Once we minus one day, the loop can calculate ahc from Monday as expected 
     */
    $this_week_day = date("Y-m-d",strtotime($this_monday." -1 day"));
    
    for($j=0;$j<$day_of_week;$j++){
        $this_week_day         = date("Y-m-d",strtotime($this_week_day." +1 day"));
        $this_week_day_month   = date("m/d",strtotime($this_week_day));
        $this_week_day_explode = explode("-",$this_week_day);
        if(isset($ds_op_md[$this_week_day_explode[0]][$this_week_day_explode[1]][$this_week_day_explode[2]])){
            foreach($ds_op_md[$this_week_day_explode[0]][$this_week_day_explode[1]][$this_week_day_explode[2]] as $doat_key => $doat_val){
                $ds_org_md_by_project_this[$doat_key][$this_week_day_month] = $doat_val['MD'];
                $ds_org_wd_by_project_this[$doat_key][$this_week_day_month] = $doat_val['WD'];
            }
        }
    }

    
    foreach ($projects as $p_key => $p_val) {
        $sub_project_task          = array();
        $sub_project_wmd           = array();   //Each project's daily md or wd
        $sub_project_task_count    = array();
        $sub_project_task_temp     = array();
        $finished_sub_project_task = array();
        $total_sub_project_task    = array();
        $finished_sub_project_task_per_day          = array();
        $finished_sub_project_task_per_engineer_day = array();
        $this_week_day_number_name                  = date("Y-m-d",strtotime($this_monday." -1 day"));

        $sub_project_task_temp['FINISHED']   = isset($list_finished_project_task_withdate[$p_key]) ? $list_finished_project_task_withdate[$p_key] : array();
        $sub_project_task_temp['UNFINISHED'] = isset($list_unfinished_project_task[$p_key]) ? $list_unfinished_project_task[$p_key] : array();
        $sub_project_task_temp['BUG']        = isset($bug_note_withdate[$p_key]) ? $bug_note_withdate[$p_key] : array();
        $sub_project_task_temp['MD']['THIS_WEEK']  = isset($ds_org_md_by_project_this[$p_key])?$ds_org_md_by_project_this[$p_key]:array();
        $sub_project_task_temp['MD']['LAST_WEEK']  = isset($ds_org_md_by_project_day[$p_key])?$ds_org_md_by_project_day[$p_key]:array();
        $sub_project_task_temp['MD']['LAST_MONTH'] = isset($ds_org_md_by_project_month[$p_key])?$ds_org_md_by_project_month[$p_key]:array();
        $sub_project_task_temp['WD']['THIS_WEEK']  = isset($ds_org_wd_by_project_this[$p_key])?$ds_org_wd_by_project_this[$p_key]:array();
        $sub_project_task_temp['WD']['LAST_WEEK']  = isset($ds_org_wd_by_project_day[$p_key])?$ds_org_wd_by_project_day[$p_key]:array();
        $sub_project_task_temp['WD']['LAST_MONTH'] = isset($ds_org_wd_by_project_month[$p_key])?$ds_org_wd_by_project_month[$p_key]:array();
        
        /**
         * [['FINISHED'] Split task execution record into different date scope]
         * @var [type]
         */
        foreach ($sub_project_task_temp['FINISHED'] as $sott_key => $sott_val) {
            if(($sott_val['FINISH_DATE'] >= $GLOBALS['this_monday'])&&($sott_val['FINISH_DATE'] <= $GLOBALS['today'])){
                $sub_project_task['FINISHED']['THIS_WEEK']['GENERAL'][] = $sott_val;
            }
            if(($sott_val['FINISH_DATE'] >= $GLOBALS['last_monday'])&&($sott_val['FINISH_DATE'] <= $GLOBALS['last_sunday'])){
                $sub_project_task['FINISHED']['LAST_WEEK'][] = $sott_val;
            }
            if(($sott_val['FINISH_DATE'] >= $GLOBALS['last_month_day'])&&($sott_val['FINISH_DATE'] <= $last_month_end)){
                $sub_project_task['FINISHED']['LAST_MONTH'][] = $sott_val;
            }
            if($sott_val['FINISH_DATE'] == $GLOBALS['today']){
                $sub_project_task['FINISHED']['TODAY'][] = $sott_val;
            }
            /**
             * []
             */
            if(($sott_val['FINISH_DATE'] >= $this_monday)&&($sott_val['FINISH_DATE'] <= $GLOBALS['today'])){
                $finished_date_name = date("m/d",strtotime($sott_val['FINISH_DATE']));
                $sub_project_task['FINISHED']['THIS_WEEK']['DETAILED'][$finished_date_name][] = $sott_val;
            }
        }
            
        /**
         * [['BUG'] Split bug and bug note records into different date scope]
         * @var [type]
         */
        foreach ($sub_project_task_temp['BUG'] as $sottb_key => $sottb_val) {
            if(($sottb_val['SUBMIT_DATE'] >= $GLOBALS['this_monday'])&&($sottb_val['SUBMIT_DATE'] <= $GLOBALS['today'])){
                $sub_project_task['BUG']['THIS_WEEK']['GENERAL'][] = $sottb_val;
            }
            if(($sottb_val['SUBMIT_DATE'] >= $GLOBALS['last_monday'])&&($sottb_val['SUBMIT_DATE'] <= $GLOBALS['last_sunday'])){
                $sub_project_task['BUG']['LAST_WEEK'][] = $sottb_val;
            }
            if(($sottb_val['SUBMIT_DATE'] >= $GLOBALS['last_month_day'])&&($sottb_val['SUBMIT_DATE'] <= $last_month_end)){
                $sub_project_task['BUG']['LAST_MONTH'][] = $sottb_val;
            }
            if($sottb_val['SUBMIT_DATE'] == $GLOBALS['today']){
                $sub_project_task['BUG']['TODAY'][] = $sottb_val;
            }
            /**
             * []
             */
            if(($sottb_val['SUBMIT_DATE'] >= $this_monday)&&($sottb_val['SUBMIT_DATE'] <= $GLOBALS['today'])){
                $finished_date_name = date("m/d",strtotime($sottb_val['SUBMIT_DATE']));
                $sub_project_task['BUG']['THIS_WEEK']['DETAILED'][$finished_date_name][] = $sottb_val;
            }
        }
        //dump($sub_project_task['BUG']['THIS_WEEK']);
        //dump($sub_project_task['BUG']['THIS_WEEK']['DETAILED']);
        foreach ($sub_project_task_temp['UNFINISHED'] as $sottu_key => $sottu_val) {
            $sub_project_task['UNFINISHED'][] = $sottu_val;
        }
            
        /**
         * [['MD'] Calculate each date scope's MD]
         * @var [type]
         */
        foreach ($sub_project_task_temp['MD'] as $sottm_key => $sottm_val) {
            $sub_project_task['MD'][$sottm_key] = array_sum($sottm_val);
            foreach($sottm_val as $svm_key => $svm_val){
                $sub_project_wmd['MD'][$svm_key] = $svm_val;
            }
        }
        
        /**
         * [['WD'] Calculate each date scope's WD]
         * @var [type]
         */
        foreach ($sub_project_task_temp['WD'] as $sottw_key => $sottw_val) {
            $sub_project_task['WD'][$sottw_key] = array_sum($sottw_val);
            foreach($sottw_val as $svw_key => $svw_val){
                $sub_project_wmd['WD'][$svw_key] = $svw_val;
            }
        }

        $sub_project_task_count['TOTAL'] = (isset($sub_project_task['FINISHED']['TODAY']) ? count($sub_project_task['FINISHED']['TODAY']) : 0) + (isset($sub_project_task['UNFINISHED']) ? count($sub_project_task['UNFINISHED']) : 0);
        
        if (isset($sub_project_task['UNFINISHED'])) {
            $sub_project_task_count['UNFINISHED'] = count($sub_project_task['UNFINISHED']);
            $total_sub_project_task = caculate_tasks_effort($sub_project_task['UNFINISHED']);
        }else{
            $sub_project_task_count['UNFINISHED'] = 0;
            $total_sub_project_task = array(
                'ex_setup_time'       => 0,
                'ex_execution_time'   => 0,
                'ac_setup_time'       => 0,
                'ac_execution_time'   => 0,
                'investigate_time'    => 0,
                'bug_time'            => 0,
                'expected_total_time' => 0,
                'actual_total_time'   => 0,
                'total_effort'        => 0
            );
        }
        
        if (isset($sub_project_task['FINISHED']['TODAY'])) {
            $sub_project_task_count['FINISHED']['TODAY'] = count($sub_project_task['FINISHED']['TODAY']);
            if($_SESSION['type'] == 4){
                $finished_sub_project_task['TODAY'] = caculate_effort($sub_project_task['FINISHED']['TODAY'],isset($sub_project_task['BUG']['TODAY'])?$sub_project_task['BUG']['TODAY']:array(),1);
            }else{
                $finished_sub_project_task['TODAY'] = caculate_effort($sub_project_task['FINISHED']['TODAY'],isset($sub_project_task['BUG']['TODAY'])?$sub_project_task['BUG']['TODAY']:array(),0);
            }
        } else {
            $sub_project_task_count['FINISHED']['TODAY'] = 0;
            $finished_sub_project_task['TODAY'] = array(
                'ex_setup_time'       => 0,
                'ex_execution_time'   => 0,
                'ac_setup_time'       => 0,
                'ac_execution_time'   => 0,
                'investigate_time'    => 0,
                'bug_time'            => 0,
                'expected_total_time' => 0,
                'actual_total_time'   => 0,
                'total_effort'        => 0
            );
        }

        $sub_project_task_count['FINISHED']['THIS_WEEK']  = isset($sub_project_task['FINISHED']['THIS_WEEK']['GENERAL']) ? count($sub_project_task['FINISHED']['THIS_WEEK']['GENERAL']) : 0;
        $sub_project_task_count['FINISHED']['LAST_WEEK']  = isset($sub_project_task['FINISHED']['LAST_WEEK']) ? count($sub_project_task['FINISHED']['LAST_WEEK']) : 0;
        $sub_project_task_count['FINISHED']['LAST_MONTH'] = isset($sub_project_task['FINISHED']['LAST_MONTH']) ? count($sub_project_task['FINISHED']['LAST_MONTH']) : 0;

        /**
        * @Description: Calculte task effort finished last week
        */
        if (isset($sub_project_task['FINISHED']['LAST_WEEK'])) {
            $sub_project_task_count['FINISHED']['LAST_WEEK'] = count($sub_project_task['FINISHED']['LAST_WEEK']);
            if($_SESSION['type'] == 4){
                $finished_sub_project_task['LAST_WEEK'] = caculate_effort($sub_project_task['FINISHED']['LAST_WEEK'],isset($sub_project_task['BUG']['LAST_WEEK'])?$sub_project_task['BUG']['LAST_WEEK']:array(),1);
            }else{
                $finished_sub_project_task['LAST_WEEK'] = caculate_effort($sub_project_task['FINISHED']['LAST_WEEK'],isset($sub_project_task['BUG']['LAST_WEEK'])?$sub_project_task['BUG']['LAST_WEEK']:array(),0);
            }
            
            
            if($sub_project_task['WD']['LAST_WEEK'] != 0){
                $finished_sub_project_task_per_day['LAST_WEEK'] = round(($finished_sub_project_task['LAST_WEEK']['total_effort']/$sub_project_task['WD']['LAST_WEEK']),2);
            }else{
                $finished_sub_project_task_per_day['LAST_WEEK'] = 0;
            }
            
            if($sub_project_task['MD']['LAST_WEEK'] != 0){
                $finished_sub_project_task_per_engineer_day['LAST_WEEK'] = round(($finished_sub_project_task['LAST_WEEK']['total_effort']/$sub_project_task['MD']['LAST_WEEK']),2);
            }else{
                $finished_sub_project_task_per_engineer_day['LAST_WEEK'] = 0;
            }
            
        } else {
            $sub_project_task_count['FINISHED']['LAST_WEEK']  = 0;
            $finished_sub_project_task['LAST_WEEK'] = array(
                'ex_setup_time'       => 0,
                'ex_execution_time'   => 0,
                'ac_setup_time'       => 0,
                'ac_execution_time'   => 0,
                'investigate_time'    => 0,
                'bug_time'            => 0,
                'expected_total_time' => 0,
                'actual_total_time'   => 0,
                'total_effort'        => 0
            );
            $finished_sub_project_task_per_day['LAST_WEEK']          = 0;
            $finished_sub_project_task_per_engineer_day['LAST_WEEK'] = 0;
        }
        
        /**
        * @Description: Calculte task effort finished last month
        */
        if (isset($sub_project_task['FINISHED']['LAST_MONTH'])) {
            $sub_project_task_count['FINISHED']['LAST_MONTH'] = count($sub_project_task['FINISHED']['LAST_MONTH']);
            if($_SESSION['type'] == 4){
                $finished_sub_project_task['LAST_MONTH'] = caculate_effort($sub_project_task['FINISHED']['LAST_MONTH'],isset($sub_project_task['BUG']['LAST_MONTH'])?$sub_project_task['BUG']['LAST_MONTH']:array(),1);
            }else{
                $finished_sub_project_task['LAST_MONTH'] = caculate_effort($sub_project_task['FINISHED']['LAST_MONTH'],isset($sub_project_task['BUG']['LAST_MONTH'])?$sub_project_task['BUG']['LAST_MONTH']:array(),0);
            }
            
            
            if($sub_project_task['WD']['LAST_MONTH'] != 0){
                $finished_sub_project_task_per_day['LAST_MONTH'] = round(($finished_sub_project_task['LAST_MONTH']['total_effort']/$sub_project_task['WD']['LAST_MONTH']),2);
            }else {
                $finished_sub_project_task_per_day['LAST_MONTH'] = 0;
            }

            if($sub_project_task['MD']['LAST_MONTH'] != 0){
                $finished_sub_project_task_per_engineer_day['LAST_MONTH'] = round(($finished_sub_project_task['LAST_MONTH']['total_effort']/$sub_project_task['MD']['LAST_MONTH']),2);
            }else{
                $finished_sub_project_task_per_engineer_day['LAST_MONTH'] = 0;
            }
        } else {
            $sub_project_task_count['FINISHED']['LAST_MONTH'] = 0;
            $finished_sub_project_task['LAST_MONTH'] = array(
                'ex_setup_time'       => 0,
                'ex_execution_time'   => 0,
                'ac_setup_time'       => 0,
                'ac_execution_time'   => 0,
                'investigate_time'    => 0,
                'bug_time'            => 0,
                'expected_total_time' => 0,
                'actual_total_time'   => 0,
                'total_effort'        => 0
            );
            $finished_sub_project_task_per_day['LAST_MONTH']          = 0;
            $finished_sub_project_task_per_engineer_day['LAST_MONTH'] = 0;
        }
        
        /**
        * @Description: Calculte task effort finished this week
        */
        if (isset($sub_project_task['FINISHED']['THIS_WEEK'])) {
            $sub_project_task_count['FINISHED']['THIS_WEEK']   = count($sub_project_task['FINISHED']['THIS_WEEK']['GENERAL']);
            if($_SESSION['type'] == 4){
                $finished_sub_project_task['THIS_WEEK']['GENERAL'] = caculate_effort($sub_project_task['FINISHED']['THIS_WEEK']['GENERAL'],isset($sub_project_task['BUG']['THIS_WEEK']['GENERAL'])?$sub_project_task['BUG']['THIS_WEEK']['GENERAL']:array(),1);
            }else{
                $finished_sub_project_task['THIS_WEEK']['GENERAL'] = caculate_effort($sub_project_task['FINISHED']['THIS_WEEK']['GENERAL'],isset($sub_project_task['BUG']['THIS_WEEK']['GENERAL'])?$sub_project_task['BUG']['THIS_WEEK']['GENERAL']:array(),0);
            }
           
            if($sub_project_task['WD']['THIS_WEEK'] != 0){
                $finished_sub_project_task_per_day['THIS_WEEK'] = round(($finished_sub_project_task['THIS_WEEK']['GENERAL']['total_effort'] / $sub_project_task['WD']['THIS_WEEK']),2);
            }else{
                $finished_sub_project_task_per_day['THIS_WEEK'] = 0;
            }

            if($sub_project_task['MD']['THIS_WEEK'] != 0){
                $finished_sub_project_task_per_engineer_day['THIS_WEEK'] = round(($finished_sub_project_task['THIS_WEEK']['GENERAL']['total_effort'] / $sub_project_task['MD']['THIS_WEEK']),2);
            }else{
                $finished_sub_project_task_per_engineer_day['THIS_WEEK'] = 0;
            }
            
            foreach($sub_project_task['FINISHED']['THIS_WEEK']['DETAILED'] as $svv_key => $svv_val){
                if($_SESSION['type'] == 4){
                    $finished_sub_project_task['THIS_WEEK']['DETAILED'][$svv_key] = caculate_effort($svv_val,array(),1);
                }else{
                    $finished_sub_project_task['THIS_WEEK']['DETAILED'][$svv_key] = caculate_effort($svv_val,array(),0);
                }
            }
        } else {
            $sub_project_task_count['FINISHED']['THIS_WEEK']  = 0;
            $finished_sub_project_task['THIS_WEEK']['GENERAL'] = array(
                'ex_setup_time'       => 0,
                'ex_execution_time'   => 0,
                'ac_setup_time'       => 0,
                'ac_execution_time'   => 0,
                'investigate_time'    => 0,
                'bug_time'            => 0,
                'expected_total_time' => 0,
                'actual_total_time'   => 0,
                'total_effort'        => 0
            );
            $finished_sub_project_task_per_day['THIS_WEEK']          = 0;
            $finished_sub_project_task_per_engineer_day['THIS_WEEK'] = 0;
            $finished_sub_project_task['THIS_WEEK']['DETAILED']      = array();
        }

        $empty_finished_sub_project_week_task = array(
                'ex_setup_time'       => 0,
                'ex_execution_time'   => 0,
                'ac_setup_time'       => 0,
                'ac_execution_time'   => 0,
                'investigate_time'    => 0,
                'bug_time'            => 0,
                'expected_total_time' => 0,
                'actual_total_time'   => 0,
                'total_effort'        => 0
        );

        for($k = 0; $k < $day_of_week; $k++){
            $this_week_day_number_name = date("Y-m-d",strtotime($this_week_day_number_name." +1 day"));
            $this_week_day             = date("m/d",strtotime($this_week_day_number_name));
            $finished_sub_project_task['THIS_WEEK']['DETAILED'][$this_week_day] = (isset($finished_sub_project_task['THIS_WEEK']['DETAILED'][$this_week_day]))?$finished_sub_project_task['THIS_WEEK']['DETAILED'][$this_week_day]:$empty_finished_sub_project_week_task;
            $sub_project_task_count['MD']['THIS_WEEK'][$this_week_day]          = isset($sub_project_wmd['MD'][$this_week_day])?$sub_project_wmd['MD'][$this_week_day]:0;
        }
        ksort($finished_sub_project_task['THIS_WEEK']['DETAILED']);
        /**
         * [$this_week_day_number_name_standard Used for analysis standard days left]
         * @var [type]
         */
        $this_week_day_number_name_standard = $this_week_day_number_name;
        $finished_sub_project_task_standard = $finished_sub_project_task;
        $standard_daily_effort              = $GLOBALS['VALID_DAILY_EFFORT']['INTERNAL'] * (isset($sub_project_task_temp['MD']['THIS_WEEK'][$month_day])?$sub_project_task_temp['MD']['THIS_WEEK'][$month_day]:0);

        /**
         * The following two parts is used for detailed left days of unfinished tasks
         * They are estimated by actual average status
         */
        /**
        * [$left_days The reuqired days for all the unfinished tasks]
        * [$task_left_last_day The unfinished tasks left for the last day]
        * @var [int]
        */
        if($standard_daily_effort == 0){
            $left_days          = 0;
            $task_left_last_day = 0;
        }else{
            if($finished_sub_project_task_per_day['THIS_WEEK'] == 0){
                $left_days          = intval($total_sub_project_task['expected_total_time'] / $standard_daily_effort);
                $task_left_last_day = ($total_sub_project_task['expected_total_time']) % $standard_daily_effort;
            }else{
                $left_days          = intval($total_sub_project_task['expected_total_time'] / $finished_sub_project_task_per_day['THIS_WEEK']);
                $task_left_last_day = ($total_sub_project_task['expected_total_time']) % ($finished_sub_project_task_per_day['THIS_WEEK']);
            }
        }
            
        $finished_sub_project_task = details_for_unfinished_task($left_days,$task_left_last_day,$this_week_day_number_name,$finished_sub_project_task,$finished_sub_project_task_per_day['THIS_WEEK']);
        /**
         * The following two parts is also used for detailed left days of unfinished tasks
         * But different with the above two parts
         * They are estimated by standard estimated benchmark
         */
        
        /**
        * [$left_days The reuqired days for all the unfinished tasks]
        * @var [int]
        */
        $left_days_standard = ($standard_daily_effort != 0)?(intval($total_sub_project_task['expected_total_time']/$standard_daily_effort)):0;

        /**
         * [$task_left_last_day The unfinished tasks left for the last day]
         * @var [float]
         */
        $task_left_last_day_standard = ($standard_daily_effort != 0)?($total_sub_project_task['expected_total_time']%$standard_daily_effort):0;
        $finished_sub_project_task_standard = details_for_unfinished_task($left_days_standard,$task_left_last_day_standard,$this_week_day_number_name_standard,$finished_sub_project_task_standard,$standard_daily_effort);
 
        /**
        * @Description: Calculte the finished rate.
        * @Algorithm: All finished tasks today / all tasks     (finished task today + all unfinished tasks)
        * If no tasks finished today, the rate will be 0
        */
        if ($sub_project_task_count['TOTAL'] != 0) {
            $sub_project_task_count['FINISHED_RATE'] = round($sub_project_task_count['FINISHED']['TODAY'] / $sub_project_task_count['TOTAL'], 2) * 100;
        } else {
            $sub_project_task_count['FINISHED_RATE'] = 0;
        }

        $general_sub_project_task[$p_key]['NAME']                                   = $p_val['NAME'];        
        $general_sub_project_task[$p_key]['COUNT']                                  = $sub_project_task_count;
        $general_sub_project_task[$p_key]['TIME_EFFORT']['UNFINISHED']              = $total_sub_project_task;
        $general_sub_project_task[$p_key]['TIME_EFFORT']['FINISHED']                = $finished_sub_project_task;
        $general_sub_project_task[$p_key]['TIME_EFFORT']['STANDARD']                = $finished_sub_project_task_standard;
        $general_sub_project_task[$p_key]['TIME_EFFORT']['EFFORT_PER_DAY']          = $finished_sub_project_task_per_day;
        $general_sub_project_task[$p_key]['TIME_EFFORT']['EFFORT_PER_ENGINEER_DAY'] = $finished_sub_project_task_per_engineer_day;    
    }
    return $general_sub_project_task;
}

/**
 * Author:  CC
 * Date:    2013-05-24
 * Last Modified:   2013-07-01
 * Modified:    09/09/2013  CC
 * Remove non-workday bar
 * [task_effort_ajax Used in ajax task effort page]
 * @param  [type] $father_id [description]
 * @param  [type] $sub_org   [description]
 * @param  [type] $date_from [description]
 * @param  [type] $date_to   [description]
 * @return [type]            [description]
 */
function task_effort_ajax($projects, $date_from, $date_to) {
    $j = 0;
    $k = 0;
    /**
     * Calculate the gap between two dates
     */
    $date_gap_temp = strtotime($date_to) - strtotime($date_from);
    $date_gap      = ($date_gap_temp / 86400) + 1;

    $general_sub_project_task            = array();
    $direct_sub_project_string           = op_string($projects);
    $list_finished_project_task_withdate = list_finished_project_task_withdate($direct_sub_project_string, $date_from, $date_to);
    $ds_op_md                            = ds_op_md($direct_sub_project_string,$date_from, $date_to, "PROJECT");
    
    /**
     * [$this_week_day Minus one day]
     * @var [date]
     * This is used for the following loop. Once we minus one day, the loop can calculate ahc from Monday as expected 
     */
    $this_week_day = date("Y-m-d",strtotime($date_from." -1 day"));
    
    /**
     * [$bug_info_withdate Bug info within a date scope]
     * @var [array]
     */
    $bug_note_withdate = bugs_of_org_external($direct_sub_project_string, $date_from, $date_to);

    for($j=0;$j<$date_gap;$j++){
        $this_week_day         = date("Y-m-d",strtotime($this_week_day." +1 day"));
        $this_week_day_month   = date("m/d",strtotime($this_week_day));
        $this_week_day_explode = explode("-",$this_week_day);
        if(isset($ds_op_md[$this_week_day_explode[0]][$this_week_day_explode[1]][$this_week_day_explode[2]])){
            foreach($ds_op_md[$this_week_day_explode[0]][$this_week_day_explode[1]][$this_week_day_explode[2]] as $doat_key => $doat_val){
                $ds_project_md_by_project_this[$doat_key][$this_week_day_month] = $doat_val['MD'];
                $ds_project_wd_by_project_this[$doat_key][$this_week_day_month] = $doat_val['WD'];
            }
        }
    }

    foreach ($projects as $p_key => $p_val) {
        $sub_project_task          = array();
        $sub_project_wmd           = array();   //Each project's daily md or wd
        $sub_project_task_count    = array();
        $sub_project_task_temp     = array();
        $finished_sub_project_task = array();
        $total_sub_project_task    = array();
        $finished_sub_project_task_per_day          = array();
        $finished_sub_project_task_per_engineer_day = array();
        $this_week_day_number_name                  = date("Y-m-d",strtotime($date_from." -1 day"));

        $sub_project_task_temp['FINISHED']         = isset($list_finished_project_task_withdate[$p_val['ID']]) ? $list_finished_project_task_withdate[$p_val['ID']] : array();
        $sub_project_task_temp['BUG']              = isset($bug_note_withdate[$p_val['ID']]) ? $bug_note_withdate[$p_val['ID']] : array();
        $sub_project_task_temp['MD']['DATE_SCOPE'] = isset($ds_project_md_by_project_this[$p_val['ID']]) ? $ds_project_md_by_project_this[$p_val['ID']]: array();
        $sub_project_task_temp['WD']['DATE_SCOPE'] = isset($ds_project_wd_by_project_this[$p_val['ID']]) ? $ds_project_wd_by_project_this[$p_val['ID']]: array();
        /**
         * [$sub_project_task_temp['FINISHED'] Split task execution record into different date scope]
         * @var [type]
         */
        foreach ($sub_project_task_temp['FINISHED'] as $sptt_key => $sptt_val) {
            $sub_project_task['FINISHED']['DATE_SCOPE']['GENERAL'][] = $sptt_val;
            $finished_date_name = date("m/d",strtotime($sptt_val['FINISH_DATE']));
            $sub_project_task['FINISHED']['DATE_SCOPE']['DETAILED'][$finished_date_name][] = $sptt_val;
        }

        /**
         * [$sub_project_task_temp['BUG'] Split bugrecord into different date scope]
         * @var [type]
         */
        foreach ($sub_project_task_temp['BUG'] as $spttb_key => $spttb_val) {
            $sub_project_task['BUG']['DATE_SCOPE']['GENERAL'][] = $spttb_val;
            $finished_date_name = date("m/d",strtotime($spttb_val['SUBMIT_DATE']));
            $sub_project_task['BUG']['DATE_SCOPE']['DETAILED'][$finished_date_name][] = $spttb_val;
        }

        /**
         * [$sub_project_task_temp['MD'] Calculate each date scope's MD]
         * @var [type]
         */
        foreach ($sub_project_task_temp['MD'] as $spttm_key => $spttm_val) {
            $sub_project_task['MD'][$spttm_key] = array_sum($spttm_val);
            foreach ($spttm_val as $svm_key => $svm_val) {
                $sub_project_wmd['MD'][$svm_key] = $svm_val;
            }
        }
            
        /**
         * [$sub_project_task_temp['WD'] Calculate each date scope's WD]
         * @var [type]
         */
        foreach ($sub_project_task_temp['WD'] as $spttw_key => $spttw_val) {
            $sub_project_task['WD'][$spttw_key] = array_sum($spttw_val);;
            foreach ($spttw_val as $svw_key => $svw_val) {
                $sub_project_wmd['WD'][$svw_key] = $svw_val;
            }
        }
        
        $sub_project_task_count['FINISHED']['DATE_SCOPE']  = isset($sub_project_task['FINISHED']['DATE_SCOPE']['GENERAL']) ? count($sub_project_task['FINISHED']['DATE_SCOPE']['GENERAL']) : 0;
            
        /**
        * @Description: Calculte task effort finished this week
        */
        if (isset($sub_project_task['FINISHED']['DATE_SCOPE'])) {
            if($_SESSION['type'] == 4){
                $finished_sub_project_task['DATE_SCOPE']['GENERAL'] = caculate_effort($sub_project_task['FINISHED']['DATE_SCOPE']['GENERAL'],isset($sub_project_task['BUG']['DATE_SCOPE']['GENERAL'])?$sub_project_task['BUG']['DATE_SCOPE']['GENERAL']:array(),1);
            }else{
                $finished_sub_project_task['DATE_SCOPE']['GENERAL'] = caculate_effort($sub_project_task['FINISHED']['DATE_SCOPE']['GENERAL'],isset($sub_project_task['BUG']['DATE_SCOPE']['GENERAL'])?$sub_project_task['BUG']['DATE_SCOPE']['GENERAL']:array(),0);
            }
                
            $finished_sub_project_task_per_day['DATE_SCOPE']          = ($sub_project_task['WD']['DATE_SCOPE'] == 0)?0:round(($finished_sub_project_task['DATE_SCOPE']['GENERAL']['total_effort']/$sub_project_task['WD']['DATE_SCOPE']),2);
            $finished_sub_project_task_per_engineer_day['DATE_SCOPE'] = ($sub_project_task['MD']['DATE_SCOPE'] == 0)?0:round(($finished_sub_project_task['DATE_SCOPE']['GENERAL']['total_effort']/$sub_project_task['MD']['DATE_SCOPE']),2);
            
            foreach($sub_project_task['FINISHED']['DATE_SCOPE']['DETAILED'] as $svv_key => $svv_val){
                if($_SESSION['type'] == 4){
                    $finished_sub_project_task['DATE_SCOPE']['DETAILED'][$svv_key] = caculate_effort($svv_val,isset($sub_project_task['BUG']['DATE_SCOPE']['DETAILED'][$svv_key])?$sub_project_task['BUG']['DATE_SCOPE']['DETAILED'][$svv_key]:array(),1);
                }else{
                    $finished_sub_project_task['DATE_SCOPE']['DETAILED'][$svv_key] = caculate_effort($svv_val,isset($sub_project_task['BUG']['DATE_SCOPE']['DETAILED'][$svv_key])?$sub_project_task['BUG']['DATE_SCOPE']['DETAILED'][$svv_key]:array(),0);
                }
            }
        } else {
            $finished_sub_project_task['DATE_SCOPE']['GENERAL'] = array(
                'ex_setup_time'       => 0,
                'ex_execution_time'   => 0,
                'ac_setup_time'       => 0,
                'ac_execution_time'   => 0,
                'investigate_time'    => 0,
                'bug_time'            => 0,
                'expected_total_time' => 0,
                'actual_total_time'   => 0,
                'total_effort'        => 0
            );
            
            $finished_sub_project_task_per_day['DATE_SCOPE']          = 0;
            $finished_sub_project_task_per_engineer_day['DATE_SCOPE'] = 0;
            $finished_sub_project_task['DATE_SCOPE']['DETAILED']      = array();
        }

        for($k = 0; $k < $date_gap; $k++){
            $this_week_day_number_name = date("Y-m-d",strtotime($this_week_day_number_name." +1 day"));
            $this_week_day             = date("m/d",strtotime($this_week_day_number_name));
            if(isset($finished_sub_project_task['DATE_SCOPE']['DETAILED'][$this_week_day])){
                $finished_sub_project_task['DATE_SCOPE']['DETAILED'][$this_week_day] = $finished_sub_project_task['DATE_SCOPE']['DETAILED'][$this_week_day];
                $sub_project_task_count['MD']['DATE_SCOPE'][$this_week_day]          = isset($sub_project_wmd['MD'][$this_week_day])?$sub_project_wmd['MD'][$this_week_day]:0;
            }
            //$finished_sub_project_task['DATE_SCOPE']['DETAILED'][$this_week_day] = (isset($finished_sub_project_task['DATE_SCOPE']['DETAILED'][$this_week_day]))?$finished_sub_project_task['DATE_SCOPE']['DETAILED'][$this_week_day]:$empty_finished_sub_project_week_task;
        }
        ksort($finished_sub_project_task['DATE_SCOPE']['DETAILED']);

        foreach($finished_sub_project_task['DATE_SCOPE']['DETAILED'] as $fsot_key => $fsot_val){
            if(!isset($sub_project_task_count['MD']['DATE_SCOPE'][$fsot_key])){
                $sub_project_task_count['MD']['DATE_SCOPE'][$fsot_key] = 0;
            }
        }
        ksort(isset($sub_project_task_count['MD']['DATE_SCOPE'])?$sub_project_task_count['MD']['DATE_SCOPE']:array());

        $general_sub_project_task[$p_key]['PROJECT_NAME']            = $p_val['NAME'];
        $general_sub_project_task[$p_key]['COUNT']                   = $sub_project_task_count;
        $general_sub_project_task[$p_key]['TIME_EFFORT']['FINISHED'] = $finished_sub_project_task;  
    }
    return $general_sub_project_task;
}

/**
 * [check_working_day Check whether a day is workday, if not, contunue to loop]
 * @param  [date] $day [the target date]
 * @return [date]      [The next work-day of the target date]
 */
function check_working_day($day){
    $next_day = date("Y-m-d",strtotime($day." +1 day"));
    if(getWorkingDays($next_day, $next_day) == 0){
        return check_working_day($next_day);
    }else{
        return $next_day;
    }
}

function next_workday($day){
    $day = date("Y-m-d",strtotime($day." +1 day"));
    $work_day = $day;
    if(getWorkingDays($work_day, $work_day) == 0){
        return check_working_day($work_day);
    }else{
        return $work_day;
    }
}

function details_for_unfinished_task($left_days, $task_left_last_day, $this_week_day_number_name, $finished_sub_org_task,$finished_sub_org_task_per_day) {
    if (($left_days == 0) && ($task_left_last_day != 0)) {
        $this_week_day_number_name = next_workday($this_week_day_number_name);
        $this_week_month_day = date("m/d", strtotime($this_week_day_number_name));
        $finished_sub_org_task['THIS_WEEK']['DETAILED'][$this_week_month_day] = array(
            'ex_setup_time'       => NULL,
            'ex_execution_time'   => NULL,
            'ac_setup_time'       => NULL,
            'ac_execution_time'   => NULL,
            'investigate_time'    => NULL,
            'expected_total_time' => $task_left_last_day,
            'actual_total_time'   => NULL
        );
    }

    if ($left_days != 0) {
        for ($m = 0; $m < $left_days; $m++) {
            $this_week_day_number_name = next_workday($this_week_day_number_name);
            $this_week_month_day = date("m/d", strtotime($this_week_day_number_name));
            $finished_sub_org_task['THIS_WEEK']['DETAILED'][$this_week_month_day] = array(
                'ex_setup_time'       => NULL,
                'ex_execution_time'   => NULL,
                'ac_setup_time'       => NULL,
                'ac_execution_time'   => NULL,
                'investigate_time'    => NULL,
                'expected_total_time' => $finished_sub_org_task_per_day,
                'actual_total_time'   => NULL
            );
        }
        if ($task_left_last_day != 0) {
            $this_week_day_number_name = next_workday($this_week_day_number_name);
            $this_week_month_day = date("m/d", strtotime($this_week_day_number_name));
            $finished_sub_org_task['THIS_WEEK']['DETAILED'][$this_week_month_day] = array(
                'ex_setup_time'       => NULL,
                'ex_execution_time'   => NULL,
                'ac_setup_time'       => NULL,
                'ac_execution_time'   => NULL,
                'investigate_time'    => NULL,
                'expected_total_time' => $task_left_last_day,
                'actual_total_time'   => NULL
            );
        }
    }
    return $finished_sub_org_task;
}

/**
 * Re-arrange the returned array
 * Orginal:
 *  Array
 * (
 * [2012-January] => Array
 * (
 * [72] => Array
 * (
 * [total_org_ex_setup_time] => 0
 * [total_org_ex_execution_time] => 0
 * [total_org_ac_setup_time] => 0
 * [total_org_ac_execution_time] => 0
 * [total_org_investigate_time] => 0
 * [total_org_total_time] => 0
 * )
 * )
 * New:
 * Array
 * (
 * [72] => Array
 * (
 * [2012-January] => Array
 * (
 * [total_org_ex_setup_time] => 0
 * [total_org_ex_execution_time] => 0
 * [total_org_ac_setup_time] => 0
 * [total_org_ac_execution_time] => 0
 * [total_org_investigate_time] => 0
 * [total_org_total_time] => 0
 * )
 * )
 */

function rearrange_org_time($org_time) {
    foreach ($org_time as $ot_key => $ot_val) {
        foreach ($ot_val as $ov_key => $ov_val) {
            $org_time_by_orgid[$ov_key][$ot_key] = $ov_val;
        }
    }
    return $org_time_by_orgid;
}

/**
 * Author: CC
 * Date: 2013-02-18
 * Purpose: Bug status of each project
 * Last Modified: 08/09/2013 CC
 * 09/02/2013   CC
 * move $general_sub_project_bug from top loop under loop of $projects
 */
function bug_status($projects, $date_from, $date_to) {
    $general_project_bug = array();
    $project_bug         = project_bug_withdate($projects, $date_from, $date_to);
    foreach ($project_bug as $pb_key => $pb_val) {
        $list_project_bug_withdate[$pb_val['FK_PROJECT_ID']][] = $pb_val;
    }

    foreach ($projects as $p_key => $p_val) {
        $sub_project_bug         = array();
        $sub_project_bug_temp    = array();
        $general_sub_project_bug = array();
        $query_duration          = 0;
        $query_life              = 0;
        $bug_duration            = 0;
        $bug_life                = 0;
        $max_query_life          = 0;
        $min_query_life          = INF;  //set $min_query_life to infinite
        $max_bug_life            = 0;
        $min_bug_life            = INF;  //set $min_bug_life to infinite

        $sub_project_bug_temp = isset($list_project_bug_withdate[$p_key]) ? $list_project_bug_withdate[$p_key] : array();
        
        /*
         * General bug status
         * 1. By different bug system   e.g. OneBug, Mantis, Local Query
         */
        foreach ($sub_project_bug_temp as $sobt_key => $spbt_val) {
            if (isset($spbt_val['BUG_SYSTEM'])) {
                $sub_project_bug[$GLOBALS['BUG'][$spbt_val['BUG_SYSTEM']]][] = $spbt_val;
            }
        }
        
        foreach ($sub_project_bug as $spb_key => $spb_val) {
            foreach ($spb_val as $svv_key => $svv_val) {
                /**
                 * Bug and Query
                 */
                $general_sub_project_bug['ORIGINAL_DATA'][$spb_key][] = $svv_val;
                /**
                 * Query To Bug
                 */
                if (($svv_val['RELATED_BUG'] != NULL || $svv_val['RELATED_BUG'] != "") && $svv_val['BUG_SYSTEM'] == 4) {
                    $general_sub_project_bug['ORIGINAL_DATA']['queryTObug'][] = $svv_val;
                }
                /**
                 * Valid Bug
                 */
                if (isset($GLOBALS['VALID_BUG'][$svv_val['SUB_STATUS']]) && $svv_val['BUG_SYSTEM'] != 4) {
                    $general_sub_project_bug['ORIGINAL_DATA']['VALID_BUG'][] = $svv_val;
                }
                /**
                 * Invalid Bug
                 */
                if (!isset($GLOBALS['VALID_BUG'][$svv_val['SUB_STATUS']]) && $svv_val['BUG_SYSTEM'] != 4 && $svv_val['SUB_STATUS'] != 0) {
                    $general_sub_project_bug['ORIGINAL_DATA']['INVALID_BUG'][] = $svv_val;
                }
                /**
                 * Query life cycle
                 */
                if ($svv_val['BUG_SYSTEM'] == 4) {
                    if (($svv_val['BUG_STATUS'] == 5) && ($svv_val['LAST_MODIFIED'] != NULL)) {
                        $query_duration = strtotime($svv_val['LAST_MODIFIED']) - strtotime($svv_val['SUBMIT_DATE']);
                    } else {
                        $query_duration = strtotime(date('Y-m-d H:i:s')) - strtotime($svv_val['SUBMIT_DATE']);
                    }
                    if ($query_duration > $max_query_life) {
                        $max_query_life = $query_duration;
                        $general_sub_project_bug['ORIGINAL_DATA']['MAX_QUERY_LIFE'] = $svv_val;
                    }
                    if ($query_duration < $min_query_life) {
                        $min_query_life = $query_duration;
                        $general_sub_project_bug['ORIGINAL_DATA']['MIN_QUERY_LIFE'] = $svv_val;
                    }
                    $query_life += $query_duration;
                } else { //Bug life cycle
                    if (($svv_val['BUG_STATUS'] == 5) && ($svv_val['LAST_MODIFIED'] != NULL)) {
                        $bug_duration = strtotime($svv_val['LAST_MODIFIED']) - strtotime($svv_val['SUBMIT_DATE']);
                    } else {
                        $bug_duration = strtotime(date('Y-m-d H:i:s')) - strtotime($svv_val['SUBMIT_DATE']);
                    }
                    if ($bug_duration > $max_bug_life) {
                        $max_bug_life = $bug_duration;
                        $general_sub_project_bug['ORIGINAL_DATA']['MAX_BUG_LIFE'] = $svv_val;
                    }
                    if ($bug_duration < $min_bug_life) {
                        $min_bug_life = $bug_duration;
                        $general_sub_project_bug['ORIGINAL_DATA']['MIN_BUG_LIFE'] = $svv_val;
                    }
                    $bug_life += $bug_duration;
                }
            }
        }

        $general_project_bug[$p_val['ID']]['NAME']                 = $p_val['NAME'];
        $general_project_bug[$p_val['ID']]['COUNT']['BUG']         = isset($general_sub_project_bug['ORIGINAL_DATA']['BUG']) ? count($general_sub_project_bug['ORIGINAL_DATA']['BUG']) : 0;
        $general_project_bug[$p_val['ID']]['COUNT']['QUERY']       = isset($general_sub_project_bug['ORIGINAL_DATA']['QUERY']) ? count($general_sub_project_bug['ORIGINAL_DATA']['QUERY']) : 0;
        $general_project_bug[$p_val['ID']]['COUNT']['VALID_BUG']   = isset($general_sub_project_bug['ORIGINAL_DATA']['VALID_BUG']) ? count($general_sub_project_bug['ORIGINAL_DATA']['VALID_BUG']) : 0;
        $general_project_bug[$p_val['ID']]['COUNT']['INVALID_BUG'] = isset($general_sub_project_bug['ORIGINAL_DATA']['INVALID_BUG']) ? count($general_sub_project_bug['ORIGINAL_DATA']['INVALID_BUG']) : 0;
        $general_project_bug[$p_val['ID']]['COUNT']['queryTObug']  = isset($general_sub_project_bug['ORIGINAL_DATA']['queryTObug']) ? count($general_sub_project_bug['ORIGINAL_DATA']['queryTObug']) : 0;
        

        /**
         * Query Life Cycle
         */
        if ($general_project_bug[$p_val['ID']]['COUNT']['QUERY'] != 0) {
            $general_project_bug[$p_val['ID']]['ORIGINAL_DATA']['MAX_QUERY_LIFE'] = $general_sub_project_bug['ORIGINAL_DATA']['MAX_QUERY_LIFE'];
            $general_project_bug[$p_val['ID']]['ORIGINAL_DATA']['MIN_QUERY_LIFE'] = $general_sub_project_bug['ORIGINAL_DATA']['MIN_QUERY_LIFE'];
            $general_project_bug[$p_val['ID']]['LIFE']['QUERY']['AVERAGE'] = round($query_life / (60 * 60 * $general_project_bug[$p_val['ID']]['COUNT']['QUERY']), 1);
            $general_project_bug[$p_val['ID']]['LIFE']['QUERY']['MAX']     = round($max_query_life / (60 * 60), 1);
            $general_project_bug[$p_val['ID']]['LIFE']['QUERY']['MIN']     = round($min_query_life / (60 * 60), 1);
        } else {
            $general_project_bug[$p_val['ID']]['ORIGINAL_DATA']['MAX_QUERY_LIFE'] = array();
            $general_project_bug[$p_val['ID']]['ORIGINAL_DATA']['MIN_QUERY_LIFE'] = array();
            $general_project_bug[$p_val['ID']]['LIFE']['QUERY']['AVERAGE'] = 0;
            $general_project_bug[$p_val['ID']]['LIFE']['QUERY']['MAX']     = 0;
            $general_project_bug[$p_val['ID']]['LIFE']['QUERY']['MIN']     = 0;
        }
        /**
         * Bug Life Cycle
         */
        if ($general_project_bug[$p_val['ID']]['COUNT']['BUG'] != 0) {
            $general_project_bug[$p_val['ID']]['ORIGINAL_DATA']['MAX_BUG_LIFE'] = $general_sub_project_bug['ORIGINAL_DATA']['MAX_BUG_LIFE'];
            $general_project_bug[$p_val['ID']]['ORIGINAL_DATA']['MIN_BUG_LIFE'] = $general_sub_project_bug['ORIGINAL_DATA']['MIN_BUG_LIFE'];
            $general_project_bug[$p_val['ID']]['LIFE']['BUG']['AVERAGE'] = round($bug_life / (60 * 60 * $general_project_bug[$p_val['ID']]['COUNT']['BUG']), 1);
            $general_project_bug[$p_val['ID']]['LIFE']['BUG']['MAX']     = round($max_bug_life / (60 * 60), 1);
            $general_project_bug[$p_val['ID']]['LIFE']['BUG']['MIN']     = round($min_bug_life / (60 * 60), 1);
        } else {
            $general_project_bug[$p_val['ID']]['ORIGINAL_DATA']['MAX_BUG_LIFE'] = array();
            $general_project_bug[$p_val['ID']]['ORIGINAL_DATA']['MIN_BUG_LIFE'] = array();
            $general_project_bug[$p_val['ID']]['LIFE']['BUG']['AVERAGE'] = 0;
            $general_project_bug[$p_val['ID']]['LIFE']['BUG']['MAX']     = 0;
            $general_project_bug[$p_val['ID']]['LIFE']['BUG']['MIN']     = 0;
        }
    }

    return $general_project_bug;
}

/* search group info
 */

function all_group_info($oid) {
    $group_info = "SELECT distinct * FROM group_info WHERE FK_ORG_ID in ($oid);";
    $rst_group_info = $GLOBALS['db']->query($group_info);
    $all_group_info = $rst_group_info->fetch_all(MYSQLI_ASSOC);
    return $all_group_info;
}

/* search org owner name */

function org_owner_name($oid, $current_date) {
    $qry_owner_name = "SELECT distinct USER_NAME,ORG_NAME,OWNER_ID,ORG_ID,EXP_END FROM org_info,user_info WHERE org_info.ORG_ID='$oid' AND org_info.OWNER_ID=user_info.USER_ID AND EXP_END>'$current_date'";
    $rst_owner_name = $GLOBALS['db']->query($qry_owner_name);
    $sub_owner_name = $rst_owner_name->fetch_assoc();
    return $sub_owner_name;
}

/* search current org people */

function current_org_info($oid, $current_month) {
    $qry_current_org_info = "SELECT ui.USER_NAME,ui.USER_ID,ui.INTERN_START,ui.EMPLOYEE_START,oi.EXP_END,ui.LEVEL,ui.EMPLOYEE_END,oi.ORG_ID,oi.ORG_NAME,oi.ORG_TYPE,oi.OWNER_ID,oi.EXP_START,oi.EXP_END,oi.ACT_START,oi.ACT_END,oi.STATUS,oi.COMMENT 
FROM org_info AS oi,user_info AS ui WHERE FATHER='$oid' AND oi.OWNER_ID=ui.USER_ID AND (ui.EMPLOYEE_END>'$current_month' OR ui.EMPLOYEE_END IS NULL) AND oi.EXP_END>'$current_month';";
    $rst_current_org_info = $GLOBALS['db']->query($qry_current_org_info);
    $sub_current_org_info = $rst_current_org_info->fetch_all(MYSQLI_ASSOC);
    return $sub_current_org_info;
}

/* search org all people */

function org_all_people($oid, $current_month) {
    $qry_all_people = "SELECT DISTINCT user_info.USER_NAME ,user_info.USER_ID,user_info.INTERN_START,user_info.EMPLOYEE_START,user_info.EMPLOYEE_END,org_info.EXP_END FROM (org_info JOIN user_org_group ON org_info.ORG_ID=
user_org_group.FK_ORG_ID)JOIN user_info ON user_org_group.FK_USER_ID=user_info.USER_ID WHERE user_org_group.FK_ORG_ID IN($oid)AND (user_info.EMPLOYEE_END >'$current_month' OR EMPLOYEE_END IS NULL) AND 
    org_info.EXP_END>'$current_month'";
    $rst_all_people = $GLOBALS['db']->query($qry_all_people);
    $sub_all_people = $rst_all_people->fetch_all(MYSQLI_ASSOC);
    return $sub_all_people;
}

/*
 * search current month attrition people
 */

function current_attrition_statistics($current_month) {
    $qry_current_attrition_statistics = "SELECT FK_USER_ID,USER_NAME,TYPE,RESIGN_DATE,LAST_ORG,REASON,LEVEL FROM resign_info, user_info where user_info.USER_ID=resign_info.FK_USER_ID AND (RESIGN_DATE BETWEEN '$current_month-01' AND '$current_month-31');";
    $rst_current_attrition_statistics = $GLOBALS['db']->query($qry_current_attrition_statistics);
    $sub_current_attrition_statistics = $rst_current_attrition_statistics->fetch_all(MYSQLI_ASSOC);
    return $sub_current_attrition_statistics;
}

/* search attrition people */

function attrition_statistics($current_year, $current_year_month) {
    $qry_attrition_statistics = "SELECT FK_USER_ID,USER_NAME,RESIGN_DATE,LAST_ORG,REASON,LEVEL FROM resign_info, user_info where user_info.USER_ID=resign_info.FK_USER_ID AND (RESIGN_DATE BETWEEN '$current_year-01-01' AND '$current_year_month-31');";
    $rst_attrition_statistics = $GLOBALS['db']->query($qry_attrition_statistics);
    $sub_attrition_statistics = $rst_attrition_statistics->fetch_all(MYSQLI_ASSOC);
    return $sub_attrition_statistics;
}

/*
 * search current entry info
 */

function current_entry($current_year_month) {
    $qry_current_entry = "SELECT USER_ID,USER_NAME,EMPLOYEE_START,FK_ORG_ID ,FK_USER_ID FROM user_info,user_org_group WHERE FK_USER_ID=USER_ID AND EMPLOYEE_START BETWEEN '$current_year_month-01' AND '$current_year_month-31'";
    $rst_current_entry = $GLOBALS['db']->query($qry_current_entry);
    $sub_current_entry = $rst_current_entry->fetch_all(MYSQLI_ASSOC);
    return $sub_current_entry;
}

/* search  all people count */

function all_people_count($current_oid, $current_date) {
    $level_count = array();
    $intern = array();
    $fte = array();
    $borrow = array();
    $count = array();
    $qry_all_org = all_childs_orgs_rs($current_oid, $current_date);
    $qry_all_org[] = $current_oid;
    $oid_string_temp = null;
    foreach ($qry_all_org as $aco) {
        $oid_string_temp = $oid_string_temp . "'" . $aco . "',";
    }
    $qry_all_org = substr($oid_string_temp, 0, -1);
    $qry_group_info = all_group_info($qry_all_org);
    foreach ($qry_group_info as $gi_key => $gi_val) {
        if ($gi_val['GRP_TYPE'] != 5 && $gi_val['GRP_TYPE'] != 9) {
            $new_group_info[] = $gi_val;
        }
    }
    foreach ($new_group_info as $gi_key => $gi_val) {

        $all_people_name = all_gi_name($gi_val['FK_ORG_ID'], $gi_val['GRP_ID'], $current_date);
        foreach ($all_people_name as $apm_key => $apm_val) {
            $level_count[$apm_val["USER_ID"]] = $apm_val["LEVEL"];
            if ($apm_val['EMPLOYEE_TYPE'] == 0) {
                if ($apm_val['INTERN_END'] >= $current_date) {
                    $intern[$apm_val["USER_ID"]] = $apm_val;
                } else if ($apm_val['INTERN_END'] < $current_date) {
                    $fte[$apm_val["USER_ID"]] = $apm_val;
                }
            } else if ($apm_val['EMPLOYEE_TYPE'] == 1) {
                $fte[$apm_val["USER_ID"]] = $apm_val;
            } else if ($apm_val['EMPLOYEE_TYPE'] == 2) {
                $borrow[$apm_val["USER_ID"]] = $apm_val;
            }
        }
        foreach ($all_people_name as $quit_key => $quit_val) {
            $quit = array();
            if ($quit_val['EMPLOYEE_END'] != NULL)
                $quit[] = $quit_val;
        }
    }
    $count[] = array('intern' => $intern, 'fte' => $fte, 'borrow' => $borrow, 'quit' => $quit, 'level_count' => $level_count);
    return $count;
}

/* search current org or child org member name
 */

function aside_people_name($current_oid_info, $oid, $current_month) {
    $current_owner = array();
    $people_name = array();
    $child_people_name = array();
    $current_people_name = array();
    $current_people_name_t = array();
    if ($current_oid_info != null) {
        $qry_group_info = all_group_info($oid);
        foreach ($qry_group_info as $gi_key => $gi_val) {
            if ($gi_val['GRP_TYPE'] != 5 && $gi_val['GRP_TYPE'] != 9) {
                $new_group_info[] = $gi_val;
            }
        }
        foreach ($new_group_info as $new_gi_key => $new_gi_val) {
            $get_new_group_name = all_gi_name($new_gi_val['FK_ORG_ID'], $new_gi_val['GRP_ID'], $current_month);
            foreach ($get_new_group_name as $gnt_key => $gnt_val) {
                $current_people_name_t[$oid][] = array(
                    'INTERN_START' => $gnt_val['INTERN_START'],
                    'EMPLOYEE_START' => $gnt_val['EMPLOYEE_START'],
                    'USER_ID' => $gnt_val['USER_ID'],
                    'USER_NAME' => $gnt_val['USER_NAME']);
            }
        }
        foreach ($current_oid_info as $oi_key => $oi_val) {

            $new_group_info = ARRAY();
            $current_owner[$oi_val['ORG_ID']][] = array(
                'USER_ID' => $oi_val['USER_ID'],
                'USER_NAME' => $oi_val['USER_NAME'],
                'INTERN_START' => $oi_val['INTERN_START'],
                'EMPLOYEE_START' => $oi_val['EMPLOYEE_START']);
            $qry_all_org = all_childs_orgs_rs($oi_val['ORG_ID'], $current_month);
            $oid_string_temp = null;
            foreach ($qry_all_org as $aco) {
                $oid_string_temp = $oid_string_temp . "'" . $aco . "',";
            }
            $qry_all_org = substr($oid_string_temp, 0, -1);
            $qry_group_info = all_group_info($qry_all_org);
            foreach ($qry_group_info as $gi_key => $gi_val) {
                if ($gi_val['GRP_TYPE'] != 5 && $gi_val['GRP_TYPE'] != 9) {
                    $new_group_info[] = $gi_val;
                }
            }
            foreach ($new_group_info as $new_gi_key => $new_gi_val) {
                $get_new_group_name = all_gi_name($new_gi_val['FK_ORG_ID'], $new_gi_val['GRP_ID'], $current_month);
                foreach ($get_new_group_name as $gn_key => $gn_val) {
                    $child_people_name[$oi_val['ORG_ID']][] = array(
                        'INTERN_START' => $gn_val['INTERN_START'],
                        'EMPLOYEE_START' => $gn_val['EMPLOYEE_START'],
                        'USER_ID' => $gn_val['USER_ID'],
                        'USER_NAME' => $gn_val['USER_NAME']);
                }
            }
        }
    } else {
        $qry_group_info = all_group_info($oid);
        foreach ($qry_group_info as $gi_key => $gi_val) {
            if ($gi_val['GRP_TYPE'] != 5 && $gi_val['GRP_TYPE'] != 9) {
                $new_group_info[] = $gi_val;
            }
        }
        foreach ($new_group_info as $new_gi_key => $new_gi_val) {
            $get_new_group_name = all_gi_name($new_gi_val['FK_ORG_ID'], $new_gi_val['GRP_ID'], $current_month);
            foreach ($get_new_group_name as $gn_key => $gn_val) {
                $current_people_name[$oid][] = array(
                    'INTERN_START' => $gn_val['INTERN_START'],
                    'EMPLOYEE_START' => $gn_val['EMPLOYEE_START'],
                    'USER_ID' => $gn_val['USER_ID'],
                    'USER_NAME' => $gn_val['USER_NAME']);
            }
        }
    }
    $people_name [] = array('current_people_name' => $current_people_name, 'child_people_name' => $child_people_name,
        'current_name' => $current_owner, 'current_people_name_t' => $current_people_name_t);
    return $people_name;
}

/* search resource status workong age
 */

function employee_hisoft_experience($current_org_info, $oid, $current_month) {
    $hi_experience = array();
    foreach ($current_org_info as $coi_key => $coi_val) {
        $one_year = array();
        $two_years = array();
        $three_years = array();
        $four_years = array();
        $five_years_or_above = array();
        $working_age = array();
        $new_group_info = array();
        $org_id = $coi_val["ORG_ID"] . ",";
        $qry_all_org = all_childs_orgs_rs($org_id, $current_month);
        $oid_string_temp = null;
        foreach ($qry_all_org as $aco) {
            $oid_string_temp = $oid_string_temp . "'" . $aco . "',";
        }
        $qry_all_org = substr($oid_string_temp, 0, -1);
        $qry_group_info = all_group_info($qry_all_org);

        foreach ($qry_group_info as $gi_key => $gi_val) {
            if ($gi_val['GRP_TYPE'] != 5 && $gi_val['GRP_TYPE'] != 9) {
                $new_group_info[] = $gi_val;
            }
        }

        foreach ($new_group_info as $ngi_key => $ngi_val) {
            $all_people_name = all_gi_name($ngi_val['FK_ORG_ID'], $ngi_val['GRP_ID'], $current_month);
            foreach ($all_people_name as $apm_key => $apm_val) {
                if ($apm_val['INTERN_START'] != NULL) {
                    $working_age[$coi_val["ORG_NAME"]][] = (strtotime($current_month) - strtotime($apm_val['INTERN_START'])) / 3600 / 24 / 365;
                } else {
                    $working_age[$coi_val["ORG_NAME"]][] = (strtotime($current_month) - strtotime($apm_val['EMPLOYEE_START'])) / 3600 / 24 / 365;
                }
            }
        }
        foreach ($working_age as $wa_key => $wa_val) {
            foreach ($wa_val as $wv_key => $wv_val) {
                if (floor($wv_val) == 1) {
                    $hi_experience[$coi_val["ORG_ID"]]['one_year'][] = $wv_val;
                } else if (floor($wv_val) == 2) {
                    $hi_experience[$coi_val["ORG_ID"]]['two_years'][] = $wv_val;
                } else if (floor($wv_val) == 3) {
                    $hi_experience[$coi_val["ORG_ID"]]['three_years'][] = $wv_val;
                } else if (floor($wv_val) == 4) {
                    $hi_experience[$coi_val["ORG_ID"]]['four_years'][] = $wv_val;
                } else if (floor($wv_val) >= 5) {
                    $hi_experience[$coi_val["ORG_ID"]]['five_years_or_above'][] = $wv_val;
                }
            }
        }
    }
    return $hi_experience;
}

/*
 * count attrition info
 */

function count_attrition($current_org_info, $current_year, $current_year_month) {
    $existed_org = array();
    $qry_all_org = array();
    $all_org = array();
    $str_month = substr($current_year_month, 5, 7);
    $int_month = (int) $str_month;
    $current_year_info = array();
    foreach ($current_org_info as $coi_key => $coi_val) {
        $org_id = $coi_val["ORG_ID"] . ",";
        $qry_all_org = all_childs_orgs_rs($org_id, $current_year);
        $qry_all_org[] = $coi_val["ORG_ID"];
        $all_org [] = $qry_all_org;
    }
    foreach ($all_org as $ao_key => $ao_val) {
        foreach ($ao_val as $av_key => $av_val) {
            $all_org_to_one_array[] = $av_val;
        }
    }
    $attrition_statistics = attrition_statistics($current_year, $current_year_month);
    foreach ($attrition_statistics as $as_key => $as_val) {
        if (in_array($as_val["LAST_ORG"], $all_org_to_one_array)) {
            $year_month = substr($as_val["RESIGN_DATE"], 0, 7);
            //$dure_org[] = $as_val;
            $existed_org[$year_month][] = $as_val;
        }
    }

    return $existed_org;
}

/*
 * count arrtition people level
 */

function arrtition_people_level($current_org_info, $current_year, $current_year_month) {
    $each_people_level_temp = array();
    $count_attriton = count_attrition($current_org_info, $current_year, $current_year_month);
    foreach ($count_attriton as $ca_key => $ca_val) {
        foreach ($ca_val as $cv_key => $cv_val) {
            $each_people_level[] = $cv_val['LEVEL'];
        }
    }
    $level_count_info = array_count_values($each_people_level);
    foreach ($level_count_info as $lci_key => $lci_val) {
        $lci_key = substr($lci_key, 1, 2);
        $new_lci_val[$lci_key] = $lci_val;
    }
    krsort($new_lci_val);
    foreach ($new_lci_val as $nlv_key => $nlv_val) {
        if ($nlv_key > 3) {
            $nlv_key = "L" . $nlv_key;
        } else {
            $nlv_key = "I" . $nlv_key;
        }
        $final_lci_val[$nlv_key] = $nlv_val;
    }
    return $final_lci_val;
}

/*
 * entry info and count 
 */
function entry_count($oid, $current_year_month) {
    $new_search_entry = array();
    $qry_all_org = all_childs_orgs_rs($oid, $current_year_month);
    $qry_all_org[] = $oid;
    $search_entry = current_entry($current_year_month);
    foreach ($search_entry as $se_key => $se_val) {
        if (in_array($se_val["FK_ORG_ID"], $qry_all_org)) {
            $new_search_entry[] = $se_val;
        }
    }
    return $new_search_entry;
}

/*
 * last month people count
 */

function last_month_people($oid, $month_int, $current_org_info, $str_year, $month_end_total, $month_leave_statistics, $count_current_entry, $last_month_people_statistics, $current_year) {
    $i = $month_int;
    $count_last_month_people[$i] = $month_end_total + count($month_leave_statistics['count_current_month_attrition']) - count($count_current_entry);
    for ($i = $month_int; $i > 1; $i--) {
        $str_month = sprintf('%02d', $i);
        $new_year_month = $str_year . "-" . $str_month;
        $month_leave_statistics = last_month_basic_date($oid, $current_org_info, $current_year, $new_year_month);
        $month_resign[$i] = count($month_leave_statistics['count_current_month_attrition']);
        $month_entry[$i] = count(entry_count($oid, $new_year_month));
        $str_month = sprintf('%02d', $i);
        $count_last_month_people[$i - 1] = $count_last_month_people[$i] + $month_resign[$i] - $month_entry[$i];
    }
    $last_month_people_statistics = $count_last_month_people;
    return $last_month_people_statistics;
}

/*
 * last month people count
 */

function last_month_people_detail($oid, $month_int, $current_org_info, $str_year, $current_org_detail, $month_leave_statistics, $count_current_entry, $last_month_people_statistics, $current_year) {
    $i = $month_int;
    $resigned_employees = array();
    $entried_employees = array();
    $month_end_employees_temp = array();
    $month_end_employees = array();

    /*
     * Re-arrange array 
     * Target:
     * array{
     *  'user_id' => user_id
     * }
     */
    $resigned_employees[$i] = re_arrange_resign($month_leave_statistics);
    $entried_employees[$i] = re_arrange_entry($count_current_entry);
    foreach ($current_org_detail as $cod_key => $cod_val) {
        foreach ($cod_val as $cov_key => $cov_val) {
            $month_end_employees_temp[$i][$cov_key] = $cov_key;
        }
    }
    $month_end_employees[$i] = $month_end_employees_temp[$i];
    /*
     * Add resgned employee into this array
     */
    foreach ($resigned_employees[$i] as $re_key => $re_val) {
        $month_end_employees[$i - 1][$re_key] = $re_val;
    }
    /*
     * Remove new joined employees
     */
    foreach ($month_end_employees[$i] as $mee_key => $mee_val) {
        if (!isset($entried_employees[$i][$mee_key])) {
            $month_end_employees[$i - 1][$mee_key] = $mee_val;
        }
    }

    for ($m = ($month_int - 1); $m > 1; $m--) {
        $str_month = sprintf('%02d', $m);
        $new_year_month = $str_year . "-" . $str_month;
        $month_leave_statistics = last_month_basic_date($oid, $current_org_info, $current_year, $new_year_month);
        $month_resign[$m] = $month_leave_statistics['count_current_month_attrition'];
        $month_entry[$m] = entry_count($oid, $new_year_month);


        $resigned_employees[$m] = re_arrange_resign($month_resign[$m]);
        $entried_employees[$m] = re_arrange_entry($month_entry[$m]);
        /*
         * Add resgned employee into this array
         */
        foreach ($resigned_employees[$m] as $re_key => $re_val) {
            $month_end_employees[$m - 1][$re_key] = $re_val;
        }

        /*
         * Remove new joined employees
         */
        foreach ($month_end_employees[$m] as $mee_key => $mee_val) {
            if (!isset($entried_employees[$m][$mee_key])) {
                $month_end_employees[$m - 1][$mee_key] = $mee_val;
            }
        }
    }
    $last_month_people_statistics = $month_end_employees;
    return $last_month_people_statistics;
}

/*
 * Re-arrange array of resigned and  new joined employees
 */

function re_arrange_resign($month_leave) {
    $resigned_employees = array();
    foreach ($month_leave as $ml_key => $ml_val) {
        $resigned_employees[$ml_val['FK_USER_ID']] = $ml_val['FK_USER_ID'];
    }
    return $resigned_employees;
}

function re_arrange_entry($month_entry) {
    $entried_employees = array();
    foreach ($month_entry as $me_key => $me_val) {
        $entried_employees[$me_val['USER_ID']] = $me_val['USER_ID'];
    }
    return $entried_employees;
}

/*
 * return last_month basic date
 */

function last_month_basic_date($oid, $current_org_info, $current_year, $current_year_month) {
    $existed_org = array();
    $count_current_month_resign_or_attrition = array();
    $count_current_month_resign = array();
    $count_current_month_borrow = array();
    $count_current_month_transfer = array();
    $count_current_month_attrition = array();
    foreach ($current_org_info as $coi_key => $coi_val) {
        $org_id = $coi_val["ORG_ID"] . ",";
        $qry_all_org = all_childs_orgs_rs($org_id, $current_year_month);
        $qry_all_org[] = $coi_val["ORG_ID"];
        $count_current_month_attrition = current_attrition_statistics($current_year_month);
        foreach ($count_current_month_attrition as $ccma_key => $ccma_val) {
            if (in_array($ccma_val["LAST_ORG"], $qry_all_org)) {
                $year_month = substr($ccma_val["RESIGN_DATE"], 0, 7);
                $existed_org[$year_month] = $ccma_val;
            }
        }
    }

    foreach ($existed_org as $eo_key => $eo_val) {
        if ($eo_val['TYPE'] == 1) {
            $count_current_month_resign[$eo_key] = $eo_val;
        } else if ($eo_val['TYPE'] == 3) {
            $count_current_month_borrow[$eo_key] = $eo_val;
        } else if ($eo_val['TYPE'] == 5) {
            $count_current_month_transfer[$eo_key] = $eo_val;
        }
    }
    $count_current_month_resign_or_attrition = array('count_current_month_resign' => $count_current_month_resign, 'count_current_month_attrition' => $count_current_month_attrition);
    return $count_current_month_resign_or_attrition;
}

/*
 * current org total count
 */

function current_org_total($oid, $year_month) {
    $current_people = all_people_count($oid, $year_month);
    foreach ($current_people as $cpc_key => $cpc_val) {
        $count_current_people = count($cpc_val['fte']) + count($cpc_val['intern']);
    }
    return $count_current_people;
}

/*
 * current org detailed info
 */

function current_org_detail($oid, $year_month) {
    $current_people = all_people_count($oid, $year_month);
    foreach ($current_people as $cpc_key => $cpc_val) {
        $count_current_people[] = $cpc_val['fte'];
        $count_current_people[] = $cpc_val['intern'];
    }
    return $count_current_people;
}

/*
 *  get resign info
 */

function resign_info($org_id, $year_month) {
    $qry_resign_info = "SELECT COUNT(FK_USER_ID) COUNT FROM resign_info WHERE  (RESIGN_DATE BETWEEN '$year_month-01' AND '$year_month-31') AND TYPE='1' AND LAST_ORG='$org_id';";
    $rst_resign_info = $GLOBALS['db']->query($qry_resign_info);
    $resign_info = $rst_resign_info->fetch_assoc();
    return $resign_info['COUNT'];
}

/*
 * Calculate all task of the specific tester
 */

function task_time_of_employee($user_id, $year_month) {
    $year_month_from = $year_month . "-01";
    $year_month_end = $year_month . "-31";
    $qry_task_effort = "SELECT ROUND(SUM(tae.AC_EXECUTION_TIME+tae.AC_SETUP_TIME)/60,2) TOTAL_TIME FROM task_assignment_execution AS tae WHERE TESTER='$user_id' AND (ASSIGN_DATE BETWEEN '$year_month_from' AND '$year_month_end');";
    $rst_task_effort = $GLOBALS['db']->query($qry_task_effort);
    $person_task_effort = $rst_task_effort->fetch_assoc();
    return $person_task_effort['TOTAL_TIME'];
}

/*
 *  Calculate the UR depend on iterations' task (For customer)
 */

function ur_iteration_tasks($total_task_effort_by_iteration, $bhc_count, $working_days) {
    $UR_customer = array();
    foreach ($total_task_effort_by_iteration as $ttei_key => $ttei_val) {
        if ($bhc_count[$ttei_key] == 0) {
            $UR_customer[$ttei_key] = 0;
        } else {
            $UR_customer[$ttei_key] = round($ttei_val / ($working_days * 6.5 * $bhc_count[$ttei_key]), 4) * 100;
        }
    }
    return $UR_customer;
}

/*
 *  Calculate the UR depend on ahc' task (For internal)
 */

function ur_ahc($total_task_effort_by_ahc, $ahc_count, $working_days) {
    $UR_internal = array();
    foreach ($total_task_effort_by_ahc as $tte_key => $tte_val) {
        $UR_internal[$tte_key] = round($tte_val / ($working_days * 8 * $ahc_count[$tte_key]), 4) * 100;
    }
    return $UR_internal;
}

/*
 * Query tasks of each user id (Depend on actual head cout)
 */

function task_effort_by_ahc($total_employee, $current_month) {
    $total_task_effort_by_ahc = array();
    foreach ($total_employee as $te_key => $te_val) {
        $task_time_per_person = array();
        foreach ($te_val as $tv_key => $tv_val) {
            $task_time_per_person[] = task_time_of_employee($tv_key, $current_month);
        }
        $total_task_effort_by_ahc[$te_key] = array_sum($task_time_per_person);
    }
    return $total_task_effort_by_ahc;
}

/*
 * Query tasks of each user id (Depend on actual head cout) monthly
 */

function task_effort_by_ahc_monthly($total_employee, $current_month) {
    $total_task_effort_by_ahc_monthly = 0;
    $task_time_per_person = array();
    foreach ($total_employee as $te_key => $te_val) {
        $personal_task_effort = task_time_of_employee($te_key, $current_month);
        $task_time_per_person[] = $personal_task_effort;
        $_SESSION['personal_task_effort'][$te_key][$current_month] = isset($personal_task_effort) ? $personal_task_effort : 0;
    }
    $total_task_effort_by_ahc_monthly = array_sum($task_time_per_person);
    return $total_task_effort_by_ahc_monthly;
}

/*
 *  Display the general resource status
 */

function general_resource_status($current_month, $resource_status_total, $resource_status, $oid, $su_id, $gtype) {
    $org_rs = array();
    $org_rst = array();
    foreach ($resource_status as $rs_key => $rs_val) {
        foreach ($rs_val as $rv_key => $rv_val) {
            $org_rs[$rv_key][$rs_key] = $rv_val;
        }
    }
    foreach ($resource_status_total as $rst_key => $rst_val) {
        foreach ($rst_val as $rvt_key => $rvt_val) {
            $org_rst[$rvt_key][$rst_key] = $rvt_val;
        }
    }
    ?>
    <table class="common_table center_align">
        <caption><?php echo "Resource Status : " . $current_month; ?></caption>
        <thead>
            <tr>
                <th rowspan="2">Organization Name</th>
                <th colspan="3">Head Count</th>
                <th colspan="2">UR</th>
                <th rowspan="2">Attrition</th>
            </tr>
            <tr>
                <th>BHC</th>
                <th>AHC</th>
                <th>Billable Rate</th>
                <th>Customer</th>
                <th>Internal</th>
            </tr>
        </thead>

        <?php
        foreach ($org_rst as $ort_key => $ort_val) {
            ?>
            <tr>
                <td class="total"><?php echo $ort_val['org_name']; ?></td>
                <td>
                    <a href="../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&gtype=<?php echo base64_encode($gtype); ?>&url=<?php echo base64_encode("monitor/billable_hc_info.php"); ?>&ooid=<?php echo base64_encode($ort_key); ?>#tabs_ops"><?php echo $ort_val['bhc']; ?></a>
                </td>
                <td>
                    <a href="../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&gtype=<?php echo base64_encode($gtype); ?>&url=<?php echo base64_encode("monitor/actual_hc_info.php"); ?>&ooid=<?php echo base64_encode($ort_key); ?>#tabs_ops"><?php echo $ort_val['ahc']; ?></a>
                </td>
                <td class="<?php echo billable_rate($ort_val['brate']) ?>">
                    <?php echo $ort_val['brate'] . "%"; ?></td>
                <td class="<?php echo billable_rate($ort_val['ur_customer']) ?>">
                    <a href="../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&gtype=<?php echo base64_encode($gtype); ?>&url=<?php echo base64_encode("monitor/org_ur_rate.php"); ?>&ooid=<?php echo base64_encode($ort_key); ?>#tabs_ops"><?php echo $ort_val['ur_customer'] . "%"; ?></a>
                </td>
                <td class="<?php echo billable_rate($ort_val['ur_internal']) ?>">
                    <a href="../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&gtype=<?php echo base64_encode($gtype); ?>&url=<?php echo base64_encode("monitor/org_ur_rate.php"); ?>&ooid=<?php echo base64_encode($ort_key); ?>#tabs_ops"><?php echo $ort_val['ur_internal'] . "%"; ?></a>
                </td>
                <td>
                    <a href="../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&gtype=<?php echo base64_encode($gtype); ?>&url=<?php echo base64_encode("monitor/attrition_statistics.php"); ?>&ooid=<?php echo base64_encode($ort_key); ?>#tabs_ops"><?php echo $ort_val['attrition']; ?></a>
                </td>
            </tr>
            <?php
        }

        foreach ($org_rs as $or_key => $or_val) {
            ?>
            <tr>
                <td class="highlight"><?php echo $or_val['org_name']; ?></td>
                <td>
                    <a href="../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&gtype=<?php echo base64_encode($gtype); ?>&url=<?php echo base64_encode("monitor/billable_hc_info.php"); ?>&ooid=<?php echo base64_encode($or_key); ?>#tabs_ops"><?php echo $or_val['bhc']; ?></a>
                </td>
                <td>
                    <a href="../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&gtype=<?php echo base64_encode($gtype); ?>&url=<?php echo base64_encode("monitor/actual_hc_info.php"); ?>&ooid=<?php echo base64_encode($or_key); ?>#tabs_ops"><?php echo $or_val['ahc']; ?></a>
                </td>
                <td class="<?php echo billable_rate($or_val['brate']) ?>">
                    <?php echo $or_val['brate'] . "%"; ?></td>
                <td class="<?php echo billable_rate($or_val['ur_customer']) ?>">
                    <a href="../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&gtype=<?php echo base64_encode($gtype); ?>&url=<?php echo base64_encode("monitor/org_ur_rate.php"); ?>&ooid=<?php echo base64_encode($or_key); ?>#tabs_ops"><?php echo $or_val['ur_customer'] . "%"; ?></a>
                </td>
                <td class="<?php echo billable_rate($or_val['ur_internal']) ?>">
                    <a href="../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&gtype=<?php echo base64_encode($gtype); ?>&url=<?php echo base64_encode("monitor/org_ur_rate.php"); ?>&ooid=<?php echo base64_encode($or_key); ?>#tabs_ops"><?php echo $or_val['ur_internal'] . "%"; ?></a>
                </td>
                <td>
                    <a href="../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&gtype=<?php echo base64_encode($gtype); ?>&url=<?php echo base64_encode("monitor/attrition_statistics.php"); ?>&ooid=<?php echo base64_encode($or_key); ?>#tabs_ops"><?php echo $or_val['attrition']; ?></a>
                </td>
            </tr>
            <?php
        }
        ?>
    </table>
    <?php
}

/*
 * qry all intern to employee info
 */

function all_intern_to_employee() {
    $qry_all_intern_to_employee = "SELECT USER_ID,INTERN_START,EMPLOYEE_START,EMPLOYEE_TYPE,USER_NAME,LEVEL FROM user_info WHERE INTERN_START IS NOT NULL AND EMPLOYEE_TYPE='1' ";
    $rst_all_intern_to_employee = $GLOBALS['db']->query($qry_all_intern_to_employee);
    $sub_all_intern_to_employee = $rst_all_intern_to_employee->fetch_all(MYSQLI_ASSOC);
    return $sub_all_intern_to_employee;
}

/*
 * qry all intern to employee attrition info
 */

function intern_to_employee_attrition($year_month) {
    $all_intern_to_employee = all_intern_to_employee();
    $resign_info = current_attrition_statistics($year_month);
    foreach ($resign_info as $ri_key => $ri_val) {
        $new_resign_info[$ri_val['FK_USER_ID']] = $ri_val;
    }
    foreach ($all_intern_to_employee as $aite_key => $aite_val) {
        $new_intern_to_employee[$aite_val['USER_ID']] = $aite_val;
    }
    if (isset($new_resign_info)) {
        $intern_to_employee_attrition = array_intersect_key($new_resign_info, $new_intern_to_employee);
        return $intern_to_employee_attrition;
    } else {
        return NULL;
    }
}

/*
 * Date: 2012-12-11
 * Author:CC
 * Purpose: Query all cached org performance data from DB of this year
 */

function year_org_effort() {
    $qry_org_effort = "SELECT FK_PROJECT_ID,YEAR,MONTH,WEEK,RAW_DATA,PROCESSED_DATA FROM cache_project_performance;";
    $rst_org_effort = $GLOBALS['db']->query($qry_org_effort);
    $year_org_effort_temp = $rst_org_effort->fetch_all(MYSQLI_ASSOC);
    foreach ($year_org_effort_temp as $yoet_key => $yoet_val) {
        $year_org_effort[$yoet_val['FK_PROJECT_ID']][$yoet_val['YEAR']][$yoet_val['MONTH']][$yoet_val['WEEK']] = $yoet_val;
    }
    return $year_org_effort;
}

/*
 * Date: 2012-12-11
 * Author:CC
 * Purpose: Query all cached org manday data from DB of this year
 */

function year_org_manday() {
    $qry_manday = "SELECT FK_ORG_ID,YEAR,MONTH,WEEK,MD FROM daily_org_ahc;";
    $rst_org_manday = $GLOBALS['db']->query($qry_manday);
    $org_manday_temp = $rst_org_manday->fetch_all(MYSQLI_ASSOC);
    foreach ($org_manday_temp as $omt_key => $omt_val) {
        $year_org_manday[$omt_val['FK_ORG_ID']][$omt_val['YEAR']][$omt_val['MONTH']][$omt_val['WEEK']][] = $omt_val;
    }
    return $year_org_manday;
}

/*
 * Date: 2013-05-06
 * Author:CC
 * Purpose: Query all cached org bhc data from DB of this year
 */

function year_org_bhc() {
    $qry_bhc = "SELECT FK_ORG_ID,YEAR,MONTH,WEEK,DAY,BHC,WD FROM daily_org_bhc;";
    $rst_org_bhc = $GLOBALS['db']->query($qry_bhc);
    $org_bhc_temp = $rst_org_bhc->fetch_all(MYSQLI_ASSOC);
    foreach ($org_bhc_temp as $obt_key => $obt_val) {
        $year_org_bhc[$obt_val['FK_ORG_ID']][$obt_val['YEAR']][$obt_val['MONTH']][$obt_val['WEEK']][] = $obt_val;
    }
    return $year_org_bhc;
}

/*
 * Date: 2012-12-11
 * Author:CC
 * Purpose: Calculte each effort based on the raw data in a array
 */

function calc_orgs_effort($month_org_effort) {
    $tatol_task_effort = 0;
    $total_task_effort_internal = 0;
    $total_task_effort_external = 0;
    foreach ($month_org_effort as $moe_key => $moe_val) {
        $processed_data = json_decode($moe_val['PROCESSED_DATA'], TRUE);
        $total_task_effort_internal +=$processed_data['task_internal']['actual_total'];
        $total_task_effort_external +=$processed_data['task_customer']['actual_total'];
    }
    $month_org_effort_internal_count = round($total_task_effort_internal / 60, 2);
    $month_org_effort_external_count = round($total_task_effort_external / 60, 2);
    $month_org_effort_count = array(
        'internal' => $month_org_effort_internal_count,
        'external' => $month_org_effort_external_count,
    );
    return $month_org_effort_count;
}

/**
 * Date: 2012-12-11
 * Modified: 2013-05-03
 * Author: CC
 * [get_month_org_effort Calculate task effort based on the original org effort $yearly_org_effort]
 * @param  [array] $yearly_org_effort [Original task effort]
 * @param  [array] $father_child_orgs [all child orgs]
 * @param  [char] $year              [year]
 * @param  [char] $month             [month]
 * @param  [int] $week_flag         [include week '0' or not. '0' for include and '1' for not include]
 * @return [array]                    [calculated task effort, includs 'internal' and 'external']
 */
function get_month_org_effort($yearly_org_effort, $father_child_orgs, $year, $month,$week_flag) {
    $month_org_effort_temp = array();
    foreach ($father_child_orgs as $fco_key => $fco_val) {
        foreach ($fco_val as $fv_ley => $fv_val) {
            if(isset($yearly_org_effort[$fv_val][$year][$month])){
                if($week_flag == 0){
                    $month_org_effort_temp[$fco_key][$fv_val][0] = $yearly_org_effort[$fv_val][$year][$month][0];
                }else{
                    foreach($yearly_org_effort[$fv_val][$year][$month] as $yoe_key => $yoe_val){
                        if($yoe_key != 0){
                            $month_org_effort_temp[$fco_key][$fv_val][$yoe_key] = $yearly_org_effort[$fv_val][$year][$month][$yoe_key];
                        }
                    }
                }
            }else{
                $month_org_effort_temp[$fco_key][$fv_val] = array('0' => array('PROCESSED_DATA' => 0));
            }
        }
        foreach ($month_org_effort_temp[$fco_key] as $moet_key => $moet_val) {
            foreach ($moet_val as $mv_key => $mv_val) {
                $month_org_effort[$fco_key][] = $mv_val;
            }
        }
        $month_org_effort[$fco_key] = calc_orgs_effort($month_org_effort[$fco_key]);
    }
    return $month_org_effort;
}

/**
 * Date: 2012-12-11
 * Modified: 2013-05-03
 * Author: CC
 * [get_month_org_manday Calculate man-day based on th original org mnday $yearly_org_manday]
 * @param  [array] $yearly_org_manday [original org manday]
 * @param  [array] $father_child_orgs [all child orgs]
 * @param  [char] $year              [year]
 * @param  [char] $month             [month]
 * @param  [int] $week_flag         [include week '0' or not. '0' for include and '1' for not include ]
 * @return [array]                    [calculated man-day]
 */
function get_month_org_manday($yearly_org_manday, $father_child_orgs, $year, $month,$week_flag) {
    foreach ($father_child_orgs as $fco_key => $fco_val) {
        $month_manday = array();
        foreach ($fco_val as $fv_ley => $fv_val) {
            $month_manday_temp = 0;
            if (isset($yearly_org_manday[$fv_val][$year][$month])) {
                if($week_flag == 0){
                    $month_manday_array[0] = isset($yearly_org_manday[$fv_val][$year][$month][0])?$yearly_org_manday[$fv_val][$year][$month][0]:array();;
                }else{
                    foreach($yearly_org_manday[$fv_val][$year][$month] as $yom_key => $yom_val){
                        if($yom_key != 0){
                            $month_manday_array[$yom_key] = $yearly_org_manday[$fv_val][$year][$month][$yom_key];
                        }else{

                        }

                    }
                }
                foreach ($month_manday_array as $mma_key => $mma_val) {
                    foreach ($mma_val as $mv_key => $mv_val) {
                        $month_manday_temp += $mv_val['MD'];
                    }
                }
                $month_manday[] = $month_manday_temp;
            } else {
                $month_manday[] = 0;
            }
        }
        $month_org_manday_count[$fco_key] = array_sum($month_manday);
    }
    return $month_org_manday_count;
}

/**
 * Date: 2012-12-11
 * Modified: 2013-05-06
 * Author: CC
 * [get_month_org_bhc Calculate bhc based on th original org bhc $yearly_org_bhc]
 * @param  [array] $yearly_org_bhc [original org bhc]
 * @param  [array] $father_child_orgs [all child orgs]
 * @param  [char] $year              [year]
 * @param  [char] $month             [month]
 * @param  [int] $week_flag         [include week '0' or not. '0' for include and '1' for not include ]
 * @return [array]                    [calculated bhc]
 */
function get_month_org_bhc($yearly_org_bhc, $father_child_orgs, $year, $month,$week_flag) {
    foreach ($father_child_orgs as $fco_key => $fco_val) {
        $month_bhc = array();
        foreach ($fco_val as $fv_ley => $fv_val) {
            $month_bhc_temp = 0;
            if (isset($yearly_org_bhc[$fv_val][$year][$month])) {
                if($week_flag == 0){
                    $month_bhc_array[0] = $yearly_org_bhc[$fv_val][$year][$month][0];
                }else{
                    foreach($yearly_org_bhc[$fv_val][$year][$month] as $yom_key => $yom_val){
                        if($yom_key != 0){
                            $month_bhc_array[$yom_key] = $yearly_org_bhc[$fv_val][$year][$month][$yom_key];
                        }
                    }
                }
                foreach ($month_bhc_array as $mba_key => $mba_val) {
                    foreach ($mba_val as $mv_key => $mv_val) {
                        $month_bhc_temp += $mv_val['WD']*$mv_val['BHC'];
                    }
                }
                $month_bhc[] = $month_bhc_temp;
            } else {
                $month_bhc[] = 0;
            }
        }
        $month_org_bhc_count[$fco_key] = array_sum($month_bhc);
    }
    return $month_org_bhc_count;
}

/*
 * Date:2012-12-11
 * Author:CC
 * Purpose: Calculate UR based on a org effort
 * org_effort array e.g. 
 * Array
 * (
 *    [10] => 186.67
 *    [12] => 167.25
 *    [13] => 95.67
 *    [46] => 0
 *    [47] => 0
 * )
 */

function org_efort_ur($org_effort, $org_manday, $month_org_bhc) {
    foreach ($org_effort as $oei_key => $oei_val) {
        if ($org_manday[$oei_key] != 0) {
            $ur_temp_internal = round($oei_val['internal'] / ($org_manday[$oei_key] * 8), 2);
            if ($month_org_bhc[$oei_key] != 0) {
                $ur_temp_external = round($oei_val['external'] / ($month_org_bhc[$oei_key] * 6.5), 2);
            } else {
                $ur_temp_external = 0;
            }
        } else {
            $ur_temp_internal = 0;
            $ur_temp_external = 0;
        }
        $UR_internal[$oei_key] = $ur_temp_internal * 100;
        $UR_external[$oei_key] = $ur_temp_external * 100;
    }
    //sort by the value desc
    arsort($UR_internal);
    arsort($UR_external);
    $UR = array(
        'internal' => $UR_internal,
        'external' => $UR_external
    );
    return $UR;
}

/*2013-8-21
* auther:shiyan
* add new function
*/

// user's finished task infomation
function list_personal_finished_task($project_string,$user_id,$date_from,$date_to){
    $qry_personal_tae = "SELECT ii.FK_PROJECT_ID,tae.TESTER,tae.ITERATION_ID ITERATION_ID, ITERATION_NAME, SEQUENCE_ID, NO, TASK_ID, 
                    TASK_TITLE, SCENARIO_ID, SCENARIO_TITLE, ASSIGN_DATE, FINISH_DATE,ENVIRONMENT, BUILD_INFO,EX_SETUP_TIME,
                    EX_EXECUTION_TIME,AC_SETUP_TIME, AC_EXECUTION_TIME, INVESTIGATE_TIME, tae.COMMENTS,CORRECT, tae.STATUS as STATUS ,
                    tae.BUG_INFO as BUG_INFO FROM task_assignment_execution as tae,iteration_info as ii,project_info as pi
                    WHERE tae.ITERATION_ID = ii.ITERATION_ID AND ii.FK_PROJECT_ID = pi.ID AND tae.TESTER IN ($user_id) 
                    AND (FINISH_DATE BETWEEN '$date_from' AND '$date_to') and pi.ID in ($project_string)  
                    ORDER BY TESTER,ITERATION_NAME,TASK_ID,SCENARIO_ID ASC";
    $rst_personal_tae = $GLOBALS['db']->query($qry_personal_tae);
    $rst_personal_tae = $rst_personal_tae->fetch_all(MYSQLI_ASSOC);
    if(!empty($rst_personal_tae)){
        foreach ($rst_personal_tae as $pt_key => $pt_val){
            $personal_finished_task_info[$pt_val['FK_PROJECT_ID']][] = $pt_val ;
        }
     }else {
         $personal_finished_task_info=array();
     }
    return $personal_finished_task_info;
}

//bug and bug_note infomation
function list_personal_bugs_info($project_string,$user_id, $date_from, $date_to) {
    $qry_bug = "SELECT bl.FK_PROJECT_ID,EXECUTE_TIME,BUG_SYSTEM, BUG_ID, ITERATION_NAME,ii.ITERATION_ID,BUILD_ID, 
                ui.USER_NAME REPORTER_NAME,ui.NICK_NAME NICK_NAME, bl.ACCOUNT, TASK_TYPE, BUG_CATEGORY,BUG_TYPE, FEATURE, RELATED_BUG, 
                CAST(SUBMIT_DATE AS DATE) SUBMIT_DATE, PACTERA_STATUS,bl.BUG_STATUS, bl.SUB_STATUS,bl.COMMENTS, bl.ID, REPORTER,
                ca.`OWNER`,ca.ACCOUNT CITRITE_ACCOUNT FROM ((bug_library AS bl left join user_info AS ui ON bl. REPORTER=ui.USER_ID)
                LEFT JOIN iteration_info AS ii ON bl.ITERATION_ID=ii.ITERATION_ID) 
                LEFT JOIN (customer_account AS ca) ON (ca.MAINTAINER = ui.USER_ID)
                WHERE bl.FK_PROJECT_ID in ($project_string) AND (CAST(SUBMIT_DATE AS DATE) BETWEEN '$date_from' AND '$date_to')
                and ui.USER_ID in ($user_id) ORDER BY ui.USER_ID,SUBMIT_DATE ASC";
    $result = $GLOBALS['db']->query($qry_bug);
    $bug_info_temp = $result->fetch_all(MYSQLI_ASSOC);

    $qry_bug_note = "SELECT ui.USER_ID,bn.EXECUTE_TIME,bn.ISSUE_ID,bl.BUG_ID,bl.REPORTER,bn.OWNER,bl.FK_PROJECT_ID,CAST(bn.SUBMIT_DATE AS DATE) SUBMIT_DATE,
                ui.USER_NAME,ui.NICK_NAME NICK_NAME,ca.`OWNER`,ca.ACCOUNT CITRITE_ACCOUNT FROM 
                (bug_library AS bl, user_info AS ui, bug_note AS bn) LEFT JOIN (customer_account AS ca) ON (ca.MAINTAINER = ui.USER_ID) 
                WHERE bl.FK_PROJECT_ID in ($project_string) AND (CAST(bn.SUBMIT_DATE AS DATE) BETWEEN '$date_from' AND '$date_to')
                AND bl.ID = bn.ISSUE_ID AND bn.`OWNER`=ui.USER_ID ORDER BY ui.USER_ID,bn.SUBMIT_DATE asc";
    $result_note = $GLOBALS['db']->query($qry_bug_note);
    $bug_info_note_temp = $result_note->fetch_all(MYSQLI_ASSOC);

    if(!empty($bug_info_note_temp)){
        foreach($bug_info_note_temp as $bint_key => $bint_Val){
            $list_personal_bug_note[$bint_Val['FK_PROJECT_ID']][] = $bint_Val;
        }
    }
    
    if(!empty($bug_info_temp)){
        foreach($bug_info_temp as $bit_key => $bit_val){
            $list_personal_bug_note[$bit_val['FK_PROJECT_ID']][] = $bit_val;
        }
    }

    if(empty($list_personal_bug_note)){
        $list_personal_bug_note =array();
    }

    return $list_personal_bug_note;
}

/**
 * Author: CC
 * Date: 09/02/2013
 * [personal_task_effort_ajax personal task effort ajax]
 * @param  [type] $projects  [description]
 * @param  [type] $user_id   [description]
 * @param  [type] $date_from [description]
 * @param  [type] $date_to   [description]
 * @return [type]            [description]
 */
function personal_task_effort_ajax($projects,$user_id, $date_from, $date_to) {
    $j = 0;
    $k = 0;
    /**
     * Calculate the gap between two dates
     */
    $date_gap_temp = strtotime($date_to) - strtotime($date_from);
    $date_gap      = ($date_gap_temp / 86400) + 1;

    $general_sub_project_task  = array();
    $project_string            = op_string($projects);
    $personal_finished_task    = list_personal_finished_task($project_string,$user_id, $date_from, $date_to);
    $ds_op_md                  = ds_op_md($project_string, $date_from, $date_to, 'PROJECT');
    $personal_bug_info         = list_personal_bugs_info($project_string, $user_id, $date_from, $date_to);

    if(empty($personal_finished_task)&&empty($personal_bug_info))
    {
        error_without_article("204");
        exit();
    }
        
    $this_week_day = date("Y-m-d",strtotime($date_from." -1 day"));

    for($j=0;$j<$date_gap;$j++){
        $this_week_day         = date("Y-m-d",strtotime($this_week_day." +1 day"));
        $this_week_day_month   = date("m/d",strtotime($this_week_day));
        $this_week_day_explode = explode("-",$this_week_day);
        foreach($ds_op_md[$this_week_day_explode[0]][$this_week_day_explode[1]][$this_week_day_explode[2]] as $doat_key => $doat_val){
            $ds_project_md_by_project_this[$doat_key][$this_week_day_month] = $doat_val['MD'];
        }
    }

        foreach ($projects as $p_key => $p_val) {
            $sub_project_task          = array();
            $sub_project_wmd           = array();   //Each project's daily md or wd
            $sub_project_task_count    = array();
            $sub_project_task_temp     = array();
            $finished_sub_project_task = array();
            $total_sub_project_task    = array();
            $finished_sub_project_task_per_day          = array();
            $finished_sub_project_task_per_engineer_day = array();
            $this_week_day_number_name                  = date("Y-m-d",strtotime($date_from." -1 day"));

            $sub_project_task_temp['FINISHED']         = isset($personal_finished_task[$p_val['ID']]) ? $personal_finished_task[$p_val['ID']] : array();
            $sub_project_task_temp['BUG']              = isset($personal_bug_info[$p_val['ID']]) ? $personal_bug_info[$p_val['ID']] : array();
            $sub_project_task_temp['MD']['DATE_SCOPE'] = $ds_project_md_by_project_this[$p_val['ID']];

            foreach ($sub_project_task_temp['FINISHED'] as $sptt_key => $sptt_val) {
                $sub_project_task['FINISHED']['DATE_SCOPE']['GENERAL'][] = $sptt_val;
                $finished_date_name = date("m/d",strtotime($sptt_val['FINISH_DATE']));
                $sub_project_task['FINISHED']['DATE_SCOPE']['DETAILED'][$finished_date_name][] = $sptt_val;
            }

            foreach ($sub_project_task_temp['BUG'] as $spttb_key => $spttb_val) {
                $sub_project_task['BUG']['DATE_SCOPE']['GENERAL'][] = $spttb_val;
                $finished_date_name = date("m/d",strtotime($spttb_val['SUBMIT_DATE']));
                $sub_project_task['BUG']['DATE_SCOPE']['DETAILED'][$finished_date_name][] = $spttb_val;
            }

            foreach ($sub_project_task_temp['MD'] as $spttm_key => $spttm_val) {
                $sub_project_task['MD'][$spttm_key] = array_sum($spttm_val);
                foreach ($spttm_val as $svm_key => $svm_val) {
                    $sub_project_wmd['MD'][$svm_key] = $svm_val;
                }
            }

            //foreach ($sub_project_task as $spt_key => $spt_val) {
            $sub_project_task_count['FINISHED']['DATE_SCOPE']  = isset($sub_project_task['FINISHED']['DATE_SCOPE']['GENERAL']) ? count($sub_project_task['FINISHED']['DATE_SCOPE']['GENERAL']) : 0;

            if (isset($sub_project_task['FINISHED']['DATE_SCOPE'])) {
                    $finished_sub_project_task['DATE_SCOPE']['GENERAL']       = caculate_effort($sub_project_task['FINISHED']['DATE_SCOPE']['GENERAL'],isset($sub_project_task['BUG']['DATE_SCOPE']['GENERAL'])?$sub_project_task['BUG']['DATE_SCOPE']['GENERAL']:array(),0);
                    $sub_project_task['WD']['DATE_SCOPE'] = getWorkingDays($date_from, $date_to);
                    $finished_sub_project_task_per_day['DATE_SCOPE']          = round(($finished_sub_project_task['DATE_SCOPE']['GENERAL']['expected_total_time']/$sub_project_task['WD']['DATE_SCOPE']),2);
                    $finished_sub_project_task_per_engineer_day['DATE_SCOPE'] = round(($finished_sub_project_task['DATE_SCOPE']['GENERAL']['expected_total_time']/$sub_project_task['MD']['DATE_SCOPE']),2);

                    foreach($sub_project_task['FINISHED']['DATE_SCOPE']['DETAILED'] as $svv_key => $svv_val){
                        $finished_sub_project_task['DATE_SCOPE']['DETAILED'][$svv_key] = caculate_effort($svv_val,isset($sub_project_task['BUG']['DATE_SCOPE']['DETAILED'][$svv_key])?$sub_project_task['BUG']['DATE_SCOPE']['DETAILED'][$svv_key]:array(),0);
                    }
                } else {
                    $finished_sub_project_task['DATE_SCOPE']['GENERAL'] = array(
                        'ex_setup_time'       => 0,
                        'ex_execution_time'   => 0,
                        'ac_setup_time'       => 0,
                        'ac_execution_time'   => 0,
                        'investigate_time'    => 0,
                        'bug_time'            => 0,
                        'expected_total_time' => 0,
                        'actual_total_time'   => 0,
                        'total_effort'        => 0
                    );

                    $finished_sub_project_task_per_day['DATE_SCOPE']          = 0;
                    $finished_sub_project_task_per_engineer_day['DATE_SCOPE'] = 0;
                    $finished_sub_project_task['DATE_SCOPE']['DETAILED']      = array();
                }

                for($k = 0; $k < $date_gap; $k++){
                    $this_week_day_number_name = date("Y-m-d",strtotime($this_week_day_number_name." +1 day"));
                    $this_week_day             = date("m/d",strtotime($this_week_day_number_name));
                    if(isset($finished_sub_project_task['DATE_SCOPE']['DETAILED'][$this_week_day])){
                        $finished_sub_project_task['DATE_SCOPE']['DETAILED'][$this_week_day] = $finished_sub_project_task['DATE_SCOPE']['DETAILED'][$this_week_day];
                        $sub_project_task_count['MD']['DATE_SCOPE'][$this_week_day]          = $sub_project_wmd['MD'][$this_week_day];
                    }
                }
            ksort($finished_sub_project_task['DATE_SCOPE']['DETAILED']);

            foreach($finished_sub_project_task['DATE_SCOPE']['DETAILED'] as $fsot_key => $fsot_val){
                if(!isset($sub_project_task_count['MD']['DATE_SCOPE'][$fsot_key])){
                    $sub_project_task_count['MD']['DATE_SCOPE'][$fsot_key] = 0;
                }
            }
            ksort($sub_project_task_count['MD']['DATE_SCOPE']);

            $general_sub_project_task[$p_key]['PROJECT_NAME']            = $p_val['NAME'];
            $general_sub_project_task[$p_key]['COUNT']                   = $sub_project_task_count;
            $general_sub_project_task[$p_key]['TIME_EFFORT']['FINISHED'] = $finished_sub_project_task;  
        }
    return $general_sub_project_task;
}

//2013-9-2
//list completed task status    
function list_task_completed_count($oid,$date_from,$date_to){
    if(($date_from == "")||($date_to == "")){
        $date_from = "2000-01-01";
        $date_to   = $GLOBALS['today'];
    }
    $qry_tae_status_count="SELECT tsl.ID,tsl.`NAME`, COUNT(tae.`STATUS`) AS COUNT,pi.EXP_START,pi.EXP_END,pi.ACT_START,pi.ACT_END FROM task_assignment_execution as tae, iteration_info as ii, project_info as pi,task_status_list AS tsl 
                          WHERE tae.iteration_id = ii.iteration_id AND ii.FK_PROJECT_ID=pi.ID and tae.`STATUS`=tsl.ID and pi.ID in ($oid) and tsl.ID not in ({$GLOBALS['UNFINISHED_STATUS_STRING']}) and tae.ASSIGN_DATE BETWEEN '$date_from' AND '$date_to' AND tae.FINISH_DATE BETWEEN '$date_from' AND '$date_to' AND CORRECT = 'N' GROUP BY tae.`STATUS` ORDER BY tae.`STATUS`";
    $rst_tsc_info  = $GLOBALS['db']->query($qry_tae_status_count);
    $tsc_info_temp = $rst_tsc_info->fetch_all(MYSQLI_ASSOC);
    $tsc_info      = array();
    if(isset($tsc_info_temp)&&!(empty($tsc_info_temp))){
        foreach ($tsc_info_temp as $tit_key => $tit_value) {
            $tsc_info[$tit_value['NAME']] = $tit_value;
        }
    }
    return $tsc_info;
}
//list completed task status    
function list_task_uncompleted_count($oid,$date_from,$date_to){
    if(($date_from == "")||($date_to == "")){
        $date_from = "2000-01-01";
        $date_to   = $GLOBALS['today'];
    }
    $qry_tae_status_count="SELECT tsl.ID,tsl.`NAME`, COUNT(tae.`STATUS`) AS COUNT,pi.EXP_START,pi.EXP_END,pi.ACT_START,pi.ACT_END FROM task_assignment_execution as tae, iteration_info as ii, project_info as pi,task_status_list AS tsl 
                          WHERE tae.iteration_id = ii.iteration_id AND ii.FK_PROJECT_ID=pi.ID and tae.`STATUS`=tsl.ID and pi.ID in ($oid) and tsl.ID in ({$GLOBALS['UNFINISHED_STATUS_STRING']}) and tae.ASSIGN_DATE BETWEEN '$date_from' AND '$date_to' AND tae.FINISH_DATE BETWEEN '$date_from' AND '$date_to' AND CORRECT = 'N' GROUP BY tae.`STATUS` ORDER BY tae.`STATUS`";
    $rst_tsc_info  = $GLOBALS['db']->query($qry_tae_status_count);
    $tsc_info_temp = $rst_tsc_info->fetch_all(MYSQLI_ASSOC);
    $tsc_info      = array();
    if(isset($tsc_info_temp)&&!(empty($tsc_info_temp))){
        foreach ($tsc_info_temp as $tit_key => $tit_value) {
            $tsc_info[$tit_value['NAME']] = $tit_value;
        }
    }
    $qry_tae_unassign_count="SELECT tae.`STATUS` as ID, COUNT(*) AS COUNT,pi.EXP_START,pi.EXP_END,pi.ACT_START,pi.ACT_END FROM task_assignment_execution as tae, iteration_info as ii, project_info as pi
                          WHERE tae.iteration_id = ii.iteration_id AND ii.FK_PROJECT_ID=pi.ID and pi.ID in ($oid)and tae.`STATUS` is null  and tae.ASSIGN_DATE BETWEEN '$date_from' AND '$date_to' AND tae.FINISH_DATE BETWEEN '$date_from' AND '$date_to' AND CORRECT = 'N'";
    $rst_tuc_info  = $GLOBALS['db']->query($qry_tae_unassign_count);
    $tuc_info_temp = $rst_tuc_info->fetch_all(MYSQLI_ASSOC);
    if(isset($tuc_info_temp)&&!(empty($tuc_info_temp))){
        foreach ($tuc_info_temp as $tit_key => $tit_value) {
            if($tit_value['COUNT'] != 0)
                $tsc_info['Unassign'] = $tit_value;
        }
    }
    return $tsc_info;
}

/**
 * Author: CC
 * Date: 09/25/2013
 * [project_tae_list description]
 * @param  [type] $project_id [description]
 * @return [type]             [description]
 */
function project_tae_list($project_id){
    $qry_tae_status_count = "SELECT tae.NO,tae.ITERATION_ID,tae.TASK_ID,tae.SCENARIO_ID, tae.TESTER, tae.`STATUS`, tae.`ASSIGN_DATE`, tae.`FINISH_DATE`, pi.ID FROM task_assignment_execution as tae, iteration_info as ii,project_info as pi
                          WHERE tae.iteration_id = ii.iteration_id AND ii.FK_PROJECT_ID=pi.ID AND pi.ID IN ($project_id) AND tae.CORRECT = 'N' ORDER BY tae.`NO` ASC";
    $rst_tsc_info  = $GLOBALS['db']->query($qry_tae_status_count);
    $tsc_info_temp = $rst_tsc_info->fetch_all(MYSQLI_ASSOC);
    if(!empty($tsc_info_temp)){
        foreach ($tsc_info_temp as $tit_key => $tit_value) {
            $tsc_info[$tit_value['ID']][$tit_value['STATUS']][] = $tit_value;
        }
    }else{
        $tsc_info = array();
    }
    return $tsc_info;
}

/**
 * Author: CC
 * Date: 09/23/2013
 * [iteration_tae_list Lsit all task records between a date scope]
 * @param  [int] $iteration_id [iteration id]
 * @return [array]               [description]
 */
function iteration_tae_list($iteration_id){
    $qry_tae_status_count = "SELECT tae.NO,tae.ITERATION_ID,tae.TASK_ID,tae.SCENARIO_ID, tae.TESTER, tae.`STATUS`, tae.`ASSIGN_DATE`, tae.`FINISH_DATE` FROM task_assignment_execution as tae, iteration_info as ii
                          WHERE tae.iteration_id = ii.iteration_id AND tae.ITERATION_ID IN ($iteration_id) AND tae.CORRECT = 'N' ORDER BY tae.`NO` ASC";
    $rst_tsc_info  = $GLOBALS['db']->query($qry_tae_status_count);
    $tsc_info_temp = $rst_tsc_info->fetch_all(MYSQLI_ASSOC);
    if(!empty($tsc_info_temp)){
        foreach ($tsc_info_temp as $tit_key => $tit_value) {
            $tsc_info[$tit_value['ITERATION_ID']][$tit_value['STATUS']][] = $tit_value;
        }
    }else{
        $tsc_info = array();
    }
    return $tsc_info;
}

/**
 * Author: CC
 * Date: 09/24/2013
 * [task_bug_by_category description]
 * @param  [type] $iteration_info     [description]
 * @param  [type] $iteration_tae_list [description]
 * @param  [type] $bug_status_count   [description]
 * @param  [type] $date_from          [description]
 * @param  [type] $date_to            [description]
 * @return [type]                     [description]
 */
function task_bug_by_category($project_iteration_info,$iteration_tae_list,$bug_status_count,$date_from,$date_to){
    if(($date_from == "")||($date_to == "")){
        $date_from = "2007-01-01";
        $date_to   = $GLOBALS['today'];
    }
    $task_uncompleted_status_info = array();
    $task_completed_status_info   = array();
    $task_bug_by_category         = array();
    $height                       = array();
    foreach($iteration_tae_list as $itl_key => $itl_val){
        $i = 0;
        $j = 0;
        foreach($GLOBALS['UNFINISHED_STATUS'] as $unf_key => $unf_val){
            if(isset($iteration_tae_list[$itl_key][$unf_key])){
                foreach ($iteration_tae_list[$itl_key][$unf_key] as $itl_iu_key => $itl_iu_val) {
                    $task_uncompleted_status_info[$itl_key][$itl_iu_val['STATUS']][] = $itl_iu_val;
                    $i++;
                }
            }
        }
        if(!isset($task_uncompleted_status_info[$itl_key])){
            $task_uncompleted_status_info[$itl_key] = array();
        }
        $task_uncompleted_count[$itl_key] = $i;
        
        foreach($GLOBALS['FINISHED_STATUS'] as $fs_key => $fs_val){
            if(isset($iteration_tae_list[$itl_key][$fs_key])){
                foreach ($iteration_tae_list[$itl_key][$fs_key] as $itl_if_key => $itl_if_val) {
                    if((strtotime($itl_if_val['FINISH_DATE']) <= strtotime($date_to)) && (strtotime($itl_if_val['FINISH_DATE']) >= strtotime($date_from))){
                        $task_completed_status_info[$itl_key][$itl_if_val['STATUS']][] = $itl_if_val;
                        $j++;
                    }
                }
            }
        }
        if(!isset($task_completed_status_info[$itl_key])){
            $task_completed_status_info[$itl_key] = array();
        }
        $task_completed_count[$itl_key] = $j;
        $task_total_count[$itl_key]     = $i + $j;

        if($date_from == "2007-01-01"){
            $actual_working_day[$itl_key] = getWorkingDays($project_iteration_info[$itl_key]['START'], $project_iteration_info[$itl_key]['END']);
        }else{
            $actual_working_day[$itl_key] = getWorkingDays($date_from, $date_to);
        }
        if(isset($bug_status_count[$itl_key])){
            foreach ($bug_status_count[$itl_key] as $bsc_key => $bsc_value) {
                $bug_temp[$itl_key]['CATEGORY'][$bsc_value['BUG_CATEGORY']]['LIST'][$bsc_value['BUG_ID']] = $bsc_value;
                $bug_temp[$itl_key]['STATUS'][$bsc_value['BUG_STATUS']]['LIST'][$bsc_value['BUG_ID']]     = $bsc_value;
                //CC: Only calc SUB_STATUS if BUG_STATUS is 5(Close);
                if($bsc_value['BUG_STATUS'] == 5){
                    $bug_temp[$itl_key]['SUB_STATUS'][$bsc_value['SUB_STATUS']]['LIST'][$bsc_value['BUG_ID']] = $bsc_value;
                }
            }
            foreach ($bug_temp[$itl_key] as $bt_key => $bt_val) {
                foreach ($bt_val as $bv_key => $bv_val) {
                    $bug_temp[$itl_key][$bt_key][$bv_key]['COUNT'] = count($bv_val['LIST']);
                }
            }
        }else{
            $bug_temp[$itl_key] = array();
        }
        $task_column[$itl_key] = (count($task_completed_status_info[$itl_key]) + count($task_uncompleted_status_info[$itl_key]) +4);
        $bug_column[$itl_key]  = (isset($bug_temp[$itl_key]['SUB_STATUS']))?(count($bug_temp[$itl_key]['CATEGORY']) + count($bug_temp[$itl_key]['STATUS']) + count($bug_temp[$itl_key]['SUB_STATUS']) + 3):3;

        $height[$itl_key] = ($task_column[$itl_key] > $bug_column[$itl_key])?($task_column[$itl_key]*38):($bug_column[$itl_key]*38);
        $task_bug_by_category[$itl_key] = array(
            'task_uncompleted_count'       => $task_uncompleted_count[$itl_key],
            'task_completed_count'         => $task_completed_count[$itl_key],
            'task_total_count'             => $task_total_count[$itl_key],
            'task_uncompleted_status_info' => $task_uncompleted_status_info[$itl_key],
            'task_completed_status_info'   => $task_completed_status_info[$itl_key],
            'actual_working_day'           => $actual_working_day[$itl_key],
            'bug'                          => $bug_temp[$itl_key],
            'height'                       => $height[$itl_key]
            );
    }
    return $task_bug_by_category;
}

/**
 * Author: CC
 * Date: 09/25/2013
 * [task_by_category description]
 * @param  [type] $project_iteration_info [description]
 * @param  [type] $iteration_tae_list     [description]
 * @return [type]                         [description]
 */
function task_by_category($project_iteration_info,$iteration_tae_list){
    $task_uncompleted_status_info = array();
    $task_completed_status_info   = array();
    $task_by_category             = array();
    
    foreach($iteration_tae_list as $itl_key => $itl_val){
        $i = 0;
        $j = 0;
        foreach($GLOBALS['UNFINISHED_STATUS'] as $unf_key => $unf_val){
            if(isset($iteration_tae_list[$itl_key][$unf_key])){
                foreach ($iteration_tae_list[$itl_key][$unf_key] as $itl_iu_key => $itl_iu_val) {
                    $task_uncompleted_status_info[$itl_key][$itl_iu_val['STATUS']][] = $itl_iu_val;
                    $i++;
                }
            }
        }
        if(!isset($task_uncompleted_status_info[$itl_key])){
            $task_uncompleted_status_info[$itl_key] = array();
        }
        $task_uncompleted_count[$itl_key] = $i;
        
        foreach($GLOBALS['FINISHED_STATUS'] as $fs_key => $fs_val){
            if(isset($iteration_tae_list[$itl_key][$fs_key])){
                foreach ($iteration_tae_list[$itl_key][$fs_key] as $itl_if_key => $itl_if_val) {
                    $task_completed_status_info[$itl_key][$itl_if_val['STATUS']][] = $itl_if_val;
                    $j++;
                }
            }
        }
        if(!isset($task_completed_status_info[$itl_key])){
            $task_completed_status_info[$itl_key] = array();
        }
        $task_completed_count[$itl_key] = $j;
        $task_total_count[$itl_key]     = $i + $j;

        $actual_working_day[$itl_key] = getWorkingDays($project_iteration_info[$itl_key]['START'], $project_iteration_info[$itl_key]['END']);

        $task_by_category[$itl_key] = array(
            'task_uncompleted_count'       => $task_uncompleted_count[$itl_key],
            'task_completed_count'         => $task_completed_count[$itl_key],
            'task_total_count'             => $task_total_count[$itl_key],
            'task_uncompleted_status_info' => $task_uncompleted_status_info[$itl_key],
            'task_completed_status_info'   => $task_completed_status_info[$itl_key],
            'actual_working_day'           => $actual_working_day[$itl_key]
            );
    }
    return $task_by_category;
}

function project_actual_remain($project_iteration_info,$iteration_tae_list){
    foreach($iteration_tae_list as $itl_key => $itl_val){
        $m = 0;
        foreach ($itl_val as $iv_key => $iv_val) {
            foreach($iv_val as $ivv_key => $ivv_val){
                $m++;
                if(isset($GLOBALS['FINISHED_STATUS'][$ivv_val['STATUS']])){
                    $finished_task_records_daily[$itl_key][$ivv_val['FINISH_DATE']][] = $ivv_val;
                }
            }
        }
        ksort($finished_task_records_daily[$itl_key]);
        $task_total_count[$itl_key] = $m;

        $date_from = $project_iteration_info[$itl_key]['START'];
        $date_to   = $project_iteration_info[$itl_key]['END'];

        $iteration_remain_count = array();
        $actual_remain_count    = array();

        $task_actual_count     = $task_total_count[$itl_key];
        $first_task_day = key($finished_task_records_daily[$itl_key]);

        $task_total_working_day = getWorkingDays($date_from, $date_to);

        $iteration_remain_count[date("Y-m-d",strtotime($date_from." -1 day"))][] = $task_total_count[$itl_key];
        $actual_remain_count[date("Y-m-d",strtotime($date_from." -1 day"))][]    = $task_total_count[$itl_key];
        $real_date = $date_from;
        if(!empty($task_count_all_day)){
            if($real_date < $first_task_day){
                 while($real_date < $first_task_day){
                    $actual_remain_count[$real_date][]  = $task_actual_count;
                    $task_actual_working_day            = getWorkingDays($date_from, $real_date);
                    $iteration_remain_count[date("Y-m-d",strtotime($real_date))][] = (int)($task_total_count[$itl_key]/$task_total_working_day*($task_total_working_day-$task_actual_working_day));
                    $real_date = date("Y-m-d",strtotime($real_date." +1 day"));
                }
            }
        }
        foreach ($finished_task_records_daily[$itl_key] as $ftrd_key => $ftrd_val) {
            while($real_date <= $ftrd_key){
                if($real_date == $ftrd_key){
                    $actual_remain_count[$ftrd_key][] = $task_actual_count - count($ftrd_val);
                    $task_actual_count                = $task_actual_count - count($ftrd_val);
                } else {
                    $actual_remain_count[$real_date][] = $task_actual_count;
                }
                if($real_date <= $date_to){
                    $task_actual_working_day = getWorkingDays($date_from, $real_date);
                    $iteration_remain_count[date("Y-m-d",strtotime($real_date))][] = (int)($task_total_count[$itl_key]/$task_total_working_day*($task_total_working_day-$task_actual_working_day));
                }
                $real_date = date("Y-m-d",strtotime($real_date." +1 day"));
            }
        }  
        if($real_date <= $date_to){
            while($real_date<=$date_to){
                $task_actual_working_day = getWorkingDays($date_from, $real_date);
                $iteration_remain_count[date("Y-m-d",strtotime($real_date))][] = (int)($task_total_count[$itl_key]/$task_total_working_day*($task_total_working_day-$task_actual_working_day));
                $real_date = date("Y-m-d",strtotime($real_date." +1 day"));
            }
        }
        $project_actual_remain[$itl_key] = array(
            'project' => $iteration_remain_count,
            'actual' => $actual_remain_count
            );
    }
    return $project_actual_remain;
}

function iteration_manday(){

}

/*
//2013-9-9
//list project day's count and total count
 function list_task_count_project($oid,$date_from,$date_to){
     if(($date_from == "")||($date_to == "")){
         $date_from = "2000-01-01";
         $date_to   = $GLOBALS['today'];
     }
     $qry_tae_status_count="SELECT COUNT(tae.`STATUS`) AS COUNT
                         FROM task_assignment_execution as tae, iteration_info as ii, project_info as pi
                         WHERE tae.iteration_id = ii.iteration_id AND ii.FK_PROJECT_ID=pi.ID and pi.ID in ('$oid') 
                         and tae.ASSIGN_DATE >='$date_from' AND tae.FINISH_DATE <='$date_to' and tae.CORRECT = 'N' and tae.ASSIGN_DATE <= tae.FINISH_DATE;";
     $rst_tsc_info  = $GLOBALS['db']->query($qry_tae_status_count);
     $tsc_info_temp = $rst_tsc_info->fetch_all(MYSQLI_ASSOC);
     return $tsc_info_temp;
 }

 function list_task_count_per_day($oid,$date_from,$date_to){
     if(($date_from == "")||($date_to == "")){
         $date_from = "2000-01-01";
         $date_to   = $GLOBALS['today'];
     }
     $qry_tae_status_count="SELECT COUNT(tae.`STATUS`) AS COUNT,FINISH_DATE 
                         FROM task_assignment_execution as tae, iteration_info as ii, project_info as pi,task_status_list AS tsl 
                         WHERE tae.iteration_id = ii.iteration_id AND ii.FK_PROJECT_ID=pi.ID and tae.`STATUS`=tsl.ID and 
                         pi.ID in ('$oid') and tae.ASSIGN_DATE >='$date_from'AND tae.FINISH_DATE <='$date_to' and tae.ASSIGN_DATE <= tae.FINISH_DATE
                         and tsl.ID not in ({$GLOBALS['UNFINISHED_STATUS_STRING']}) AND CORRECT = 'N' group by tae.FINISH_DATE ORDER BY tae.FINISH_DATE;";
     $rst_tsc_info  = $GLOBALS['db']->query($qry_tae_status_count);
     $tsc_info_temp = $rst_tsc_info->fetch_all(MYSQLI_ASSOC);
     return $tsc_info_temp;
 }
//list iteration day's count and total count
 function list_task_count_iteration($iteration_id){
     $qry_tae_status_count="SELECT COUNT(*) AS COUNT FROM task_assignment_execution AS tae WHERE tae.iteration_id IN ('$iteration_id') AND CORRECT = 'N' ";   
     $rst_tsc_info  = $GLOBALS['db']->query($qry_tae_status_count);
     $tsc_info_temp = $rst_tsc_info->fetch_all(MYSQLI_ASSOC);
     return $tsc_info_temp;
 }

 function list_task_count_iteration_per_day($iteration_id,$date_from,$date_to){
     $qry_tae_status_count="SELECT COUNT(tae.`STATUS`) AS COUNT,FINISH_DATE 
                         FROM task_assignment_execution AS tae
                         WHERE tae.iteration_id in ('$iteration_id') and tae.ASSIGN_DATE >='$date_from'AND tae.FINISH_DATE <='$date_to' 
                         AND tae.ASSIGN_DATE <= tae.FINISH_DATE and tae.`STATUS` not IN ('','1','2','5','10') AND CORRECT = 'N' group by tae.FINISH_DATE ORDER BY tae.FINISH_DATE;";
     $rst_tsc_info  = $GLOBALS['db']->query($qry_tae_status_count);
     $tsc_info_temp = $rst_tsc_info->fetch_all(MYSQLI_ASSOC);
     return $tsc_info_temp; 
 }
 */

?>

