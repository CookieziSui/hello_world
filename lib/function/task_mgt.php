<?php
require_once __DIR__ . '../../../mysql.php';
require_once __DIR__ . '/../../lib/function/org_mgt.php';
require_once __DIR__ . '/../../lib/inc/constant_org.php';
require_once __DIR__ . '/../../lib/function/log.php';
require_once __DIR__ . '/../../lib/function/escape_special_character.php';

/**
 * Author: CC
 * [create_iteration Create a new iteration]
 * Last Modified: 08/12/2013
 * Due to split org and project, rename the field ORG_ID to FK_PROJECT_ID
 * @param  [type] $iteration_info [description]
 * @return [type]                 [description]
 */
function create_iteration($iteration_info) {
    $qry_new_iteration = "INSERT INTO iteration_info (ITERATION_NAME,FK_PROJECT_ID,CREATOR,EXPECTED_START,EXPECTED_END,ESTIMATED_START,ESTIMATED_END,ACTUAL_START,ACTUAL_END,STATUS,COMMENT) VALUES ('" . $iteration_info['iteration_name'] . "','" . $iteration_info['org_id'] . "','" . $iteration_info['creator'] . "',NULLIF('" . $iteration_info['expected_start'] . "',''),NULLIF('" . $iteration_info['expected_end'] . "',''),NULLIF('" . $iteration_info['estimated_start'] . "',''),NULLIF('" . $iteration_info['estimated_end'] . "',''),NULLIF('" . $iteration_info['actual_start'] . "',''),NULLIF('" . $iteration_info['actual_end'] . "',''),'" . $iteration_info['status'] . "',NULLIF('" . $iteration_info['comment'] . "',''));";
    $rst_new_iteration = $GLOBALS['db']->query($qry_new_iteration);
}

/**
 * Author: CC
 * Last Modified: 08/07/2013
 * [update_iteration Update the specific iteration]
 * @param  [array] $iteration_info [array of iteration info]
 */
function update_iteration($iteration_info) {
    $update_iteration = "UPDATE iteration_info SET ITERATION_NAME='" . $iteration_info['iteration_name'] . "',EXPECTED_START=NULLIF('" . $iteration_info['expected_start'] . "',''),EXPECTED_END=NULLIF('" . $iteration_info['expected_end'] . "',''),ESTIMATED_START=NULLIF('" . $iteration_info['estimated_start'] . "',''),ESTIMATED_END=NULLIF('" . $iteration_info['estimated_end'] . "',''),ACTUAL_START=NULLIF('" . $iteration_info['actual_start'] . "',''),ACTUAL_END=NULLIF('" . $iteration_info['actual_end'] . "',''),STATUS='" . $iteration_info['status'] . "',COMMENT=NULLIF('" . $iteration_info['comment'] . "','') WHERE ITERATION_ID='" . $iteration_info['iteration_id'] . "';";
    $rst_update_iteration = $GLOBALS['db'] ->query($update_iteration);
}


/*
 *  Query iteration info by org id
 *  query_iteration_info($oid,$status)
 *  $status: 1 for active, 0 for inactive
 */

function query_iteration_info($pid) {
    $qry_itera = "SELECT ITERATION_ID,ITERATION_NAME,EXPECTED_START,EXPECTED_END,ESTIMATED_START,ESTIMATED_END,ACTUAL_START,ACTUAL_END,`STATUS`,COMMENT FROM iteration_info WHERE FK_PROJECT_ID='$pid' ORDER BY EXPECTED_START DESC";
    $rst_itera = $GLOBALS['db']->query($qry_itera);
    $iteration_info_original = $rst_itera->fetch_all(MYSQLI_ASSOC);
    if (!empty($iteration_info_original)) {
        foreach ($iteration_info_original as $iio_key => $iio_val) {
            $iteration_info_status[$iio_val['STATUS']][] = $iio_val;
        }
        $iteration_info = array(
            'original' => $iteration_info_original,
            'status' => $iteration_info_status
        );
        return $iteration_info;
    } else {
        return false;
    }
}

/*
 * display iteration info
 */

function display_iteration_info($iteration_info,$permission,$oid,$su_id,$gtype,$optype) {
    ?>
    <table class="table table-striped">
        <input type="hidden" name="oid" value="<?php echo $oid; ?>">
        <input type="hidden" name="su_id" value="<?php echo $su_id; ?>">
        <input type="hidden" name="gtype" value="<?php echo $gtype; ?>">
        <caption><h4>Iteration Info</h4></caption>
        <thead>
            <tr>
                <th><input type='checkbox' id='check_all'></th>
                <th>No</th>
                <th>Name</th>
                <th colspan="2">Expected Start/End</th>
                <th colspan="2">Estimate Start/End</th>
                <th colspan="2">Actual Start/End</th>
                <th>Status</th>
                <?php
                if ($permission == $GLOBALS['WRITE']) {
                    echo "<th></th>";
                }
                ?>
            </tr>
        </thead>
        <?php
        foreach ($iteration_info as $si_key => $si_val) {
            $short_iteration_title = substr($si_val['ITERATION_NAME'], 0, 30);
            ?>
            <tr>
                <td><input type="checkbox" name="iteration_id[]" value="<?php echo $si_val['ITERATION_ID']; ?>"></td>
                <td><?php echo ($si_key + 1) ?></td>
                <td title="<?php echo $si_val['ITERATION_NAME']; ?>"><?php echo $short_iteration_title; ?></td>
                <td><?php echo ($si_val['EXPECTED_START'] == "0000-00-00" || $si_val['EXPECTED_START'] == NULL) ? "" : date("m/d/y", strtotime($si_val['EXPECTED_START'])); ?></td>
                <td><?php echo ($si_val['EXPECTED_END'] == "0000-00-00" || $si_val['EXPECTED_END'] == NULL) ? "" : date("m/d/y", strtotime($si_val['EXPECTED_END'])); ?></td>
                <td><?php echo ($si_val['ESTIMATED_START'] == "0000-00-00" || $si_val['ESTIMATED_START'] == NULL) ? "" : date("m/d/y", strtotime($si_val['ESTIMATED_START'])); ?></td>
                <td><?php echo ($si_val['ESTIMATED_END'] == "0000-00-00" || $si_val['ESTIMATED_END'] == NULL) ? "" : date("m/d/y", strtotime($si_val['ESTIMATED_END'])); ?></td>
                <td><?php echo ($si_val['ACTUAL_START'] == "0000-00-00" || $si_val['ACTUAL_START'] == NULL) ? "" : date("m/d/y", strtotime($si_val['ACTUAL_START'])); ?></td>
                <td><?php echo ($si_val['ACTUAL_END'] == "0000-00-00" || $si_val['ACTUAL_END'] == NULL) ? "" : date("m/d/y", strtotime($si_val['ACTUAL_END'])); ?></td>
                <td><?php echo $si_val['STATUS'] == 1 ? "<span class='badge badge-success'>Active</span>" : "<span class='badge badge-important'>Inactive</span>"; ?></td>
                <?php
                if ($permission == $GLOBALS['WRITE']) {
                    ?>
                    <td>
                        <?php
                        $parm_string = $si_val['ITERATION_ID'] . "@|" . $si_val['ITERATION_NAME'] . "@|" . $si_val['EXPECTED_START'] . "@|" . $si_val['EXPECTED_END'] . "@|" . $si_val['ESTIMATED_START'] . "@|" . $si_val['ESTIMATED_END'] . "@|" . $si_val['ACTUAL_START'] . "@|" . $si_val['ACTUAL_END'] . "@|" . $si_val['STATUS'] . "@|" . $si_val['COMMENT'];
                        $parm = base64_encode($parm_string);
                        ?>
                        <a href='../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&optype=<?php echo base64_encode($optype); ?>&url=<?php echo base64_encode("task/update_iteration.php"); ?>&gtype=<?php echo base64_encode($gtype); ?>&type=edit&parm=<?php echo $parm; ?>'><img width='16' height='16' src='../../../lib/image/icons/edit.png'/></a>&nbsp;&nbsp;
                        <a onclick="return delete_item()" href='../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&optype=<?php echo base64_encode($optype); ?>&url=<?php echo base64_encode("task/update_iteration.php"); ?>&gtype=<?php echo base64_encode($gtype); ?>&type=delete&parm=<?php echo $parm; ?>'><img width='16' height='16' src='../../../lib/image/icons/remove.png'/></a>
                    </td><?php } ?>
            </tr>
            <?php
        }
        ?>	
    </table>
    <?php
}

/*
 * Query iteration info with iteration id
 */

function iteration_info($iteration_id) {
    $qry_iteration_info = "SELECT ITERATION_NAME FROM iteration_info WHERE ITERATION_ID='$iteration_id';";
    $rst_iteration_info = $GLOBALS['db']->query($qry_iteration_info);
    $iteration_info = $rst_iteration_info->fetch_all(MYSQLI_ASSOC);
    return $iteration_info;
}

/*
 * Delete the specific iteration
 */

function delete_iteration($iteration_id) {
    $qry_del_iteration = "DELETE FROM iteration_info WHERE ITERATION_ID='" . $iteration_id . "'";
    $rst_del_iteration = $GLOBALS['db']->query($qry_del_iteration);
}

/**
 * Author: CC
 * Date: 08/07/2013
 * Last Modified: 08/07/2013
 * [case_id Test Case in local Case Library]
 * @return [type] [description]
 */
function case_id(){
    $case_library    = "select DISTINCT CASE_ID from case_library";
    $rst_cl          = $GLOBALS['db']->query($case_library);
    $task_id_cl_temp = $rst_cl->fetch_all(MYSQLI_ASSOC); 
    foreach ($task_id_cl_temp as $tict_key => $tict_val) {
        /**
         * When ading cases, will use isset(array[key]) to check whether the inputed case is in case ibrary,
         * here transfer all case id to upper case as the KEY
         */
        $task_id_cl[strtoupper($tict_val['CASE_ID'])] = $tict_val['CASE_ID'];
    }
    return $task_id_cl;
}

/**
 * Author: CC
 * Date: 08/07/2013
 * Last Modified: 08/07/2013
 * [case_id_cpts Test case id of CPTS, pre-get in local DB]
 * @return [type] [description]
 */
function case_id_cpts(){
    $qry_tc_cpts  = "SELECT TESTCASE_ID FROM testcase_id";
    $rst_tc_cpts  = $GLOBALS['db']->query($qry_tc_cpts);
    $tc_cpts_temp = $rst_tc_cpts->fetch_all(MYSQLI_ASSOC);
    foreach ($tc_cpts_temp as $tct_key => $tct_val) {
        /**
         * When ading cases, will use isset(array[key]) to check whether the inputed case is one of CPTS case,
         * here transfer all case id to upper case as the KEY
         */
        $tc_cpts[strtoupper($tct_val['TESTCASE_ID'])] = $tct_val['TESTCASE_ID'];
    }
    return $tc_cpts;
}


/*
 * Search task id in local case library
 */

function query_local_task_library($task_id) {
    $qry_local_task_library = "SELECT CASE_ID FROM case_library WHERE CASE_ID='$task_id'";
    $rst_local_task_library = $GLOBALS['db']->query($qry_local_task_library);
    $local_task_library_result = $rst_local_task_library->fetch_all(MYSQLI_ASSOC);
    return $local_task_library_result;
}

/*
 * Locally, there is a table used for testcase id get from CPTS named testcase_id
 * Search task id in local cpts library using task id
 */

function query_cpts_task_library($task_id) {
    $qry_cpts_task_library = "SELECT TESTCASE_ID FROM testcase_id WHERE TESTCASE_ID='$task_id'";
    $rst_cpts_task_library = $GLOBALS['db']->query($qry_cpts_task_library);
    $cpts_task_id_result = $rst_cpts_task_library->fetch_all(MYSQLI_ASSOC);
    return $cpts_task_id_result;
}

/**
 * Author: CC
 * Date: 08/07/2013
 * Last Modified: 08/07/2013
 * [insert_new_task_cl Insrt new tasks into case library,Only for other tasks (Not case)]
 * @param  [array] $task [array of tasks]
 * @return NULL
 */
function insert_new_task_cl($task) {
    foreach ($task as $ot_val) {
        $array_other_task[] = "('2','$ot_val','$ot_val')";
    }
    $qry_insert_case = "INSERT INTO case_library (TASK_TYPE,CASE_ID,SCENARIO_ID) VALUES ".implode(',', $array_other_task).";";
    $rst_insert_case = $GLOBALS['db']->query($qry_insert_case);
}

/*
 * Get task info through task_no
 */

function get_task_info_no($task_no) {
    $query = "SELECT * FROM task_assignment_execution WHERE NO='" . $task_no . "'";
    $result = $GLOBALS['db']->query($query);
    $row = $result->fetch_assoc();
    return $row;
}

/*
 * Get support info through task_no
 */

//function get_support_info_no($task_no) {
//    $qry_support_info = "SELECT ID,TASK_ID,SUPPORTER,TIME,DATE,SUBMITTER,COMMENT FROM task_support_info WHERE TASK_ID='" . $task_no . "'";
//    $rst_support_info = $GLOBALS['db']->query($qry_support_info);
//    $support_info = $rst_support_info->fetch_all(MYSQLI_ASSOC);
//    return $support_info;
//}

/**
 * Author: CC
 * Date: 08/07/2013
 * [get_task_info_tae Query task_info through the specific sequence_id]
 * @param  [array] $sequence_id [array of target tasks' sequence id]
 * @return [array]              [array of target tasks' info]
 */
function get_task_info_tae($sequence_id) {
    $qry_task_info_tae = "SELECT TASK_TYPE,TASK_ID,TASK_TITLE,SCENARIO_ID,SCENARIO_TITLE,EX_SETUP_TIME,EX_EXECUTION_TIME FROM task_assignment_execution WHERE SEQUENCE_ID IN (" . implode(',', $sequence_id) . ")";
    $rst_tak_info_tae = $GLOBALS['db']->query($qry_task_info_tae);
    $task_info_tae = $rst_tak_info_tae->fetch_all(MYSQLI_ASSOC);
    return $task_info_tae;
}

/**
 * Author: CC
 * Date: 08/07/2013
 * [insert_dup_task Insert duplicate task into TAE]
 * @param  [array] $task_info_tae [array of duplicated tasks' info]
 * @param  [int] $dup_copies [Copies of the duplicated tasks]
 * @param  [int] $iteration_id  [description]
 * @return NULL
 */
function insert_dup_task($task_info_tae, $iteration_id, $dup_copies) {
    for($i = 1; $i <= $dup_copies; $i++){
        $date_time = date("Y-m-d H:i:s");
        foreach ($task_info_tae as $tit_key => $tit_val) {
            $sequence_id = md5($i.$date_time.$iteration_id.$tit_val['TASK_ID']);
            $array_tae_record[] = "('".escape_special_character($iteration_id)."','1','".escape_special_character($tit_val['TASK_ID'])."','".escape_special_character($tit_val['TASK_TITLE'])."','".escape_special_character($tit_val['SCENARIO_ID'])."','".escape_special_character($tit_val['SCENARIO_TITLE'])."',NULLIF('".escape_special_character($tit_val['EX_SETUP_TIME'])."',''),NULLIF('".escape_special_character($tit_val['EX_EXECUTION_TIME'])."',''),'".escape_special_character($sequence_id)."')";
        }
    }
    
    $qry_insert_dup_task = "INSERT INTO task_assignment_execution (ITERATION_ID,TASK_TYPE,TASK_ID,TASK_TITLE,SCENARIO_ID,SCENARIO_TITLE,EX_SETUP_TIME,EX_EXECUTION_TIME,SEQUENCE_ID) VALUES ".implode(',', $array_tae_record).";";
    $rst_insert_dup_task = $GLOBALS['db']->query($qry_insert_dup_task);
}

/*
 * Delete task from TAE
 */
/**
 * Author: CC
 * Last Modified: 08/07/2013
 * [delete_task_tae Delete task from TAE]
 * @param  [array] $sequence_id [array of sequence id]
 * @return NULL
 */
function delete_task_tae($sequence_id,$user_id,$login_id,$mark) {
    if($mark == '0'){
        $qry_del_task = "DELETE FROM task_assignment_execution WHERE SEQUENCE_ID IN (".implode(',', $sequence_id).");";
        $db_operation = array(
            'table' => "task_assignment_execution",
            'field' => "SEQUENCE_ID",
            'value' => str_replace("'", "", $sequence_id)
        );
    }else{
        $qry_del_task = "DELETE FROM task_assignment_execution WHERE NO IN (".implode(',', $sequence_id).") and (STATUS is null or STATUS='1');";  
        $db_operation = array(
            'table' => "task_assignment_execution",
            'field' => "No",
            'value' => str_replace("'", "", $sequence_id)
        );
    }
    $rst_del_task = $GLOBALS['db']->query($qry_del_task);
    log_operation($user_id, $login_id, "Task", 1, $db_operation);
}

/**
 * Author: CC
 * Last Modified: 08/07/2013
 * Last Modified: 10/11/2013    CC  Optimized the query, reduce the query time.
 * [get_task_info Query task info for the specific case id]
 * @param  [type] $task_id [description]
 * @return [type]          [description]
 */
