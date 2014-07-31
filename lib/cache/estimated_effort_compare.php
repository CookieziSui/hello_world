<?php

require_once __DIR__ . '/../function/task_mgt.php';
require_once __DIR__ . '/../inc/constant_org.php';
session_start();
//dump($_SESSION['task_records_level']);
foreach ($_SESSION['task_records_level'] as $trl_key => $trl_val) {
    if (isset($trl_val['SENIOR']) && isset($trl_val['JUNIOR'])) {
        if ($trl_val['SENIOR']['BASELINE_AC_SETUP'] > $trl_val['JUNIOR']['BASELINE_AC_SETUP']) {
            $JUNIOR_SENIOR_SETUP[$trl_key] = $trl_val;
        }
         if ($trl_val['SENIOR']['BASELINE_AC_EXECUTION'] > $trl_val['JUNIOR']['BASELINE_AC_EXECUTION']) {
            $JUNIOR_SENIOR_EXECUTION[$trl_key] = $trl_val;
        }
    }
}
$i=1;
//dump($JUNIOR_SENIOR_EXECUTION);
?>
<table>
    <thead>
        <tr>
            <th rowspan="2">No</th>
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
        </tr>
    </thead>
    <tbody>       
        <?php foreach ($JUNIOR_SENIOR_EXECUTION as $trl_key => $trl_val) { ?>
            <tr>
                <td><?php echo $i; ?></td>
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
            </tr>
            <?php $i++;
        }
        ?>
    </tbody>
</table>