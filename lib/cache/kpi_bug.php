<link rel="stylesheet" type="text/css" href="../../lib/css/3rd_party/css/bootstrap.css">
<?php
// ==================================================================
// Author: CC
// Date: December 15, 2013
// Bug Effective (Per Engineer)
//
// [ Generate KPI(bug) for each engineer  ]
//
// ------------------------------------------------------------------

require_once __DIR__ . '../../function/date.php';
require_once __DIR__ . '../../inc/constant_bug.php';
require_once __DIR__ . '../../../mysql.php';
require_once __DIR__ . '../../../debug.php';

//Query user info
$qry_user_info = "SELECT USER_ID,HR_ID,USER_NAME, oi.`NAME` ORG_NAME FROM user_info AS ui, user_org_group AS uog, org_info AS oi WHERE EMPLOYEE_END IS NULL AND ui.USER_ID=uog.FK_USER_ID AND uog.FK_ORG_ID=oi.ID;";
$rst_user_info = $db->query($qry_user_info);
$original_user_info = $rst_user_info->fetch_all(MYSQLI_ASSOC);
foreach ($original_user_info as $oui_key => $oui_val) {
	$user_info[$oui_val['USER_ID']] = $oui_val;
}

$qry_project_info = "SELECT ID PROJECT_ID,`NAME` PROJECT_NAME FROM project_info;";
$rst_project_info = $db->query($qry_project_info);
$original_project_info = $rst_project_info->fetch_all(MYSQLI_ASSOC);
foreach ($original_project_info as $opi_key => $opi_val) {
	$project_info[$opi_val['PROJECT_ID']] = $opi_val['PROJECT_NAME'];
}

$qry_yearly_bug = "SELECT BUG_SYSTEM,BUG_ID,FK_PROJECT_ID PROJECT_ID,REPORTER,SUB_STATUS,date(SUBMIT_DATE) SUBMIT_DATE  FROM `bug_library` WHERE (SUBMIT_DATE BETWEEN '".$year."-01-01' AND '".$year."-12-31');";
$rst_yearly_bug = $db->query($qry_yearly_bug);
$original_yearly_bug = $rst_yearly_bug->fetch_all(MYSQLI_ASSOC);
foreach ($original_yearly_bug as $oyb_key => $oyb_val) {
	if(isset($GLOBALS['VALID_BUG'][$oyb_val['SUB_STATUS']])&& $oyb_val['BUG_SYSTEM'] != 4){
		$personal_yearly_valid_bug_original[$oyb_val['REPORTER']][$oyb_val['PROJECT_ID']][] = $oyb_val;
		$personal_yearly_manday_original[$oyb_val['REPORTER']][$oyb_val['PROJECT_ID']][$oyb_val['SUBMIT_DATE']] = $oyb_val['SUBMIT_DATE'];
	}
}

// Yearly tae to calculate manDay
$qry_yearly_tae = "SELECT pi.ID PROJECT_ID, pi.`NAME` PROJECT_NAME,ii.ITERATION_ID,ii.ITERATION_NAME,tae.TASK_ID,tae.TASK_TITLE,tae.SCENARIO_ID,tae.SCENARIO_TITLE,(EX_SETUP_TIME+EX_EXECUTION_TIME) EXPECT_TIME,(AC_SETUP_TIME+AC_EXECUTION_TIME+(if(INVESTIGATE_TIME IS NULL,0,INVESTIGATE_TIME))) ACTUAL_TIME,FINISH_DATE,tae.TESTER,ui.USER_NAME FROM task_assignment_execution AS tae, iteration_info AS ii, project_info AS pi, user_info AS ui 
WHERE tae.ITERATION_ID=ii.ITERATION_ID AND ii.FK_PROJECT_ID=pi.ID AND tae.TESTER=ui.USER_ID AND (tae.FINISH_DATE BETWEEN '".$year."-01-01' AND '".$year."-12-31') AND ui.EMPLOYEE_END IS NULL;";
$rst_yearly_tae = $db->query($qry_yearly_tae);
$original_yearly_tae = $rst_yearly_tae->fetch_all(MYSQLI_ASSOC);
foreach ($original_yearly_tae as $oyt_key => $oyt_val) {
	$personal_yearly_manday_original[$oyt_val['TESTER']][$oyt_val['PROJECT_ID']][$oyt_val['FINISH_DATE']] = $oyt_val['FINISH_DATE'];
}

