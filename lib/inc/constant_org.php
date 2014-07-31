<?php
// ==================================================================
//
// Author: CC
// Date: 
// Description: Some constants for org management
// Last Modified: 08/12/2013
//
// ------------------------------------------------------------------

/*
 * Create four new default groups:
 * If the org type is department, create the following 3 groups:
 * 1. Admin
 * 2. QA
 * 3. Delegate
 * If the org type is project,create the following 4 groups:
 * 1. Admin
 * 2. Engineer
 * 3. QA
 * 4. Delegate
 */
$depart_groups = array(
	array('name' => 'Admin', 'type' => '1'),
    array('name' => 'Engineer', 'type' => '3'),
	array('name' => 'QA', 'type' => '5'),
	array('name' => 'Delegate', 'type' => '7'),
	array('name' => '3rd_Party', 'type' => '15')
);
$project_groups = array(
    array('name' => 'Admin', 'type' => '1'),
    array('name' => 'Engineer', 'type' => '3'),
    array('name' => 'QA', 'type' => '5'),
    array('name' => 'Delegate', 'type' => '7'),
    array('name' => '3rd_Party', 'type' => '15')
);


/*
 * Purpose: Variables for org management
 * Author: CC
 * Date: 2012-11-15
 *

  /*
 * Employee in these group will be calculated as actual head count of a organization
 */
$VALID_GROUP = array(
    '1'  => 'Admin',
    '3'  => 'Engineer',
    '7'  => 'Delegate',
    '11' => 'Idle Resource'
);

/*
 * Employee in these group will not be calculated as actual head count of a organization
 */
$INVALID_GROUP = array(
    '5'  => 'QA',
    '9'  => 'Custom',
    '13' => 'Idle Asset',
    '15' => '3rd_Party'
);

/*
 * Define a group type that stored the idle asset users
 */
$IDLE_ASSET_GROUP_TYPE = 13;

/**
 * Role of each employee
 */
$EMPLOYEE_ROLE = array(
    '1' => 'Test Engineer (Manual)',
    '2' => 'Test Engineer (Automated)',
    '5' => 'Lead',
    '6' => 'Delegate Lead',
    '9' => 'PM',
    '11' => 'SPM'
    );

/*
 * Define the relationship betweent Pactera level and project level
 * There are some small part of task records without tester's level info, drop these task in the catagory of Junior
 */
$LEVEL = array(
    ''    => 'JUNIOR',
    'I'   => 'INTERN',
    'I1'  => 'INTERN',
    'I2'  => 'INTERN',
    'I3'  => 'INTERN',
    'B1'  => 'JUNIOR',
    'L1'  => 'JUNIOR',
    'B2'  => 'JUNIOR',
    'L2'  => 'JUNIOR',
    'B3'  => 'JUNIOR',
    'L3'  => 'JUNIOR',
    'B4'  => 'JUNIOR',
    'L4'  => 'JUNIOR',
    'B5'  => 'JUNIOR',
    'L5'  => 'JUNIOR',
    'B6'  => 'SENIOR',
    'L6'  => 'JUNIOR',
    'B7'  => 'SENIOR',
    'L7'  => 'JUNIOR',
    'B8'  => 'SENIOR',
    'L8'  => 'JUNIOR',
    'B9'  => 'SENIOR',
    'L9'  => 'JUNIOR',
    'B10' => 'SENIOR',
    'L10' => 'JUNIOR',
    'E1'  => 'SENIOR'
);

/**
 * Permission
 * None: 1
 * Read Only: 2
 * Write: 4
 * Audit: 6
 */
$NONE      = 1;
$READ_ONLY = 2;
$WRITE     = 4;
$AUDIT     = 6;

/**
 * Default disabled privileges for each group,
 * these cannot be assigned
 * 1.Admin 
 * 3.Engineer 
 * 5.QA 
 * 7.Delegate 
 * 9.Custom
 * 11.Idle Resource
 * 13.Idle Asset
 * 15.3rd_Party (Customer)
 */
