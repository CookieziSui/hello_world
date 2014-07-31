<?php

require_once __DIR__ . '/../function/week_info.php';
/*
 * Purpose: each kinds of date, if any pgae will use date or date time, just need to include this page
 * Author: CC
 * Date: 2012-11-15
 */

$year            = date('Y');
$month           = date('m');
$year_month      = date('Y-m');
$month_int       = date('n');
$day             = date('d');
$today           = date('Y-m-d');
$yesterday       = date('Y-m-d',strtotime("yesterday"));
$tomorrow        = date('Y-m-d',strtotime("tomorrow"));
$month_today     = date('m/d');
$month_yesterday = date('m/d',strtotime("yesterday"));
$day_of_week     = date('N');

/**
 * [$last_saturday Last Saturday since current day]
 * @var [date]
 */
$last_saturday = last_saturday();

/**
 * [$week_number Week number of current day]
 * @var [int]
 */
$week_number = month_week_number();

/*
*The year and month info of last month
*This founction is mainly used for a specific situation
*Jan of each year
*For this situation, last month means Dec of last year
*/
/**
 * [$last_month Last month with Year - Month]
 * @var [date]
 */
$last_month           = date("Y-m",strtotime(date( 'Y-m-01' )." -1 month"));

/**
 * [$last_month_day Last month with Year - Month - Day]
 * @var [date]
 */
$last_month_day       = date("Y-m-d",strtotime(date( 'Y-m-01')." -1 month"));

/**
 * [$last_month_year Last month only Year number]
 * @var [year]
 */
$last_month_year      = date("Y",strtotime(date( 'Y-m-01' )." -1 month"));

/**
 * [$last_month_month Last month only Month number]
 * @var [month]
 */
$last_month_month     = date("m",strtotime( date( 'Y-m-01' )." -1 month"));

/**
 * [$last_month_month_int Last month, Month number without zero]
 * @var [month]
 */
$last_month_month_int = date("n",strtotime(date( 'Y-m-01' )." -1 month"));

/**
 * [$last_day_of_month description]
 * @var [type]
 */
$last_day_of_last_month = date("t", strtotime($last_month));

/**
 * [$last_sunday Get the date of last Sunday]
 * @var [date]
 */
$last_sunday = last_sunday();

/**
 * [$last_sunday Get the date of last Friday]
 * @var [date]
 */
$last_friday = last_friday();

/**
 * [$this_monday Get the date of this Monday]
 * @var [date]
 */
$this_monday = this_monday();

/**
 * [$last_monday_string Get the date of last Monday string]
 * @var [Unix Timestamp]
 */
$last_monday_string = strtotime("-6 days", strtotime($last_sunday));

/**
 * [$last_monday The date of last Monday]
 * @var [date]
 */
$last_monday = date("Y-m-d", $last_monday_string);
?>
