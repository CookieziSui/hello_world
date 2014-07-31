<?php
// ==================================================================
//
// Author: CC
// Date: 2013-05-30
// [ Cache the org/project ahc daily ]
// Execute Frequency: 00:05 Everyday
// 
// Part I: Cache org
// Part II: Cache project
// 
// Description:
// Script in this page will calculate daily bhc info of each org
// 
// 1. Get last day's bhc
// 2. Copy last day's bhc to today's bhc
// 		2.1 If exists, skip
// 		2.2 else, add the bhc depend on last day's data
//
// ------------------------------------------------------------------

require_once __DIR__ . '/../function/org_mgt.php';
require_once __DIR__ . '/../function/get_working_days.php';
require_once __DIR__ . '/../function/date.php';
require_once __DIR__ . '/../function/check_data_existence.php';

$target_week_number = month_week_number_target_date($today);
$WD                 = getWorkingDays($today, $today);

/**
 * Part I: cache org
 * [$org_bhc Query the bhc of yesterday]
 */
$org_bhc   = op_bhc_of_day($yesterday, "ORG");
$cache_org = array();
foreach($org_bhc as $ob_key => $ob_val){
	//1. check the target data existence
	$record_count = check_cache_bhc_data_existence($ob_key, $today, "ORG");
	/**
	 * 2. Add bhc info into daily_org_bhc
	 * 		If the record count is 0, it means there is no bhc info of the target date
	 *   	else, the bhc info is already existes, just leave it along.  
	 */
	if($record_count == 0){
		$cache_org[] = "('$ob_key','$year','$month','$target_week_number','$day','".$ob_val['BHC']."','$WD',NULLIF('".$ob_val['DATA']."',''))";
	}
	
}
$insert_org_bhc = "INSERT INTO daily_org_bhc (FK_ORG_ID,YEAR,MONTH,WEEK,DAY,BHC,WD,DATA) VALUES ".implode(',', $cache_org).";";
$rst_org_bhc    = $GLOBALS['db'] -> query($insert_org_bhc);

/**
 * Part II: cache project
 * [$org_bhc Query the bhc of yesterday]
 */
$project_bhc   = op_bhc_of_day($yesterday, "PROJECT");
$cache_project = array();
foreach($project_bhc as $pb_key => $pb_val){
	//1. check the target data existence
	$record_count = check_cache_bhc_data_existence($pb_key, $today, "PROJECT");
	/**
	 * 2. Add bhc info into daily_org_bhc
	 * 		If the record count is 0, it means there is no bhc info of the target date
	 *   	else, the bhc info is already existes, just leave it along.  
	 */
	if($record_count == 0){
		$cache_project[] = "('$pb_key','$year','$month','$target_week_number','$day','".$pb_val['BHC']."','$WD',NULLIF('".$pb_val['DATA']."',''))";
	}
	
}
$insert_project_bhc = "INSERT INTO daily_project_bhc (FK_PROJECT_ID,YEAR,MONTH,WEEK,DAY,BHC,WD,DATA) VALUES ".implode(',', $cache_project).";";
$rst_project_bhc    = $GLOBALS['db'] -> query($insert_project_bhc);
?>