// ==================================================================
//
// 1   Organization Info   org_mgt/org_info.php    1
// 2   Group Info  org_mgt/group_info.php  1
// 3   Asset Overview  asset/asset.php 2
// 4   Asset Requests  asset/asset_request.php 2
// 5   My Asset    asset/my_asset.php  2
// 6   My Request  asset/my_request.php    2
// 7   Request To Me   asset/asset_request_to_me.php   2
// 8   Iteration Info  task/iteration_info.php 3
// 9   Assign Task task/iteration_task_assign.php  3
// 10  Task Status task/task_status.php    3
// 11  Execute Task    task/my_task.php    3
// 12  Task History    task/task_execution_record.php  3
// 13  My Task Records task/my_task_records.php    3
// 14  Support Info    task/support_info.php   3
// 18  Dashboard   monitor/dashboard.php   4
// 19  SQL Interface   monitor/sql_interface.php   4
// 20  Bug Info    bug_mgt/bug_info.php    5
// 21  Asset History   asset/asset_history.php 2
// 23  Resign Info org_mgt/resign_info.php 1
// 24  Project Info    org_mgt/project_info.php    1
//
// ------------------------------------------------------------------

$DEFAULT_PRIVILEGE = array(
    '1' => array(
        '8'  => 'Iteration Info',
        '9'  => 'Assign Task',
        '10' => 'Task Status',
        '11' => 'Execute Task',
        '12' => 'Task History',
        '13' => 'My Task Records',
        '14' => 'Support Info',
        '20' => 'Bug Info'
        ),
    '3' => array(
        '1'  => 'Organization Info',
        '2'  => 'Group Info',
        '7'  => 'Request To Me',
        '8'  => 'Iteration Info',
        '9'  => 'Assign Task',
        '18' => 'Dashboard',
        '19' => 'SQL Interface',
        '22' => 'Asset Status',
        '23' => 'Resign Info'
        ),
    '5' => array(
        '1'  => 'Organization Info',
        '2'  => 'Group Info',
        '7'  => 'Request To Me',
        '8'  => 'Iteration Info',
        '9'  => 'Assign Task'
        ),
    '7' => array(
        '8'  => 'Iteration Info',
        '9'  => 'Assign Task',
        '10' => 'Task Status',
        '11' => 'Execute Task',
        '12' => 'Task History',
        '13' => 'My Task Records',
        '14' => 'Support Info',
        '20' => 'Bug Info'
        ),
    '9' => array(
        '1'  => 'Organization Info',
        '2'  => 'Group Info',
        '7'  => 'Request To Me',
        '8'  => 'Iteration Info',
        '9'  => 'Assign Task',
        '17' => 'Bug Analysis',
        '18' => 'Dashboard',
        '19' => 'SQL Interface',
        '23' => 'Resign Info'
        ),
    '11' => array(
        '1'  => 'Organization Info',
        '2'  => 'Group Info',
        '5'  => 'My Asset',
        '6'  => 'My Request',
        '7'  => 'Request To Me',
        '19' => 'SQL Interface',
        '20' => 'Bug Info',
        '23' => 'Resign Info'
        ),
    '13' => array(
        '1'  => 'Organization Info',
        '2'  => 'Group Info',
        '3'  => 'Asset Overview',
        '4'  => 'Asset Request',
        '5'  => 'My Asset',
        '6'  => 'My Request',
        '7'  => 'Request To Me',
        '8'  => 'Iteration Info',
        '9'  => 'Assign Task',
        '10' => 'Task Status',
        '11' => 'Execute Task',
        '12' => 'Task History',
        '13' => 'My Task Records',
        '14' => 'Support Info',
        '19' => 'SQL Interface',
        '20' => 'Bug Info',
        '21' => 'Asset History',
        '23' => 'Resign Info',
        ),
    '15' => array(
        '1'  => 'Organization Info',
        '2'  => 'Group Info',
        '3'  => 'Asset Overview',
        '4'  => 'Asset Request',
        '5'  => 'My Asset',
        '6'  => 'My Request',
        '7'  => 'Request To Me',
        '11' => 'Execute Task',
        '13' => 'My Task Records',
        '19' => 'SQL Interface',
        '21' => 'Asset History',
        '23' => 'Resign Info'
        ),
);

