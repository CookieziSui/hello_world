<?php
// ==================================================================
// Author: CC
// Data； 08/08/2013
// [ Cache the org/project ahc daily ]
// Part I: Cache org
// Part II: Cache project
//
// ------------------------------------------------------------------

require_once __DIR__ . '/../function/org_mgt.php';
require_once __DIR__ . '/../function/get_working_days.php';
require_once __DIR__ . '/../function/date.php';
require_once __DIR__ . '/../function/check_data_existence.php';

/**
 * Part I: cache org
 */
$org_user_temp = calculate_op_user("ORG");
$org_user_t    = $org_user_temp['DETAIL'];
$WD            = $org_user_t['WD'];
$org_user      = $org_user_t['USER_ORG'];

/*
 * Push this array into DB
 */
//1. check the target data existence
check_cache_ahc_data_existence('daily_org_ahc', 'YEAR', 'MONTH', 'WEEK', 'DAY', $year, $month, $week_number, $day);

//2. insert data into DB
foreach ($org_user as $ou_db_key => $ou_db_val) {
	$employee_list = json_encode($ou_db_val['LIST']);
	$cache_org[]   = "('$ou_db_key','$year','$month','$week_number','$day','" . $ou_db_val['AHC'] . "','$employee_list','$WD','" . $ou_db_val['MAN_DAYS'] . "')";
}
$insert_org_ahc = "INSERT INTO daily_org_ahc (FK_ORG_ID,YEAR,MONTH,WEEK,DAY,AHC,DATA,WD,MD) VALUES ".implode(',', $cache_org).";";
$rst_org_ahc    = $GLOBALS['db']->query($insert_org_ahc);

/**
 * Part I: cache org
 */
$project_user_temp = calculate_op_user("PROJECT");
$project_user_t    = $project_user_temp['DETAIL'];
$WD                = $project_user_t['WD'];
$project_user      = $project_user_t['USER_ORG'];

/*
 * Push this array into DB
 */
//1. check the target data existence
check_cache_ahc_data_existence('daily_project_ahc', 'YEAR', 'MONTH', 'WEEK', 'DAY', $year, $month, $week_number, $day);

//2. insert data into DB
foreach ($project_user as $pu_key => $pu_val) {
	$employee_list   = json_encode($pu_val['LIST']);
	$cache_project[] = "('$pu_key','$year','$month','$week_number','$day','" . $pu_val['AHC'] . "','$employee_list','$WD','" . $pu_val['MAN_DAYS'] . "')";
}
$insert_project_ahc = "INSERT INTO daily_project_ahc (FK_PROJECT_ID,YEAR,MONTH,WEEK,DAY,AHC,DATA,WD,MD) VALUES ".implode(',', $cache_project).";";
$rst_project_ahc    = $GLOBALS['db']->query($insert_project_ahc);
?>
