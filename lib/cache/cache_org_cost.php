<?php
// ==================================================================
//
// Author: CC
// Date: 2012-12-06
// Description: Monthly calculate org's cost (PM or PM above)
// Modified: 08/11/2013
// Change the sql statement. Do not insert in a loop
//
// ------------------------------------------------------------------

require_once __DIR__ . '/../function/org_mgt.php';
require_once __DIR__ . '/../function/date.php';
require_once __DIR__ . '/../inc/constant_asset.php';
require_once __DIR__ . '/../function/check_data_existence.php';

$monthly_org_cost = calculate_org_cost($year, $month, $day, $today,$VALID_ASSET_TYPE_DEPRECIATION, "ORG");

/**
 * Insert the cache data into DB: cache_org_cost
 */

/*
 * 1. check these data already existed or not
 */
check_cache_cost_data_existence('cache_org_cost', 'YEAR', 'MONTH', $year, $month);

//2. insert data into DB
foreach ($monthly_org_cost['COST'] as $moc_key => $moc_val) {
    $cost_data = json_encode($moc_val);
    $moc_array[] = "('$moc_key','$year','$month','$cost_data')";
}
$qry_insert_cost      = "INSERT INTO cache_org_cost (FK_ORG_ID,YEAR,MONTH,DATA) VALUES ".implode(',', $moc_array).";";
$insert_org_cost_data = $GLOBALS['db']->query($qry_insert_cost);
?>