$DEFAULT_PRIVILEGE_PROJECT = array(
    '1' => array(
        '23'  => 'Resign Info'
        ),
    '3' => array(
        '1'  => 'Organization Info',
        '2'  => 'Group Info',
        '7'  => 'Request To Me',
        '8'  => 'Iteration Info',
        '9'  => 'Assign Task',
        '18' => 'Dashboard',
        '19' => 'SQL Interface',
        '22' => 'Asset Status',
        '23' => 'Resign Info'
        ),
    '5' => array(
        '1'  => 'Organization Info',
        '2'  => 'Group Info',
        '7'  => 'Request To Me',
        '8'  => 'Iteration Info',
        '9'  => 'Assign Task'
        ),
    '7' => array(
        '23'  => 'Resign Info'
        ),
    '9' => array(
        '1'  => 'Organization Info',
        '2'  => 'Group Info',
        '7'  => 'Request To Me',
        '8'  => 'Iteration Info',
        '9'  => 'Assign Task',
        '17' => 'Bug Analysis',
        '18' => 'Dashboard',
        '19' => 'SQL Interface',
        '23' => 'Resign Info'
        ),
    '11' => array(
        '1'  => 'Organization Info',
        '2'  => 'Group Info',
        '5'  => 'My Asset',
        '6'  => 'My Request',
        '7'  => 'Request To Me',
        '19' => 'SQL Interface',
        '20' => 'Bug Info',
        '23' => 'Resign Info'
        ),
    '13' => array(
        '1'  => 'Organization Info',
        '2'  => 'Group Info',
        '3'  => 'Asset Overview',
        '4'  => 'Asset Request',
        '5'  => 'My Asset',
        '6'  => 'My Request',
        '7'  => 'Request To Me',
        '8'  => 'Iteration Info',
        '9'  => 'Assign Task',
        '10' => 'Task Status',
        '11' => 'Execute Task',
        '12' => 'Task History',
        '13' => 'My Task Records',
        '14' => 'Support Info',
        '19' => 'SQL Interface',
        '20' => 'Bug Info',
        '21' => 'Asset History',
        '23' => 'Resign Info',
        ),
    '15' => array(
        '1'  => 'Organization Info',
        '2'  => 'Group Info',
        '3'  => 'Asset Overview',
        '4'  => 'Asset Request',
        '5'  => 'My Asset',
        '6'  => 'My Request',
        '7'  => 'Request To Me',
        '11' => 'Execute Task',
        '13' => 'My Task Records',
        '19' => 'SQL Interface',
        '21' => 'Asset History',
        '23' => 'Resign Info'
        ),
);

/**
 * [$DEFAULT_PRIVILEGE_REOURCE 
 * This is the default privileges for groups, if the father org's type is resource
 * Privileges in this array will be set to '1' or do not display
 * This will be only used when creatting new org.
 * ]
 * @var array
 * Type:
 * 1.Admin 
 * 3.Engineer 
 * 5.QA 
 * 7.Delegate 
 * 9.Custom
 * 11.Idle Resource
 * 13.Idle Asset
 * 15.3rd_Party (Customer)
 * Permission:
 * 1.None
 * 2.Read
 * 4.Write
 * 6.Audit
 */
