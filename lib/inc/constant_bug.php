<?php

/**
 *  Constant for bug mgt
 */
$Regression = 1;
$Not_Regression = 2;
$Query = 3;
$None = 4;

$BUG_CATEGORY = array(
    1 => array("Regression", "background:hsla(190,90%,80%,0.8)"),
    2 => array("Non-Regression", "background:hsla(60,90%,50%,0.7)"),
//    3 => array("Query", "background:hsla(100,80%,70%,0.7)"),
//    4 => array("None", "background:hsla(10,90%,50%,0.7)"),
//    5 => array("HFDB Issue", "background:hsla(10,90%,50%,0.7)"),
//    6 => array("Case Issue", "background:hsla(10,90%,50%,0.7)"),
    7 => array("Data Not Required", "background:hsla(60,90%,50%,0.7)"),
);
$BUG_CATEGORY_COLOR = array(
    1 => array("Regression", "important"),
    2 => array("Non-Regression", "warning"),
    7 => array("Data Not Required", "inverse"),
);
$BUG_TYPE = array(
    1 => array("Function", "background:hsla(190,90%,80%,0.8)"),
    2 => array("L10N", "background:hsla(60,90%,50%,0.7)"),
    3 => array("I18N", "background:hsla(100,80%,70%,0.7)"),
    4 => array("UI", "background:hsla(100,80%,70%,0.7)"),
    5 => array("Other", "background:hsla(10,90%,50%,0.7)"),
);
$TASK_TYPE = array(
    1 => array("Case", "background:hsla(190,90%,80%,0.8)"),
    2 => array("CPR", "background:hsla(60,90%,50%,0.7)"),
    3 => array("HTR", "background:hsla(100,80%,70%,0.7)"),
    4 => array("BVT", "background:hsla(10,90%,50%,0.7)"),
    5 => array("PTR", "background:hsla(100,80%,70%,0.7)"),
    6 => array("Dummy", "background:hsla(100,80%,70%,0.7)"),
    7 => array("Other", "background:hsla(100,80%,70%,0.7)"),
    8 => array("Configure Env", "background:hsla(100,80%,70%,0.7)"),
    9 => array("Case Design", "background:hsla(100,80%,70%,0.7)"),
    10 => array("Exploratory", "background:hsla(100,80%,70%,0.7)")
);
$BUG_STATUS = array(
//    1 => array("New", "background:hsla(190,90%,80%,0.8)"),
//    2 => array("Assigned", "background:hsla(190,90%,80%,0.8)"),
    3 => array("Open", "background:hsla(190,90%,80%,0.8)"),
//    4 => array("Resolved", "background:hsla(190,90%,80%,0.8)"),
    5 => array("Closed", "background:hsla(190,90%,80%,0.8)"),
//    6 => array("Postship", "background:hsla(190,90%,80%,0.8)"),
//    7 => array("Confirm", "background:hsla(190,90%,80%,0.8)"),
//    8 => array("Acknowledged", "background:hsla(190,90%,80%,0.8)"),
//    9 => array("Feedback", "background:hsla(190,90%,80%,0.8)"),
);
$BUG_SUB_STATUS = array(
    0 => array("No Given Reason", "background:hsla(190,90%,80%,0.8)"),
    1 => array("Duplicated", "background:hsla(190,90%,80%,0.8)"),
    2 => array("As Design", "background:hsla(190,90%,80%,0.8)"),
    3 => array("Code Change", "background:hsla(190,90%,80%,0.8)"),
    4 => array("Not Reproduce", "background:hsla(190,90%,80%,0.8)"),
    5 => array("User Error", "background:hsla(190,90%,80%,0.8)"),
    6 => array("3rd Party Issue", "background:hsla(190,90%,80%,0.8)"),
    7 => array("Postship", "background:hsla(190,90%,80%,0.8)"),
    8 => array("Document change", "background:hsla(190,90%,80%,0.8)"),
    9 => array("Information", "background:hsla(190,90%,80%,0.8)")
);
$BUG_SYSTEM = array(
    3 => array("Onebug", "background:hsla(190,90%,80%,0.8)"),
    1 => array("Mantis", "background:hsla(190,90%,80%,0.8)"),
    2 => array("Vantive", "background:hsla(190,90%,80%,0.8)"),
    4 => array("Local Query", "background:hsla(190,90%,80%,0.8)"),
    5 => array("BugTracker", "background:hsla(190,90%,80%,0.8)")
);

$BUG_SUB_STATUS_COLOR = array(
    0  => "hsla(170, 100%, 70%, 1.0)",
    1  => "hsla(265, 85%, 70%, 1.0)",
    2  => "hsla(208, 79%, 54%, 1.0)",
    3  => "hsla(37, 85%, 60%, 1.0)",
    4  => "hsla(80, 52%, 30%, 0.8)",
    5  => "hsla(240, 2%, 40%, 1.0)",
    6  => "hsla(150, 80%, 75%, 1.0)",
    7  => "hsla(194, 66%, 61%, 1.0)",
    8  => "hsla(60, 84%, 60%, 1.0)",
    9  => "hsla(185, 55%, 40%, 1.0)"
);
$BUG_STATUS_COLOR = array(
    3  => "hsla(6, 66%, 56%, 1.0)",
    5  => "hsla(116, 41%, 53%, 1.0)",
);
$BUG_CATEGORY_COLORS = array(
    1  => "hsla(116, 41%, 53%, 1.0)",
    2  => "hsla(6, 66%, 56%, 1.0)",
    7  => "hsla(208, 79%, 54%, 1.0)",
);
$BUG_SEVERITY_COLORS = array(
    1  => "hsla(116, 41%, 53%, 1.0)",
    2  => "hsla(6, 66%, 56%, 1.0)",
    3  => "hsla(208, 79%, 54%, 1.0)",
);
$BUG_PRIORITY_COLORS = array(
    1  => "hsla(116, 41%, 53%, 1.0)",
    2  => "hsla(6, 66%, 56%, 1.0)",
    3  => "hsla(208, 79%, 54%, 1.0)",
);
/*
 * This is depend on the array $BUG_SYSTEM
 */
$BUG = array(
    1 => "BUG",
    2 => "BUG",
    3 => "BUG",
    4 => "QUERY",
    5 => "BUG",
);

/*
 * Valid bug status
 * Depend of bug sub status
 */
$VALID_BUG = array(
    3 => 3,
    6 => 6,
    7 => 7,
    8 => 8
);
?>