function get_task_info($task_id) {
    foreach ($task_id as $ti_key => $ti_val) {
        $task_id_array[] = "'{$ti_val}'";
    }
    $qry_task_info = "SELECT CASE_ID,CASE_TITLE,SCENARIO_ID,SCENARIO_TITLE,SETUP_TIME,EXECUTION_TIME,ESTIMATE_SETUPTIME,ESTIMATE_EXECTIME FROM case_library WHERE CASE_ID in (".implode(',', $task_id_array).");";
    $rst_task_info = $GLOBALS['db']->query($qry_task_info);
    $task_info     = $rst_task_info->fetch_all(MYSQLI_ASSOC);
    return $task_info;
}

/**
 * Author: CC
 * Last Modified: 09/16/2013
 * Print the task info into a table for displaying
 */
function print_task_info($task_info) {
    $flag = NULL;
    foreach ($task_info as $ti_key => $ti_val) {
        if ($ti_val['CASE_ID'] !== $flag) {
            ?>
            <tr>
                <td><input type='checkbox' name='case_id[]' value='<?php echo $ti_val['CASE_ID']; ?>' class='father' id='<?php echo $ti_val['CASE_ID']; ?>'></td>
                <td><?php echo $ti_val['CASE_ID']; ?></td>
                <td colspan='6'><?php echo $ti_val['CASE_TITLE']; ?></td>
            </tr>
            <tr class='child_" . $task_id_upper . "'>
                <td></td>
                <td align='right'>
                    <?php
                    $task_detail_1 = array(
                        'CASE_ID'            => $ti_val['CASE_ID'],
                        'CASE_TITLE'         => $ti_val['CASE_TITLE'],
                        'SCENARIO_ID'        => $ti_val['SCENARIO_ID'],
                        'SCENARIO_TITLE'     => $ti_val['SCENARIO_TITLE'],
                        'ESTIMATE_SETUPTIME' => $ti_val['ESTIMATE_SETUPTIME'],
                        'ESTIMATE_EXECTIME'  => $ti_val['ESTIMATE_SETUPTIME']
                        );
                    echo "<input id='scenario_id' name='scenario_id[]' type='checkbox' value='" .base64_encode(serialize($task_detail_1)). "' class='child_" . $ti_val['CASE_ID'] . "'>";
                    ?>
                    <?php
                    //echo "<input id='scenario_id' name='scenario_id[]' type='checkbox' class='child_" . $ti_val['CASE_ID'] . "'>";
                    ?>
                </td>
                <td><?php echo $ti_val['SCENARIO_ID'] ?></td>
                <td><?php echo utf8_decode($ti_val['SCENARIO_TITLE']) ?></td>
                <td><?php echo $ti_val['ESTIMATE_SETUPTIME'] ?></td>
                <td><?php echo $ti_val['ESTIMATE_EXECTIME'] ?></td>
            </tr>
            <?php
            $flag = $ti_val['CASE_ID'];
        } else {
            ?>
            <tr class='child_" . $task_id_upper . "'>
                <td></td>
                <td align='right'>
                    <?php
                    $task_detail_2 = array(
                        'CASE_ID'            => $ti_val['CASE_ID'],
                        'CASE_TITLE'         => $ti_val['CASE_TITLE'],
                        'SCENARIO_ID'        => $ti_val['SCENARIO_ID'],
                        'SCENARIO_TITLE'     => $ti_val['SCENARIO_TITLE'],
                        'ESTIMATE_SETUPTIME' => $ti_val['ESTIMATE_SETUPTIME'],
                        'ESTIMATE_EXECTIME'  => $ti_val['ESTIMATE_SETUPTIME']
                        );
                    echo "<input id='scenario_id' name='scenario_id[]' type='checkbox' value='" .base64_encode(serialize($task_detail_2)). "' class='child_" . $ti_val['CASE_ID'] . "'>";
                    ?>
                    <?php
                    //echo "<input id='scenario_id' name='scenario_id[]' type='checkbox' class='child_" . $ti_val['CASE_ID'] . "'></td>";
                    ?>
                <td><?php echo $ti_val['SCENARIO_ID'] ?></td>
                <td><?php echo utf8_decode($ti_val['SCENARIO_TITLE']) ?></td>
                <td><?php echo $ti_val['ESTIMATE_SETUPTIME'] ?></td>
                <td><?php echo $ti_val['ESTIMATE_EXECTIME'] ?></td>
            </tr>
            <?php
        }
    }
}

/**
 * Author: CC
 * Last Modified: 09/16/2013
 * Insert the search result (task or case) into task_assignment_execution 
 */
function insert_tae($iteration_id, $task_info) {
    $date_time = date("Y-m-d H:i:s");
    foreach($iteration_id as $ii_val){
        foreach($task_info as $ti_val){
            $array_task_info = unserialize(base64_decode($ti_val));
            $sequence_id = md5($date_time.$ii_val.$array_task_info['CASE_ID']);
            $array_tae_record[] = "('$ii_val','1','".$array_task_info['CASE_ID']."','".escape_special_character($array_task_info['CASE_TITLE'])."','".$array_task_info['SCENARIO_ID']."','".escape_special_character($array_task_info['SCENARIO_TITLE'])."',NULLIF('".$array_task_info['ESTIMATE_SETUPTIME']."',''),NULLIF('".$array_task_info['ESTIMATE_EXECTIME']."',''),'$sequence_id')";
        }
    }    
    $qry_insert_tae = "INSERT INTO task_assignment_execution (ITERATION_ID,TASK_TYPE,TASK_ID,TASK_TITLE,SCENARIO_ID,SCENARIO_TITLE,EX_SETUP_TIME,EX_EXECUTION_TIME,SEQUENCE_ID) VALUES ".implode(',', $array_tae_record).";";
    $rst_insert_tae = $GLOBALS['db']->query($qry_insert_tae);
}

/**
 * Author: CC
 * Date: 08/07/2013
 * [available_iteration_list All available iteration of the org. Options of "Duplicate to"]
 * @param  [int] $pid [project]
 * @return [type]      [description]
 */
function available_iteration_list($pid) {
    $qry_iteration_list = "SELECT ITERATION_ID,ITERATION_NAME FROM iteration_info WHERE FK_PROJECT_ID='$pid' AND `STATUS`='1';";
    $rst_iteration_list = $GLOBALS['db']->query($qry_iteration_list);
    $available_iteration_list = $rst_iteration_list->fetch_all(MYSQLI_ASSOC);
    return $available_iteration_list;
}

/*
 * List all available iteration and tasks no matter what the status are
 */

function list_org_task_withdate($org_string, $date_from, $date_to) {
    $qry_iteration_task = "SELECT ii.ORG_ID,tae.ITERATION_ID ITERATION_ID,ITERATION_NAME, SEQUENCE_ID, NO, TASK_ID, TASK_TITLE, SCENARIO_ID, SCENARIO_TITLE,ASSIGN_DATE, FINISH_DATE,ENVIRONMENT, BUILD_INFO, TESTER,EX_SETUP_TIME,EX_EXECUTION_TIME,AC_SETUP_TIME, AC_EXECUTION_TIME, INVESTIGATE_TIME, tae.COMMENTS,CORRECT, tae.STATUS as STATUS , tae.BUG_INFO as BUG_INFO FROM task_assignment_execution as tae, iteration_info as ii, org_info as oi WHERE ii.ORG_ID in ($org_string) AND CORRECT='N' AND tae.iteration_id = ii.iteration_id AND ii.ORG_id=oi.ORG_ID AND (ASSIGN_DATE BETWEEN '$date_from' AND '$date_to') ORDER BY ITERATION_NAME, TASK_ID,SCENARIO_ID,TESTER ASC;";
    $rst_iteration_task = $GLOBALS['db']->query($qry_iteration_task);
    $iteration_task = $rst_iteration_task->fetch_all(MYSQLI_ASSOC);
    if (empty($iteration_task)) {
        $org_task = array();
    } else {
        foreach ($iteration_task as $it_key => $it_val) {
            $org_task[$it_val['ORG_ID']][] = $it_val;
        }
    }
    return $org_task;
}

/**
 * Author: CC
 * Last Modified: 08/0/2013
 * [list_finished_org_task_withdate List all available iteration and tasks where the tasks are finished]
 * @param  [string] $project_string [description]
 * @param  [date] $date_from      [description]
 * @param  [date] $date_to        [description]
 * @return [array]                 [description]
 */
function list_finished_project_task_withdate($project_string, $date_from, $date_to) {
    $qry_iteration_task = "SELECT ii.FK_PROJECT_ID,tae.ITERATION_ID ITERATION_ID, ITERATION_NAME, SEQUENCE_ID, NO, TASK_ID, TASK_TITLE, SCENARIO_ID, SCENARIO_TITLE, ASSIGN_DATE, FINISH_DATE,ENVIRONMENT, BUILD_INFO, TESTER,EX_SETUP_TIME,EX_EXECUTION_TIME,AC_SETUP_TIME, AC_EXECUTION_TIME, INVESTIGATE_TIME, tae.COMMENTS,CORRECT, tae.STATUS as STATUS , tae.BUG_INFO as BUG_INFO FROM task_assignment_execution as tae, iteration_info as ii, project_info as pi WHERE ii.FK_PROJECT_ID in ($project_string) AND CORRECT='N' AND tae.iteration_id = ii.iteration_id AND ii.FK_PROJECT_ID=pi.ID AND (FINISH_DATE BETWEEN '$date_from' AND '$date_to') ORDER BY ITERATION_NAME, TASK_ID,SCENARIO_ID,TESTER ASC;";
    $rst_iteration_task = $GLOBALS['db']->query($qry_iteration_task);
    $iteration_task = $rst_iteration_task->fetch_all(MYSQLI_ASSOC);
    if (empty($iteration_task)) {
        $finished_org_task = array();
    } else {
        foreach ($iteration_task as $it_key => $it_val) {
            $finished_org_task[$it_val['FK_PROJECT_ID']][] = $it_val;
        }
    }
    return $finished_org_task;
}

/**
 * Author: CC
 * Last Modified: 08/08/2013
 * [list_unfinished_org_task List all available iteration and tasks where the tasks are unfinished]
 * @param  [string] $project_string [description]
 * @return [array]                 [description]
 */
function list_unfinished_project_task($project_string) {
    $qry_iteration_task = "SELECT ii.FK_PROJECT_ID,tae.ITERATION_ID ITERATION_ID,ITERATION_NAME, SEQUENCE_ID, NO, TASK_ID, TASK_TITLE, SCENARIO_ID, SCENARIO_TITLE,ASSIGN_DATE, FINISH_DATE,ENVIRONMENT, BUILD_INFO, TESTER,EX_SETUP_TIME,EX_EXECUTION_TIME,AC_SETUP_TIME, AC_EXECUTION_TIME, INVESTIGATE_TIME, tae.COMMENTS,CORRECT, tae.STATUS as STATUS , tae.BUG_INFO as BUG_INFO FROM task_assignment_execution as tae, iteration_info as ii, project_info as pi WHERE ii.FK_PROJECT_ID in ($project_string) AND CORRECT='N' AND tae.iteration_id = ii.iteration_id AND ii.FK_PROJECT_ID=pi.ID AND ((tae.STATUS IS NULL) OR tae.STATUS='1') ORDER BY ITERATION_NAME, TASK_ID,SCENARIO_ID,TESTER ASC;";
    $rst_iteration_task = $GLOBALS['db']->query($qry_iteration_task);
    $iteration_task = $rst_iteration_task->fetch_all(MYSQLI_ASSOC);
    if (empty($iteration_task)) {
        $unfinished_org_task = array();
    } else {
        foreach ($iteration_task as $it_key => $it_val) {
            $unfinished_org_task[$it_val['FK_PROJECT_ID']][] = $it_val;
        }
    }
    return $unfinished_org_task;
}

/**
 * Author: CC
 * Last Modified: 08/08/2013
 * [list_org_task_withdate_by_iteration List all available iteration and tasks no matter what the status are. Seperated  by iteration]
 * @param  [type] $projects  [description]
 * @param  [type] $date_from [description]
 * @param  [type] $date_to   [description]
 * @return [type]            [description]
 */
function list_org_task_withdate_by_iteration($projects, $date_from, $date_to) {
    $string_projects = op_string($projects);
    $qry_iteration_task = "SELECT ii.FK_PROJECT_ID,tae.ITERATION_ID ITERATION_ID,ITERATION_NAME, SEQUENCE_ID, NO, TASK_ID, TASK_TITLE, SCENARIO_ID, SCENARIO_TITLE,ASSIGN_DATE, FINISH_DATE,ENVIRONMENT, BUILD_INFO, TESTER,EX_SETUP_TIME,EX_EXECUTION_TIME,AC_SETUP_TIME, AC_EXECUTION_TIME, INVESTIGATE_TIME, tae.COMMENTS,CORRECT, tae.STATUS as STATUS , tae.BUG_INFO as BUG_INFO FROM task_assignment_execution as tae, iteration_info as ii, project_info as pi WHERE ii.FK_PROJECT_ID in ($string_projects) AND CORRECT='N' AND tae.iteration_id = ii.iteration_id AND ii.FK_PROJECT_ID=pi.ID AND (FINISH_DATE BETWEEN '$date_from' AND '$date_to') ORDER BY ITERATION_NAME, TASK_ID,SCENARIO_ID,TESTER ASC;";
    $rst_iteration_task = $GLOBALS['db']->query($qry_iteration_task);
    $iteration_task = $rst_iteration_task->fetch_all(MYSQLI_ASSOC);
    if (empty($iteration_task)) {
        $org_iteration_task = array();
    } else {
        foreach ($iteration_task as $it_key => $it_val) {
            $org_iteration_task['TASK'][$it_val['ITERATION_ID']][] = $it_val;
            $org_iteration_task['ITERATION_LIST'][$it_val['ITERATION_ID']] = $it_val['ITERATION_NAME'];
        }
    }
    return $org_iteration_task;
}

/**
 * Author: CC
 * Last Modified: 08/07/2013
 * [list_iteration_task_notdone List all available iteration and tasks with status "Unassign" and "Assigned"]
 * @param  [int] $pid                  [project id]
 * @param  [array] $TASK_STATUS_ASSIGNED [description]
 * @return [array]                       [array of tasks not done]
 */
function list_iteration_task_notdone($pid, $TASK_STATUS_ASSIGNED) {
    $qry_iteration_task_notdone = "SELECT tae.ITERATION_ID ITERATION_ID,ITERATION_NAME, SEQUENCE_ID, NO, TASK_ID, TASK_TITLE, SCENARIO_ID, SCENARIO_TITLE,ASSIGN_DATE,FINISH_DATE, ENVIRONMENT, BUILD_INFO, TESTER,EX_SETUP_TIME,EX_EXECUTION_TIME,AC_SETUP_TIME, AC_EXECUTION_TIME, INVESTIGATE_TIME, tae.COMMENTS,CORRECT, tae.STATUS as STATUS, tae.BUG_INFO as BUG_INFO 
    FROM task_assignment_execution as tae, iteration_info as ii, project_info as pi 
    WHERE ii.FK_PROJECT_ID='$pid' AND CORRECT='N' AND tae.iteration_id = ii.iteration_id AND ii.FK_PROJECT_ID=pi.ID AND (tae.STATUS is null OR tae.STATUS='$TASK_STATUS_ASSIGNED') ORDER BY TASK_ID ASC,SEQUENCE_ID,SCENARIO_ID ASC,STATUS ASC,TESTER ASC;";
    $rst_iteration_task_notdone = $GLOBALS['db']->query($qry_iteration_task_notdone);
    $iteration_task_notdone_temp = $rst_iteration_task_notdone->fetch_all(MYSQLI_ASSOC);
    if (!empty($iteration_task_notdone_temp)) {
        foreach ($iteration_task_notdone_temp as $itn_key => $itn_val) {
            $iteration_task_notdone['TASK'][$itn_val['ITERATION_ID']][] = $itn_val;
            $iteration_task_notdone['ITERATION_LIST'][$itn_val['ITERATION_ID']] = $itn_val['ITERATION_NAME'];
        }
    } else {
        $iteration_task_notdone['TASK'] = array();
        $iteration_task_notdone['ITERATION_LIST'] = array();
    }
    return $iteration_task_notdone;
}

/**
 * Author: CC
 * Last Modified: 08/07/2013
 * [personal_iteration_task_notdone List all available iteration and tasks under the current user name]
 * @param  [int] $pid                     [project id]
 * @param  [int] $user_id                 [user id]
 * @param  [array] $TASK_STATUS_ASSIGNED    [description]
 * @param  [array] $TASK_STATUS_INPROGRESS  [description]
 * @param  [array] $TASK_STATUS_ACTIVEQUERY [description]
 * @return [array]                          [description]
 */