$DEFAULT_PRIVILEGE_REOURCE = array(
    '1' => array(
        '8'  => 1, //'Iteration Info',
        '9'  => 1, //'Assign Task',
        '10' => 1, //'Task Status',
        '11' => 1, //'Execute Task',
        '12' => 1, //'Task History',
        '13' => 1, //'My Task Records'
        '14' => 1  //'Support Info',
        ),
    '3' => array(
        '1'  => 1, //'Organization Info',
        '2'  => 1, //'Group Info',
        '7'  => 1, //'Request To Me',
        '8'  => 1, //'Iteration Info',
        '9'  => 1, //'Assign Task',
        '18' => 1, //'Dashboard',
        '19' => 1, //'SQL Interface',
        '22' => 1, //'Asset Status',
        '23' => 1 //'Resign Info'
        ),
    '5' => array(
        '1'  => 1, //'Organization Info',
        '2'  => 1, //'Group Info',
        '7'  => 1, //'Request To Me',
        '8'  => 1, //'Iteration Info',
        '9'  => 1 //'Assign Task'
        ),
    '7' => array(
        '8'  => 1, //'Iteration Info',
        '9'  => 1, //'Assign Task',
        '11' => 1, //'Execute Task',
        '13' => 1 //'My Task Records'
        ),
    '9' => array(
        '1'  => 1, //'Organization Info',
        '2'  => 1, //'Group Info',
        '7'  => 1, //'Request To Me',
        '8'  => 1, //'Iteration Info',
        '9'  => 1, //'Assign Task',
        '17' => 1, //'Bug Analysis',
        '18' => 1, //'Dashboard',
        '19' => 1, //'SQL Interface',
        '23' => 1 //'Resign Info'
        ),
    '11' => array(
        '1'  => 1, //'Organization Info',
        '2'  => 1, //'Group Info',
        '5'  => 1, //'My Asset',
        '6'  => 1, //'My Request',
        '7'  => 1, //'Request To Me',
        '19' => 1, //'SQL Interface',
        '20' => 1, //'Bug Info',
        '23' => 1, //'Resign Info'
        ),
    '13' => array(
        '1'  => 1, //'Organization Info',
        '2'  => 1, //'Group Info',
        '3'  => 1, //'Asset Overview',
        '4'  => 1, //'Asset Request',
        '5'  => 1, //'My Asset',
        '6'  => 1, //'My Request',
        '7'  => 1, //'Request To Me',
        '8'  => 1, //'Iteration Info',
        '9'  => 1, //'Assign Task',
        '10' => 1, //'Task Status',
        '11' => 1, //'Execute Task',
        '12' => 1, //'Task History',
        '13' => 1, //'My Task Records',
        '14' => 1, //'Support Info',
        '19' => 1, //'SQL Interface',
        '20' => 1, //'Bug Info',
        '21' => 1, //'Asset History',
        '23' => 1, //'Resign Info',
        ),
    '15' => array(
        '1'  => 1, //'Organization Info',
        '2'  => 1, //'Group Info',
        '3'  => 1, //'Asset Overview',
        '4'  => 1, //'Asset Request',
        '5'  => 1, //'My Asset',
        '6'  => 1, //'My Request',
        '7'  => 1, //'Request To Me',
        '11' => 1, //'Execute Task',
        '13' => 1, //'My Task Records',
        '19' => 1, //'SQL Interface',
        '20' => 1, //'Bug Info',
        '21' => 1, //'Asset History',
        '23' => 1, //'Resign Info'
        ),
);
/**
 * [$DEFAULT_PRIVILEGE_PROJECT 
 * This is the default privileges for groups, if the father org's type is project,
 * Privileges in this array will be set to '1' or do not display
 * This will be only used when creatting new org.
 * ]
 * @var array
 * 1.Admin 
 * 3.Engineer 
 * 5.QA 
 * 7.Delegate 
 * 9.Custom
 * 11.Idle Resource
 * 13.Idle Asset
 * 15.3rd_Party (Customer)
 * Permission:
 * 1.None
 * 2.Read
 * 4.Write
 * 6.Audit
 */
