<link rel="stylesheet" type="text/css" href="../../lib/css/3rd_party/css/bootstrap.css">
<?php
// ==================================================================
//
// Author: CC
// Date: 2013-12-12
// Execute Frequency: December 31, Every Year
// 
// Effective Work Time (Per Engineer)
// TBD		   Algorithm:PersonalEffectiveDailyHours/TeamAverageEffectiveDailyHours × 100% × TeamWeight 
// 
// New Algorithm:
// PersonalEffectiveDailyHours = PersonalEffectiveTask/EffectiveWorkday
// PersonalEffectiveTask: Get from TAE directlt
// EffectiveWorkday: Get from daily_project_ahc
// 
// Description:
// Calculate each engineer's Task Effort
//
// ------------------------------------------------------------------

require_once __DIR__ . '../../function/date.php';
require_once __DIR__ . '../../../mysql.php';
require_once __DIR__ . '../../../debug.php';


// Effective Work Time (Per Engineer)
// 		   Algorithm:PersonalEffectiveDailyHours/TeamAverageEffectiveDailyHours × 100% × TeamWeight 
// 		   One engineer may worked for several projects, so calculate each project seperately.
//Query user info
$qry_user_info = "SELECT USER_ID,HR_ID,USER_NAME, oi.`NAME` ORG_NAME FROM user_info AS ui, user_org_group AS uog, org_info AS oi WHERE EMPLOYEE_END IS NULL AND ui.USER_ID=uog.FK_USER_ID AND uog.FK_ORG_ID=oi.ID;";
$rst_user_info = $db->query($qry_user_info);
$original_user_info = $rst_user_info->fetch_all(MYSQLI_ASSOC);
foreach ($original_user_info as $oui_key => $oui_val) {
	$user_info[$oui_val['USER_ID']] = $oui_val;
}

// Yearly tae
$qry_yearly_tae = "SELECT pi.ID PROJECT_ID, pi.`NAME` PROJECT_NAME,ii.ITERATION_ID,ii.ITERATION_NAME,tae.TASK_ID,tae.TASK_TITLE,tae.SCENARIO_ID,tae.SCENARIO_TITLE,(EX_SETUP_TIME+EX_EXECUTION_TIME) EXPECT_TIME,(AC_SETUP_TIME+AC_EXECUTION_TIME+(if(INVESTIGATE_TIME IS NULL,0,INVESTIGATE_TIME))) ACTUAL_TIME,FINISH_DATE,tae.TESTER,ui.USER_NAME FROM task_assignment_execution AS tae, iteration_info AS ii, project_info AS pi, user_info AS ui 
WHERE tae.ITERATION_ID=ii.ITERATION_ID AND ii.FK_PROJECT_ID=pi.ID AND tae.TESTER=ui.USER_ID AND (tae.FINISH_DATE BETWEEN '".$year."-01-01' AND '".$year."-12-31') AND ui.EMPLOYEE_END IS NULL;";
$rst_yearly_tae = $db->query($qry_yearly_tae);
$original_yearly_tae = $rst_yearly_tae->fetch_all(MYSQLI_ASSOC);
foreach ($original_yearly_tae as $oyt_key => $oyt_val) {
	$personal_yearly_effort_original[$oyt_val['TESTER']][] = $oyt_val;
}

// Yearly bug
$qry_yearly_bug = "SELECT BUG_ID,FK_PROJECT_ID,REPORTER,EXECUTE_TIME ACTUAL_TIME,DATE(SUBMIT_DATE) SUBMIT_DATE FROM bug_library WHERE (SUBMIT_DATE BETWEEN '".$year."-01-01' AND '".$year."-12-31');";
$rst_yearly_bug = $db->query($qry_yearly_bug);
$original_yearly_bug = $rst_yearly_bug->fetch_all(MYSQLI_ASSOC);
foreach ($original_yearly_bug as $oyb_key => $oyb_val) {
	$personal_yearly_effort_original[$oyb_val['REPORTER']][] = $oyb_val;
}

// Yearly support
$qry_yearly_support = "SELECT upgi.FK_PROJECT_ID PROJECT_ID, tsf.TASK_ID,tsf.SUPPORTER,tsf.TIME ACTUAL_TIME, tsf.DATE, upgi.START_DATE, upgi.END_DATE FROM `task_support_info` AS tsf, user_project_group_history AS upgi WHERE TASK_ID IS NOT NULL AND (DATE BETWEEN upgi.START_DATE AND upgi.END_DATE) AND tsf.SUPPORTER=upgi.FK_USER_ID AND (DATE BETWEEN '".$year."-01-01' AND '".$year."-12-31');";
$rst_yearly_support = $db->query($qry_yearly_support);
$original_yearly_support = $rst_yearly_support->fetch_all(MYSQLI_ASSOC);
foreach ($original_yearly_support as $oys_key => $oys_val) {
	$personal_yearly_effort_original[$oys_val['SUPPORTER']][] = $oys_val;
}