function personal_iteration_task_notdone($pid, $user_id, $TASK_STATUS_ASSIGNED, $TASK_STATUS_INPROGRESS, $TASK_STATUS_ACTIVEQUERY) {
    $qry_personal_iteration_task_notdone = "SELECT tae.ITERATION_ID ITERATION_ID,ITERATION_NAME, SEQUENCE_ID, NO, TASK_ID, TASK_TITLE, SCENARIO_ID, SCENARIO_TITLE,ASSIGN_DATE,FINISH_DATE, ENVIRONMENT, BUILD_INFO, TESTER,EX_SETUP_TIME,EX_EXECUTION_TIME,AC_SETUP_TIME, AC_EXECUTION_TIME, INVESTIGATE_TIME, tae.COMMENTS,CORRECT, tae.STATUS as STATUS, tae.BUG_INFO as BUG_INFO FROM task_assignment_execution as tae, iteration_info as ii, project_info as pi 
    WHERE ii.FK_PROJECT_ID='$pid' AND CORRECT='N' AND tae.iteration_id = ii.iteration_id AND ii.FK_PROJECT_ID=pi.ID AND tae.TESTER='$user_id' AND (tae.STATUS ='$TASK_STATUS_ASSIGNED' OR tae.STATUS ='$TASK_STATUS_INPROGRESS' OR tae.STATUS='$TASK_STATUS_ACTIVEQUERY') ORDER BY TASK_ID ASC,SEQUENCE_ID,SCENARIO_ID ASC,STATUS ASC,TESTER ASC;";
    $rst_personal_iteration_task_notdone = $GLOBALS['db']->query($qry_personal_iteration_task_notdone);
    $personal_iteration_task_notdone_temp = $rst_personal_iteration_task_notdone->fetch_all(MYSQLI_ASSOC);
    if (!empty($personal_iteration_task_notdone_temp)) {
        foreach ($personal_iteration_task_notdone_temp as $pitn_key => $pitn_val) {
            $personal_iteration_task_notdone['TASK'][$pitn_val['ITERATION_ID']][] = $pitn_val;
            $personal_iteration_task_notdone['ITERATION_LIST'][$pitn_val['ITERATION_ID']] = $pitn_val['ITERATION_NAME'];
        }
    } else {
        $personal_iteration_task_notdone['TASK'] = array();
        $personal_iteration_task_notdone['ITERATION_LIST'] = array();
    }
    return $personal_iteration_task_notdone;
}

/*
 * List all available iteration and tasks under the current user name accord to the specific date
 */

function personal_iteration_task_withdate($user_id, $date_from, $date_to) {
    $qry_personal_iteration_task = "SELECT tae.ITERATION_ID ITERATION_ID,ITERATION_NAME, SEQUENCE_ID, NO, TASK_ID, TASK_TITLE, SCENARIO_ID, SCENARIO_TITLE,ASSIGN_DATE,FINISH_DATE, ENVIRONMENT, BUILD_INFO, TESTER,EX_SETUP_TIME,EX_EXECUTION_TIME,AC_SETUP_TIME, AC_EXECUTION_TIME, INVESTIGATE_TIME, tae.COMMENTS,CORRECT, tae.STATUS as STATUS, tae.BUG_INFO as BUG_INFO FROM task_assignment_execution as tae, iteration_info as ii, project_info as pi 
    WHERE  CORRECT='N' AND tae.iteration_id = ii.iteration_id AND ii.FK_PROJECT_id=pi.ID AND tae.TESTER='$user_id' AND (tae.FINISH_DATE BETWEEN '$date_from' AND '$date_to') ORDER BY ITERATION_NAME, TASK_ID,SCENARIO_ID,TESTER ASC;";
    $rst_personal_iteration_task = $GLOBALS['db']->query($qry_personal_iteration_task);
    $personal_iteration_task_temp = $rst_personal_iteration_task->fetch_all(MYSQLI_ASSOC);

    if (empty($personal_iteration_task_temp)) {
        $personal_iteration_task = array();
    } else {
        foreach ($personal_iteration_task_temp as $pitt_key => $pitt_val) {
            $personal_iteration_task['TASK'][$pitt_val['ITERATION_ID']][] = $pitt_val;
            $personal_iteration_task['ITERATION_LIST'][$pitt_val['ITERATION_ID']] = $pitt_val['ITERATION_NAME'];
        }
    }
    return $personal_iteration_task;
}

/*
 * Push  all the task of the iteration into table 
 */

function push_into_table($iteration_task, $member_of_org, $TASK_STATUS,$permission) {
    $flag = NULL;
    foreach ($iteration_task as $it_key => $it_val) {
        if ($it_val['SEQUENCE_ID'] !== $flag) {
            task_id($it_val, $member_of_org, $TASK_STATUS,$permission);
            scenario_info($it_val, $member_of_org, $TASK_STATUS,$permission);
            $flag = $it_val['SEQUENCE_ID'];
        } else {
            scenario_info($it_val, $member_of_org, $TASK_STATUS,$permission);
        }
    }
}

/*
 * Print employee into an select
 */

function push_emp_select($member_of_org, $it_val,$permission) {
    foreach ($member_of_org as $moo_key => $moo_val) {
        if($permission == $GLOBALS['WRITE']){
            if ($moo_val['USER_ID'] == $it_val['TESTER']) {
                echo "<option value='" . $moo_val['USER_ID'] . "@@" . $moo_val['USER_NAME'] . "@@" . $moo_val['LEVEL'] . "@@".$moo_val['EMAIL']."' selected>" . $moo_val['USER_NAME'] . "</option>";
            } else {
                echo "<option value='" . $moo_val['USER_ID'] . "@@" . $moo_val['USER_NAME'] . "@@" . $moo_val['LEVEL'] . "@@".$moo_val['EMAIL']."'>" . $moo_val['USER_NAME'] . "</option>";
            }
        }else{
            if ($moo_val['USER_ID'] == $it_val['TESTER']) {
                if(!empty($moo_val['OWNER'])){
                    echo "<option value='" . $moo_val['USER_ID'] . "@@" . $moo_val['USER_NAME'] . "@@" . $moo_val['LEVEL'] . "@@".$moo_val['EMAIL']."' selected>" . $moo_val['OWNER'] . "</option>";
                }else{
                    echo "<option value='" . $moo_val['USER_ID'] . "@@" . $moo_val['USER_NAME'] . "@@" . $moo_val['LEVEL'] . "@@".$moo_val['EMAIL']."' selected>" . $moo_val['NICK_NAME'] . "</option>";
                }
            } else {
                if(!empty($moo_val['OWNER'])){
                    echo "<option value='" . $moo_val['USER_ID'] . "@@" . $moo_val['USER_NAME'] . "@@" . $moo_val['LEVEL'] . "@@".$moo_val['EMAIL']."'>" . $moo_val['OWNER'] . "</option>";
                }else{
                    echo "<option value='" . $moo_val['USER_ID'] . "@@" . $moo_val['USER_NAME'] . "@@" . $moo_val['LEVEL'] . "@@".$moo_val['EMAIL']."'>" . $moo_val['NICK_NAME'] . "</option>";
                }
            }            
        }
        
    }
}

/*
 * Print task summary (task id and name)
 */

function task_id($it_val, $member_of_org, $TASK_STATUS,$permission) {
    
    $short_task_title = substr($it_val['TASK_TITLE'], 0, 30);
    ?>
    <tr class='task_assign_father' >
        <td class="checkbox_td" ><input type="checkbox" value="<?php echo $it_val['SEQUENCE_ID'] ?>" ></td>
        <td class='click_me' title='<?php echo $it_val['TASK_TITLE'] ?>' id='<?php echo $it_val['SEQUENCE_ID'] ?>' colspan="4">         
            <?php echo $it_val['TASK_ID'] . "|" . $short_task_title; ?>
        </td>
        <td>
            <input type='text' id='<?php echo $it_val['SEQUENCE_ID'] ?>' class='span12 task_assign_whole_env_parameter' name='task_assign_whole_env_parameter[]'/>
        </td>
        <td>
            <input type='text' id='<?php echo $it_val['SEQUENCE_ID'] ?>' class='span12 task_assign_whole_build' name='task_assign_whole_build[]'/>
        </td>
        <td>
            <select id='<?php echo $it_val['SEQUENCE_ID'] ?>' class='span12 task_assign_whole_tester chinese' name='whole_tester[]'>
                <option></option>
                <?php
                push_emp_select($member_of_org, $it_val,$permission);
                ?>
            </select>
        </td>

        <td colspan='3' class='chinese' id='<?php echo $it_val['SEQUENCE_ID'] ?>'>
            <span class="badge badge-<?php echo $TASK_STATUS[$it_val['STATUS']]['1'] ?>">
                <?php echo $TASK_STATUS[$it_val['STATUS']]['0'] ?>
            </span>     
        </td>
    </tr>
    <?php
}

/*
 * Print task detail info (scenario)
 */

function scenario_info($it_val, $member_of_org, $TASK_STATUS,$permission) {
    $short_scenario_title = substr($it_val['SCENARIO_TITLE'], 0, 100);
    ?>
    <tr class='child_<?php echo $it_val['SEQUENCE_ID'] ?>' style='display:none'>
        <td>&nbsp;</td>
        <td colspan="4" title="<?php echo $it_val['SCENARIO_ID'] . "|" . $it_val['SCENARIO_TITLE']; ?>">
            <input type="checkbox" name="scenario" value="<?php echo $it_val['NO'].'_1'; ?>" >   
            <?php echo substr($it_val['SCENARIO_ID'], -3) . "|" . $short_scenario_title; ?>
        </td>
    <input type='hidden' name='sequence_id[]' value='<?php echo $it_val['SEQUENCE_ID']; ?>'/>
    <input type='hidden' name='no[]' value='<?php echo $it_val['NO'] ?>'/>
    <input type='hidden' name='task_id[]' value='<?php echo $it_val['TASK_ID']; ?>'/>
    <input type='hidden' name='scenario_id[]' value='<?php echo $it_val['SCENARIO_ID']; ?>'/>
    <input type='hidden' name='ex_setup[]' value='<?php echo $it_val['EX_SETUP_TIME']; ?>'/>
    <input type='hidden' name='ex_execution[]' value='<?php echo $it_val['EX_EXECUTION_TIME']; ?>'/>
    <td>
        <input type='text' class='span12 env env_child_<?php echo $it_val['NO'].'_1';?> env_child_<?php echo $it_val['SEQUENCE_ID']; ?>' id = 'env_<?php echo $it_val['NO'].'_1' ?>' name='env_parameter[]' value="<?php echo $it_val['ENVIRONMENT']; ?>"/>
    </td>
    <td>
        <input type='text' class='span12 build build_child_<?php echo $it_val['NO'].'_1';?> build_child_<?php echo $it_val['SEQUENCE_ID']; ?>' id = 'bld_<?php echo $it_val['NO'].'_1' ?>' name='build_info[]' value="<?php echo $it_val['BUILD_INFO'] ?>"/>
    </td>
    <td>
        <select class='span12 tester select_tester_<?php echo $it_val['NO'].'_1';?> select_tester_<?php echo $it_val['SEQUENCE_ID']; ?> chinese' id = 'tester_<?php echo $it_val['NO'].'_1' ?>' name='tester[]'>
            <option></option>
            <?php
            push_emp_select($member_of_org, $it_val,$permission);
            ?>
        </select>
    </td>
    <td>
        <span class="span12">
            <a><?php echo $it_val['EX_SETUP_TIME']; ?>
                +
                <?php echo $it_val['EX_EXECUTION_TIME']; ?></a>
        </span>
    </td>
    <td>
        <span class='badge badge-<?php echo $TASK_STATUS[$it_val['STATUS']]['1'] ?>'>
            <?php echo $TASK_STATUS[$it_val['STATUS']]['0'] ?>
        </span>
    </td>
    <td>
        <input type="text" name='comments[]' class="span12" value='<?php echo $it_val['COMMENTS'] ?>'/>
    </td>
    </tr>  
    <?php
}

/**
 * Author: CC
 * Last Modified: 08/07/2013
 * [assing_task Assign task into DB]
 * @param  [type] $no           [description]
 * @param  [type] $tester       [description]
 * @param  [type] $tester_level [description]
 * @param  [type] $env          [description]
 * @param  [type] $ex_setup     [description]
 * @param  [type] $ex_execution [description]
 * @param  [type] $bld          [description]
 * @param  [type] $cmt          [description]
 * @return [type]               [description]
 */
function assing_task($no, $tester, $tester_level, $env, $ex_setup, $ex_execution, $bld, $cmt) {
    $qry_assign_task = "UPDATE task_assignment_execution SET TESTER='$tester', LEVEL='$tester_level',ENVIRONMENT='$env', ASSIGN_DATE='".date("Y-m-d")."',BUILD_INFO='$bld',EX_SETUP_TIME=NULLIF('$ex_setup',''),EX_EXECUTION_TIME=NULLIF('$ex_execution',''),STATUS='1',COMMENTS='$cmt' WHERE NO='$no'and (STATUS is null or STATUS='1');";
    $rst_assign_task = $GLOBALS['db']->query($qry_assign_task);
}

/*
 *   @Date: 2013-02-01
 *   @Author: CC
 *   @Description:    
 *       Through task id and tester level,
 *   Get the corresponding task coeffieciency,
 *   @Return: SETUP coeffiiency and EXECUTION coefficiency
 */

function estimated_level_coefficiency_task_id($task_id, $level) {
    $setup_field = $GLOBALS['LEVEL'][$level] . "_SETUP_COEFFICIENCY";
    $execution_field = $GLOBALS['LEVEL'][$level] . "_EXECUTION_COEFFICIENCY";
    $qry_coefficiency = "SELECT $setup_field,$execution_field, ESTIMATE_SETUPTIME, ESTIMATE_EXECTIME FROM task_estimation_baseline,case_library WHERE FK_TASK_ID = SCENARIO_ID AND FK_TASK_ID='$task_id';";
    $rst_coefficiency = $GLOBALS['db']->query($qry_coefficiency);
    $coefficiency_row = $rst_coefficiency->fetch_assoc();

    $setup_coefficiency = $coefficiency_row[$setup_field];
    $execution_coefficiency = $coefficiency_row[$execution_field];
    $general_setup = $coefficiency_row['ESTIMATE_SETUPTIME'];
    $general_execution = $coefficiency_row['ESTIMATE_EXECTIME'];
    $coefficiency = array(
        'SETUP' => $setup_coefficiency,
        'EXECUTION' => $execution_coefficiency,
        'GENERAL_SETUP' => $general_setup,
        'GENERAL_EXECUTION' => $general_execution
    );
    return $coefficiency;
}

/*
 * Push  all the task of the iteration into table for task status
 */

function status_push_into_table($iteration_task, $available_user) {
    $task_scenario         = array();
    $task_scenario_summary = array();
    foreach ($iteration_task as $it_key => $it_val) {
        $task_scenario[$it_val['SEQUENCE_ID']][] = $it_val;
    }
    foreach ($task_scenario as $ts_key => $ts_val) {
        $total_actual_time = 0;
        $total_expect_time = 0;
        $total_build       = array();
        $total_tester      = array();
        $total_bug         = array();
        $total_comment     = array();
        $total_status      = array();
        foreach ($ts_val as $tv_key => $tv_val) {
            $task_id                         = $tv_val['TASK_ID'];
            $task_title                      = $tv_val['TASK_TITLE'];
            $total_expect_time               += ($tv_val['EX_SETUP_TIME'] + $tv_val['EX_EXECUTION_TIME']);
            $total_actual_time               += ($tv_val['AC_SETUP_TIME'] + $tv_val['AC_EXECUTION_TIME'] + $tv_val['INVESTIGATE_TIME']);
            $total_build[]                   = $tv_val['BUILD_INFO'];
            $total_tester[$tv_val['TESTER']] = $available_user[$tv_val['TESTER']]['user_name'];
            $total_status[]                  = $tv_val['STATUS'];
            $total_bug[]                     = $tv_val['BUG_INFO'];
            $total_comment[]                 = $tv_val['COMMENTS'];
        }
        $task_scenario_summary[$ts_key] = array(
            'task_id'           => $task_id,
            'task_title'        => $task_title,
            'total_expect_time' => $total_expect_time,
            'total_actual_time' => $total_actual_time,
            'total_build'       => array_unique($total_build),
            'total_tester'      => $total_tester,
            'total_status'      => array_count_values($total_status),
            'total_bug'         => array_unique($total_bug),
            'total_comment'     => array_unique($total_comment)
        );
    }
    foreach ($task_scenario_summary as $tss_key => $tss_val) {
        task_status_id($tss_key, $tss_val);
        foreach ($task_scenario[$tss_key] as $tsa_key => $tsa_val) {
            status_scenario_info($tsa_val, $available_user);
        }
    }
}

/*
 * Print task summary (task id and name) for task status
 */

function task_status_id($sequence_id, $task_content) {
    $short_task_title = substr($task_content['task_title'], 0, 40);
    ?>
    <tr class='father'  id="<?php echo $sequence_id ?>">
        <td colspan="6"><?php echo $task_content['task_id'] . "|" . $short_task_title; ?></td>
        <td>
            <?php
            foreach ($task_content['total_build'] as $tv_build) {
                echo $tv_build . ",";
            }
            ?>
        </td>
        <td class="chinese">
        <?php
            foreach ($task_content['total_tester'] as $tv_tester_id => $tv_tester_name) {
                echo $tv_tester_name . "<br>";
            } 
        ?>
        </td>
        <td> <?php echo round($task_content['total_expect_time'] / 60, 2); ?> </td>
        <td> <?php echo round($task_content['total_actual_time'] / 60, 2); ?> </td>
        <td>
            <?php
            foreach ($task_content['total_status'] as $tv_status_key => $tv_status_val) {
                echo $GLOBALS['TASK_STATUS'][$tv_status_key][0] . ":" . $tv_status_val . "<br>";
            }
            ?>
        </td>
        <td colspan="2">
            <?php
            foreach ($task_content['total_bug'] as $tv_bug) {
                echo $tv_bug . ($tv_bug == "" ? "" : ",");
            }
            ?>
        </td>
    </tr>
    <?php
}

/*
 * Print task detail info (scenario) for task status
 */