$project_yearly_manday_original = array();
$project_yearly_manday = array();
foreach ($personal_yearly_manday_original as $pywo_key => $pywo_val) {
	// $pywo_key 	user_id
	foreach ($pywo_val as $pjv_key => $pjv_val) {
		// $pjv_key 	project id
		$personal_yearly_manday[$pywo_key][$pjv_key] = count($pjv_val);
		$project_yearly_manday_original[$pjv_key][]  = count($pjv_val);;
	}
}

foreach ($project_yearly_manday_original as $pyw_key => $pyw_val) {
	$project_yearly_manday[$pyw_key] = array_sum($pyw_val);
}


$project_yearly_valid_bug_original = array();
$personal_yearly_valid_bug = array();
foreach ($personal_yearly_valid_bug_original as $pyvbo_key => $pyvbo_val) {
	// $pyvbo_key	user_id
	foreach ($pyvbo_val as $pv_key => $pv_val) {
		// $pv_key 	project_id
		foreach($pv_val as $pvv_key => $pvv_val){
			$project_yearly_valid_bug_original[$pv_key][] = $pvv_val;
		}
		$personal_yearly_valid_bug[$pyvbo_key][$pv_key] = count($pv_val);
	}
}


$project_engineers = array();
foreach ($personal_yearly_valid_bug as $pyvb_key => $pyvb_value) {
	// $pyvb_key	user_id
	foreach ($pyvb_value as $ppv_key => $ppv_val) {
		// $ppv_key 	project_id
		$project_engineers[$ppv_key][] = $pyvb_key;
	}
}

$project_yearly_valid_bug = array();
foreach ($project_yearly_valid_bug_original as $pyvbo_key => $pyvbo_val) {
	$project_yearly_valid_bug[$pyvbo_key] = count($pyvbo_val);
}
// dump($project_yearly_valid_bug);

$project_yearly_average_bugManDay = array();
foreach ($project_yearly_valid_bug as $pyvb_key => $pyvb_val) {
	$project_yearly_average_bugManDay[$pyvb_key]['perManDay'] = round($pyvb_val/$project_yearly_manday[$pyvb_key],3);
	$project_yearly_average_bugManDay[$pyvb_key]['manDay'] = $project_yearly_manday[$pyvb_key];
	$project_yearly_average_bugManDay[$pyvb_key]['BugCount'] = $pyvb_val;
}
// dump($project_yearly_average_bugManDay['73']);

$engineerValidBugExpectSeperate = array();
foreach ($personal_yearly_manday as $pym_key => $pym_val) {
	// $pym_key 	engineer id
	foreach ($pym_val as $ppyvbb_key => $ppyvbb_val) {
		// $ppyvbb_key 	project id
		if(isset($project_yearly_average_bugManDay[$ppyvbb_key])){
			$engineerValidBugExpectSeperate[$pym_key][$ppyvbb_key] = round($ppyvbb_val*$project_yearly_average_bugManDay[$ppyvbb_key]['perManDay'],3);
		}
	}
}

$engineerValidBugPercent = array();
foreach ($engineerValidBugExpectSeperate as $evbes_key => $evbes_val) {
	// $pyvbe_key 	user_id
	foreach ($evbes_val as $evbb_key => $evbb_val) {
		// $pyvbe_key 	project_id
		if(isset($personal_yearly_valid_bug[$evbes_key][$evbb_key])){
			$engineerValidBugPercent[$evbes_key][$evbb_key] = $personal_yearly_valid_bug[$evbes_key][$evbb_key]/$evbb_val;
		}else{
			$engineerValidBugPercent[$evbes_key][$evbb_key] = 0;
		}
	}
}

foreach ($engineerValidBugPercent as $evbp_key => $evbp_val) {
	$validBugCount = 0;
	$project_count = 0;
	foreach ($evbp_val as $ev_key => $ev_val) {
		$validBugCount += $ev_val;
		if($ev_val != 0){
			$project_count ++;
		}
	}
	if($project_count == 0){
		$engineerValidBug[$evbp_key] = 0;
	}else{
		$engineerValidBug[$evbp_key] = round($validBugCount/$project_count,3)*100;
	}	
	// $engineerValidBug[$evbp_key] = round($validBugCount/count($evbp_val),3);
}
// dump($engineerValidBug);
// dump($engineerValidBugSeperate);
// exit();
// $project_yearly_average_valid_bug = array();
// foreach ($project_yearly_valid_bug as $proyvb_key => $proyvb_val) {
// 	$project_yearly_average_valid_bug[$proyvb_key]['BugCount'] = $proyvb_val;
// 	$project_yearly_average_valid_bug[$proyvb_key]['HeadCount'] = count($project_engineers[$proyvb_key]);
// 	$project_yearly_average_valid_bug[$proyvb_key]['perEngineer'] = round($proyvb_val/count($project_engineers[$proyvb_key]),2);
// }

