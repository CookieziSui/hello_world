<?php
require_once __DIR__ . '/../function/task_mgt.php';
require_once __DIR__ . '/../inc/constant_org.php';
require_once __DIR__ . '/../function/date.php';
set_time_limit(0);
ini_set('memory_limit','512M');
/*
 * Purpose: 
 * Calculate estimated task effort
 * Author: CC
 * Date: 2013-01-11
 */

/*
 * Query all the tasks execution records in TAE
 */
$qry_task_records = "SELECT TASK_ID, TASK_TITLE, SCENARIO_ID, SCENARIO_TITLE, TESTER, LEVEL, FINISH_DATE, AC_SETUP_TIME, AC_EXECUTION_TIME FROM task_assignment_execution WHERE (FINISH_DATE BETWEEN '2011-07-01' AND '2013-07-31')";
$rst_task_records = $db->query($qry_task_records);
$task_records_temp = $rst_task_records->fetch_all(MYSQLI_ASSOC);
foreach ($task_records_temp as $trt_key => $trt_val) {
    $task_records[$trt_val['SCENARIO_ID']][$trt_val['LEVEL']][] = $trt_val;
}

$task_records_level = array();
foreach ($task_records as $tr_key => $tr_val) {
    $count = 0;
    $actual_setup_total = 0;
    $actual_execution_total = 0;
    $array_actual_setup = array();
    $array_actual_execution = array();
    foreach ($tr_val as $tv_key => $tv_val) {
        foreach ($tv_val as $tvv_key => $tvv_val) {
            $array_actual_setup[$LEVEL[$tv_key]][] = $tvv_val['AC_SETUP_TIME'];
            $array_actual_execution[$LEVEL[$tv_key]][] = $tvv_val['AC_EXECUTION_TIME'];
            $actual_setup_total +=$tvv_val['AC_SETUP_TIME'];
            $actual_execution_total +=$tvv_val['AC_EXECUTION_TIME'];
            $count++;
        }
    }
    foreach ($array_actual_setup as $aas_key => $aas_val) {
        $task_records_level[$tr_key][$aas_key]['AC_SETUP_TIME'] = array_sum($aas_val);
        $task_records_level[$tr_key][$aas_key]['COUNT'] = count($aas_val);
        if (($actual_setup_total / $count) == 0) {
            $task_records_level[$tr_key][$aas_key]['BASELINE_AC_SETUP'] = 0;
        } else {
            $task_records_level[$tr_key][$aas_key]['BASELINE_AC_SETUP'] = round((array_sum($aas_val) / count($aas_val)) / ($actual_setup_total / $count), 2);
        }
    }
    foreach ($array_actual_execution as $aae_key => $aae_val) {
        $task_records_level[$tr_key][$aae_key]['AC_EXECUTION_TIME'] = array_sum($aae_val);
        $task_records_level[$tr_key][$aae_key]['COUNT'] = count($aae_val);
        if (($actual_execution_total / $count) == 0) {
            $task_records_level[$tr_key][$aae_key]['BASELINE_AC_EXECUTION'] = 0;
        } else {
            $task_records_level[$tr_key][$aae_key]['BASELINE_AC_EXECUTION'] = round((array_sum($aae_val) / count($aae_val)) / ($actual_execution_total / $count), 2);
        }
    }
    $task_records_level[$tr_key]['COUNT'] = $count;
    $task_records_level[$tr_key]['TOTAL_AC_SETUP'] = $actual_setup_total;
    $task_records_level[$tr_key]['TOTAL_AC_EXECUTION'] = $actual_execution_total;
    $task_records_level[$tr_key]['BASELINE_AC_SETUP'] = round($actual_setup_total / $count, 2);
    $task_records_level[$tr_key]['BASELINE_AC_EXECUTION'] = round($actual_execution_total / $count, 2);
}
session_start();
$_SESSION['task_records_level'] = $task_records_level;

