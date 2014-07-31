<?php

$TASK_STATUS_ASSIGNED      = 1;
$TASK_STATUS_INPROGRESS    = 2;
$TASK_STATUS_PASS          = 3;
$TASK_STATUS_FAIL          = 4;
$TASK_STATUS_BLOCK         = 5;
$TASK_STATUS_SKIP          = 6;
$TASK_STATUS_EXCLUDE       = 7;
$TASK_STATUS_COMPLETEQUERY = 8;
$TASK_STATUS_PASSQUERY     = 9;
$TASK_STATUS_ACTIVEQUERY   = 10;

//@formatter:off

$TASK_STATUS = array(
    '' => array("Unassign", ""),
    1  => array("Assigned", "info"),
    2  => array("In Progress", "info"),
    3  => array("Pass", "success"),
    4  => array("Fail", "important"),
    5  => array("Block", "inverse"),
    6  => array("Skip", "inverse"),
    7  => array("Exclude", "inverse"),
    8  => array("Completed w/Query", "warning"),
    9  => array("Pass w/Issue", "warning"),
    10 => array("Active w/Query", "warning"),
);

$TASK_STATUS_COLOR = array(
    '' => "hsla(240, 2%, 40%, 1.0)", 
    1  => "hsla(265, 85%, 70%, 1.0)",
    2  => "hsla(60, 84%, 60%, 1.0)",
    3  => "hsla(116, 41%, 53%, 1.0)",
    4  => "hsla(6, 66%, 56%, 1.0)",
    5  => "hsla(37, 85%, 60%, 1.0)",
    6  => "hsla(150, 80%, 75%, 1.0)",
    7  => "hsla(194, 66%, 61%, 1.0)",
    8  => "hsla(208, 79%, 54%, 1.0)",
    9  => "hsla(185, 55%, 40%, 1.0)",
    10 => "hsla(80, 52%, 30%, 0.8)"
);
//@formatter:on

$UNFINISHED_STATUS = array(
    '' => array("Unassign", ""),
    1  => array("Assigned", "info"),
    2  => array("In Progress", "info"),
    5  => array("Block", "info"),
    10 => array("Active w/Query", "warning")
);
$FINISHED_STATUS = array(
    3  => array("Pass", "success"),
    4  => array("Fail", "important"),
    6  => array("Skip", "inverse"),
    7  => array("Exclude", "inverse"),
    8  => array("Completed w/Query", "warning"),
    9  => array("Pass w/Issue", "warning"),
);

$STATUS_CAN_ASSIGN = "'1'";
/**
 * [$DATA_TAKEN_TIME_STAMP 
 * The dead line(DL) of data taken. 
 * If the timestamp is larger than this deadline, MP will calculate data including today's data.
 * If the timestamp is smaller than this deadline, MP will calculate data before yeaterday]
 * @var time stamp
 */
$DATA_TAKEN_TIME_DL = date("14:00:00");
?>