// $engineerValidBug = array();
// $engineerValidBugSeperate = array();
// foreach ($personal_yearly_valid_bug as $ppyvb_key => $ppyvb_val) {
// 	// $ppyvb_key 	engineer id
// 	foreach ($ppyvb_val as $ppyvbb_key => $ppyvbb_val) {
// 		// $ppyvbb_key 	project id
// 		$engineerValidBugSeperate[$ppyvb_key][$ppyvbb_key] = round($ppyvbb_val/$project_yearly_average_valid_bug[$ppyvbb_key]['perEngineer'],2)*100;
// 	}
// }

// foreach ($engineerValidBugSeperate as $evbs_key => $evbs_val) {
// 	$validBugRate = 0;
// 	foreach ($evbs_val as $ev_key => $ev_val) {
// 		$validBugRate += $ev_val;
// 	}
// 	$engineerValidBug[$evbs_key] = $validBugRate/count($evbs_val)."%";
// }

?>
<table class="table table-striped table-condensed table-hover">
<caption><h3>Project BUG Statistics of 2013</h3></caption>
<thead>
	<tr>
		<th>Project Name</th>
		<th>Valid Bug Count</th>
		<th>Man Day</th>
		<th>ValidBug/ManDay</th>
	</tr>
</thead>
<?php
foreach ($project_yearly_average_bugManDay as $pyavb_key => $pyavb_val) { ?>
		<tr>
			<td><?php echo $project_info[$pyavb_key]; ?></td>
			<td><?php echo $pyavb_val['BugCount']; ?></td>
			<td><?php echo $pyavb_val['manDay']; ?></td>
			<td><?php echo $pyavb_val['perManDay']; ?></td>
		</tr>
	<?php
}
?>
</table>

<table class="table table-striped table-condensed table-hover">
<caption><h3>Engineer BUG Statistics of 2013</h3></caption>
<thead>
	<tr>
		<th>Resource Org</th>
		<th>HR ID</th>
		<th>Name</th>
		<th>Man Day</th>
		<th>Expected Bug Count</th>
		<th>Actual Bug Count</th>
		<th>Average Bug</th>
	</tr>
</thead>
<?php
foreach ($engineerValidBug as $evb_key => $evb_val) { 
	if(isset($user_info[$evb_key])){ ?>
		<tr>
			<td><?php echo $user_info[$evb_key]['ORG_NAME']; ?></td>
			<td><?php echo $user_info[$evb_key]['HR_ID']; ?></td>
			<td><?php echo $user_info[$evb_key]['USER_NAME']; ?></td>
			<td>
				<?php 
				$arrayEngineerProjectManDay = array();
				foreach ($personal_yearly_manday[$evb_key] as $pym_key => $pym_val) {
					if(isset($project_yearly_average_bugManDay[$pym_key])){
						$arrayEngineerProjectManDay[] = $project_info[$pym_key].":".$pym_val;
					}
				}
				echo implode('<br>', $arrayEngineerProjectManDay); ?>
			</td>
			<td>
				<?php 
				$arrayEngineerExpectProjectBug = array();
				foreach ($engineerValidBugExpectSeperate[$evb_key] as $evbes_key => $evbes_val) {
					// if(isset($project_yearly_average_bugManDay[$pyvb_key])){
						$arrayEngineerExpectProjectBug[] = $project_info[$evbes_key].":".$evbes_val;
					// }
				}
				echo implode('<br>', $arrayEngineerExpectProjectBug); 
				?>
			</td>
			<td>
				<?php 
				$arrayEngineerActualProjectBug = array();
				if(isset($personal_yearly_valid_bug[$evb_key])){
					foreach ($personal_yearly_valid_bug[$evb_key] as $pyvb_key => $pyvb_val) {
						// if(isset($project_yearly_average_bugManDay[$pyvb_key])){
							$arrayEngineerActualProjectBug[] = $project_info[$pyvb_key].":".$pyvb_val;
						// }
					}
				}
				echo implode('<br>', $arrayEngineerActualProjectBug); 
				?>
			</td>
			<td>
				<?php
				if($evb_val >= 100){ ?>
					<span class="label label-warning"><?php echo $evb_val."%"; ?></span>
				<?php } else{ ?>
					<span class="label label-info"><?php echo $evb_val."%"; ?></span>
				<?php }
				?>
				
			</td>
		</tr>
	<?php	
	}
}
?>
</table>