$taskData = array();
foreach ($task_records_level as $trl_key => $trl_val) {

    $taskData[] = "('$trl_key','".(isset($trl_val['SENIOR']['BASELINE_AC_SETUP']) ? $trl_val['SENIOR']['BASELINE_AC_SETUP'] : 0)."','".(isset($trl_val['SENIOR']['BASELINE_AC_EXECUTION']) ? $trl_val['SENIOR']['BASELINE_AC_EXECUTION'] : 0)."','".(isset($trl_val['JUNIOR']['BASELINE_AC_SETUP']) ? $trl_val['JUNIOR']['BASELINE_AC_SETUP'] : 0)."','".(isset($trl_val['JUNIOR']['BASELINE_AC_EXECUTION']) ? $trl_val['JUNIOR']['BASELINE_AC_EXECUTION'] : 0)."','".(isset($trl_val['INTERN']['BASELINE_AC_SETUP']) ? $trl_val['INTERN']['BASELINE_AC_SETUP'] : 0)."','".(isset($trl_val['INTERN']['BASELINE_AC_EXECUTION']) ? $trl_val['INTERN']['BASELINE_AC_EXECUTION'] : 0)."')";
    
    $original_data = json_encode($trl_val);
    $taskDataHistory[] = "('$trl_key','$year','$month','" . (isset($trl_val['SENIOR']['BASELINE_AC_SETUP']) ? $trl_val['SENIOR']['BASELINE_AC_SETUP'] : 0) . "','" . (isset($trl_val['SENIOR']['BASELINE_AC_EXECUTION']) ? $trl_val['SENIOR']['BASELINE_AC_EXECUTION'] : 0) . "','" . (isset($trl_val['JUNIOR']['BASELINE_AC_SETUP']) ? $trl_val['JUNIOR']['BASELINE_AC_SETUP'] : 0) . "','" . (isset($trl_val['JUNIOR']['BASELINE_AC_EXECUTION']) ? $trl_val['JUNIOR']['BASELINE_AC_EXECUTION'] : 0) . "','" . (isset($trl_val['INTERN']['BASELINE_AC_SETUP']) ? $trl_val['INTERN']['BASELINE_AC_SETUP'] : 0) . "','" . (isset($trl_val['INTERN']['BASELINE_AC_EXECUTION']) ? $trl_val['INTERN']['BASELINE_AC_EXECUTION'] : 0) . "','$original_data')";

    $update_cl="UPDATE case_library SET ESTIMATE_SETUPTIME='".$trl_val['BASELINE_AC_SETUP']."', ESTIMATE_EXECTIME='".$trl_val['BASELINE_AC_EXECUTION']."' WHERE SCENARIO_ID='$trl_key';";
    $rst_update_cl=$db->query($update_cl);
}
    $truncate_teb = "TRUNCATE task_estimation_baseline";
    $rst_truncate = $db->query($truncate_teb);
    
    $insert = "INSERT INTO task_estimation_baseline (FK_TASK_ID,SENIOR_SETUP_COEFFICIENCY,SENIOR_EXECUTION_COEFFICIENCY,JUNIOR_SETUP_COEFFICIENCY,JUNIOR_EXECUTION_COEFFICIENCY,INTERN_SETUP_COEFFICIENCY,INTERN_EXECUTION_COEFFICIENCY) 
    VALUES ".implode(",", $taskData);
    $rst_insert = $db->query($insert);
    
    $insert_his = "INSERT INTO task_estimation_baseline_history (FK_TASK_ID,YEAR,MONTH,SENIOR_SETUP_COEFFICIENCY,SENIOR_EXECUTION_COEFFICIENCY,JUNIOR_SETUP_COEFFICIENCY,JUNIOR_EXECUTION_COEFFICIENCY,INTERN_SETUP_COEFFICIENCY,INTERN_EXECUTION_COEFFICIENCY,DATA) 
    VALUES ".implode(",", $taskDataHistory);
    $rst_insert = $db->query($insert_his);