function status_scenario_info($tss_val, $available_user) {
    $short_scenario_title = substr($tss_val['SCENARIO_TITLE'], 0, 30);
    $short_scenario_comment = substr($tss_val['COMMENTS'], 0, 30);
    ?>
    <tr class='child_<?php echo $tss_val['SEQUENCE_ID'] ?>' style='display:none'>
        <td colspan="3" title="<?php echo $tss_val['SCENARIO_ID'] . "|" . $tss_val['SCENARIO_TITLE']; ?>">
            <?php echo substr($tss_val['SCENARIO_ID'], -3) . "|" . $short_scenario_title; ?>
        </td>
        <td><?php echo date("m/d/y", strtotime($tss_val['ASSIGN_DATE'])); ?></td>
        <td>
            <?php echo ($tss_val['FINISH_DATE'] != NULL) ? (date("m/d/y", strtotime($tss_val['FINISH_DATE']))) : "N/A"; ?></td>
        <td><?php echo $tss_val['ENVIRONMENT']; ?></td>
        <td> <?php echo $tss_val['BUILD_INFO'] ?></td>
        <td class="chinese">
           <?php
               echo $available_user[$tss_val['TESTER']]['user_name'];
           ?>
        </td>
        <td><?php echo round($tss_val['EX_SETUP_TIME']/60,2) . "+" . round($tss_val['EX_EXECUTION_TIME']/60,2); ?></td>
        <td><?php echo round($tss_val['AC_SETUP_TIME']/60,2) . "+" . round($tss_val['AC_EXECUTION_TIME']/60,2) . "+" . (!empty($tss_val['INVESTIGATE_TIME'])?round($tss_val['INVESTIGATE_TIME']/60,2):''); ?></td>
        <td style='<?php echo $GLOBALS['TASK_STATUS'][$tss_val['STATUS']]['1'] ?>'><?php echo $GLOBALS['TASK_STATUS'][$tss_val['STATUS']]['0'] ?></td>
        <td><?php echo $tss_val['BUG_INFO'] ?></td>
        <td title="<?php echo $tss_val['COMMENTS'] ?>"><?php echo $short_scenario_comment ?></td>
    </tr>  
    <?php
}

/*
 *  Push  all the task of the iteration into table for my task records
 */

function my_task_records_push_into_table($iteration_task,$oid, $su_id, $gtype, $optype,$i) {
    $personal_task_scenario         = array();
    $personal_task_scenario_summary = array();
    foreach ($iteration_task as $it_key => $it_val) {
        $personal_task_scenario[$it_val['SEQUENCE_ID']][] = $it_val;
    }
    foreach ($personal_task_scenario as $mts_key => $mts_val) {
        $total_actual_time = 0;
        $total_build       = array();
        $total_bug         = array();
        $total_comment     = array();
        $total_status      = array();
        foreach ($mts_val as $mv_key => $mv_val) {
            $task_id = $mv_val['TASK_ID'];
            $task_title = $mv_val['TASK_TITLE'];
            $total_actual_time +=($mv_val['AC_SETUP_TIME'] + $mv_val['AC_EXECUTION_TIME'] + $mv_val['INVESTIGATE_TIME']);
            $total_build[] = $mv_val['BUILD_INFO'];
            $total_status[] = $mv_val['STATUS'];
            $total_bug[] = $mv_val['BUG_INFO'];
            $total_comment[] = $mv_val['COMMENTS'];
        }
        $personal_task_scenario_summary[$mts_key] = array(
            'task_id'           => $task_id,
            'task_title'        => $task_title,
            'total_actual_time' => $total_actual_time,
            'total_build'       => array_unique($total_build),
            'total_status'      => array_count_values($total_status),
            'total_bug'         => array_unique($total_bug),
            'total_comment'     => array_unique($total_comment)
        );
    }
    foreach ($personal_task_scenario_summary as $ptss_key => $ptss_val) {
        my_task_records_id($ptss_key, $ptss_val);
        foreach ($personal_task_scenario[$ptss_key] as $ptsa_key => $ptsa_val) {
            my_task_records_scenario_info($ptsa_val, $oid, $su_id, $gtype, $optype,$i);
            $i++;
        }
    }
    return $i;
}

/*
 * Print task summary (task id and name) for task status
 */

function my_task_records_id($ptss_key, $ptss_val) {
    $short_task_title = substr($ptss_val['task_title'], 0, 40);
    ?>
    <table>
        <tr class='father' id="<?php echo $ptss_key ?>">
            <td colspan="6" class="td-width-3"><?php echo $ptss_val['task_id'] . "|" . $short_task_title; ?></td>
            <td class="td-width-1">
                <?php
                foreach ($ptss_val['total_build'] as $ptv_build) {
                    echo $ptv_build . ",";
                }
                ?>
            </td>
            <td class="td-width-2"> <?php echo round($ptss_val['total_actual_time'] / 60, 2); ?> </td>
            <td class="td-width-5" >
                <?php
                foreach ($ptss_val['total_status'] as $ptv_status_key => $ptv_status_val) {
                    echo $GLOBALS['TASK_STATUS'][$ptv_status_key][0] . ":" . $ptv_status_val . "<br>";
                }
                ?>
            </td>
            <td colspan="3" class="td-width-4">
                <?php
                foreach ($ptss_val['total_bug'] as $ptv_bug) {
                    echo $ptv_bug . ($ptv_bug == "" ? "" : ",");
                }
                ?>
            </td>
        </tr>
    </table>
    <?php
}

/*
 * Print task detail info (scenario) for task status
 */

function my_task_records_scenario_info($ptsa_val, $oid, $su_id, $gtype, $optype,$i) {
    $short_scenario_title   = substr($ptsa_val['SCENARIO_TITLE'], 0, 30);
    $short_scenario_comment = substr($ptsa_val['COMMENTS'], 0, 40);
    $param_comment          = base64_encode($ptsa_val['COMMENTS']);
    $param = $ptsa_val['TASK_ID'] . "@@@" . $ptsa_val['SCENARIO_ID'] . "@@@" . $ptsa_val['ENVIRONMENT'] . "@@@" . $ptsa_val['BUILD_INFO'] . "@@@" . $ptsa_val['EX_SETUP_TIME'] . "@@@" .$ptsa_val['EX_EXECUTION_TIME'] .  "@@@" . $ptsa_val['AC_SETUP_TIME'] . "@@@" . $ptsa_val['AC_EXECUTION_TIME'] . "@@@" . $ptsa_val['INVESTIGATE_TIME'] . "@@@" . $ptsa_val['BUG_INFO'] . "@@@" . $ptsa_val['STATUS'] . "@@@" . $ptsa_val['FINISH_DATE'] . "@@@" . $param_comment;
    ?>
    <div class='child_<?php echo $ptsa_val['SEQUENCE_ID'] ?>' style="display:none">
        <table>
            <input type="hidden" id="seq_id_<?php echo $i;?>" value="<?php echo $ptsa_val['SEQUENCE_ID'];?>">
            <input type="hidden" id="t_id_<?php echo $i;?>" value="<?php echo $ptsa_val['TASK_ID'];?>">
            <input type="hidden" id="s_id_<?php echo $i;?>" value="<?php echo $ptsa_val['SCENARIO_ID'];?>">
            <input type="hidden" id="st_id_<?php echo $i;?>" value="<?php echo $ptsa_val['SCENARIO_TITLE'];?>">
            <input type="hidden" id="env_<?php echo $i;?>" value="<?php echo $ptsa_val['ENVIRONMENT'];?>">
            <input type="hidden" id="bui_<?php echo $i;?>" value="<?php echo $ptsa_val['BUILD_INFO'];?>">
            <input type="hidden" id="est_<?php echo $i;?>" value="<?php echo $ptsa_val['EX_SETUP_TIME'];?>">
            <input type="hidden" id="eet_<?php echo $i;?>" value="<?php echo $ptsa_val['EX_EXECUTION_TIME'];?>">
            <input type="hidden" id="ast_<?php echo $i;?>" value="<?php echo $ptsa_val['AC_SETUP_TIME'];?>">
            <input type="hidden" id="aet_<?php echo $i;?>" value="<?php echo $ptsa_val['AC_EXECUTION_TIME'];?>">
            <input type="hidden" id="it_<?php echo $i;?>" value="<?php echo $ptsa_val['INVESTIGATE_TIME'];?>">
            <input type="hidden" id="bi_<?php echo $i;?>" value="<?php echo $ptsa_val['BUG_INFO'];?>">
            <input type="hidden" id="sta_<?php echo $i;?>" value="<?php echo $ptsa_val['STATUS'];?>">
            <input type="hidden" id="ad_<?php echo $i;?>" value="<?php echo $ptsa_val['ASSIGN_DATE'];?>">
            <input type="hidden" id="fd_<?php echo $i;?>" value="<?php echo $ptsa_val['FINISH_DATE'];?>">
            <input type="hidden" id="com_<?php echo $i;?>" value="<?php echo $ptsa_val['COMMENTS'];?>">
            <input type="hidden" id="no_<?php echo $i;?>" value="<?php echo $ptsa_val['NO'];?>">
            <input type="hidden" id="i_id_<?php echo $i;?>" value="<?php echo $ptsa_val['ITERATION_ID'];?>">
            <tr>
                <td></td>
                <td class="td-width" title="<?php echo $ptsa_val['SCENARIO_ID']; ?>"><?php echo substr($ptsa_val['SCENARIO_ID'], -3); ?></td>
                <td class="td-width-2" title="<?php echo $ptsa_val['SCENARIO_TITLE']; ?>"><?php echo substr($short_scenario_title,0,20); ?></td>
                <td class="td-width-5"><?php echo date("m/d/y", strtotime($ptsa_val['ASSIGN_DATE'])); ?></td>
                <td class="td-width-5"><?php echo date("m/d/y", strtotime($ptsa_val['FINISH_DATE'])); ?></td>
                <td class="td-width-7" title="<?php echo $ptsa_val['ENVIRONMENT'];?>"><?php echo substr($ptsa_val['ENVIRONMENT'],0,20); ?></td>
                <td class="td-width-1"><span><?php echo $ptsa_val['BUILD_INFO'] ?></span></td>
                <td class="td-width-2"><?php echo $ptsa_val['AC_SETUP_TIME'] . "+" . $ptsa_val['AC_EXECUTION_TIME'] . "+" . $ptsa_val['INVESTIGATE_TIME'] ?></td>
                <td class="td-width-5" style='<?php echo $GLOBALS['TASK_STATUS'][$ptsa_val['STATUS']]['1'] ?>'><?php echo $GLOBALS['TASK_STATUS'][$ptsa_val['STATUS']]['0'] ?></td>
                <td class="td-width-7" title="<?php echo $ptsa_val['BUG_INFO'];?>"><span><?php echo substr($ptsa_val['BUG_INFO'],0,20); ?></span></td>
                <td class="td-width chinese" title="<?php echo $ptsa_val['COMMENTS'] ?>">
                    <span><?php echo $short_scenario_comment ?></span>
                </td>
                <td class="td-width-6 correct_execute" id="<?php echo $i;?>"><a style="cursor:pointer" ><img src="../../../lib/image/icons/execute.png" width="24" height="24"></a></td>
            </tr>  
        </table>
        <div id="correct_task_results_<?php echo $i;?>">

        </div>
    </div>
    <?php
}

/*
 * Push  all the task of the iteration into table 
 */

function push_into_table_execute($iteration_task, $member_of_org, $TASK_STATUS, $oid, $su_id, $gtype, $optype) {
    $flag = NULL;
    $i    = 0;
    foreach ($iteration_task as $it_key => $it_val) {
        if ($it_val['SEQUENCE_ID'] !== $flag) {
            task_id_execute($it_val, $member_of_org, $TASK_STATUS);
            scenario_info_execute($it_val, $member_of_org, $TASK_STATUS, $oid, $su_id, $gtype, $optype,$i);
            $flag = $it_val['SEQUENCE_ID'];
        } else {
            scenario_info_execute($it_val, $member_of_org, $TASK_STATUS, $oid, $su_id, $gtype, $optype,$i);
        }
        $i++;
    }
}

/*
 * Print employee into an select
 */

function push_emp_select_execute($member_of_org, $it_val) {
    foreach ($member_of_org as $moo_key => $moo_val) {
        if ($moo_val['USER_ID'] == $it_val['TESTER']) {
            echo "<option value='" . $moo_val['USER_ID'] . "' selected>" . $moo_val['USER_NAME'] . "</option>";
        } else {
            echo "<option value='" . $moo_val['USER_ID'] . "'>" . $moo_val['USER_NAME'] . "</option>";
        }
    }
}

/*
 * Print task summary (task id and name)
 */

function task_id_execute($it_val, $member_of_org, $TASK_STATUS) {
    $short_task_title = substr($it_val['TASK_TITLE'], 0, 40);
    ?>
    <div class="father_<?php echo $it_val['SEQUENCE_ID'];?> child_<?php echo $it_val['ITERATION_ID'];?>">
        <table>
            <tr class='father' id="<?php echo $it_val['SEQUENCE_ID'] ?>">
                <td class='td-width-9 task_assign_father' colspan="3"><?php echo $it_val['TASK_ID'] ?></td>
                <td class='td-width-10 click_me'  id='<?php echo $it_val['SEQUENCE_ID'] ?>' title='<?php echo $it_val['TASK_TITLE'] ?>' colspan="6"><?php echo $short_task_title; ?></td>
            </tr>
        </table>
    </div>
    <?php
}

/*
 * Print task detail info (scenario)
 */

function scenario_info_execute($it_val, $member_of_org, $TASK_STATUS, $oid, $su_id, $gtype, $optype,$i) {
    $short_scenario_title   = substr($it_val['SCENARIO_TITLE'], 0, 30);
    $short_scenario_comment = substr($it_val['COMMENTS'], 0, 30);
    $param_comment          = base64_encode($it_val['COMMENTS']);
    $param                  = $it_val['TASK_ID'] . "@@@" . $it_val['SCENARIO_ID'] . "@@@" . $it_val['ENVIRONMENT'] . "@@@" . $it_val['BUILD_INFO'] . "@@@" . $it_val['EX_SETUP_TIME'] . "@@@" .$it_val['EX_EXECUTION_TIME'] . "@@@" .$it_val['AC_SETUP_TIME'] . "@@@" . $it_val['AC_EXECUTION_TIME'] . "@@@" . $it_val['INVESTIGATE_TIME'] . "@@@" . $it_val['BUG_INFO'] . "@@@" . $it_val['STATUS'] . "@@@" . $it_val['FINISH_DATE'] . "@@@" . $param_comment;
    ?>
    <div class='child_<?php echo $it_val['SEQUENCE_ID'] ?>' id="div_<?php echo $i;?>">
        <table>
            <input type="hidden" id="seq_id_<?php echo $i;?>" value="<?php echo $it_val['SEQUENCE_ID'];?>">
            <input type="hidden" id="t_id_<?php echo $i;?>" value="<?php echo $it_val['TASK_ID'];?>">
            <input type="hidden" id="s_id_<?php echo $i;?>" value="<?php echo $it_val['SCENARIO_ID'];?>">
            <input type="hidden" id="st_id_<?php echo $i;?>" value="<?php echo $it_val['SCENARIO_TITLE'];?>">
            <input type="hidden" id="env_<?php echo $i;?>" value="<?php echo $it_val['ENVIRONMENT'];?>">
            <input type="hidden" id="bui_<?php echo $i;?>" value="<?php echo $it_val['BUILD_INFO'];?>">
            <input type="hidden" id="est_<?php echo $i;?>" value="<?php echo $it_val['EX_SETUP_TIME'];?>">
            <input type="hidden" id="eet_<?php echo $i;?>" value="<?php echo $it_val['EX_EXECUTION_TIME'];?>">
            <input type="hidden" id="ast_<?php echo $i;?>" value="<?php echo $it_val['AC_SETUP_TIME'];?>">
            <input type="hidden" id="aet_<?php echo $i;?>" value="<?php echo $it_val['AC_EXECUTION_TIME'];?>">
            <input type="hidden" id="it_<?php echo $i;?>" value="<?php echo $it_val['INVESTIGATE_TIME'];?>">
            <input type="hidden" id="bi_<?php echo $i;?>" value="<?php echo $it_val['BUG_INFO'];?>">
            <input type="hidden" id="sta_<?php echo $i;?>" value="<?php echo $it_val['STATUS'];?>">
            <input type="hidden" id="ad_<?php echo $i;?>" value="<?php echo $it_val['ASSIGN_DATE'];?>">
            <input type="hidden" id="fd_<?php echo $i;?>" value="<?php echo $it_val['FINISH_DATE'];?>">
            <input type="hidden" id="com_<?php echo $i;?>" value="<?php echo $it_val['COMMENTS'];?>">
            <input type="hidden" id="no_<?php echo $i;?>" value="<?php echo $it_val['NO'];?>">
            <input type="hidden" id="i_id_<?php echo $i;?>" value="<?php echo $it_val['ITERATION_ID'];?>">
            <tr>
                <td class="td-width-9" title="<?php echo $it_val['SCENARIO_ID'] . "|" . $it_val['SCENARIO_TITLE']; ?>" colspan="3">
                    <?php echo substr($it_val['SCENARIO_ID'], -3).'|'.$short_scenario_title; ?>
                </td>
                <td class="td-width-8" id="n_env_<?php echo $i;?>"><?php echo $it_val['ENVIRONMENT']; ?></td>
                <td class="td-width-1" id="n_build_<?php echo $i;?>"><?php echo $it_val['BUILD_INFO'] ?></td>
                <td class="td-width-7" id="n_time_<?php echo $i;?>"><?php echo "&nbsp;" . $it_val['AC_SETUP_TIME'] . "&nbsp;+&nbsp;" . $it_val['AC_EXECUTION_TIME'] ?></td>
                <td class="td-width-1" id="n_status_<?php echo $i;?>">
                    <span class='badge badge-<?php echo $TASK_STATUS[$it_val['STATUS']]['1'] ?>'>
                        <?php echo $TASK_STATUS[$it_val['STATUS']]['0'] ?>
                    </span>
                </td>
                <td class="td-width-7" id="n_comments_<?php echo $i;?>" title="<?php echo $it_val['COMMENTS']; ?>"><?php echo $short_scenario_comment; ?></td>
                <td class="td-width update_task_result" id="<?php echo $i;?>" name="<?php echo $it_val['SEQUENCE_ID'] ?>"><a style="cursor:pointer" ><img src="../../../lib/image/icons/execute.png" width="24" height="24"></a></td>
            </tr>  
        </table>
        <div id="submit_task_result_<?php echo $i;?>"></div>
    </div>
    <?php
}

