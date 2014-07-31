<?php

/*
 * Purpose: check the data existence of DB table
 * Author: CC
 * Date: 2012-11-20
 * Comment:
 * If the target data had already been instered into DB, this fucntion will help to delete them
 */

/*
 * This function is for cached employee data.
 * It will be called in 'cache_employee_performance'
 */

function check_cache_effort_data_existence($table, $column_year, $column_month, $column_week, $index_year, $index_month, $index_week) {
    $qry_check = "SELECT * FROM $table WHERE $column_year='" . $index_year . "' AND $column_month='" . $index_month . "' AND $column_week='" . $index_week . "';";
    $rst_qry = $GLOBALS['db']->query($qry_check);
    $record_count = $rst_qry->num_rows;
    if ($record_count > 0) {
        $del_qry = "DELETE FROM $table WHERE $column_year='" . $index_year . "' AND $column_month='" . $index_month . "' AND $column_week='" . $index_week . "';";
        $rst_del = $GLOBALS['db']->query($del_qry);
    }
}

/*
 * This function is for cached ahc data.
 */

function check_cache_ahc_data_existence($table, $column_year, $column_month, $column_week, $column_day, $index_year, $index_month, $index_week, $index_day) {
    $qry_check = "SELECT * FROM $table WHERE $column_year='" . $index_year . "' AND $column_month='" . $index_month . "' AND $column_week='" . $index_week . "' AND $column_day='" . $index_day . "';";
    $rst_qry = $GLOBALS['db']->query($qry_check);
    $record_count = $rst_qry->num_rows;
    if ($record_count > 0) {
        $del_qry = "DELETE FROM $table WHERE $column_year='" . $index_year . "' AND $column_month='" . $index_month . "' AND $column_week='" . $index_week . "' AND $column_day='" . $index_day . "';";
        $rst_del = $GLOBALS['db']->query($del_qry);
    }
}

/*
 * This function is for cached monthly org cost data.
 */

function check_cache_cost_data_existence($table, $column_year, $column_month, $index_year, $index_month) {
    $qry_check = "SELECT * FROM $table WHERE $column_year='" . $index_year . "' AND $column_month='" . $index_month . "';";
    $rst_qry = $GLOBALS['db']->query($qry_check);
    $record_count = $rst_qry->num_rows;
    if ($record_count > 0) {
        $del_qry = "DELETE FROM $table WHERE $column_year='" . $index_year . "' AND $column_month='" . $index_month . "';";
        $rst_del = $GLOBALS['db']->query($del_qry);
    }
}

/**
 * [check_cache_bhc_data_existence Check bhc of the specific date, if exists, the count will be 1, if not exist, the count will be 0]
 * @param  [int] $oid   [org id]
 * @param  [char] $year  [year]
 * @param  [char] $month [month]
 * @param  [char] $day   [day]
 * @return [int]        [record count]
 */
function check_cache_bhc_data_existence($oid, $target_date, $table){
    $prefix_table      = strtolower($table);
    $qry_check_bhc = "SELECT * FROM daily_".$prefix_table."_bhc WHERE FK_".$table."_ID = '$oid' AND CONCAT(YEAR,'-',MONTH,'-',DAY) >= '$target_date';";
    $rst_check_bhc = $GLOBALS['db'] -> query($qry_check_bhc);
    $record_count  = $rst_check_bhc->num_rows;
    return $record_count;
}
?>
