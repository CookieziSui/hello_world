<?php

/*
 * Purpose: Get week number and other week info
 * Copied from stackoverflow
 * URL: http://stackoverflow.com/questions/5853380/php-get-number-of-week-for-month
 */
/**
 * Returns the amount of weeks into the month a date is
 * @param $date a YYYY-MM-DD formatted date
 * @param $rollover The day on which the week rolls over (Define the first day of a week)
 */
//function week_info($date, $rollover) {
//    $cut = substr($date, 0, 8);
//    $daylen = 86400;
//
//    $timestamp = strtotime($date);
//    $first = strtotime($cut . "00");
//    $elapsed = ($timestamp - $first) / $daylen;
//
//    $i = 1;
//    $weeks = 1;
//
//    for ($i; $i <= $elapsed; $i++) {
//        $dayfind = $cut . (strlen($i) < 2 ? '0' . $i : $i);
//        $daytimestamp = strtotime($dayfind);
//
//        $day = strtolower(date("l", $daytimestamp));
//        if ($day == strtolower($rollover))
//            $weeks++;
//    }
//
//    return $weeks;
//}
?>

<?php

/*
 * Purpose: Get week number and other week info
 * Copied from stackoverflow
 * URL: http://stackoverflow.com/questions/12619404/find-the-week-number-in-month-with-a-calendar-starting-on-a-friday
 */

function month_week_number() {
    $currentDate = time();

//Get the day number of the month
    $dayOfMonth = date('j', $currentDate);

//Set the date format to 'YYYY-MM'
    $ym = date('Y-m', $currentDate);

//Find the first Monday
    $firstMonday = strtotime("Monday " . $ym);

//Get the date of first Monday
    $dateOfFirstMonday = date('j', $firstMonday);

// we need to reduce count by 1 if monday is the 1st, to compensate
// for overshooting by a week due to the >= in the while loop
    //$week_number = ($dateOfFirstMonday == 1) ? -1 : 0;
    $week_number = 0;
    while ($dayOfMonth >= $dateOfFirstMonday) {
        $week_number++;
        $dayOfMonth = $dayOfMonth - 7;
    }

    return $week_number;
}

/**
 * [month_week_number_target_date Get the week number of the target date]
 * @param  [date] $target_date [Target date]
 * @return [int]              [Week number]
 */
function month_week_number_target_date($target_date) {
    $currentDate = strtotime($target_date);

//Get the day number of the month
    $dayOfMonth = date('j', $currentDate);

//Set the date format to 'YYYY-MM'
    $ym = date('Y-m', $currentDate);

//Find the first Monday
    $firstMonday = strtotime("Monday " . $ym);

//Get the date of first Monday
    $dateOfFirstMonday = date('j', $firstMonday);

// we need to reduce count by 1 if monday is the 1st, to compensate
// for overshooting by a week due to the >= in the while loop
    //$week_number = ($dateOfFirstMonday == 1) ? -1 : 0;
    $week_number = 0;
    while ($dayOfMonth >= $dateOfFirstMonday) {
        $week_number++;
        $dayOfMonth = $dayOfMonth - 7;
    }

    return $week_number;
}

/*
 * Purpose: Get the date of last Saturday
 */

function last_saturday() {
    $lastSaturday = strtotime("last Saturday");
    $dateOfLastSaturday = date('Y-m-d', $lastSaturday);
    return $dateOfLastSaturday;
}
/*
 * Purpose: Get the date of last Sunday
 */

function last_sunday() {
    $lastSunday = strtotime("last Sunday");
    $dateOfLastSunday = date('Y-m-d', $lastSunday);
    return $dateOfLastSunday;
}
/*
 * Purpose: Get the date of last Friday
 */

function last_friday() {
    $lastFriday = strtotime("last Friday");
    $dateOfLastFriday = date('Y-m-d', $lastFriday);
    return $dateOfLastFriday;
}

/**
 * [this_monday Get the date of this Monday]
 * @return [type] [description]
 */
function this_monday(){
    $today        = strtotime(date('Y-m-d'));
    $weekday_name = date('l', $today);
    if($weekday_name == "Monday"){
        $thisMonday = $today;
    } else {
        $thisMonday = strtotime("this week last monday");
    }
    $dateOfThisMonday = date('Y-m-d', $thisMonday);
    return $dateOfThisMonday;
}
?>