dump("I am done!");
exit();
?>
<!--
Display these into table for analysis with PMs before next step
-->
<table>
    <thead>
        <tr>
            <th rowspan="2">Task ID</th>
            <th rowspan="2">Count</th>
            <th rowspan="2">Total Setup</th>
            <th rowspan="2">BS</th>
            <th rowspan="2">Total Execute</th>
            <th rowspan="2">BS</th>
            <th colspan="6">Senior</th>
            <th colspan="6">Junior</th>
            <th colspan="6">Intern</th>
        </tr>
        <tr>
            <th>Count</th>
            <th>Total Setup</th>
            <th>COE</th>
            <th>Count</th>
            <th>Total Execute</th>
            <th>COE</th>

            <th>Count</th>
            <th>Total Setup</th>
            <th>COE</th>
            <th>Count</th>
            <th>Total Execute</th>
            <th>COE</th>

            <th>Count</th>
            <th>Total Setup</th>
            <th>COE</th>
            <th>Count</th>
            <th>Total Execute</th>
            <th>COE</th>

            <th>DATA</th>
        </tr>
    </thead>
    <tbody>       
        <?php foreach ($task_records_level as $trl_key => $trl_val) { ?>
            <tr>
                <td><?php echo $trl_key; ?></td>
                <td><?php echo $trl_val['COUNT']; ?></td>
                <td><?php echo $trl_val['TOTAL_AC_SETUP']; ?></td>
                <td><?php echo $trl_val['BASELINE_AC_SETUP']; ?></td>
                <td><?php echo $trl_val['TOTAL_AC_EXECUTION']; ?></td>
                <td><?php echo $trl_val['BASELINE_AC_EXECUTION']; ?></td>
                <td><?php echo isset($trl_val['SENIOR']) ? $trl_val['SENIOR']['COUNT'] : 0; ?></td>
                <td><?php echo isset($trl_val['SENIOR']) ? $trl_val['SENIOR']['AC_SETUP_TIME'] : 0; ?></td>
                <td><?php echo isset($trl_val['SENIOR']) ? $trl_val['SENIOR']['BASELINE_AC_SETUP'] : 0; ?></td>
                <td><?php echo isset($trl_val['SENIOR']) ? $trl_val['SENIOR']['COUNT'] : 0; ?></td>
                <td><?php echo isset($trl_val['SENIOR']) ? $trl_val['SENIOR']['AC_EXECUTION_TIME'] : 0; ?></td>
                <td><?php echo isset($trl_val['SENIOR']) ? $trl_val['SENIOR']['BASELINE_AC_EXECUTION'] : 0; ?></td>
                <td><?php echo isset($trl_val['JUNIOR']) ? $trl_val['JUNIOR']['COUNT'] : 0; ?></td>
                <td><?php echo isset($trl_val['JUNIOR']) ? $trl_val['JUNIOR']['AC_SETUP_TIME'] : 0; ?></td>
                <td><?php echo isset($trl_val['JUNIOR']) ? $trl_val['JUNIOR']['BASELINE_AC_SETUP'] : 0; ?></td>
                <td><?php echo isset($trl_val['JUNIOR']) ? $trl_val['JUNIOR']['COUNT'] : 0; ?></td>
                <td><?php echo isset($trl_val['JUNIOR']) ? $trl_val['JUNIOR']['AC_EXECUTION_TIME'] : 0; ?></td>
                <td><?php echo isset($trl_val['JUNIOR']) ? $trl_val['JUNIOR']['BASELINE_AC_EXECUTION'] : 0; ?></td>
                <td><?php echo isset($trl_val['INTERN']) ? $trl_val['INTERN']['COUNT'] : 0; ?></td>
                <td><?php echo isset($trl_val['INTERN']) ? $trl_val['INTERN']['AC_SETUP_TIME'] : 0; ?></td>
                <td><?php echo isset($trl_val['INTERN']) ? $trl_val['INTERN']['BASELINE_AC_SETUP'] : 0; ?></td>
                <td><?php echo isset($trl_val['INTERN']) ? $trl_val['INTERN']['COUNT'] : 0; ?></td>
                <td><?php echo isset($trl_val['INTERN']) ? $trl_val['INTERN']['AC_EXECUTION_TIME'] : 0; ?></td>
                <td><?php echo isset($trl_val['INTERN']) ? $trl_val['INTERN']['BASELINE_AC_EXECUTION'] : 0; ?></td>
                <td><?php echo json_encode($trl_val) ;?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>