/*
 * Search task execution record
 */

function search_task_execution_record($task_id_name) {
    $qry_ter = "SELECT pi.`NAME`,ii.ITERATION_NAME,tae.TASK_ID,tae.TASK_TITLE,tae.SCENARIO_ID,tae.SCENARIO_TITLE,ui.USER_NAME,tae.TESTER,tae.FINISH_DATE,tae.ENVIRONMENT,tae.AC_SETUP_TIME,
        tae.AC_EXECUTION_TIME,tae.BUILD_INFO,tae.STATUS,tae.BUG_INFO,tae.COMMENTS,tae.SEQUENCE_ID,ca.`OWNER`,ui.NICK_NAME,ca.ACCOUNT,ui.EMAIL,ui.LEVEL,
        teb.SENIOR_SETUP_COEFFICIENCY as SSC,teb.SENIOR_EXECUTION_COEFFICIENCY as SEC,teb.JUNIOR_SETUP_COEFFICIENCY as JSC,teb.JUNIOR_EXECUTION_COEFFICIENCY as JEC,
        teb.INTERN_SETUP_COEFFICIENCY as ISC,teb.INTERN_EXECUTION_COEFFICIENCY as IEC, cl.ESTIMATE_SETUPTIME, cl.ESTIMATE_EXECTIME
        FROM case_library AS cl,iteration_info as ii,project_info as pi, user_info AS ui LEFT JOIN (customer_account AS ca) ON (ui.USER_ID=ca.MAINTAINER),
        task_assignment_execution AS tae LEFT JOIN (task_estimation_baseline as teb) ON (teb.FK_TASK_ID = tae.SCENARIO_ID )
        WHERE tae.TESTER=ui.USER_ID AND tae.ITERATION_ID = ii.ITERATION_ID AND ii.FK_PROJECT_ID = pi.ID AND cl.SCENARIO_ID=tae.SCENARIO_ID 
        AND (tae.ITERATION_ID in(SELECT distinct tae_a.ITERATION_ID FROM iteration_info as ii_a, task_assignment_execution AS tae_a 
        WHERE tae_a.ITERATION_ID = ii_a.ITERATION_ID AND ii_a.ITERATION_NAME LIKE '%$task_id_name%' AND tae_a.CORRECT='N')OR CONCAT_WS(',',tae.TASK_ID,tae.TASK_TITLE,tae.SCENARIO_TITLE) LIKE '%$task_id_name%') AND tae.CORRECT='N' ORDER BY FINISH_DATE DESC;";
    $rst_ter = $GLOBALS['db']->query($qry_ter);
    $search_task_execution_record = $rst_ter->fetch_all(MYSQLI_ASSOC);
    return $search_task_execution_record;
}

/**
 * DATE:2013-10-18
 * SY
 * only push case info into table
 */
function push_task_info_into_table($result_task_info,$user_info, $TASK_STATUS, $permission,$task_id,$task_info) {
    if(empty($result_task_info)){
        error_without_article('204');
        exit;
    }
    ?>
    <div>
        <span class="label label-info">General</span>
    </div><br>
    <table class='table table-striped table-condensed table-hover'>
        <thead>
            <?php
                if($_SESSION['type'] != 4){ ?>
                <tr>
                    <th colspan='6'>
                          <select id="task_id_scope" placeholder="Select Task ID">
                              <option value="">All Task ID</option>
                              <?php 
                              foreach($task_info as $rti_key => $rti_val){
                                  if($task_id == $rti_key){
                                      echo " <option value='$rti_key' selected>".$rti_key."</option>";
                                  }else{
                                      echo " <option value='$rti_key'>".$rti_key."</option>";
                                  }
                              }
                              ?>
                          </select>
                    </th>
                    <?php
                        if ($permission >= $GLOBALS['WRITE']) { ?>
                            <th colspan='2'>Expected Setup (H)</th>
                            <th colspan='2'>Expected Execution (H)</th>
                            <?php 
                        }
                    ?>
                </tr>
                <tr>
                    <th colspan='6'>Task ID/Title</th>
                    <?php if ($permission >= $GLOBALS['WRITE']) { ?>
                    <th colspan='4' style='width: 40%'>Senior&nbsp;/&nbsp;Junior&nbsp;/&nbsp;Intern</th>
                    <?php }?>
                </tr>
                <?php }else{ ?>
                 <tr>
                    <th>
                        <select id="task_id_scope" placeholder="Select Task ID">
                           <option value="">ALL</option>
                           <?php 
                           foreach($task_info as $rti_key => $rti_val){
                               if($task_id == $rti_key){
                                   echo " <option value='$rti_key' selected>".$rti_key."</option>";
                               }else{
                                   echo " <option value='$rti_key'>".$rti_key."</option>";
                               }
                           }
                           ?>
                       </select>
                    </th>
                </tr>
                <tr>
                    <th colspan='6' style='width: 60%'>Task ID/Title</th>
                    <th colspan='2' style='width: 20%'>Expected Setup (H)</th>
                    <th colspan='2' style='width: 20%'>Expected Execution (H)</th>
                <tr>
                <?php }
                ?>
            </tr>
        </thead>
        <tr class="child_<?php echo $task_id;?>">
            <td colspan='2' style='width: 25%'><b>Scenario ID/Title</b></td>
            <td style='width: 8%'><b>Tester</b></td>
            <td style='width: 8%'><b>Finish</b></td>
            <td style='width: 10%'><b>Environment</b></td>
            <td style='width: 9%'><b>Build</b></td>
            <?php  if ($permission >= $GLOBALS['WRITE']) { ?>
                <td style='width: 10%'><b>Setup/Execution (H)</b></td>
            <?php  } ?>
            <td style='width: 10%'><b>Status</b></td>
            <td style='width: 10%'><b>Bug</b></td>
            <td style='width: 10%'><b>Comments</b></td>
       </tr>
    </table>
    <?php
    if(empty($task_id)){
        $i=1;
        foreach ($result_task_info as $ster_key => $ster_val) {
            ?>
            <div>
                <table class='table table-striped table-condensed table-hover'>
                <?php
                    push_task_info_tbody_into_table($ster_key,$ster_val,$permission,$i,0,'');  
                ?>
                </table>
                <div id="sub_title_<?php echo $i;?>"></div>
            </div>
            <?php
            $i++;
        }
    }else{
        ?>
        <div>
            <table class='table table-striped table-condensed table-hover'>
            <?php
                push_task_info_tbody_into_table($task_id,$task_info[$task_id],$permission,0,0,'');  
            ?>
            </table>
            <div id="sub_title_0"></div>
        </div>
        <?php
    }
}

/**
 * DATE:2013-10-28
 * Shiyan
 * only push info into table body
 */
function push_task_info_into_table_by_page($result_task_info,$permission,$task_id,$page) {
    if(empty($task_id)){
        $i=1;
        foreach ($result_task_info as $ster_key => $ster_val) {
            ?>
            <div>
                <table class='table table-striped table-condensed table-hover'>
                <?php
                    push_task_info_tbody_into_table($ster_key,$ster_val,$permission,$i,1,$page);  
                ?>
                </table>
                <div id="sub_title_<?php echo 'page'.$page.$i;?>"></div>
            </div>
            <?php
            $i++;
        }
    }
}

/**
 * SY
 * @param type $ster_key:$scenario_id
 * @param type $ster_val:$task_info
 * @param type $permission
 * @param type $i
 * @param type $mark
 * @param type $page
 */
function push_task_info_tbody_into_table($ster_key,$ster_val,$permission,$i,$mark,$page){
    if($mark == 0){?>
        <tr class="father" id="<?php echo $i;?>" value="<?php echo $ster_key;?>" style="cursor: pointer">
        <?php
    }else if($mark ==1){?>
        <tr class="father" id="<?php echo 'page'.$page.$i;?>" value="<?php echo $ster_key;?>" style="cursor: pointer">
        <?php
    }
        if(empty($ster_val['TITLE'])){
            ?>
            <td colspan='6' style='width: 60%'><?php echo $ster_key;?></td>
            <?php 
        }else{
            ?>
            <td colspan='2' style='width: 15%'><?php echo $ster_key;?></td>
            <td title="<?php echo $ster_val['TITLE'];?>" colspan='4' style='width: 45%'><?php echo substr($ster_val['TITLE'],0,40);?></td>
            <?php 
        }?>
        <?php if ($permission >= $GLOBALS['WRITE']) { 
            if($_SESSION['type'] == 4){ ?>
                <td colspan='2' style='width: 20%'><?php echo $ster_val['ESTIMATE_SETUPTIME'];?></td>
                <td colspan='2' style='width: 20%'><?php echo $ster_val['ESTIMATE_EXECTIME'];?></td>
            <?php }else{ ?>
                <td colspan='2' style='width: 20%'><?php echo round($ster_val['SSC']*$ster_val['ESTIMATE_SETUPTIME'],2).'&nbsp;/&nbsp;'.round($ster_val['JSC']*$ster_val['ESTIMATE_SETUPTIME'],2).'&nbsp;/&nbsp;'.round($ster_val['ISC']*$ster_val['ESTIMATE_SETUPTIME'],2);?></td>
                <td colspan='2' style='width: 20%'><?php echo round($ster_val['SEC']*$ster_val['ESTIMATE_EXECTIME'],2).'&nbsp;/&nbsp;'.round($ster_val['JEC']*$ster_val['ESTIMATE_EXECTIME'],2).'&nbsp;/&nbsp;'.round($ster_val['IEC']*$ster_val['ESTIMATE_EXECTIME'],2);?></td>
            <?php
            }
        }
        ?>
    </tr>
    <?php
}

function push_task_record_into_table_by_task_id($search_task_execution_record,$user_info, $TASK_STATUS, $permission,$task_id,$tester_id) {
    $space_val = $task_id.'@'.null;
    ?>
    <div>
        <span class="label label-info">Detail</span>
    </div><br>
    <div>
        <select id="tester_id_scope" placeholder="Select Tester">
            <option value="<?php echo $space_val;?>">All Testers</option>
            <?php 
                foreach ($search_task_execution_record[$task_id] as $ster_key => $ster_val) {
                    $value = $task_id.'@'.$ster_key;
                    if($_SESSION['type'] == 4){
                        if((empty($user_info[$ster_key]['OWNER']))&&(empty($user_info[$ster_key]['NICK_NAME']))){
                            if($tester_id == $ster_key){
                                ?>
                                <option value='<?php echo $value;?>' selected><?php echo $user_info[$ster_key]['ACCOUNT'];?></option>;
                                <?php
                            }else{
                                ?>
                                <option value='<?php echo $value;?>'><?php echo $user_info[$ster_key]['ACCOUNT'];?></option>;
                                <?php
                            }
                        }else{
                            if($tester_id == $ster_key){
                                ?>
                                <option value='<?php echo $value;?>' selected><?php echo empty($user_info[$ster_key]['OWNER'])?$user_info[$ster_key]['NICK_NAME']:$user_info[$ster_key]['OWNER'];?></option>;
                                <?php
                            }else{
                                ?>
                                <option value='<?php echo $value;?>'><?php echo empty($user_info[$ster_key]['OWNER'])?$user_info[$ster_key]['NICK_NAME']:$user_info[$ster_key]['OWNER'];?></option>;
                                <?php
                            }
                        }
                    }else{
                        if($tester_id == $ster_key){
                            ?>
                            <option value='<?php echo $value;?>' selected><?php echo $user_info[$ster_key]['USER_NAME'];?></option>;
                            <?php
                        }else{
                            ?>
                            <option value='<?php echo $value;?>'><?php echo $user_info[$ster_key]['USER_NAME'];?></option>;
                            <?php
                        }
                    }
                }
            ?>
        </select>
    </div>
    <table class='table table-striped table-condensed table-hover'>
        <thead>
            <tr>
                <th>Project</th>
                <th>Tester</th>
                <?php 
                if($_SESSION['type'] != 4 ){
                    ?>
                    <th>Account</th>
                    <?php                  
                }
                if ($permission >= $GLOBALS['WRITE']) { ?>
                    <th>Actual Setup Time(h)</th>
                    <th>Actual Execution Time(h)</th>
                    <?php 
                }?>
                <th>Status</th>
                 <?php 
                if($_SESSION['type'] != 4 ){
                    ?>
                    <th>Email</th>
                    <?php
                }?>
            </tr>
        </thead> 
        <?php
        foreach ($search_task_execution_record[$task_id] as $stert_key => $stert_val) {
            if(!empty($tester_id)){
                if($stert_key == $tester_id){
                    foreach ($stert_val as $sv_key => $sv_val) {
                        push_task_info_by_task_id_into_table($sv_val,$stert_key,$user_info,$TASK_STATUS, $permission);
                    }
                    echo "<tr><td colspan='11'></td></tr>";    
                }
            }else{
                foreach ($stert_val as $sv_key => $sv_val) {
                    push_task_info_by_task_id_into_table($sv_val,$stert_key,$user_info,$TASK_STATUS, $permission);
                }
            } 
            if(empty($tester_id)){
                echo "<tr><td colspan='11'></td></tr>";  
            }
        }
        ?>
    </table>
    <?php
    if(empty($tester_id)){
        push_task_record_into_table($search_task_execution_record[$task_id],$tester_id,$user_info, $TASK_STATUS,$permission,$task_id);
    }
}

/**
 * SY
 * @param type $ster_val:task_records_info
 * @param type $ster_key:tester_id
 * @param type $user_info
 * @param type $TASK_STATUS
 * @param type $permission
 */
function push_task_info_by_task_id_into_table($ster_val,$ster_key,$user_info,$TASK_STATUS, $permission){
    ?>
    <tr class='tester_info'>
        <td><?php echo $ster_val['PROJECT'];?></td>
        <td>
            <?php
            if($_SESSION['type'] == 4){
                if(empty($user_info[$ster_key]['OWNER'])&&empty($user_info[$ster_key]['NICK_NAME'])){
                    echo $user_info[$ster_key]['ACCOUNT'];
                }else{
                    echo empty($user_info[$ster_key]['OWNER'])?$user_info[$ster_key]['NICK_NAME']:$user_info[$ster_key]['OWNER'];
                }
            }else{
                echo $user_info[$ster_key]['USER_NAME'];
            }
            ?>
        </td>
        <?php 
         if($_SESSION['type'] != 4){
            ?>
            <td><?php echo $user_info[$ster_key]['ACCOUNT'];?></td>
            <?php 
         } 
        if ($permission >= $GLOBALS['WRITE']) { ?>
            <td><?php echo $ster_val['SETUP_TIME'];?></td>
            <td><?php echo $ster_val['EXECUTE_TIME'];?></td>
            <?php 
        }?>
        <td>
            <?php
                foreach ($ster_val['STATUS']['GENERAL'] as $tv_status_key => $tv_status_val) {
                    echo $GLOBALS['TASK_STATUS'][$tv_status_key][0] . ":" . $tv_status_val . "<br>";
                }
            ?>
        </td>
        <?php 
         if($_SESSION['type'] != 4){
            ?>
            <td><?php echo $user_info[$ster_key]['EMAIL'];?></td>
            <?php 
         }
         ?>
    </tr>
    <?php
}
/*
 * Push searched task execution record into table
 */

function push_task_record_into_table($search_task_execution_record,$tester_id,$user_info,$TASK_STATUS, $permission) {
    foreach ($search_task_execution_record as $ster_key => $st_val) {//ster_key :tester_id
        if(empty($tester_id)){
            push_task_record_into_table_by_tester($st_val,$user_info,$TASK_STATUS,$permission);
        }else{
            if($ster_key == $tester_id){
                push_task_record_into_table_by_tester($st_val,$user_info,$TASK_STATUS,$permission);
            }
        }
        ?>
        </table> 
        <?php
    }
}

/**
 * SY
 * @param type $st_val:$task_info['sequence_id']
 * @param type $user_info
 * @param type $TASK_STATUS
 * @param type $permission
 */