$DEFAULT_PRIVILEGE_PROJECT = array(
    '1' => array(
        '23' => 1 //'Resign Info'
        ),
    '3' => array(
        '1'  => 1, //'Organization Info',
        '2'  => 1, //'Group Info',
        '7'  => 1, //'Request To Me',
        '8'  => 1, //'Iteration Info',
        '9'  => 1, //'Assign Task',
        '18' => 1, //'Dashboard',
        '19' => 1, //'SQL Interface',
        '22' => 1, //'Asset Status',
        '23' => 1 //'Resign Info'
        ),
    '5' => array(
        '1'  => 1, //'Organization Info',
        '2'  => 1, //'Group Info',
        '7'  => 1, //'Request To Me',
        '8'  => 1, //'Iteration Info',
        '9'  => 1 //'Assign Task'
        ),
    '7' => array(
        '23' => 1 //'Resign Info'
        ),
    '9' => array(
        '1'  => 1, //'Organization Info',
        '2'  => 1, //'Group Info',
        '7'  => 1, //'Request To Me',
        '8'  => 1, //'Iteration Info',
        '9'  => 1, //'Assign Task',
        '17' => 1, //'Bug Analysis',
        '18' => 1, //'Dashboard',
        '19' => 1, //'SQL Interface',
        '23' => 1 //'Resign Info',
        ),
    '11' => array(
        '1'  => 1, //'Organization Info',
        '2'  => 1, //'Group Info',
        '5'  => 1, //'My Asset',
        '6'  => 1, //'My Request',
        '7'  => 1, //'Request To Me',
        '19' => 1, //'SQL Interface',
        '20' => 1, //'Bug Info',
        '23' => 1, //'Resign Info'
        ),
    '13' => array(
        '1'  => 1, //'Organization Info',
        '2'  => 1, //'Group Info',
        '3'  => 1, //'Asset Overview',
        '4'  => 1, //'Asset Request',
        '5'  => 1, //'My Asset',
        '6'  => 1, //'My Request',
        '7'  => 1, //'Request To Me',
        '8'  => 1, //'Iteration Info',
        '9'  => 1, //'Assign Task',
        '10' => 1, //'Task Status',
        '11' => 1, //'Execute Task',
        '12' => 1, //'Task History',
        '13' => 1, //'My Task Records',
        '14' => 1, //'Support Info',
        '19' => 1, //'SQL Interface',
        '20' => 1, //'Bug Info',
        '21' => 1, //'Asset History',
        '23' => 1 //'Resign Info'
        ),
    '15' => array(
        '1'  => 1, //'Organization Info',
        '2'  => 1, //'Group Info',
        '3'  => 1, //'Asset Overview',
        '4'  => 1, //'Asset Request',
        '5'  => 1, //'My Asset',
        '6'  => 1, //'My Request',
        '7'  => 1, //'Request To Me',
        '11' => 1, //'Execute Task',
        '13' => 1, //'My Task Records',
        '19' => 1, //'SQL Interface',
        '20' => 1, //'Bug Info',
        '21' => 1, //'Asset History',
        '23' => 1, //'Resign Info'
        ),
);
//0 - Intern - yellow, 1 - Employee, 2 - Borrow - red, 3 - Vendor - green, 4 - Customer - purple
$DEFAULT_CORLOR_RESOURCE_TYPE = array(
        '0' => array(
            '0' => 'Intern',
            '1' => 'faa732'
        ),
//        '1' => array(
//            '0' => 'Employee',
//            '1' => 'ffffff'
//        ),
        '2' => array(
            '0' => 'Borrow',
            '1' => 'D84D47'
        ),
        '3' => array(
            '0' => 'Vendor',
            '1' => '5bb75b'
        ),
        '4' => array(
            '0' => 'Customer',
            '1' => '59008C'
        ),
        '5' => array(
                '0' => 'Idle',
                '1' => 'c5c5c5'
        )
);

?>