// Yearly bug note
$qry_yearly_bug_note = "SELECT upgi.FK_PROJECT_ID FK_PROJECT_ID,bn.ISSUE_ID,bn.`OWNER`,bn.EXECUTE_TIME ACTUAL_TIME,DATE(bn.SUBMIT_DATE) SUBMIT_DATE, upgi.START_DATE, upgi.END_DATE 
FROM bug_note AS bn, user_project_group_history AS upgi WHERE (bn.SUBMIT_DATE BETWEEN upgi.START_DATE AND upgi.END_DATE) AND bn.`OWNER`=upgi.FK_USER_ID AND (bn.SUBMIT_DATE BETWEEN '".$year."-01-01' AND '".$year."-12-31');";
$rst_yearly_bug_note = $db->query($qry_yearly_bug_note);
$original_yearly_bug_note = $rst_yearly_bug_note->fetch_all(MYSQLI_ASSOC);
foreach ($original_yearly_bug_note as $oybn_key => $oybn_val) {
	$personal_yearly_effort_original[$oybn_val['OWNER']][] = $oybn_val;
}

$project_yearly_effort_original = array();
foreach ($personal_yearly_effort_original as $pyeo_key => $pyeo_val) {
	// $pyeo_key	user_id
	$EMPLOYEE_TOTAL_ACT = 0;
	foreach($pyeo_val as $ppv_key => $ppv_val){
		// $ppv_key 	finish date
		$EMPLOYEE_TOTAL_ACT += $ppv_val['ACTUAL_TIME'];
	}
	$personal_yearly_effort[$pyeo_key] = round($EMPLOYEE_TOTAL_ACT/60,4);
}
// dump($personal_yearly_effort);

$qry_yearly_ahc = "SELECT * FROM `daily_project_ahc` WHERE YEAR='2013' AND WD='1';";
$rst_yearly_ahc = $db->query($qry_yearly_ahc);
$original_yearly_ahc = $rst_yearly_ahc->fetch_all(MYSQLI_ASSOC);
$yearly_ahc = array();
foreach ($original_yearly_ahc as $oya_key => $oya_val) {
	$ahc_data = json_decode($oya_val['DATA'],TRUE);
	$date = $oya_val['YEAR']."-".$oya_val['MONTH']."-".$oya_val['DAY'];
	if(!empty($ahc_data)){
		foreach ($ahc_data as $ad_key => $ad_value) {
			$yearly_ahc[$ad_value['FK_USER_ID']][$date] = $date;
		}
	}
}
$personal_yearly_ahc = array();
foreach ($yearly_ahc as $ya_key => $ya_val) {
	// $ya_key 	user_id
	$personal_yearly_ahc[$ya_key] = count($ya_val);
}
// dump($personal_yearly_ahc);
// exit();
foreach ($personal_yearly_ahc as $pye_key => $pye_val) {
	// $pye_key 	user_id
	$actual_effort = isset($personal_yearly_effort[$pye_key])?$personal_yearly_effort[$pye_key]:0;
	$wordDay = $pye_val;
	$engineerEffectiveWorkTime[$pye_key]['TOTAL_ACT'] = $actual_effort;
	$engineerEffectiveWorkTime[$pye_key]['WORKING_DAY'] = $wordDay;
	$engineerEffectiveWorkTime[$pye_key]['EffortDay'] = round($actual_effort/$wordDay,4);
}
// dump($engineerEffectiveWorkTime);
?>
<table class="table table-striped table-condensed table-hover">
<caption><h3>Engineer Daily Effort Statistics of 2013</h3></caption>
<thead>
	<tr>
		<th>Resource Org</th>
		<th>HR ID</th>
		<th>Name</th>
		<th>Total Effort(h)</th>
		<th>Work Day(d)</th>
		<th>Effort/Day(h)</th>
	</tr>
</thead>
<?php
foreach ($engineerEffectiveWorkTime as $eewt_key => $eewt_val) { 
	if(isset($user_info[$eewt_key])){ ?>
		<tr>
			<td><?php echo $user_info[$eewt_key]['ORG_NAME']; ?></td>
			<td><?php echo $user_info[$eewt_key]['HR_ID']; ?></td>
			<td><?php echo $user_info[$eewt_key]['USER_NAME']; ?></td>
			<td><?php echo $eewt_val['TOTAL_ACT']; ?></td>
			<td><?php echo $eewt_val['WORKING_DAY']; ?></td>
			<td>
				<?php
				if(($eewt_val['EffortDay'] >= 8)||($eewt_val['EffortDay'] <= 6)){ ?>
					<span class="label label-warning"><?php echo $eewt_val['EffortDay']; ?></span>
				<?php } else{ ?>
					<span class="label label-info"><?php echo $eewt_val['EffortDay']; ?></span>
				<?php }
				?>
			</td>
		</tr>
	<?php }
}
?>
</table>