function push_task_record_into_table_by_tester($st_val,$user_info,$TASK_STATUS,$permission){
    $task_id_flag = NULL;
    $sequence_id_flag = NULL;
    foreach($st_val as $stv_key => $stv_val){//$st_val:sequence_id
        foreach($stv_val['GENERAL'] as $ster_val){
            if ($ster_val['TASK_ID'] != $task_id_flag) {
            ?>
            <div>
                <span class="label label-info">
                    <?php
                        if($_SESSION['type'] == 4){
                            if(empty($user_info[$ster_val['TESTER']]['OWNER'])&&empty($user_info[$ster_val['TESTER']]['NICK_NAME'])){
                                echo $user_info[$ster_val['TESTER']]['ACCOUNT'];
                            }else{
                                echo empty($user_info[$ster_val['TESTER']]['OWNER'])?$user_info[$ster_val['TESTER']]['NICK_NAME']:$user_info[$ster_val['TESTER']]['OWNER'];
                            }
                        }else{
                            echo $user_info[$ster_val['TESTER']]['USER_NAME'];
                        }
                    ?>
                </span>
            </div>
            <table class='table table-striped table-condensed table-hover'>
            <?php
                print_trit_header($permission);
                print_trit_body($ster_val,$TASK_STATUS, $permission);
            } else {
                if ($stv_key != $sequence_id_flag)
                echo "<tr><td colspan='11'></td></tr>";
                print_trit_body($ster_val,$TASK_STATUS, $permission);
            }
            $task_id_flag = $ster_val['TASK_ID'];
            $sequence_id_flag = $stv_key;
        }
    }
}
/*
 * table header for push_task_record_into_table
 */

function print_trit_header($permission) {
    ?>
    <thead>
        <tr>
            <th colspan="2">Scenario ID/Title</th>
            <th>Finish</th>
            <th>Environment</th>
            <th>Build</th>
            <?php if ($permission >= $GLOBALS['WRITE']) { ?>
                <th colspan='2'>Actual Setup/Execution(h)</th>
            <?php }
            ?>
            <th class="task_status">Status</th>
            <th>Bug</th>
            <th>Comments</th>
        </tr>
    </thead>
    <?php
}

/*
 * table body for push_task_record_into_table
 */

function print_trit_body($table_content,$TASK_STATUS, $permission) {
    $short_scenario_title = substr($table_content['SCENARIO_TITLE'], 0, 20);
    $short_environment = substr($table_content['ENVIRONMENT'], 0, 20);
    $short_bug = substr($table_content['BUG_INFO'], 0, 30);
    $short_comment = substr(htmlspecialchars($table_content['COMMENTS']), 0, 30);
    ?>
    <tr>
        <td><?php echo $table_content['SCENARIO_ID']; ?></td>
        <td title="<?php echo $table_content['SCENARIO_TITLE']; ?>"><?php echo $short_scenario_title; ?></td>
        <td><?php echo $table_content['FINISH_DATE']; ?></td>
        <td title="<?php echo $table_content['ENVIRONMENT']; ?>"><?php echo $short_environment; ?></td>
        <td><?php echo $table_content['BUILD_INFO']; ?></td>
        <?php if ($permission >= $GLOBALS['WRITE']) { ?>
            <td colspan="2"><?php echo round($table_content['AC_SETUP_TIME']/60,2) . " / " . round($table_content['AC_EXECUTION_TIME']/60,2); ?></td>
        <?php }
        ?>
        <td><?php echo $TASK_STATUS[$table_content['STATUS']]['0'] ?></td>
        <td title="<?php echo $table_content['BUG_INFO']; ?>"><?php echo $short_bug; ?></td>
        <td title="<?php echo htmlspecialchars($table_content['COMMENTS']); ?>"><?php echo $short_comment; ?></td>
    </tr>
    <?php
}

/**
 * Author: CC
 * Date: 08/07/2013
 * [project_support_info Query project support info]
 * @param  [array] $projects  [projects]
 * @param  [date] $date_from [description]
 * @param  [date] $date_to   [description]
 * @return [array]            [description]
 */
function project_support_info($projects, $date_from, $date_to) {
    $string_projects      = op_string($projects);
    $members_of_project   = members_of_project($string_projects);
    $all_support_info     = all_support_info();
    $project_support_info = array();
    foreach ($all_support_info as $asi_key => $asi_val) {
        foreach ($members_of_project as $moo_key => $moo_val) {
            if (($asi_val['SUPPORTER'] == $moo_val['USER_ID']) && ($asi_val['DATE'] >= $date_from) && ($asi_val['DATE'] <= $date_to)) {
                $project_support_info[] = $asi_val;
            }
        }
    }
    return $project_support_info;
}

/**
 * Author: CC
 * Last Modified:
 * [personal_support_info Query personal support info]
 * @param  [type] $user_id [description]
 * @return [type]          [description]
 */
function personal_support_info($user_id) {
    $qry_personal_support_info = "SELECT tsi.ID, tsi.TASK_ID,tae.TASK_ID,SUPPORTER,ui_1.USER_NAME SUPPORTER_NAME,TIME,DATE,SUBMITTER,ui_2.USER_NAME SUBMITTER_NAME,COMMENT FROM 
    (((task_support_info AS tsi
        LEFT JOIN user_info AS ui_1 ON tsi.SUPPORTER=ui_1.USER_ID)
LEFT JOIN user_info AS ui_2 ON tsi.SUBMITTER=ui_2.USER_ID)
LEFT JOIN task_assignment_execution AS tae ON tsi.TASK_ID=tae.NO)
WHERE SUPPORTER='$user_id' ORDER BY DATE DESC;";
    $rst_personal_support_info = $GLOBALS['db']->query($qry_personal_support_info);
    $personal_support_info = $rst_personal_support_info->fetch_all(MYSQLI_ASSOC);
    return $personal_support_info;
}

/*
 * Query personal support info widt date
 */

function personal_support_info_withdate($user_id, $date_from, $date_to) {
    $qry_personal_support_info = "SELECT tsi.ID, tsi.TASK_ID,tae.TASK_ID,SUPPORTER,ui_1.USER_NAME SUPPORTER_NAME,TIME,DATE,SUBMITTER,ui_2.USER_NAME SUBMITTER_NAME,COMMENT FROM 
    (((task_support_info AS tsi
        LEFT JOIN user_info AS ui_1 ON tsi.SUPPORTER=ui_1.USER_ID)
LEFT JOIN user_info AS ui_2 ON tsi.SUBMITTER=ui_2.USER_ID)
LEFT JOIN task_assignment_execution AS tae ON tsi.TASK_ID=tae.NO)
WHERE SUPPORTER='$user_id' AND (DATE BETWEEN '$date_from' AND '$date_to')ORDER BY DATE DESC;";
    $rst_personal_support_info = $GLOBALS['db']->query($qry_personal_support_info);
    $personal_support_info = $rst_personal_support_info->fetch_all(MYSQLI_ASSOC);
    return $personal_support_info;
}

/**
 * Author: CC
 * [push_support_info_into_table Push support info into table]
 * Last Modified: 08/07/2013
 * Hide operations and submit new support info manually
 * @param  [type] $support_info  [description]
 * @param  [type] $oid           [description]
 * @param  [type] $su_id         [description]
 * @param  [type] $gtype         [description]
 * @param  [type] $submitter     [description]
 * @param  [type] $permission    [description]
 * @param  [type] $required_perm [description]
 * @return [type]                [description]
 */
function push_support_info_into_table($support_info, $pid, $su_id, $gtype, $submitter, $permission, $required_perm) {
    ?>
    <table class="table table-condensed table-striped table-hover">
        <caption><h4>Support Info</h4></caption>
        <thead>
            <tr>
                <th>NO</th>
                <th>Task</th>
                <th>Time</th>
                <th>Date</th>
                <th>Supporter</th>
                <th>Submitter</th>
                <th>Comment</th>
            </tr>
        </thead>
        <?php
        foreach ($support_info as $si_key => $si_val) {
            $short_task_id = substr($si_val['TASK_ID'], 0, 40);
            $short_comment = substr(htmlspecialchars($si_val['COMMENT']), 0, 40);
            //$parm_string = $si_val['ID'] . "@@@" . $si_val['TIME'] . "@@@" . $si_val['DATE'] . "@@@" . $si_val['COMMENT'];
            //$parm = base64_encode($parm_string);
            ?>
            <tr>
                <td><?php echo ($si_key + 1); ?></td>
                <td title="<?php echo $si_val['TASK_ID']; ?>"><?php echo $short_task_id; ?></td>
                <td><?php echo $si_val['TIME']; ?></td>
                <td><?php echo $si_val['DATE']; ?></td>
                <td class="chinese"><?php echo $si_val['SUPPORTER_NAME']; ?></td>
                <td class="chinese"><?php echo $si_val['SUBMITTER_NAME']; ?></td>
                <td title="<?php echo htmlspecialchars($si_val['COMMENT']); ?>" class="chinese"><?php echo $short_comment; ?></td>
                <!-- <td>
                    <?php
                    //if ($submitter == $si_val['SUBMITTER'] || $permission >= $required_perm) {
                        ?>
                        <a href='../../general/home/index.php?oid=<?php //echo base64_encode($oid); ?>&su_id=<?php //echo base64_encode($su_id); ?>&url=<?php //echo base64_encode("task/submit_support.php"); ?>&gtype=<?php //echo base64_encode($gtype); ?>&type=edit&parm=<?php //echo $parm; ?>#tabs_ops'><img width='16' height='16' src='../../../lib/image/icons/edit.png'/></a>&nbsp;&nbsp;
                        <a href='../../general/home/index.php?oid=<?php //echo base64_encode($oid); ?>&su_id=<?php //echo base64_encode($su_id); ?>&url=<?php //echo base64_encode("task/support_info_delete.php"); ?>&gtype=<?php //echo base64_encode($gtype); ?>&type=delete&parm=<?php //echo $parm; ?>#tabs_ops'><img width='16' height='16' src='../../../lib/image/icons/remove.png'/></a>
                        <?php
                    //} else {
                    //    echo "&nbsp;&nbsp;&nbsp;&nbsp;";
                    //}
                    ?>
                </td> -->
            </tr>
        <?php }
        ?>
    </table>
    <?php
}

/*
 * Query all support info
 */

function all_support_info() {
    $qry_all_support_info = "SELECT tsi.ID, tsi.TASK_ID,tae.TASK_ID,SUPPORTER,ui_1.USER_NAME SUPPORTER_NAME,TIME,DATE,SUBMITTER,ui_2.USER_NAME SUBMITTER_NAME,DATE,COMMENT FROM 
    (((task_support_info AS tsi 
    LEFT JOIN user_info AS ui_1 ON tsi.SUPPORTER=ui_1.USER_ID)
    LEFT JOIN user_info AS ui_2 ON tsi.SUBMITTER=ui_2.USER_ID)
    LEFT JOIN task_assignment_execution AS tae ON tsi.TASK_ID=tae.NO) ORDER BY DATE DESC";
    $rst_all_support_info = $GLOBALS['db']->query($qry_all_support_info);
    $all_support_info     = $rst_all_support_info->fetch_all(MYSQLI_ASSOC);
    return $all_support_info;
}

/*
 * New support info through updating task
 */

function new_support_by_task($task_no, $supporter, $support_time, $support_date, $submitter, $comment) {
    $qry_support = "INSERT INTO task_support_info (TASK_ID,SUPPORTER,TIME,DATE,SUBMITTER,COMMENT) VALUES ('$task_no','$supporter','$support_time','$support_date','$submitter','$comment');";
    $result_support = $GLOBALS['db']->query($qry_support);
}

/*
 * Copy the old support info to the new task
 */

function update_support_info($task_no,$new_task_no) {
    $update_support_info = "UPDATE task_support_info SET TASK_ID='$new_task_no' WHERE TASK_ID='$task_no'";
    $rst_update_support  = $GLOBALS['db']->query($update_support_info);
}

/*
 * Personal bug info status with date 
 */

function personal_bug_info_withdate($user_id, $date_from, $date_to) {
    $qry_personal_bug_info = "SELECT bl.BUG_ID,ii.ITERATION_NAME,bl.BUILD_ID,bl.ACCOUNT,bl.BUG_STATUS,bl.EXECUTE_TIME,REPORTER,ui.USER_NAME FROM bug_library AS bl, user_info AS ui,iteration_info AS ii WHERE bl.ITERATION_ID=ii.ITERATION_ID AND bl.REPORTER='$user_id' AND bl.REPORTER=ui.USER_ID AND (CAST(bl.SUBMIT_DATE AS DATE) BETWEEN '$date_from' AND '$date_to');";
    $rst_personal_bug_info = $GLOBALS['db']->query($qry_personal_bug_info);
    $personal_bug_info_withdate = $rst_personal_bug_info->fetch_all(MYSQLI_ASSOC);
    return $personal_bug_info_withdate;
}

/*
 * Query all bug not info status of the user 
 */

function personal_bug_note_info_withdate($user_id, $date_from, $date_to) {
    $qry_personal_bug_note = "SELECT bl.BUG_ID,ii.ITERATION_NAME,bl.BUILD_ID,bl.ACCOUNT,REPORTER,ui.USER_NAME,bl.BUG_STATUS,bn.EXECUTE_TIME NOTE_TIME FROM bug_library AS bl, bug_note AS bn, user_info AS ui,iteration_info AS ii WHERE bl.ITERATION_ID=ii.ITERATION_ID AND bl.REPORTER=ui.USER_ID AND OWNER='$user_id' AND (CAST(bn.SUBMIT_DATE AS DATE) BETWEEN '$date_from' AND '$date_to') AND bl.ID=bn.ISSUE_ID;";
    $rst_personal_bug_note = $GLOBALS['db']->query($qry_personal_bug_note);
    $personal_bug_note_withdate = $rst_personal_bug_note->fetch_all(MYSQLI_ASSOC);
    return $personal_bug_note_withdate;
}

/*
 * Dispaly the general task efort
 */

function display_general_task_effort($user_name, $total_task_time, $total_support_time, $total_bug_time, $total_task_effort) {
    ?>
    <table class="table table-striped table-bordered table-condensed">
        <caption><h4>General Task Effort</h4></caption>
        <thead>
            <tr>
                <th>Name</th>
                <th>Task (h)</th>
                <th>Support (h)</th>
                <th>Bug (h)</th>
                <th>Total (h)</th>
            </tr>
        </thead>
        <tr>
            <td  class="chinese"><?php echo $user_name; ?></td>
            <td><?php echo $total_task_time; ?></td>
            <td><?php echo $total_support_time; ?></td>
            <td><?php echo $total_bug_time; ?></td>
            <td class="<?php echo ($total_task_effort < 6.5 ? "warn" : ""); ?>"><?php echo $total_task_effort; ?></td>
        </tr>
    </table>
    <?php
}

/*
 * Push personal support info into table
 */

function personal_support_info_into_table($personal_support_info_withdate, $available_user) {
    ?>
    <table class="table table-striped table-condensed">
        <thead>
            <tr>
                <th>Supporter</th>
                <th>Task</th>
                <th>Time</th>
                <th>Date</th>
                <th>Submitter</th>
                <th class="task_comment">Comments</th>
            </tr>
        </thead>
        <?php foreach ($personal_support_info_withdate as $psiw_key => $psiw_val) { ?>
            <tr>
                <td class="chinese">
                    <?php 
                    echo $available_user[$psiw_val['SUPPORTER']]['user_name']; 
                    ?>
                </td>
                <td><?php echo $psiw_val['TASK_ID']; ?></td>
                <td><?php echo round($psiw_val['TIME']/60,2); ?></td>
                <td><?php echo date("m/d/y", strtotime($psiw_val['DATE'])); ?></td>
                <td class="chinese">
                    <?php 
                    echo $available_user[$psiw_val['SUBMITTER']]['user_name']; 
                    ?>
                </td>
                <td class="chinese"><?php echo $psiw_val['COMMENT']; ?></td>
            </tr>
        <?php }
        ?>
    </table>
    <?php
}

/*
 * Push personal bug & bug not info into table
 */

function personal_bug_info_into_table($bug_info) {
    ?>
    <tr>
        <td><?php echo $bug_info['BUG_ID']; ?></td>
        <td><?php echo $bug_info['ITERATION_NAME']; ?></td>
        <td><?php echo $bug_info['BUILD_ID']; ?></td>
        <td><?php echo $bug_info['ACCOUNT']; ?></td>
        <td class = "chinese"><?php echo $bug_info['USER_NAME']; ?></td>
        <td><?php echo $GLOBALS['BUG_STATUS'][$bug_info['BUG_STATUS']][0]; ?></td>
        <td><?php echo (isset($bug_info['EXECUTE_TIME']) ? round($bug_info['EXECUTE_TIME']/60,2) : ''); ?></td>
        <!--<td><?php // echo (isset($bug_info['NOTE_OWNER']) ? $bug_info['NOTE_OWNER'] : ''); ?></td>-->
        <td><?php echo (isset($bug_info['NOTE_TIME']) ? round($bug_info['NOTE_TIME']/60,2) : ''); ?></td>
    </tr>
    <?php
}

/**
 * Author: CC
 * Last Modified: 08/11/2013
 * [personal_bug_info_into_table_subscribe Push bug & bug not info into table]
 * @param  [type] $bug_info  [description]
 * @param  [type] $user_type [description]
 * @return [type]            [description]
 */
function personal_bug_info_into_table_subscribe($bug_info, $user_type) {
    ?>
    <tr>
        <td><?php echo $bug_info['BUG_ID']; ?></td>
        <td><?php echo $bug_info['COMMENTS']; ?></td>
        <td><?php echo $bug_info['ACCOUNT']; ?></td>
        <?php
        if($user_type != 4){ ?>
            <td class='chinese'><?php echo $bug_info['USER_NAME']; ?></td>
            <td><?php echo $GLOBALS['BUG_STATUS'][$bug_info['BUG_STATUS']][0]; ?></td>
        <?php } else{
            echo "<td class='chinese'>".(isset($bug_info['OWNER']) ? $bug_info['OWNER']: $bug_info['NICK_NAME'])."</td>";
        }
        ?>
        
        <td><?php echo (isset($bug_info['EXECUTE_TIME']) ? round($bug_info['EXECUTE_TIME']/60,2) : ''); ?></td>
    </tr>
    <?php
}

/*
 *  collect all the four kinds of time, re-arrange them by user_id.
 * e.g.
 * user_name,task_time,support_time,bug_time,bug_note_time
 */

function general_org_task_efort_analysis($available_user, $task_time, $support_time, $bug_time, $bug_note_time) {
    $general_org_task_effort = array();
    foreach ($available_user as $au_key => $au_val) {
        $general_org_task_effort[$au_key] = array(
            'user_name' => $au_val['user_name'],
            'task_time' => isset($task_time[$au_key]['sum_time']) ? $task_time[$au_key]['sum_time'] : 0,
            'ex_time'   => isset($task_time[$au_key]['ex_time']) ? $task_time[$au_key]['ex_time'] : 0,
            'support_time' => isset($support_time[$au_key]['sum_time']) ? $support_time[$au_key]['sum_time'] : 0,
            'bug_time' => isset($bug_time[$au_key]['sum_time']) ? $bug_time[$au_key]['sum_time'] : 0,
            'bug_note_time' => isset($bug_note_time[$au_key]['sum_time']) ? $bug_note_time[$au_key]['sum_time'] : 0
        );
    }
    return $general_org_task_effort;
}

/*
 * Push all of the data into table
 */

function display_general_org_task_effort($general_org_task_effort) {
    ?>
    <table id="by_tester" class="table table-striped table-bordered table-condensed">
        <caption><h4>General Task Effort</h4></caption>
        <thead>
            <tr>
                <th>Name</th>
                <th>Actual(Expected) Task (h)</th>
                <th>Support (h)</th>
                <th>Bug (h)</th>
                <th>Bug Note (h)</th>
                <th>Total (h)</th>
            </tr>
        </thead>
        <?php
        foreach ($general_org_task_effort as $gote_key => $gote_val) {
            $total = round(($gote_val['task_time'] + $gote_val['support_time'] + $gote_val['bug_time'] + $gote_val['bug_note_time']) / 60, 2);
            ?>
            <tr>
                <td class="chinese"><?php echo $gote_val['user_name']; ?></td>
                <td class="tester" id ="<?php echo $gote_key.'@'.$gote_val['user_name']; ?>" style="cursor:pointer" value="1"><?php echo round($gote_val['task_time'] / 60, 2).' ('.round($gote_val['task_time'] / 60, 2).')'; ?></td>
                <td class="tester" id ="<?php echo $gote_key.'@'.$gote_val['user_name']; ?>" style="cursor:pointer" value="4"><?php echo round($gote_val['support_time'] / 60, 2); ?></td>
                <td class="tester" id ="<?php echo $gote_key.'@'.$gote_val['user_name']; ?>" style="cursor:pointer" value="2"><?php echo round($gote_val['bug_time'] / 60, 2); ?></td>
                <td class="tester" id ="<?php echo $gote_key.'@'.$gote_val['user_name']; ?>" style="cursor:pointer" value="3"><?php echo round($gote_val['bug_note_time'] / 60, 2); ?></td>
                <td class="<?php echo ($total < 6.5 ? "warn" : ""); ?>"><?php echo $total; ?></td>
            </tr>
        <?php } ?>
    </table>     
    <?php
}

/*
 * Calculate monthly_task_effort_bhc
 */

function monthly_task_effort_bhc($month_int, $str_year, $sub_org, $orgs) {
    $monthly_task_effort_bhc = array();
    for ($n = $month_int; $n > 0; $n--) {
        $str_m = sprintf('%02d', $n);
        $date_from = $str_year . "-" . $str_m . "-01";
        $date_to = $str_year . "-" . $str_m . "-31";
        $org_time[$str_m] = task_effort($sub_org, $orgs, $date_from, $date_to);
    }

    /*
     * Cal total task effort
     */

    foreach ($org_time as $ot_key => $ot_val) {
        $father_org_total = 0;
        foreach ($ot_val as $ov_key => $ov_val) {
            $father_org_total +=$ov_val['total_org_total_time'];
        }
        $monthly_task_effort_bhc[$ot_key] = $father_org_total;
    }
    return $monthly_task_effort_bhc;
}

/*
 * Calculate monthly_task_effort_ahc
 */

function monthly_task_effort_ahc($month_int, $str_year, $ooid) {
    $monthly_task_effort_ahc = array();
    for ($j = $month_int; $j > 0; $j--) {
        $str_mm = sprintf('%02d', $j);
        $current_month = $str_year . "-" . $str_mm;
        $monthly_task_effort_ahc[$str_mm] = task_effort_by_ahc_monthly($_SESSION['monthly_ahc_list'][$ooid][$j], $current_month);
    }
    return $monthly_task_effort_ahc;
}

/**
 * Author: CC
 * Date: 2012-11-12
 * [tae_in_dates Get all valid employees' task]
 * @param  [type] $date_start [description]
 * @param  [type] $date_end   [description]
 * @return [type]             [description]
 */
function tae_in_dates($date_start, $date_end) {
    $qry_task_effort = "SELECT NO,ITERATION_ID,TASK_ID,SCENARIO_ID,ASSIGN_DATE,FINISH_DATE,TESTER,EX_SETUP_TIME,EX_EXECUTION_TIME,AC_SETUP_TIME,AC_EXECUTION_TIME,INVESTIGATE_TIME FROM task_assignment_execution WHERE (FINISH_DATE BETWEEN '$date_start' AND '$date_end') AND CORRECT <> 'Y';";
    $rst_task_effort = $GLOBALS['db']->query($qry_task_effort);
    $task_effort = $rst_task_effort->fetch_all(MYSQLI_ASSOC);
    return $task_effort;
}

/**
 * @author: CC
 * Date: 2012-11-13
 * [support_in_dates Get all valid employees' support info]
 * @param  [type] $date_start [description]
 * @param  [type] $date_end   [description]
 * @return [type]             [description]
 */
function support_in_dates($date_start, $date_end) {
    $qry_support_effort = "SELECT ID,TASK_ID,SUPPORTER,TIME,DATE,SUBMITTER FROM task_support_info WHERE (DATE BETWEEN '$date_start' AND '$date_end');";
    $rst_support_effort = $GLOBALS['db']->query($qry_support_effort);
    $support_effort_temp = $rst_support_effort->fetch_all(MYSQLI_ASSOC);

    if (!empty($support_effort_temp)) {
        foreach ($support_effort_temp as $set_key => $set_val) {
            $support_effort[$set_val['SUPPORTER']][] = $set_val;
        }
    } else {
        $support_effort[] = array();
    }
    return $support_effort;
}

/**
 * @author CC
 * @return no return values. just display the page
 * @date 2013-01-26
 * @description dispaly the personal task records through params
 * */
function display_personal_task_records($oid, $emp, $date_from, $date_to, $su_id, $gtype, $optype) {
    $total_task_time_temp = 0;
    $total_support_time_temp = 0;
    $total_bug_time_temp = 0;
    foreach($emp as $emp_key => $emp_val){
        $emp_available[$emp_key]['user_name'] = $emp_val['USER_NAME'];
    }
    /*
     * Query all the task status info
     */

    /*
     * Query all support  info status
     */
    $personal_support_info_withdate = personal_support_info_withdate($_SESSION['user_id'], $date_from, $date_to);
    /*
     * Query all bug info status
     */
    $personal_bug_info_withdate = personal_bug_info_withdate($_SESSION['user_id'], $date_from, $date_to);
    /*
     * Query all bug not info status of the user 
     */
    $personal_bug_note_info_withdate = personal_bug_note_info_withdate($_SESSION['user_id'], $date_from, $date_to);


    /*
     * Caculate total support time
     */

    foreach ($personal_support_info_withdate as $psiw_key => $psiw_val) {
        $total_support_time_temp +=$psiw_val['TIME'];
    }

    /*
     * Caculate total bug time
     */

    foreach ($personal_bug_info_withdate as $pbiw_key => $pbiw_val) {
        $total_bug_time_temp +=$pbiw_val['EXECUTE_TIME'];
    }
    foreach ($personal_bug_note_info_withdate as $pbniw_key => $pbniw_val) {
        $total_bug_time_temp +=$pbniw_val['NOTE_TIME'];
    }


    $total_support_time = round($total_support_time_temp / 60, 2);
    $total_bug_time = round($total_bug_time_temp / 60, 2);


    /*
     * Query all the iterations with tasks through the date
     */
    $list_iteration_task_withdate_temp = personal_iteration_task_withdate($_SESSION['user_id'], $date_from, $date_to);
    if (!isset($list_iteration_task_withdate_temp['TASK'])) {
        $list_iteration_task_withdate = array();
    } else {
        $list_iteration_task_withdate = $list_iteration_task_withdate_temp['TASK'];
    }
    if (!empty($list_iteration_task_withdate)) {
        /*
         * Caculate total task execution time
         */
        foreach ($list_iteration_task_withdate as $litw_key => $litw_val) {
            foreach ($litw_val as $lv_key => $lv_val) {
                $total_task_time_temp +=($lv_val['AC_SETUP_TIME'] + $lv_val['AC_EXECUTION_TIME'] + $lv_val['INVESTIGATE_TIME']);
            }
        }
        $total_task_time = round($total_task_time_temp / 60, 2);
    } else {
        $total_task_time = 0;
    }

    $total_task_effort = $total_task_time + $total_support_time + $total_bug_time;
    ?>
    <div id="general_task_effort">
        <?php
        display_general_task_effort($_SESSION['user_name'], $total_task_time, $total_support_time, $total_bug_time, $total_task_effort);
        ?>
    </div>
    <br>
    <?php if (!empty($list_iteration_task_withdate_temp['ITERATION_LIST'])) { ?>
        <div id="task_status">
            <h3>Task Status</h3>
            <?php
            $i = 0;
            foreach ($list_iteration_task_withdate_temp['ITERATION_LIST'] as $litwt_key => $litwt_val) {
                $iteration_task = $list_iteration_task_withdate[$litwt_key];
                ?>
                <table class="table table-condensed table-striped table-hover">
                    <caption><h4><?php echo $litwt_val; ?></h4></caption>
                    <thead>
                        <tr>
                            <th class="td-width">Id</th>
                            <th class="td-width-2" colspan="2">Title</th>
                            <th class="td-width-5">Assign</th>
                            <th class="td-width-5">Finish</th>
                            <th class="td-width-7">Environment</th>
                            <th class="td-width-1">Build</th>
                            <th class="td-width-2">Time</th>
                            <th class="td-width-5">Status</th>
                            <th class="td-width-7">Bug</th>
                            <th class="td-width">Comments</th>
                            <th class="td-width-6"></th>
                        </tr>
                    </thead>
                </table>
                <?php
                $i = my_task_records_push_into_table($iteration_task, $oid, $su_id, $gtype, $optype,$i);
                ?>
            <?php }
            ?>
        </div>
        <?php
    }
    if ($total_support_time != 0) {
        ?>
        <div id="support_status">
            <h3>Support Status</h3>
            <?php
            personal_support_info_into_table($personal_support_info_withdate,$emp_available);
            ?>
        </div>
    <?php } ?>
    <br>
    <?php
    if ($total_bug_time != 0) {
        ?>
        <div id="bug_status">
            <h3>Bug Status</h3>
            <table class="table table-striped table-bordered table-condensed">
                <thead>
                    <tr>
                        <th>Bug ID</th>
                        <th>Iteration</th>
                        <th>Build</th>
                        <th>Account</th>
                        <th>Owner</th>
                        <th>Status</th>
                        <th>Execute Time</th>
                        <th>Note Time</th>     
                    </tr>
                </thead>
                <?php
                foreach ($personal_bug_info_withdate as $pbiw_key => $pbiw_val) {
                    personal_bug_info_into_table($pbiw_val);
                }
                foreach ($personal_bug_note_info_withdate as $pbniw_key => $pbniw_val) {
                    personal_bug_info_into_table($pbniw_val);
                }
                ?>
            </table>
        </div>
        <?php
    }
}

/*
 * 2013-9-17
 * shiyan
 * display general task effort by engineer and iteration
 * mark 
 * 0:engineer
 * 1:iteration
 * 2:iteration info
 * 3:engineer info
 */
function display_iteration_records($subscripted_project,$emp, $user_type, $date_from, $date_to,$mark) {
    if(empty($date_from)||empty($date_to)){
        $date_from = '2000-01-01';
        $date_to   = $GLOBALS['today'];
    }
    $available_user         = array();
    $each_tester_info       = array();
    $each_iteration_info    = array();
    $tester_iteration_time  = array();
    /**
     * Query all support info of the org
     */
    $each_support_time     = array();
    $each_support_time_sum = array();
    $support_info_temp     = project_support_info($subscripted_project, $date_from, $date_to);
    foreach ($support_info_temp as $sit_val) {
        $each_support_time[$sit_val['SUPPORTER']][] = $sit_val['TIME'];
        $each_tester_info[$sit_val['SUPPORTER']]['support_info'][] = $sit_val;
    }

    foreach ($each_support_time as $est_key => $est_val) {
        $user_name = ($user_type != 4)?$emp[$est_key]['USER_NAME']:((!empty($emp[$est_key]['OWNER']))?$emp[$est_key]['OWNER']:$emp[$est_key]['NICK_NAME']);
        $available_user[$est_key] = array(
            'user_id'   => $est_key,
            'user_name' => $user_name
        );
        $each_support_time_sum[$est_key] = array(
            'user_id'   => $est_key,
            'user_name' => $user_name,
            'sum_time'  => array_sum($est_val)
        );
    }
        
    /**
     * Query all the bug info of the org
     */
    $tester_bug_time            = array();
    $tester_bug_time_sum        = array();
    $tester_bug_time_iteration  = array();

    $bug_info_temp = project_bug_withdate($subscripted_project, $date_from, $date_to);
    foreach ($bug_info_temp as $bit_val) {
        $tester_bug_time[$bit_val['REPORTER']][] = $bit_val['EXECUTE_TIME'];
        $tester_bug_time_iteration[$bit_val['ITERATION_ID']][$bit_val['ITERATION_NAME']][] = $bit_val['EXECUTE_TIME'];
        $each_tester_info[$bit_val['REPORTER']]['bug_info'][] = $bit_val;
        $each_iteration_info[$bit_val['ITERATION_ID']]['bug_info'][] = $bit_val;
    }
    
    // query every iteration's bug_time 
    foreach($tester_bug_time_iteration as $tbti_key => $tbti_val){
        foreach ($tbti_val as $key => $value) {
            $tester_iteration_time[$tbti_key][$key]['BUG_TIME'] = array_sum($value);
        }
    }
    
    foreach ($tester_bug_time as $tbt_key => $tbt_val) {
        $user_name = ($user_type != 4)?$emp[$tbt_key]['USER_NAME']:((!empty($emp[$tbt_key]['OWNER']))?$emp[$tbt_key]['OWNER']:$emp[$tbt_key]['NICK_NAME']);
        $available_user[$tbt_key] = array(
            'user_id'   => $tbt_key,
            'user_name' => $user_name
        );
        $tester_bug_time_sum[$tbt_key] = array(
            'user_id'   => $tbt_key,
            'user_name' => $user_name,
            'sum_time'  => array_sum($tbt_val)
        );
    }
   
    

    /*
     * Query all bug note info
     */
    $each_bug_note_time             = array();
    $each_bug_note_time_sum         = array();
    $each_bug_note_time_iteration   = array();
    
    $bug_note_info_temp     = project_bug_note_info($subscripted_project, $date_from, $date_to);
    foreach ($bug_note_info_temp as $bnit_val) {
        $each_bug_note_time[$bnit_val['BUG_NOTE_OWNER']][] = $bnit_val['NOTE_TIME'];
        $each_bug_note_time_iteration[$bnit_val['ITERATION_ID']][$bnit_val['ITERATION_NAME']][] = $bnit_val['NOTE_TIME'];
        $each_tester_info[$bnit_val['BUG_NOTE_OWNER']]['bug_note_info'][] = $bnit_val;
        $each_iteration_info[$bnit_val['ITERATION_ID']]['bug_note_info'][] = $bnit_val;
    }
    // query every iteration's bug_note_time 
    foreach($each_bug_note_time_iteration as $tbnti_key => $tbnti_val){
        foreach ($tbnti_val as $key => $value) {
             $tester_iteration_time[$tbnti_key][$key]['BUG_NOTE_TIME'] = array_sum($value);
        }
    }

    foreach ($each_bug_note_time as $ebnt_key => $ebnt_val) {
        $user_name = ($user_type != 4)?$emp[$ebnt_key]['USER_NAME']:((!empty($emp[$ebnt_key]['OWNER']))?$emp[$ebnt_key]['OWNER']:$emp[$ebnt_key]['NICK_NAME']);
        $available_user[$ebnt_key] = array(
            'user_id'   => $ebnt_key,
            'user_name' => $user_name
        );
        $each_bug_note_time_sum[$ebnt_key] = array(
            'user_id'   => $ebnt_key,
            'user_name' => $user_name,
            'sum_time'  => array_sum($ebnt_val)
        );
    }
    /*
     * Query all the iterations with tasks through the date
     */
    $org_iteration_task_withdate_temp = list_org_task_withdate_by_iteration($subscripted_project, $date_from, $date_to);
    if (!isset($org_iteration_task_withdate_temp['TASK'])) {
        $org_iteration_task_withdate = array();
    } else {
        $org_iteration_task_withdate = $org_iteration_task_withdate_temp['TASK'];
    }
    if (!empty($org_iteration_task_withdate)) {
        $tester_task_time           = array();
        $tester_task_time_sum       = array();
        $tester_task_time_iteration = array();
        foreach ($org_iteration_task_withdate as $oitw_val) {
            foreach ($oitw_val as $ov_val) {
                $tester_task_time[$ov_val['TESTER']]['AC_TOTAL_TIME'][] = $ov_val['AC_SETUP_TIME'] + $ov_val['AC_EXECUTION_TIME'] + $ov_val['INVESTIGATE_TIME'];
                $tester_task_time[$ov_val['TESTER']]['EX_TOTAL_TIME'][] = $ov_val['EX_SETUP_TIME'] + $ov_val['EX_EXECUTION_TIME'];
                $tester_task_time_iteration[$ov_val['ITERATION_ID']][$ov_val['ITERATION_NAME']]['AC_TASK_TIME'][] = $ov_val['AC_SETUP_TIME'] + $ov_val['AC_EXECUTION_TIME'] + $ov_val['INVESTIGATE_TIME'];
                $tester_task_time_iteration[$ov_val['ITERATION_ID']][$ov_val['ITERATION_NAME']]['EX_TASK_TIME'][] = $ov_val['EX_SETUP_TIME'] + $ov_val['EX_EXECUTION_TIME'];
                $each_tester_info[$ov_val['TESTER']]['task_info'][] = $ov_val;
                $each_iteration_info[$ov_val['ITERATION_ID']]['task_info'][] = $ov_val;
            }
        }
        foreach($tester_task_time_iteration as $ttti_key => $ttti_val){
            foreach ($ttti_val as $key => $value) {
                $tester_iteration_time[$ttti_key][$key]['AC_TASK_TIME'] = array_sum($value['AC_TASK_TIME']);
                $tester_iteration_time[$ttti_key][$key]['EX_TASK_TIME'] = array_sum($value['EX_TASK_TIME']);
            }
        }  
        foreach ($tester_task_time as $ttt_key => $ttt_val) {
            $user_name = ($user_type != 4)?$emp[$ttt_key]['USER_NAME']:((!empty($emp[$ttt_key]['OWNER']))?$emp[$ttt_key]['OWNER']:$emp[$ttt_key]['NICK_NAME']);
            $available_user[$ttt_key] = array(
                'user_id'   => $ttt_key,
                'user_name' => $user_name
            );
            $tester_task_time_sum[$ttt_key] = array(
                'user_id'   => $ttt_key,
                'user_name' => $user_name,
                'sum_time'  => array_sum($ttt_val['AC_TOTAL_TIME']),
                'ex_time'   => array_sum($ttt_val['EX_TOTAL_TIME'])
            );
        }
    } else {
        $tester_task_time_sum = NULL;
    }
    $iteration_info['each_iteration_info'] = $each_iteration_info;
    $iteration_info['each_tester_info']    = $each_tester_info;
    $iteration_info['available_user']      = $available_user;
    if(isset($_SESSION['iteration_info'])){
        unset($_SESSION['iteration_info']);
    }
    $_SESSION['iteration_info'] = $iteration_info;
    $general_org_task_effort    = general_org_task_efort_analysis($available_user, $tester_task_time_sum, $each_support_time_sum, $tester_bug_time_sum, $each_bug_note_time_sum);
    $general_iteration_effort   = general_iteration_effort_analysis($tester_iteration_time);
    if(!empty($general_org_task_effort)||!empty($general_iteration_effort)){
        ?>
        <div id="task_effort">
             <?php
             if($mark == 0||$mark == 3){
                 display_general_org_task_effort($general_org_task_effort);
             }else{
                 display_general_iteration_effort($general_iteration_effort);
             }
            ?>
        </div>
        <?php
    } else {
        error_without_article("204");
    }
}

function display_iteration_tester_records($iteration_info,$iteration_id,$iteration_name,$tester_id,$tester_name, $user_type,$mark,$flag,$emp) {
    $each_iteration_info = $iteration_info['each_iteration_info'];
    $each_tester_info    = $iteration_info['each_tester_info'];
    $available_user      = $iteration_info['available_user'];
    ?>
    <div id="task_effort">
        <?php
            if($mark == 2){
                ?>
                <div>
                    <span class="label label-info"><?php 
                        if(!empty($iteration_name)){
                            echo $iteration_name; 
                         }?>
                    </span>
                 </div><br>
                <?php
                    push_task_tester_info_into_table($each_iteration_info, $iteration_id,$available_user,$user_type,$flag);
            }else if($mark == 3){
                ?>
                <div>
                    <span class="label label-info"><?php 
                        if(!empty($tester_name)){
                            echo $tester_name; 
                         }?>
                    </span>
                </div><br>
                <?php
                push_task_tester_info_into_table($each_tester_info, $tester_id,$available_user,$user_type,$flag);
                if($flag == 4){
                    ?>
                        <div id="support_status">
                            <h3>Support Status</h3>
                            <?php
                            if (!empty($each_tester_info[$tester_id]['support_info'])) {
                                foreach($emp as $e_key => $e_val){
                                    if($_SESSION['type']!=4){
                                        $user[$e_key]['user_name'] = $e_val['USER_NAME'];
                                    }else{
                                        if(isset($e_val['OWNER'])){
                                            $user[$e_key]['user_name'] = $e_val['OWNER'];
                                        }else{
                                            $user[$e_key]['user_name'] = $e_val['NICK_NAME'];
                                        }
                                    }
                                }
                                personal_support_info_into_table($each_tester_info[$tester_id]['support_info'],$user);
                            } else {
                                echo "<p class='chinese'>No records found!</p><br />";
                            }?>
                        </div>
                    <?php 
                }
            }
            ?>
        </div>
        <?php
}

function push_task_tester_info_into_table($each_iteration_info,$iteration_id,$available_user,$user_type,$flag){
    if($flag == 1){
    ?>
    <div id="task_status"> 
        <h3 class="chinese">Task Status</h3>
        <?php
        if (!empty($each_iteration_info[$iteration_id]['task_info'])) {
                $iteration_task = $each_iteration_info[$iteration_id]['task_info'];
                ?>
                <table class="table table-condensed table-striped table-hover">
                    <thead>
                        <tr>
                            <th colspan="3">Title/Id</th>
                            <th>Assign</th>
                            <th>Finish</th>
                            <th class="span4">Environment</th>
                            <th>Build</th>
                            <th class="span1 chinese">Tester</th>
                            <th class="span1" colspan="2">Expected/Actual Time</th>
                            <th class="span1">Status</th>
                            <th>Bug</th>
                            <th class="span1">Comments</th>
                        </tr>
                    </thead>
                    <?php
                    status_push_into_table($iteration_task,$available_user);
                    ?>
                </table>
                <?php
        } else {
             echo "<p class='chinese'>No records found!</p><br />";
        }
        ?>
    </div>
    <?php
    }
    if($flag == 2){
        ?>
        <div id="bug_status">
            <h3 class="chinese">Bug Status</h3>
            <?php 
            if (!empty($each_iteration_info[$iteration_id]['bug_info'])){
                foreach($each_iteration_info[$iteration_id]['bug_info'] as $eii_val){
                    $each_iteration_info[$iteration_id]['bug_info']['build']['bug_category'][$eii_val['BUILD_ID']][$eii_val['BUG_CATEGORY']][]=$eii_val;
                }
                foreach ($each_iteration_info[$iteration_id]['bug_info']['build']['bug_category'] as $eii_key => $eii_val) {
                    ?>
                    <div>
                        <span class="label label-success"><?php echo 'Build: '.$eii_key; ?></span>
                    </div>
                    <?php
                    foreach($eii_val as $ev_key=>$ev_val){
                        ?>
                        <div>
                            <span  class="label label-<?php echo $GLOBALS['BUG_CATEGORY_COLOR'][$ev_key][1]; ?>"><?php echo $GLOBALS['BUG_CATEGORY_COLOR'][$ev_key][0]; ?></span>
                        </div><br>
                        <table class="table table-striped table-bordered table-condensed">
                            <thead>
                                <tr>
                                    <th>Bug ID</th>
                                    <th>Bug Title</th>
                                    <th>Account</th>
                                    <?php
                                    if($user_type != 4){ ?>
                                    <th>Reporter</th>
                                    <?php } ?>
                                    <th>Status</th>
                                    <th>Execute Time</th>
                                </tr>
                            </thead>
                            <?php
                            foreach($ev_val as $evv_val){
                                personal_bug_info_into_table_subscribe($evv_val,$user_type);
                            }
                            ?>
                        </table>
                        <?php 
                        }
                    }
                ?>
            </div>
            <?php
        }else {
             echo "<p class='chinese'>No records found!</p><br />";
        }
    }
    if($flag == 3){
    ?>
        <div id="bug_note_status">
            <h3 class="chinese">Bug Note Status</h3> 
            <?php
            if (!empty($each_iteration_info[$iteration_id]['bug_note_info'])){
                foreach($each_iteration_info[$iteration_id]['bug_note_info'] as $eii_val){
                    $each_iteration_info[$iteration_id]['bug_note_info']['build'][$eii_val['BUILD_ID']][]=$eii_val;
                }
                foreach ($each_iteration_info[$iteration_id]['bug_note_info']['build'] as $eii_key => $eii_val) {
                    ?>
                    <div>
                        <span class="label label-success"><?php echo 'Build: '.$eii_key; ?></span>
                    </div><br>
                    <?php
                ?>
                <table class="table table-striped table-bordered table-condensed">
                    <thead>
                        <tr>
                            <th>Bug ID</th>
                            <th>Content</th>
                            <th>Account</th>
                            <?php
                            if($user_type != 4){ ?>
                            <th>Reporter</th>
                            <?php } ?>
                            <th>Note Owner</th>
                            <th>Note Time</th>
                        </tr>
                    </thead>
                    <?php                       
                    foreach ($eii_val as $eiiv_val) {
                        personal_bug_note_info_into_table_subscribe($eiiv_val,$user_type);
                    }
                    ?>
                </table>
            </div>
            <?php 
            }
        }else {
             echo "<p class='chinese'>No records found!</p><br />";
        }
    }
}
/*
 * 2013-9-16
 * author:shiyan
 * collect iteration's three kinds of time
 * iteration_id,iteration_name,task_time,bug_time,bug_note_time
 */
function general_iteration_effort_analysis($tester_iteration_time) {
    $general_iteration_effort = array();
    foreach ($tester_iteration_time as $tit_key => $tit_val) {
        foreach ($tit_val as $key => $value) {
            $general_iteration_effort[$tit_key] = array(
                'iteration_name' => $key,
                'ex_time'   => isset($tester_iteration_time[$tit_key][$key]['AC_TASK_TIME']) ? $tester_iteration_time[$tit_key][$key]['EX_TASK_TIME'] : 0,
                'task_time' => isset($tester_iteration_time[$tit_key][$key]['AC_TASK_TIME']) ? $tester_iteration_time[$tit_key][$key]['AC_TASK_TIME'] : 0,
                'bug_time' => isset($tester_iteration_time[$tit_key][$key]['BUG_TIME']) ? $tester_iteration_time[$tit_key][$key]['BUG_TIME'] : 0,
                'bug_note_time' => isset($tester_iteration_time[$tit_key][$key]['BUG_NOTE_TIME']) ? $tester_iteration_time[$tit_key][$key]['BUG_NOTE_TIME'] : 0
            );
        }
    }
    return $general_iteration_effort;
}

/*
 * Push all of the data into table
 */
function display_general_iteration_effort($general_iteration_effort) {
    ?>
    <table id="by_iteration" class="table table-striped table-bordered table-condensed">
        <thead>
            <tr>
                <th>Iteration Name</th>
                <th>Actual(Expected) Task (h)</th>
                <th>Bug (h)</th>
                <th>Bug Note (h)</th>
                <th>Total (h)</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $total_ex_time          = 0;
        $total_task_time        = 0;
        $total_bug_time         = 0;
        $total_bug_note_time    = 0;
        foreach ($general_iteration_effort as $gie_key => $gie_val) {
            $total_ex_time          += $gie_val['ex_time'];
            $total_task_time        += $gie_val['task_time'];
            $total_bug_time         += $gie_val['bug_time'];
            $total_bug_note_time    += $gie_val['bug_note_time'];
            $total = round(($gie_val['task_time'] + $gie_val['bug_time'] + $gie_val['bug_note_time']) / 60, 2);
            ?>
            <tr>
                <td class="chinese"><?php echo $gie_val['iteration_name']; ?></td>
                <td class="iteration td_hover" id="<?php echo $gie_key.'@'.$gie_val['iteration_name']; ?>"style="cursor:pointer" value="1"><?php echo round($gie_val['task_time'] / 60, 2).' ('.round($gie_val['ex_time'] / 60, 2).')'; ?></td>
                <td class="iteration td_hover" id="<?php echo $gie_key.'@'.$gie_val['iteration_name']; ?>"style="cursor:pointer" value="2"><?php echo round($gie_val['bug_time'] / 60, 2); ?></td>
                <td class="iteration td_hover" id="<?php echo $gie_key.'@'.$gie_val['iteration_name']; ?>"style="cursor:pointer" value="3"><?php echo round($gie_val['bug_note_time'] / 60, 2); ?></td>
                <td class="<?php echo ($total < 6.5 ? "warn" : ""); ?>"><?php echo $total; ?></td>
            </tr>
        <?php } ?>
            <tr>
                <td><b>Total(H)</b></td>
                <td><b><?php echo round($total_task_time / 60, 2); ?></b></td>
                <td><b><?php echo round($total_bug_time / 60, 2); ?></b></td>
                <td><b><?php echo round($total_bug_note_time / 60, 2); ?></b></td>
                <td><b><?php echo round(($total_task_time+$total_bug_time+$total_bug_note_time) / 60, 2); ?></b></td>
            </tr>
        </tbody>
    </table>     
    <?php
}

/*2013-9-22
 * shiyan
 * put bug note into table
 */
function personal_bug_note_info_into_table_subscribe($bug_info, $user_type) {
    ?>
    <tr>
        <td><?php echo $bug_info['BUG_ID']; ?></td>
        <td><?php echo $bug_info['CONTENT']; ?></td>
        <td><?php echo $bug_info['ACCOUNT']; ?></td>
        <?php
        if($user_type != 4){ ?>
            <td class='chinese'><?php echo $bug_info['USER_NAME']; ?></td>
            <td class='chinese'><?php  echo (isset($bug_info['NOTE_OWNER']) ? $bug_info['NOTE_OWNER'] : ''); ?></td>
        <?php } else{
            echo "<td class='chinese'>".(isset($bug_info['OWNER']) ? $bug_info['OWNER']: $bug_info['NICK_NAME'])."</td>";
        }
        ?>
        
        <td><?php  echo (isset($bug_info['NOTE_TIME']) ? round($bug_info['NOTE_TIME']/60,2) : ''); ?></td>
    </tr>
    <?php
}
/**
 * 2013-9-23
 * shiyan
 * list all iteration info by $oid
 */
function list_iteration_info($oid,$date_from,$date_to){
    $qry_iteration_task = "SELECT ii.FK_PROJECT_ID,ii.ACTUAL_START,ii.ACTUAL_END,ii.ESTIMATED_START,ii.ESTIMATED_END,ii.EXPECTED_START,
                    ii.EXPECTED_END,tae.ITERATION_ID ITERATION_ID,ITERATION_NAME, SEQUENCE_ID, NO, TASK_ID, TASK_TITLE, SCENARIO_ID, 
                    SCENARIO_TITLE,ASSIGN_DATE, FINISH_DATE,ENVIRONMENT, BUILD_INFO, TESTER,EX_SETUP_TIME,EX_EXECUTION_TIME,
                    AC_SETUP_TIME, AC_EXECUTION_TIME, INVESTIGATE_TIME, tae.COMMENTS,CORRECT, tae.STATUS as STATUS , tae.BUG_INFO as BUG_INFO 
                    FROM task_assignment_execution as tae, iteration_info as ii, project_info as pi WHERE ii.FK_PROJECT_ID in ($oid) AND CORRECT='N' AND tae.iteration_id = ii.iteration_id AND ii.FK_PROJECT_ID=pi.ID AND tae.FINISH_DATE BETWEEN '$date_from' AND '$date_to' 
                    ORDER BY ITERATION_ID,FINISH_DATE, TASK_ID,SCENARIO_ID,TESTER ASC;";
    $rst_iteration_task = $GLOBALS['db']->query($qry_iteration_task); 
    $iteration_task = $rst_iteration_task->fetch_all(MYSQLI_ASSOC);
    return $iteration_task;
}

?>
