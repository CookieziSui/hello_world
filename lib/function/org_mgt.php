<?php
require_once __DIR__ . '/../../lib/function/generate_permission.php';
require_once __DIR__ . '/../../lib/inc/constant_org.php';
require_once __DIR__ . '/../../lib/inc/constant_asset.php';
require_once __DIR__ . '/../../lib/function/get_working_days.php';
require_once __DIR__ . '/../../lib/function/monitor_mgt.php';
require_once __DIR__ . '/../../lib/function/date.php';
require_once __DIR__ . '/../../lib/function/log.php';
require_once __DIR__ . '../../../debug.php';
require_once __DIR__ . '../../../mysql.php';

/**
 * according $user_id to $location or return error
 */
function login_header($user_id,$location,$oid,$optype,$name){
    $projects = subscribed_project($user_id);
    if($oid != 'NULL'){//refresh page and currently user has login in 
        if($oid != ''){
            $mark = false;
            if($optype == 'ORG'){
                /*
                 * ORG
                 */
                $active_orgs     = active_org_project("ORG");
                $current_org     = query_current_org($_SESSION['user_id']);
                $child           = array();
                foreach($current_org as $co_key => $co_val){
                    if($co_key == $oid){
                        $mark = true;
                    }
                    $child[$co_key] = get_child_org(array(),$co_key,$active_orgs,1);
                }
                foreach($child as $c_key => $c_val){
                    foreach($c_val as $cv_key => $cv_val){
                        if($cv_key == $oid){
                            $mark = true;
                        }
                    }
                }
            }else{
                if($name == 'NULL'){
                    /*
                     * PROJECT
                     */
                    $active_projects        = active_org_project("PROJECT");
                    $current_project        = query_current_project($_SESSION['user_id']);
                    $direct_owned_project   = direct_owned_project($_SESSION['user_id']);
                    $current_child          = array();
                    foreach ($current_project as $cp_key => $cp_val){
                        if($oid == $cp_key){
                            $mark = true;
                        }
                        $current_child[$cp_key] = get_child_org(array(), $cp_key, $active_projects, 1);
                    }
                    foreach($current_child as $cc_key => $cc_val){
                        foreach($cc_val as $cv_key => $cv_val){
                            if($cv_key == $oid){
                                $mark = true;
                            }
                        }
                    }
                    $owned_child = array();
                    foreach ($direct_owned_project as $dop_key => $dop_val) {
                        if($dop_key == $oid){
                            $mark = true;
                        }
                        $owned_child[$dop_key] = get_child_org(array(), $dop_key, $active_projects, 1);
                    }
                    foreach($owned_child as $oc_key => $oc_val){
                        foreach($oc_val as $ov_key => $ov_val){
                            if($ov_key == $oid){
                                $mark = true;
                            }
                        }
                    }
                }else{
                    /*
                     * Subscribe
                     */
                    foreach ($projects as $p_key => $p_val){
                        if($oid == $p_key){
                            $mark = true;
                        }
                    }
                }
            }
            if($mark == false){
                header("Location: login_error.php");
            }else{
                header("Location: ".$location);
            }
        }else if(empty($name)){
             /*
            * All subscription
            */
           if(empty($projects)){
                header("Location: login_error.php");
            }else{
                header("Location: ".$location);
            }
        }else{
            header("Location: ".$location);
        }
    }else{  //refresh nav
        header("Location: ".$location);
    }
}
/**
 * get org child
 * @param type $child
 * @param type $father
 * @param type $active_orgs
 * @param type $level
 * @return type
 */
function get_child_org($child,$father,$active_orgs,$level){
    $childs = array();
    foreach($active_orgs as $ao_key => $ao_val){
        if($father == $ao_val['FATHER']){
            $child[$ao_val['ID']] = $ao_val;
            $childs[$ao_val['ID']] = $ao_val;
        }
    }
    if(!empty($childs)){
        foreach($childs as $c_key => $c_val){
            $child = get_child_org($child, $c_key, $active_orgs, $level+1);
        }
    }
    return $child;
}
/**
 * Author: CC
 * Date； 08/02/2013
 * [active_org Get all the org info into an array (exclude inactive orgs)]
 * @return [array] [array of active orgs]
 */
function active_org_project($table){
    $prefix_table  = strtolower($table);
    $qry_active_op = "SELECT ID,NAME,OWNER_ID,FATHER,USER_NAME,NICK_NAME FROM ".$prefix_table."_info, user_info WHERE STATUS='1' AND OWNER_ID=USER_ID;";
    $rst_op        = $GLOBALS['db']->query($qry_active_op);
    $ops_temp      = $rst_op->fetch_all(MYSQLI_ASSOC);
    foreach($ops_temp as $ot_key => $ot_val){
        $ops[$ot_val['ID']] = $ot_val;
    }
    return $ops;
}

function all_org_project($table){
    $prefix_table = strtolower($table);
    $qry_all_op   = "SELECT ID,NAME,OWNER_ID,FATHER,USER_NAME,NICK_NAME FROM ".$prefix_table."_info, user_info WHERE OWNER_ID=USER_ID;";
    $rst_op       = $GLOBALS['db']->query($qry_all_op);
    $ops_temp     = $rst_op->fetch_all(MYSQLI_ASSOC);
    foreach($ops_temp as $ot_key => $ot_val){
        $ops[$ot_val['ID']] = $ot_val;
    }
    return $ops;
}

/**
 * Author: CC
 * Date: 07/23/2013
 * Last Modified: 07/25/2013
 * [public_projects Query all project where scope is PUBLIC]
 * @return [array] [public projects]
 */
function public_projects(){
    $qry_public_projects = "SELECT pi.ID, pi.NAME, pi.OWNER_ID, USER_NAME, HR_ID, oi.ID AS ORG_ID, oi.NAME AS ORG_NAME, pi.EXP_START, pi.EXP_END, pi.ACT_START, pi.ACT_END,`OWNER`,ACCOUNT,NICK_NAME FROM org_info AS oi, project_info AS  pi, user_info AS ui LEFT JOIN (customer_account AS ca) ON (ca.MAINTAINER = ui.USER_ID) WHERE ui.USER_ID = pi.OWNER_ID AND oi.ID = pi.FATHER AND pi.STATUS <> '0' AND SCOPE = '1' ORDER BY pi.NAME ASC;";
    $rst_public_projects = $GLOBALS['db']->query($qry_public_projects);
    $public_projects  = $rst_public_projects->fetch_all(MYSQLI_ASSOC);
    return $public_projects;
}

/**
 * Author: CC
 * Date: 07/25/2013
 * [owned_project Query all direct owned projects]
 * @param  [string] $string_org_id [organization id string]
 * @return [array]                [array of owned project]
 */
function owned_project($string_org_id){
    $qry_owned_project = "SELECT pi.ID, pi.NAME, pi.OWNER_ID, USER_NAME, HR_ID, oi.ID AS ORG_ID, oi.NAME AS ORG_NAME, pi.EXP_START, pi.EXP_END, pi.ACT_START, pi.ACT_END FROM project_info AS  pi, user_info AS ui, org_info AS oi WHERE oi.ID IN ($string_org_id) AND ui.USER_ID = pi.OWNER_ID AND oi.ID = pi.FATHER AND pi.STATUS <> '0' ORDER BY pi.NAME ASC;";
    $rst_onwer_project = $GLOBALS['db']->query($qry_owned_project);
    $owned_project_temp = $rst_onwer_project->fetch_all(MYSQLI_ASSOC);
    foreach($owned_project_temp as $opt_key => $opt_val){
        $owned_project[$opt_val['ID']] = $opt_val;
    }
    if(!isset($owned_project)){
        $owned_project = array();
    }
    return $owned_project;
}

/**
 * Author: CC
 * Date:08/05/2013
 * [direct_owned_project A user's direct owned projects]
 * @param  [type] $user_id [description]
 * @return [type]          [description]
 */
function direct_owned_project($user_id){
    /**
     * Step 1: Query the org id of this logged user
     */
    $qry_user_org = "SELECT ID, FK_USER_ID, FK_ORG_ID, FK_GROUP_ID FROM user_org_group WHERE FK_USER_ID = '$user_id';";
    $rst_user_org = $GLOBALS['db'] ->query($qry_user_org);
    $user_org = $rst_user_org->fetch_all(MYSQLI_ASSOC);
    if(empty($user_org)){
        return NULL;
    }
    $user_org_array = array();
    foreach ($user_org as $uo_key => $uo_val) {
        $user_org_array[] = "'".$uo_val['FK_ORG_ID']."'";
    }

    /**
     * Step 2: Query all the owned project of the org queried from from Step 1
     */
    $qry_owned_project = "SELECT ID AS PROJECT_ID, NAME AS PROJECT_NAME, OWNER_ID, FATHER FROM project_info WHERE FATHER IN (".implode(',', $user_org_array).");";
    $rst_owned_project = $GLOBALS['db']->query($qry_owned_project);
    $owned_project = $rst_owned_project->fetch_all(MYSQLI_ASSOC);
    
    return $owned_project;
}

/**
 * Author: CC
 * Date:08/06/2013
 * [subscribed_project Query all subscripted projects of a user]
 * @param  [int] $user_id [user id]
 * @return [array]          [array of subscripted projects]
 */
function subscribed_project($user_id){
    $subscribed_project = array();
    $qry_subscripted_project = "SELECT ups.FK_PROJECT_ID ID,pi.NAME,ups.SUBSCRIBE_START, ups.SUBSCRIBE_END,ups.COMMENT FROM user_project_subscription AS ups, project_info AS pi, user_info AS ui WHERE pi.ID =FK_PROJECT_ID AND ups.FK_USER_ID = '$user_id' AND ups.FK_USER_ID = ui.USER_ID AND ups.SUBSCRIBE_END IS NULL;";
    $rst_subscripted_project = $GLOBALS['db']->query($qry_subscripted_project);
    $subscripted_project_temp = $rst_subscripted_project->fetch_all(MYSQLI_ASSOC);

    //Make project id as the key
    foreach($subscripted_project_temp as $spt_key => $spt_val){
        $subscribed_project[$spt_val['ID']] = $spt_val;
    }

    return $subscribed_project;
}

/**
 * [project_csi Query all the project CSI]
 * @return [array] [array of project CSI]
 */
function project_csi(){
    $qry_csi = "SELECT ID, FK_PROJECT_ID, `DATE`, SCORE FROM csi ORDER BY SCORE DESC;";
    $rst_csi = $GLOBALS['db'] -> query($qry_csi);
    $csi = $rst_csi->fetch_all(MYSQLI_ASSOC);
    foreach($csi as $csi_key => $csi_val){
        $project_csi[$csi_val['FK_PROJECT_ID']] = $csi_val;
    }
    return $project_csi;
}

function project_subscription_tree($user_id){
    //Step 1: Already in a project or not
    $current_org = query_current_org($user_id);

    /**
     * [Loop each org, and find their child orgs]
     */
    foreach ($current_org as $co_key => $co_val) {
        $child_org_temp[] = active_childs_orgs($co_val['ORG_ID']);
    }

    /**
     * [Remove the duplicated orgs]
     * @var [type]
     */
    foreach($child_org_temp as $cot_key => $cot_val){
        foreach($cot_val as $cv_key => $cv_val){
            $child_org[$cv_val['ID']] = $cv_val;
        }
    }
    asort($child_org);

    /**
     * Combine these orgs in a string
     */
    foreach($child_org as $cho_key => $key_val){
        $array_child_org[] = "'".$cho_key."'";
    }

    $string_child_org = implode(',', $array_child_org);

    /**
     * [Get the owned project of these orgs above. In this array, there will be some duplicated projects]
     * @var [type]
     */
    $owned_project = owned_project($string_child_org);
    return $owned_project;
}

/**
 * Date: 08/09/2013
 * Author: CC
 * Purpose: Get the info of a specific org/project id
 */
function get_op_info_wID($id, $table) {
    $op_info = array();
    $prefix_table = strtolower($table);
    $qry_op_info  = "SELECT ID,NAME,OWNER_ID,FATHER,USER_NAME,NICK_NAME FROM ".$prefix_table."_info, user_info WHERE OWNER_ID=USER_ID AND ID ='$id';";
    $rst_op_info  = $GLOBALS['db']->query($qry_op_info);
    $op_info_temp = $rst_op_info->fetch_all(MYSQLI_ASSOC);
    foreach ($op_info_temp as $oit_key => $oit_val) {
        $op_info[$oit_val['ID']] = $oit_val;
    }
    return $op_info;
}

/**
 * Author: CC
 * Date: 09/25/2013
 * [get_op_info_wIDs Get the info of a org/project string]
 * @param  [type] $id_string [org/project id string]
 * @param  [type] $table     [DB table name]
 * @return [type]            [description]
 */
function get_op_info_wIDs($id_string, $table) {
    $op_info = array();
    $prefix_table = strtolower($table);
    $qry_op_info  = "SELECT ID,NAME,EXP_START,EXP_END,ACT_START,ACT_END, OWNER_ID,FATHER,USER_NAME,NICK_NAME FROM ".$prefix_table."_info, user_info WHERE OWNER_ID=USER_ID AND ID in ($id_string);";
    $rst_op_info  = $GLOBALS['db']->query($qry_op_info);
    $op_info_temp = $rst_op_info->fetch_all(MYSQLI_ASSOC);
    foreach ($op_info_temp as $oit_key => $oit_val) {
        $op_info[$oit_val['ID']] = $oit_val;
        if($oit_val['ACT_START'] == "" || $oit_val['ACT_START'] == "0000-00-00"){
            $op_info[$oit_val['ID']]['START'] = ($oit_val['EXP_START'] == "" || $oit_val['EXP_START'] == "0000-00-00")?date("Y-m-d"):$oit_val['EXP_START'];
        }else{
            $op_info[$oit_val['ID']]['START'] = $oit_val['ACT_START'];
        }
        if($oit_val['ACT_END'] == "" || $oit_val['ACT_END'] == "0000-00-00"){
            $op_info[$oit_val['ID']]['END'] = ($oit_val['EXP_END'] == "" || $oit_val['EXP_END'] == "0000-00-00")||$oit_val['EXP_END'] <= date("Y-m-d")?date("Y-m-d"):$oit_val['EXP_END'];
        }else{
            $op_info[$oit_val['ID']]['END'] = $oit_val['ACT_END'];
        }
    }
    return $op_info;
}

/**
 * Author: CC
 * Date: 03/08/2013
 * [org_group_list Query all the group info of a org]
 * @param  [int] $oid [org id]
 * @return [array]      [description]
 */
function org_group_list($oid){
    $qry_grp  = "SELECT ID,NAME,TYPE,FK_ORG_ID FROM org_group_info WHERE FK_ORG_ID='" . $oid . "'";
    $rst_grp  = $GLOBALS['db']->query($qry_grp);
    $grp_info = $rst_grp->fetch_all(MYSQLI_ASSOC);
    return $grp_info;
}

/**
 * Author: CC
 * Date: 03/08/2013
 * [project_group_list Query all the group info of a project]
 * @param  [int] $pid [project if]
 * @return [array]      [description]
 */
function project_group_list($pid){
    $qry_grp  = "SELECT ID,NAME,TYPE,FK_PROJECT_ID FROM project_group_info WHERE FK_PROJECT_ID='" . $pid . "'";
    $rst_grp  = $GLOBALS['db']->query($qry_grp);
    $grp_info = $rst_grp->fetch_all(MYSQLI_ASSOC);
    return $grp_info;
}

/**
 * Author: CC
 * Date: 08/06/2013
 * [employee_list Query all employees with customer view.  (Including resigned employee, and the customer account info)]
 * @return [array] [array of employee list]
 */
function all_emp(){
    $qry_all_emp_customer_view = "SELECT UI.USER_ID,UI.USER_NAME,UI.NICK_NAME,UI.LEVEL,`OWNER`,ACCOUNT FROM user_info AS UI LEFT JOIN (customer_account AS CA) ON (CA.MAINTAINER = UI.USER_ID) ORDER BY CONVERT(USER_NAME USING GBK)";
    $rst_all_emp_cv = $GLOBALS['db']->query($qry_all_emp_customer_view);
    $all_emp_temp_cv = $rst_all_emp_cv->fetch_all(MYSQLI_ASSOC);
    foreach ($all_emp_temp_cv as $aetc_key => $aetc_val) {
        $emp[$aetc_val['USER_ID']] = $aetc_val;
    }
    return $emp;
}

/**
 * Author: CC
 * Date: 08/06/2013
 * [active_emp Query employees that is on duty]
 * @return [array] [emp list]
 */
function active_emp(){
    $qry_emp  = "SELECT UI.USER_ID,UI.USER_NAME,UI.HR_ID,UI.NICK_NAME,UI.LEVEL,`OWNER`,ACCOUNT,UI.EMAIL FROM user_info AS UI LEFT JOIN (customer_account AS CA) ON (CA.MAINTAINER = UI.USER_ID) WHERE EMPLOYEE_END IS NULL ORDER BY CONVERT(USER_NAME USING GBK)";
    $rst_emp  = $GLOBALS['db']->query($qry_emp);
    $emp_temp = $rst_emp->fetch_all(MYSQLI_ASSOC);

    foreach ($emp_temp as $et_key => $et_val) {
        $emp[$et_val['USER_ID']] = $et_val;
    }
    return $emp;
}

/**
 * Author: CC
 * Date: 2012-11-12
 * [valid_project_employee Get all valid employee list]
 * Last Modified: 08/11/2013    CC
 * Change name from valid_employee to valid_project_employee
 * @return [type] [description]
 */
function valid_project_employee() {
    $query_valid_employee   = "SELECT DISTINCT FK_USER_ID FROM user_project_group";
    $rst_ve                 = $GLOBALS['db']->query($query_valid_employee);
    $valid_project_employee = $rst_ve->fetch_all(MYSQLI_ASSOC);
    return $valid_project_employee;
}

/**
 * Author: CC
 * Last Modified: 08/08/2013
 * [query_iteration_list Query iteration info by project id]
 * @param  [int] $project_id [project_id]
 * @return [array]             [description]
 */
function query_iteration_list($project_id) {
    $qry_iteration_list = "SELECT ITERATION_ID,ITERATION_NAME,CREATOR,EXPECTED_START,EXPECTED_END,ESTIMATED_START,ESTIMATED_END,ACTUAL_START,ACTUAL_END,`STATUS` FROM iteration_info WHERE FK_PROJECT_ID='$project_id';";
    $rst_iteration_list = $GLOBALS['db']->query($qry_iteration_list);
    $iteration_list     = $rst_iteration_list->fetch_all(MYSQLI_ASSOC);
    return $iteration_list;
}

/**
 * Query iteration info by iteration id
 */
function iteration_info_by_id($iteration_id_string) {
    $qry_iteration_info = "SELECT ITERATION_ID ID,ITERATION_NAME NAME,CREATOR,EXPECTED_START,EXPECTED_END,ESTIMATED_START,ESTIMATED_END,ACTUAL_START,ACTUAL_END,`STATUS` FROM iteration_info WHERE ITERATION_ID IN ($iteration_id_string);";
    $rst_iteration_info = $GLOBALS['db']->query($qry_iteration_info);
    $iteration_info_temp = $rst_iteration_info->fetch_all(MYSQLI_ASSOC);
    foreach ($iteration_info_temp as $iit_key => $iit_val) {
        $iteration_info[$iit_val['ID']] = $iit_val;
        if($iit_val['ACTUAL_START'] == "" || $iit_val['ACTUAL_START'] == "0000-00-00"){
            $iteration_info[$iit_val['ID']]['START'] = ($iit_val['ESTIMATED_START'] == "" || $iit_val['ESTIMATED_START'] == "0000-00-00")?date("Y-m-d"):$iit_val['ESTIMATED_START'];
        }else{
            $iteration_info[$iit_val['ID']]['START'] = $iit_val['ACTUAL_START'];
        }
        if($iit_val['ACTUAL_END'] == "" || $iit_val['ACTUAL_END'] == "0000-00-00"){
            $iteration_info[$iit_val['ID']]['END'] = ($iit_val['ESTIMATED_END'] == "" || $iit_val['ESTIMATED_END'] == "0000-00-00")?date("Y-m-d"):$iit_val['ESTIMATED_END'];
        }else{
            $iteration_info[$iit_val['ID']]['END'] = $iit_val['ACTUAL_END'];
        }
    }
    return $iteration_info;
}

/**
 * Author: CC
 * Date:
 * Last Modified: 08/09/2013
 * [active_childs_orgs Query all active child orgs through a org id]
 * [Direct or inherit]
 * @param  [int] $oid [description]
 * @return [array]      [description]
 */
function active_childs_orgs($oid){
    $sub_org         = array();
    $active_sub_orgs = array();
    $orgs            = active_org_project("ORG");
    $direct_sub_org  = direct_active_sub_org($oid, "ORG");
    foreach ($direct_sub_org as $dso_key => $dso_val) {
        $sub_orgs      = array();
        $sub_org_array = array();    //All the childs od direct sub orgs
        $sub_org_array = query_subOrg($dso_val['ID'], $dso_val['ID'], $orgs, $sub_orgs);
        foreach ($sub_org_array[$dso_val['ID']] as $soa_key => $soa_val) {
            $active_sub_orgs[$soa_val] = $orgs[$soa_val];
        }
    }
    return $active_sub_orgs;
}

/**
 * Author: CC
 * Date: 08/09/2013
 * [all_childs_orgs Query all active or inactive child orgs through a org id]
 * @param  [int] $oid [description]
 * @return [array]      [description]
 */
function all_childs_orgs($oid){
    $sub_org        = array();
    $orgs           = all_org_project("ORG");
    $direct_sub_org = direct_active_sub_org($oid, "ORG");
    foreach ($direct_sub_org as $dso_key => $dso_val) {
        $sub_orgs     = array();
        $sub_org_array = array();    //All the childs od direct sub orgs
        $sub_org_array = query_subOrg($dso_val['ID'], $dso_val['ID'], $orgs, $sub_orgs);
        foreach ($sub_org_array[$dso_val['ID']] as $soa_key => $soa_val) {
                $all_sub_orgs[$soa_val] = $orgs[$soa_val];
        }
    }
    return $all_sub_orgs;
}

/**
 * Query all the child orgs of an org and time
 */
function all_childs_orgs_rs($oid, $current_date) {
    /*
     * For organizations
     * Get all the org info into an array
     * SQL statement stored in org_mgt.sql.php
     */
    $rst_org = $GLOBALS['db']->query($GLOBALS['query_org']);
    $orgs = $rst_org->fetch_all(MYSQLI_ASSOC);

    $sub_org = sub_org_with_self_rs($oid, $current_date);
    foreach ($sub_org as $so_key => $so_val) {

        $sub_orgs = array();
        $sub_org_list = query_subOrg($so_val['ORG_ID'], $so_val['ORG_ID'], $orgs, $sub_orgs);

        foreach ($sub_org_list as $sol_key => $sol_val) {
            foreach ($sol_val as $sv_key => $sv_val) {
                $all_sub_orgs[] = $sv_val;
            }
        }
    }
    return $all_sub_orgs;
}

/**
 * Author: CC
 * Date: 08/08/2013
 * [op_string Transform array of project or ors to string, seperated by ',']
 * @param  [array] $ops [orgs or projects]
 * @return [string]      [description]
 */
function op_string($ops){
    $array_ops = array();
    foreach($ops as $p_key => $p_val){
        $array_ops[] = "'".$p_val['ID']."'";
    }
    $string_ops = implode(',', $array_ops);
    return $string_ops;
}

/**
 * Query the members of an org by org id(directly)
 */
function members_of_org_directly($oid) {
    $qry_members_of_org_directly = "SELECT uog.FK_USER_ID,ui.USER_NAME,ui.HR_ID FROM user_org_group as uog, user_info as ui WHERE FK_ORG_ID='$oid' AND ui.USER_ID=uog.FK_USER_ID;";
    $rst_members_of_org_directly = $GLOBALS['db']->query($qry_members_of_org_directly);
    $members_of_org_directly = $rst_members_of_org_directly->fetch_all(MYSQLI_ASSOC);
    return $members_of_org_directly;
}

/**
 * Author: CC
 * Last Modified: 08/07/2013
 * [members_of_org Members of org]
 * @param  [string] $project_string [project id]
 * @return [array]      [array of project members]
 */
function members_of_project($project_string) {
    //$qry_members_of_project = "SELECT upg.FK_USER_ID,ui.USER_NAME,ui.HR_ID FROM user_project_group as upg, user_info as ui WHERE FK_PROJECT_ID IN ($project_string) AND ui.USER_ID=upg.FK_USER_ID;";
    $qry_members_of_project = "SELECT ui.USER_ID,ui.USER_NAME,ui.NICK_NAME,ui.HR_ID,ui.LEVEL,ui.EMAIL,`OWNER`,ACCOUNT FROM user_project_group AS upg, project_group_info AS gi, user_info AS ui LEFT JOIN (customer_account AS ca) ON (ca.MAINTAINER = ui.USER_ID) WHERE ui.USER_ID=upg.FK_USER_ID AND upg.FK_GROUP_ID=gi.ID AND gi.TYPE NOT IN ('11','13','15') AND upg.FK_PROJECT_ID IN ($project_string);";
    $rst_members_of_project = $GLOBALS['db']->query($qry_members_of_project);
    $members_of_project = $rst_members_of_project->fetch_all(MYSQLI_ASSOC);
    return $members_of_project;
}

/**
 * Author: CC
 * Date: 08/12/2013
 * [members_of_org Members of orgs]
 * @param  [string] $org_string [description]
 * @return [type]             [description]
 */
function members_of_org($org_string){
    $qry_members_of_org = "SELECT ui.USER_ID,ui.USER_NAME,ui.NICK_NAME,ui.HR_ID,ui.LEVEL,`OWNER`,ACCOUNT FROM user_org_group AS uog, org_group_info AS gi, user_info AS ui LEFT JOIN (customer_account AS ca) ON (ca.MAINTAINER = ui.USER_ID) WHERE ui.USER_ID=uog.FK_USER_ID AND uog.FK_GROUP_ID=gi.ID AND gi.TYPE NOT IN ('11','13','15') AND uog.FK_ORG_ID IN ($org_string);";
    $rst_members_of_org = $GLOBALS['db']->query($qry_members_of_org);
    $members_of_org = $rst_members_of_org->fetch_all(MYSQLI_ASSOC);
    return $members_of_org;
}

/**
 * Get all the billable headcount of the specific org
 */
function org_bhc($oid, $str_year, $month_int) {
    $qry_bhc = "SELECT FK_ORG_ID,YEAR,MONTH,BHC FROM billable_headcount WHERE FK_ORG_ID IN ($oid) AND YEAR = '$str_year' AND MONTH <='$month_int';";
    $rst_org_bhc = $GLOBALS['db']->query($qry_bhc);
    $org_bhc = $rst_org_bhc->fetch_all(MYSQLI_ASSOC);
    return $org_bhc;
}

/*
 * Query all the direct-child org of this org (including itself)
 * If an org has no child, set its' own org_id to $sub_org and return
 */

function direct_active_sub_org($oid) {
    $qry_sub_org  = "SELECT ui.USER_NAME,i.ID,i.NAME,i.OWNER_ID,i.EXP_START,i.EXP_END,i.ACT_START,i.ACT_END,i.STATUS,i.COMMENT FROM org_info AS i,user_info AS ui WHERE FATHER='$oid' AND i.OWNER_ID=ui.USER_ID AND i.STATUS='1';";
    $rst_sub_org  = $GLOBALS['db']->query($qry_sub_org);
    $sub_org_temp = $rst_sub_org->fetch_all(MYSQLI_ASSOC);

    /**
     * If this org is the final leaf of the org tree, it has no child, it cannot get anything from sub_org($db, $oid)
     * So just get the info of this org
     */
    if (empty($sub_org_temp)) {
        $sub_org = get_op_info_wID($oid, "ORG");
    }
    
    foreach ($sub_org_temp as $sot_key => $sot_val) {
        $sub_org[$sot_val['ID']] = $sot_val;
    }
    return $sub_org;
}

/*
 * Query all the direct-child org of this org (including itself)   including invade orgs
 * If an org has no child, set its' own org_id to $sub_org and return
 */

function sub_org_with_self_wio($oid) {
    $qry_sub_org = "SELECT ui.USER_NAME,oi.ID AS ORG_ID,oi.NAME AS ORG_NAME,oi.OWNER_ID,oi.EXP_START,oi.EXP_END,oi.ACT_START,oi.ACT_END,oi.STATUS,oi.COMMENT 
FROM org_info AS oi,user_info AS ui WHERE FATHER='$oid' AND oi.OWNER_ID=ui.USER_ID;";
    $rst_sub_org = $GLOBALS['db']->query($qry_sub_org);
    $sub_org_wio = $rst_sub_org->fetch_all(MYSQLI_ASSOC);
    /*
     * If this org is the final leaf of the org tree, it has no child, it cannot get anything from sub_org($db, $oid)
     * So just get the info of this org
     */
    if (empty($sub_org_wio)) {
        $sub_org_wio[] = query_org_info($oid);
    }
    return $sub_org_wio;
}

/**
 * [customer_contacts_list query all customer contacts list info]
 * @return [array] [each org's customer contacts list]
 */
function customer_contacts_list($sub_org_string){
    $qry_ccl = "SELECT ORG_ID,ORG_NAME,USER_NAME,ccl.`NAME`,ccl.EMAIL,ccl.`GEO-LOCATION` FROM `customer_contacts_list` AS ccl, org_info AS oi, user_info AS ui WHERE ccl.FK_ORG_ID AND ccl.FK_ORG_ID = oi.ORG_ID AND oi.OWNER_ID=ui.USER_ID AND ccl.FK_ORG_ID IN ($sub_org_string);";
    $rst_ccl = $GLOBALS['db']->query($qry_ccl);
    $ccl_info_temp = $rst_ccl->fetch_all(MYSQLI_ASSOC);
    foreach($ccl_info_temp as $cit_key => $cit_val){
        $ccl_info[] = $cit_val;
    }
    return $ccl_info;
}

/*
 * Date: 2012-12-05
 * Author: CC
 * Purpose: Query all the direct-child org of this org 
 * If one org had no sub org, ignore it
 */

function sub_org($oid, $table) {
    $prefix_table = strtolower($table);
    $qry_sub_org  = "SELECT ui.USER_NAME,i.ID,i.NAME,i.OWNER_ID,i.EXP_START,i.EXP_END,i.ACT_START,i.ACT_END,i.STATUS,i.COMMENT FROM ".$prefix_table."_info AS i,user_info AS ui WHERE FATHER='$oid' AND i.OWNER_ID=ui.USER_ID AND i.STATUS='1';;";
    $rst_sub_org  = $GLOBALS['db']->query($qry_sub_org);
    $sub_org      = $rst_sub_org->fetch_all(MYSQLI_ASSOC);
    return $sub_org;
}

/*
 * Query all the direct-child org of this org (including itself)
 * If an org has no child, set its' own org_id to $sub_org and return
 */

function sub_org_with_self_rs($oid, $current_date) {
    $qry_sub_org = "SELECT ui.USER_NAME,oi.ORG_ID,oi.ORG_NAME,oi.ORG_TYPE,oi.OWNER_ID,oi.EXP_START,oi.EXP_END,oi.ACT_START,oi.ACT_END,oi.STATUS,oi.COMMENT 
FROM org_info AS oi,user_info AS ui WHERE FATHER='$oid' AND oi.OWNER_ID=ui.USER_ID AND oi.EXP_END>'$current_date';";
    $rst_sub_org = $GLOBALS['db']->query($qry_sub_org);
    $sub_org = $rst_sub_org->fetch_all(MYSQLI_ASSOC);
    /*
     * If this org is the final leaf of the org tree, it has no child, it cannot get anything from sub_org($db, $oid)
     * So just get the info of this org
     */
    if (empty($sub_org)) {
        $sub_org[] = query_org_info($oid);
    }
    return $sub_org;
}

/*
 * Query all the direct-child org of this org (without itself)
 * If an org has no child, return empty
 */

function sub_org_without_self($oid) {
    $qry_sub_org = "SELECT ui.USER_NAME,oi.ID AS ORG_ID,oi.NAME AS ORG_NAME,oi.OWNER_ID,oi.EXP_START,oi.EXP_END,oi.ACT_START,oi.ACT_END,oi.STATUS,oi.CCC,oi.BHC,oi.COMMENT FROM org_info AS oi,user_info AS ui WHERE oi.FATHER='$oid' AND oi.OWNER_ID=ui.USER_ID ;";
    $rst_sub_org = $GLOBALS['db']->query($qry_sub_org);
    $sub_org = $rst_sub_org->fetch_all(MYSQLI_ASSOC);
    return $sub_org;
}

/*
 * Query all the direct-child org of this org (without itself and with BHC)
 */

function sub_org_with_bhc($oid,$year,$month,$day) {
    $qry_sub_org_bhc = "SELECT ui.USER_NAME,ui.USER_ID,ui.HR_ID,oi.ID AS ORG_ID,oi.NAME AS ORG_NAME,oi.OWNER_ID,oi.ISNETAPP,oi.EXP_START,oi.EXP_END,oi.ACT_START,oi.ACT_END,oi.STATUS,oi.CCC,oi.COMMENT,dob.BHC,dob.WD,dob.DATA FROM (org_info AS oi,user_info AS ui) LEFT JOIN (daily_org_bhc AS dob) ON (oi.ID=dob.FK_ORG_ID AND `YEAR`=$year AND `MONTH`=$month AND `DAY`=$day) WHERE oi.FATHER='$oid' AND oi.OWNER_ID=ui.USER_ID ;";
    $rst_sub_org_bhc = $GLOBALS['db']->query($qry_sub_org_bhc);
    $sub_org_bhc = $rst_sub_org_bhc->fetch_all(MYSQLI_ASSOC);
    return $sub_org_bhc;
}

/**
 * Author: CC
 * Date: 2013-07-11
 * Last Modified: 2013-07-11
 * [sub_project_with_bhc Query all the sub project info of a given org id]
 * @param  [type] $oid   [description]
 * @param  [type] $year  [description]
 * @param  [type] $month [description]
 * @param  [type] $day   [description]
 * @return [type]        [description]
 */
function sub_project_with_bhc($oid,$year,$month,$day) {
    $qry_sub_project_bhc = "SELECT ui.USER_NAME,ui.USER_ID,ui.HR_ID,pi.ID,pi.NAME,pi.OWNER_ID,pi.EXP_START,pi.EXP_END,pi.ACT_START,pi.ACT_END,pi.STATUS,pi.SCOPE,pi.CCC,pi.COMMENT,pi.ISNETAPP,dpb.BHC,dpb.WD,dpb.DATA FROM (project_info AS pi,user_info AS ui) LEFT JOIN (daily_project_bhc AS dpb) ON (pi.ID=dpb.FK_PROJECT_ID AND `YEAR`=$year AND `MONTH`=$month AND `DAY`=$day) WHERE pi.FATHER='$oid' AND pi.OWNER_ID=ui.USER_ID ORDER BY pi.STATUS DESC,pi.EXP_START DESC;";
    $rst_sub_project_bhc = $GLOBALS['db']->query($qry_sub_project_bhc);
    $sub_project_bhc = $rst_sub_project_bhc->fetch_all(MYSQLI_ASSOC);
    return $sub_project_bhc;
}
/**
 * 
 * @param type $oid
 * @param type $year
 * @param type $month
 * @param type $day
 * @return type
 */
function sub_project_resource_info_with_bhc($oid,$year,$month,$day,$VALID_GROUP) {
    $valid_group = '';
    foreach($VALID_GROUP as $key => $val){
         $valid_group .= "'".$key."',";  
    }
    $qry_sub_project_bhc ="SELECT oi.`ID` AS ORG_ID,oi.`NAME` AS ORG_NAME,pi.`ID`,dpb.`BHC`,dpb.`DATA`,pi.`NAME`,ui.`USER_ID`,ui.`USER_NAME`,ui.`TYPE`,ui.`LEVEL` 
                    FROM (`project_info` AS pi,`user_info` AS ui,`user_project_group` AS upg,`project_group_info` AS pgi,`user_org_group` AS uog,`org_info` AS oi)
                    LEFT JOIN (daily_project_bhc AS dpb) ON (pi.ID=dpb.FK_PROJECT_ID AND `YEAR`=$year AND `MONTH`=$month AND `DAY`=$day)
                    WHERE  pi.`ID` = upg.`FK_PROJECT_ID` AND upg.`FK_USER_ID`= ui.`USER_ID` AND pgi.`ID` = upg.`FK_GROUP_ID` AND uog.`FK_USER_ID` = ui.`USER_ID` AND uog.`FK_ORG_ID` = oi.ID AND pgi.`TYPE` IN (".substr($valid_group,0,strlen($valid_group)-1).") AND pi.`STATUS`='1' AND ui.`EMPLOYEE_END` IS NULL AND(oi.`ID` = '$oid' OR pi.`FATHER` = '$oid')ORDER BY ui.`TYPE` DESC;";
    $rst_sub_project_bhc = $GLOBALS['db']->query($qry_sub_project_bhc);
    $sub_project_resource_bhc = $rst_sub_project_bhc->fetch_all(MYSQLI_ASSOC);
    return $sub_project_resource_bhc;
}
function org_resource_info_with_bhc($oid,$year,$month,$day,$VALID_GROUP) {
    $valid_group = '';
    foreach($VALID_GROUP as $key => $val){
         $valid_group .= "'".$key."',";  
    }
    $qry_sub_project_bhc ="SELECT pi.`ID`,dpb.`BHC`,dpb.`DATA`,pi.`NAME`,ui.`USER_ID`,ui.`USER_NAME`,ui.`TYPE`,ui.`LEVEL`,uog.`FK_ORG_ID`
                    FROM (`project_info` AS pi,`user_info` AS ui,`user_project_group` AS upg,`project_group_info` AS pgi,`user_org_group` AS uog)
                    LEFT JOIN (daily_project_bhc AS dpb) ON (pi.ID=dpb.FK_PROJECT_ID AND `YEAR`=$year AND `MONTH`=$month AND `DAY`=$day)
                    WHERE  pi.`ID` = upg.`FK_PROJECT_ID` AND upg.`FK_USER_ID`= ui.`USER_ID` AND uog.`FK_USER_ID` = ui.`USER_ID` AND pgi.`ID` = upg.`FK_GROUP_ID` AND pgi.`TYPE` IN (".substr($valid_group,0,strlen($valid_group)-1).") AND pi.`STATUS`='1' AND ui.`EMPLOYEE_END` IS NULL AND  uog.`FK_ORG_ID` in ($oid) ORDER BY uog.`FK_ORG_ID` ASC, ui.`TYPE` DESC;";
    $rst_sub_project_bhc = $GLOBALS['db']->query($qry_sub_project_bhc);
    $sub_project_resource_bhc = $rst_sub_project_bhc->fetch_all(MYSQLI_ASSOC);
    return $sub_project_resource_bhc;
}
function org_info_with_bhc($oid,$VALID_GROUP) {
    $valid_group = '';
    foreach($VALID_GROUP as $key => $val){
         $valid_group .= "'".$key."',";  
    }
    $qry_sub_project_bhc ="SELECT ui.`USER_ID`,ui.`USER_NAME`,ui.`TYPE`,ui.`LEVEL`,uog.`FK_ORG_ID`
                    FROM `user_info` AS ui,`user_org_group` AS uog,org_group_info as ogi
                    WHERE uog.`FK_USER_ID` = ui.`USER_ID` AND ogi.ID = uog.FK_GROUP_ID AND ui.`EMPLOYEE_END` IS NULL AND ui.USER_ID > '4' AND uog.`FK_ORG_ID` in ($oid) AND ogi.TYPE IN(".substr($valid_group,0,strlen($valid_group)-1).") ORDER BY uog.`FK_ORG_ID` ASC, ui.`TYPE` DESC;";
    $rst_sub_project_bhc = $GLOBALS['db']->query($qry_sub_project_bhc);
    $sub_project_resource_bhc = $rst_sub_project_bhc->fetch_all(MYSQLI_ASSOC);
    return $sub_project_resource_bhc;
}
/*
 * Get the specific org's father org id
 */

function org_of_father($oid) {
    $qry_father_org = "SELECT oi.FATHER FROM org_info AS oi WHERE oi.ORG_ID='$oid';";
    $rst_father_org = $GLOBALS['db']->query($qry_father_org);
    $father_org = $rst_father_org->fetch_all(MYSQLI_ASSOC);
    return $father_org;
}

/**
 * Author: CC
 * [create_group Create new group]
 * @param  [type] $name [group name]
 * @param  [type] $type [group type]
 * @param  [type] $org_id   [description]
 * @param  [type] $table    [description]
 * @return NULL
 */
function create_group($name, $type, $org_id, $table) {
    $prefix_table  = strtolower($table);
    $qry_new_group = "INSERT INTO ".$prefix_table."_group_info (NAME,TYPE,FK_".$table."_ID) VALUES ('$name','$type','$org_id');";
    $rst_new_group = $GLOBALS['db']->query($qry_new_group);
}

/**
 * [create_org Create a new org]
 * Author: CC
 * Modified: 2013-05-28
 * @param  [type] $org_info       [Basic org info]
 * @param  [type] $depart_groups  [Default groups]
 * @param  [type] $ss_su          [State schema-state unit]
 * @return [type]                 [The new created org id]
 */
function create_org($org_info, $depart_groups, $ss_su) {
    $qry_new_org = "INSERT INTO org_info (NAME,OWNER_ID,ISNETAPP,EXP_START,EXP_END,ACT_START,ACT_END,FATHER,STATUS,CCC,COMMENT) 
VALUES('" . $org_info['new_org_name'] . "','" . $org_info['new_org_owner'] . "','" . $org_info['new_org_netapp'] ."',NULLIF('" . $org_info['new_org_exs'] . "',''),NULLIF('" . $org_info['new_org_exe'] . "',''),NULLIF('" . $org_info['new_org_acs'] . "',''),NULLIF('" . $org_info['new_org_ace'] . "',''),'" . $org_info['father_org_id'] . "','" . $org_info['new_org_status'] . "',NULLIF('" . $org_info['new_org_ccc'] . "',''),NULLIF('" . $org_info['new_org_com'] . "',''))";
    $rst_new_org = $GLOBALS['db']->query($qry_new_org);
    $new_org_id  = $GLOBALS['db']->insert_id;
    /**
     * Create the default groups for the new created org
     */
    create_default_group($org_info['new_org_name'], $depart_groups, $new_org_id, "ORG");
    $new_created_gid = get_new_group_id($new_org_id, $org_info['new_org_owner'], "ORG");
    /**
     * Insert org owner into the Admin group
     */
    insert_into_admin($new_org_id, $new_created_gid, $org_info['new_org_owner'], "ORG");
    set_default_privlige($new_created_gid, $ss_su, "ORG");
    return $new_org_id;
}

/**
 * Author: CC
 * Date: 07/17/2013
 * [create_project Create a new project]
 * @param  [type] $project_info   [Basic project info]
 * @param  [type] $project_groups [Default groups]
 * @param  [type] $ss_su          [state schema-state unit]
 * @return [type]                 [The new created project id]
 */
function create_project($project_info, $project_groups, $ss_su) {
    $qry_new_project = "INSERT INTO project_info (NAME,OWNER_ID,ISNETAPP,EXP_START,EXP_END,ACT_START,ACT_END,FATHER,STATUS,SCOPE,CCC,COMMENT) 
VALUES('" . $project_info['new_project_name'] . "','" . $project_info['new_project_owner'] . "','" . $project_info['new_project_netapp'] ."',NULLIF('" . $project_info['new_project_exs'] . "',''),NULLIF('" . $project_info['new_project_exe'] . "',''),NULLIF('" . $project_info['new_project_acs'] . "',''),NULLIF('" . $project_info['new_project_ace'] . "',''),'" . $project_info['father_project_id'] . "','" . $project_info['new_project_status'] . "','" . $project_info['new_project_scope'] . "',NULLIF('" . $project_info['new_project_ccc'] . "',''),NULLIF('" . $project_info['new_project_com'] . "',''))";
    $rst_new_project = $GLOBALS['db']->query($qry_new_project);
    $new_project_id  = $GLOBALS['db']->insert_id;
    /**
     * Create the default groups for the new created project
     */
    create_default_group($project_info['new_project_name'], $project_groups, $new_project_id, "PROJECT");
    $new_created_gid = get_new_group_id($new_project_id, $project_info['new_project_owner'], "PROJECT");
    /**
     * Insert project owner into the Admin group
     */
    insert_into_admin($new_project_id, $new_created_gid, $project_info['new_project_owner'], "PROJECT");
    set_default_privlige($new_created_gid, $ss_su, "PROJECT");
    return $new_project_id;
}

/**
 * Update organization
 */
function update_org($org_info) {
    $qry_update_org = "UPDATE org_info SET NAME='" . $org_info['new_org_name'] . "',OWNER_ID='" . $org_info['new_org_owner'] . "',ISNETAPP='" . $org_info['new_org_netapp'] . "',EXP_START=NULLIF('" . $org_info['new_org_exs'] . "',''),EXP_END=NULLIF('" . $org_info['new_org_exe'] . "',''),ACT_START=NULLIF('" . $org_info['new_org_acs'] . "',''),ACT_END=NULLIF('" . $org_info['new_org_ace'] . "',''),STATUS='" . $org_info['new_org_status'] . "',CCC=NULLIF('" . $org_info['new_org_ccc'] . "',''), COMMENT='" . $org_info['new_org_com'] . "' WHERE ID='" . $org_info['current_org_id'] . "';";
    $rst_update = $GLOBALS['db']->query($qry_update_org);
}

/**
 * Author: CC
 * Date: 07/16/2013
 * Last Modified:07/17/2013
 * [update_project Update project info]
 * @param  [type] $project_info [Array of project info]
 * @return [type]           [description]
 */
function update_project($project_info) {
    $qry_update_project = "UPDATE project_info SET NAME='" . $project_info['new_project_name'] . "',OWNER_ID='" . $project_info['new_project_owner'] . "',ISNETAPP='" . $project_info['new_project_netapp'] . "',EXP_START=NULLIF('" . $project_info['new_project_exs'] . "',''),EXP_END=NULLIF('" . $project_info['new_project_exe'] . "',''),ACT_START=NULLIF('" . $project_info['new_project_acs'] . "',''),ACT_END=NULLIF('" . $project_info['new_project_ace'] . "',''),STATUS='" . $project_info['new_project_status'] . "',SCOPE='" . $project_info['new_project_scope'] . "',CCC=NULLIF('" . $project_info['new_project_ccc'] . "',''), COMMENT=NULLIF('" . $project_info['new_project_com'] . "','') WHERE ID='" . $project_info['current_project_id'] . "';";
    $rst_update = $GLOBALS['db']->query($qry_update_project);
}

/**
 * [create_default_group Create four new default groups]
 * 
 * If the org type is department, create the following 3 groups:
 * 1. Admin
 * 2. QA
 * 3. Delegate
 * 4. 3rd_Party
 * If the org type is project,create the following 4 groups:
 * 1. Admin
 * 2. Engineer
 * 3. QA
 * 4. Delegate
 * 5. 3rd_Party
 * 
 * Author: CC
 * Date: 07/17/2013
 * @param  [type] $new_name       [New org or project name]
 * @param  [type] $dafault_groups []
 * @param  [type] $last_id        [New org or project id]
 * @param  [type] $column         [which field will be update: FK_ORG_ID/FK_PROJECT_ID]
 * @return [type]                 [description]
 */
function create_default_group($new_name, $dafault_groups, $last_id, $column) {
    $prefix_table = strtolower($column);
    foreach ($dafault_groups as $dg_key => $dg_val) {
        $defaultGroupData[] = "('" . $new_name . "_" . $dg_val['name'] . "','" . $dg_val['type'] . "','" . $last_id . "')";
    }
    $qry_new_group = "INSERT INTO ".$prefix_table."_group_info (NAME,TYPE,FK_".$column."_ID) VALUES ".implode(",", $defaultGroupData);
    $rst_new_group = $GLOBALS['db']->query($qry_new_group);
}

/*
 * Get the new created group id
 */

function get_new_group_id($last_id, $owner, $column) {
    $prefix_table = strtolower($column);
    $qry_ngid = "SELECT ID,TYPE FROM ".$prefix_table."_group_info WHERE FK_".$column."_ID='" . $last_id . "';";
    $rst_ngid = $GLOBALS['db']->query($qry_ngid);
    $new_gid  = $rst_ngid->fetch_all(MYSQLI_ASSOC);
    return $new_gid;
}

/*
 * Insert the owner of the new created org into its' admin group
 */

function insert_into_admin($id, $grp_id, $owner, $column) {
    $prefix_table = strtolower($column);
    foreach ($grp_id as $gi_key => $gi_val) {
        if ($gi_val['TYPE'] == 1) {
            $qry_insert_owner = "INSERT INTO user_".$prefix_table."_group (FK_USER_ID,FK_".$prefix_table."_ID,FK_GROUP_ID) VALUES ('$owner','$id','" . $gi_val['ID'] . "');";
            $rst_insert_owner = $GLOBALS['db']->query($qry_insert_owner);
        }
    }
}


/**
 * Author: CC
 * Last Modified: 08/05/2013
 * [set_default_privlige 
 * Set the default privilege of each type of group
 * new_perm_withSS($db, $new_perm_withSS, $group_id);
 * 
 * Group Type:
 *     1  Admin
 *     3  Engineer
 *     5  QA
 *     7  Delegate
 *     15 3rd_Party
 * 
 * Permission:
 *     1 None
 *     2 Read
 *     4 Write
 *     6 Audit
 * ]
 * @param [array] $new_created_gid [new created group id]
 * @param [array] $ss_su           [state schema id and state unit id]
 */
function set_default_privlige($new_created_gid, $ss_su, $type) {
    if($type === "ORG"){
        $default_privlige = $GLOBALS['DEFAULT_PRIVILEGE_REOURCE'];
    }else if($type === "PROJECT"){
        $default_privlige = $GLOBALS['DEFAULT_PRIVILEGE_PROJECT'];
    }

    foreach ($new_created_gid as $ncg_key => $ncg_val) {
        switch ($ncg_val['TYPE']) {
            case 1:
                foreach ($ss_su as $sssu_key => $sssu_val) {
                    if(!isset($default_privlige['1'][$sssu_val['SUID']])){
                        $new_perm_withSS_admin[] = array(
                            'state_schema' => $sssu_val['SSID'],
                            'state_unit'   => $sssu_val['SUID'],
                            'permission'   => '4');
                    }else{
                        $new_perm_withSS_admin[] = array(
                            'state_schema' => $sssu_val['SSID'],
                            'state_unit'   => $sssu_val['SUID'],
                            'permission'   => $default_privlige['1'][$sssu_val['SUID']]);
                    }
                }
                new_perm_withSS($new_perm_withSS_admin, $ncg_val['ID'], $type);
                break;
            case 3:
                foreach ($ss_su as $sssu_key => $sssu_val) {
                    if(!isset($default_privlige['3'][$sssu_val['SUID']])){
                        $new_perm_withSS_engineer[] = array(
                            'state_schema' => $sssu_val['SSID'],
                            'state_unit'   => $sssu_val['SUID'],
                            'permission'   => '2');
                    }else{
                        $new_perm_withSS_engineer[] = array(
                            'state_schema' => $sssu_val['SSID'],
                            'state_unit'   => $sssu_val['SUID'],
                            'permission'   => $default_privlige['3'][$sssu_val['SUID']]);
                    }
                }
                new_perm_withSS($new_perm_withSS_engineer, $ncg_val['ID'], $type);
                break;
            case 5:
                foreach ($ss_su as $sssu_key => $sssu_val) {
                    if(!isset($default_privlige['5'][$sssu_val['SUID']])){
                        $new_perm_withSS_qa[] = array(
                            'state_schema' => $sssu_val['SSID'],
                            'state_unit'   => $sssu_val['SUID'],
                            'permission'   => '2');
                    }else{
                        $new_perm_withSS_qa[] = array(
                            'state_schema' => $sssu_val['SSID'],
                            'state_unit'   => $sssu_val['SUID'],
                            'permission'   => $default_privlige['5'][$sssu_val['SUID']]);
                    }
                }
                new_perm_withSS($new_perm_withSS_qa, $ncg_val['ID'], $type);
                break;
            case 7:
                foreach ($ss_su as $sssu_key => $sssu_val) {
                    $new_perm_withSS_delegate[] = array(
                        'state_schema' => $sssu_val['SSID'],
                        'state_unit'   => $sssu_val['SUID'],
                        'permission'   => '1');
                }
                new_perm_withSS($new_perm_withSS_delegate, $ncg_val['ID'], $type);
                break;
            case 15:
                foreach ($ss_su as $sssu_key => $sssu_val) {
                    if(!isset($default_privlige['15'][$sssu_val['SUID']])){
                        $new_perm_withSS_audit[] = array(
                            'state_schema' => $sssu_val['SSID'],
                            'state_unit'   => $sssu_val['SUID'],
                            'permission'   => '6');
                    }else{
                        $new_perm_withSS_audit[] = array(
                            'state_schema' => $sssu_val['SSID'],
                            'state_unit'   => $sssu_val['SUID'],
                            'permission'   => $default_privlige['15'][$sssu_val['SUID']]);
                    }
                }
                new_perm_withSS($new_perm_withSS_audit, $ncg_val['ID'], $type);
                break;
        }
    }
}

/**
 * [add_target_bhc Add bhc for the target day]
 * Author: CC
 * Date: 2013-05-29
 * CC
 * Modified: 08/08/2013
 * [Add a field: table]
 * Modified: 09/04/2013     CC
 *     If the target day is equals or larger than today, just insert, no change
 *     else insert bhc info till today
 * @param [type] $oid   [org id]
 * @param [type] $target_date  [date]
 * @param [type] $bhc   [bhc]
 * @param [type] $bhc_data    [array]
 * @param [type] $table       [description]
 */
function add_target_bhc($oid,$target_date,$bhc,$bhc_data,$table){
    $past_date_bhc      = array();
    $wd                 = getWorkingDays($target_date, $target_date);
    $array_target_date  = explode('-',$target_date);
    $target_week_number = month_week_number_target_date($target_date);
    $prefix_table = strtolower($table);
    if($target_date >= $GLOBALS['today']){
        $past_date_bhc[] = "('$oid','{$array_target_date[0]}','{$array_target_date[1]}','$target_week_number','{$array_target_date[2]}','$bhc','$wd','$bhc_data')";
        $insert_bhc   = "INSERT INTO daily_".$prefix_table."_bhc (FK_".$table."_ID,YEAR,MONTH,WEEK,DAY,BHC,WD,DATA) VALUES ".implode(',', $past_date_bhc).";";
    }else{
        $past_date_bhc[] = "('$oid','{$array_target_date[0]}','{$array_target_date[1]}','$target_week_number','{$array_target_date[2]}','$bhc','$wd','$bhc_data')";
        while($target_date < $GLOBALS['today']){
            $target_date        = date('Y-m-d',strtotime('+1 day', strtotime($target_date)));
            $wd                 = getWorkingDays($target_date, $target_date);
            $target_week_number = month_week_number_target_date($target_date);
            $array_past_date    = explode('-', $target_date);
            $past_date_bhc[] = "('$oid','{$array_past_date[0]}','{$array_past_date[1]}','$target_week_number','{$array_past_date[2]}','$bhc','$wd','$bhc_data')";
        }
        $insert_bhc = "INSERT INTO daily_".$prefix_table."_bhc (FK_".$table."_ID,YEAR,MONTH,WEEK,DAY,BHC,WD,DATA) VALUES ".implode(',', $past_date_bhc).";";
    }
    $rst_ib = $GLOBALS['db'] -> query($insert_bhc);
}

/**
 * [update_target_bhc Update bhc for the target day]
 * Author: CC
 * Date: 2013-05-30
 * CC
 * Modified: 08/08/2013
 * [Add a field: table]
 * Modified: 09/04/2013     CC
 *     If the target day is equals or larger than today, just insert, no change
 *     else delete bhc info of this org where date larger the target date, then call function add_target_bhc() to add bhc till today
 * @param [type] $oid   [org id]
 * @param [type] $target_date  [date]
 * @param [type] $bhc   [bhc]
 * @param [type] $bhc_data    [array]
 * @param [type] $table       [description]
 */
function update_target_bhc($oid,$target_date,$bhc,$bhc_data,$table){
    $array_target_date  = explode('-',$target_date);
    $prefix_table       = strtolower($table);
    if($target_date >= $GLOBALS['today']){
        $update_bhc   = "UPDATE daily_".$prefix_table."_bhc SET BHC = '$bhc', DATA = '$bhc_data' WHERE FK_".$table."_ID='$oid' AND YEAR='{$array_target_date[0]}' AND MONTH='{$array_target_date[1]}' AND DAY='{$array_target_date[2]}';";
        $rst_ib       = $GLOBALS['db'] -> query($update_bhc);
    }else{
        //Step 1: delete the past date's bhc
        $delete_past_bhc = "DELETE FROM daily_".$prefix_table."_bhc WHERE FK_".$table."_ID='$oid' AND CONCAT(YEAR,'-',MONTH,'-',DAY) >= '$target_date';";
        $rst_dpb = $GLOBALS['db']->query($delete_past_bhc);
        //Step2: Insert bhc info of past dates
        add_target_bhc($oid,$target_date,$bhc,$bhc_data,$table);
    }
}

/**
 * Author: CC
 * Modified: 08/08/2013 
 * [op_bhc_of_day Get the bhc of a day]
 * @param  [type] $date  [description]
 * @param  [type] $table [description]
 * @return [type]        [description]
 */
function op_bhc_of_day($date,$table){
    $prefix_table = strtolower($table);
    $target_date  = explode("-",$date);
    $qry_op_bhc   = "SELECT db.FK_".$table."_ID, db.YEAR, db.MONTH, db.DAY, db.BHC, db.DATA FROM daily_".$prefix_table."_bhc AS db, ".$prefix_table."_info AS i WHERE db.YEAR = '$target_date[0]' AND db.MONTH = '$target_date[1]' AND db.DAY = '$target_date[2]' AND i.ID=db.FK_".$table."_ID AND i.`STATUS`='1';";
    $rst_op_bhc   = $GLOBALS['db'] -> query($qry_op_bhc);
    $op_bhc_temp  = $rst_op_bhc -> fetch_all(MYSQLI_ASSOC);
    if(!empty($op_bhc_temp)){
        foreach($op_bhc_temp as $obt_key => $obt_val){
            $op_bhc[$obt_val['FK_'.$table.'_ID']] = $obt_val;
        }
    }else{
        $op_bhc = array();
    }
    return $op_bhc;
}

/**
 * Author: CC
 * Date: 07/15/2013
 * Last Modified:
 * [delete_org_project Execute delete operation for org_info or project_info]
 * @param  [type] $table [DB table name]
 * @param  [type] $index [DB table field]
 * @param  [type] $value [value: org_id or project_id]
 * @return [type]        [No return]
 */
function delete_org_project($table, $index, $value) {
    $qry_del = "DELETE FROM $table WHERE $index='" . $value . "'";
    $rst_del = $GLOBALS['db']->query($qry_del);

    //log this operation
    $db_operation = array(
        'table' => $table,
        'field' => $index,
        'value' => $value
        );
    log_operation($_SESSION['user_id'], $_SESSION['login_id'], "Organization", 1, $db_operation);
}

/**
 * Author: CC
 * Date: 08/10/2013
 * [delete_group Delete group]
 * @param  [type] $grp_id [description]
 * @param  [type] $table  [description]
 * @return [type]         [description]
 */
function delete_group($grp_id, $table) {
    $prefix_table = strtolower($table);
    $qry_del = "DELETE FROM ".$prefix_table."_group_info WHERE ID='" . $grp_id . "';";
    $rst_del = $GLOBALS['db']->query($qry_del);
}

/**
 * Author: CC
 * Date:
 * Get all the members of the specific group
 * Modified: 08/16/2013     CC
 * Query data joined on customer account
 */
function group_member($table) {
    $prefix_table    = strtolower($table);
    $qry_grp_member  = "SELECT ui.USER_NAME, ui.HR_ID, ui.NICK_NAME, ui.LEVEL,`OWNER`,ACCOUNT, ug.FK_USER_ID,opi.NAME,opi.ID  FROM user_".$prefix_table."_group AS ug,".$prefix_table."_info AS opi, user_info AS ui LEFT JOIN (customer_account AS ca) ON (ca.MAINTAINER = ui.USER_ID) WHERE ug.FK_USER_ID = ui.USER_ID AND opi.ID = ug.FK_".$table."_ID;";
    $rst_gm          = $GLOBALS['db']->query($qry_grp_member);
    $grp_member_temp = $rst_gm->fetch_all(MYSQLI_ASSOC);
    $grp_member      = array();
    foreach ($grp_member_temp as $gmt_key => $gmt_val) {
        $grp_member[$gmt_val['FK_USER_ID']] = $gmt_val;
    }
    return $grp_member;
}

/**
 * Author: CC
 * Date:
 * Get all the members of the specific group
 * Modified: 08/16/2013     CC
 * Query data joined on customer account
 */
function group_member_withID($grp_id, $table) {
    $prefix_table = strtolower($table);
    $qry_grp_member = "SELECT ui.USER_NAME, ui.HR_ID, ui.NICK_NAME, ui.LEVEL,`OWNER`,ACCOUNT, ug.FK_USER_ID  FROM user_".$prefix_table."_group AS ug, user_info AS ui LEFT JOIN (customer_account AS ca) ON (ca.MAINTAINER = ui.USER_ID) WHERE ug.FK_USER_ID = ui.USER_ID AND ug.FK_GROUP_ID='" . $grp_id . "'";
    $rst_gm = $GLOBALS['db']->query($qry_grp_member);
    $grp_member_temp = $rst_gm->fetch_all(MYSQLI_ASSOC);
    foreach ($grp_member_temp as $gmt_key => $gmt_val) {
        $grp_member[$gmt_val['FK_USER_ID']] = $gmt_val;
    }
    if(!isset($grp_member)){
        $grp_member = array();
    }
    return $grp_member;
}

/**
 * Author: CC
 * Last Modified: 08/06/2013
 * [Get all the members of the specific group except the current group]
 * [If this user is already exists in any other group, he cannot be added any more]
 * @param  [int] $oid   [description]
 * @param  [int] $gid   [description]
 * @param  [string] $table [description]
 * @return [array]        [description]
 */
function query_org_member($oid, $gid, $table) {
    $prefix_table = strtolower($table);
    $qry_org_member = "SELECT UI.USER_NAME USER_NAME,UG.FK_USER_ID USER_ID,UG.FK_GROUP_ID GROUP_ID,GI.NAME GROUP_NAME FROM user_".$prefix_table."_group AS UG,user_info AS UI,".$prefix_table."_group_info AS GI WHERE UI.USER_ID=UG.FK_USER_ID AND GI.ID=UG.FK_GROUP_ID AND UG.FK_".$table."_ID='" . $oid . "' AND UG.FK_GROUP_ID <>'" . $gid . "'";
    $rst_org_member = $GLOBALS['db']->query($qry_org_member);
    $org_member = $rst_org_member->fetch_all(MYSQLI_ASSOC);
    return $org_member;
}

/**
 * Author: CC
 * Last Modified: 08/06/2013
 * [insert_new_group_member Insert the new added member into a specific group]
 * @param  [int] $oid        [description]
 * @param  [int] $gid        [description]
 * @param  [array] $new_member [description]
 * @param  [string] $table      [description]
 * @return [array]             [description]
 */
function insert_new_group_member($oid, $gid, $new_member, $table) {
    $date             = date("Y-m-d");
    $array_new_member = array();
    $prefix_table     = strtolower($table);
    
    foreach ($new_member as $nm_val) {
        $array_new_member[] = "('$nm_val','$oid','$gid')";
        $array_new_member_date[] = "('$nm_val','$oid','$gid','$date')";
    }

    $qry_insert = "INSERT INTO user_".$prefix_table."_group (FK_USER_ID,FK_".$prefix_table."_ID,FK_GROUP_ID) VALUES ".implode(',', $array_new_member);
    $rst_insert = $GLOBALS['db']->query($qry_insert);
    create_user_group_history($array_new_member_date, $table);
}

/*
 * Delete the member that were removed from the group
 */

function delete_group_member($oid, $gid, $delete_member, $table) {
    $prefix_table = strtolower($table);
    foreach ($delete_member as $dm_val) {
        $qry_del = "DELETE FROM user_".$prefix_table."_group WHERE FK_USER_ID='" . $dm_val . "' AND FK_".$prefix_table."_ID='" . $oid . "' AND FK_GROUP_ID='" . $gid . "';";
        $rst_del = $GLOBALS['db']->query($qry_del);
        update_user_group_history($oid, $gid, $dm_val, $table);
    }
}

/**
 * [remove_op_record description]
 * @param  [type] $orgs        [description]
 * @param  [type] $projects    [description]
 * @param  [type] $user_id     [description]
 * @param  [type] $resign_date [description]
 * @return [type]              [description]
 */
function remove_op_record($orgs, $projects, $user_id, $resign_date){
    //Step 1 -- Delete from user_org_group & user_org_group_history
    if(!empty($orgs)){
        foreach ($orgs as $orgs_key => $orgs_val) {
            $qry_del_uog  = "DELETE FROM user_org_group WHERE FK_USER_ID = '" . $user_id . "' AND FK_ORG_ID = '" . $orgs_val['ORG_ID'] . "' AND FK_GROUP_ID = '" . $orgs_val['FK_GROUP_ID'] . "';";
            $qry_del_uogh = "UPDATE user_org_group_history SET END_DATE = '$resign_date' WHERE FK_USER_ID = '$user_id' AND FK_ORG_ID = '" . $orgs_val['ORG_ID'] . "' AND FK_GROUP_ID = '" . $orgs_val['FK_GROUP_ID'] . "';";
            $rst_del_uog  = $GLOBALS['db']->query($qry_del_uog);
            $qry_del_uogh = $GLOBALS['db']->query($qry_del_uogh);
        }
    }
    //Step 2 -- Delete from user_project_group & user_project_group_history
    if(!empty($projects)){
        foreach ($projects as $projects_key => $projects_val) {
            $qry_del_upg  = "DELETE FROM user_project_group WHERE FK_USER_ID = '" . $user_id . "' AND FK_PROJECT_ID = '" . $projects_val['PROJECT_ID'] . "' AND FK_GROUP_ID = '" . $projects_val['FK_GROUP_ID'] . "';";
            $qry_del_upgh = "UPDATE user_project_group_history SET END_DATE = '$resign_date' WHERE FK_USER_ID = '$user_id' AND FK_PROJECT_ID = '" . $projects_val['PROJECT_ID'] . "' AND FK_GROUP_ID = '" . $projects_val['FK_GROUP_ID'] . "';";
            $rst_del_upg  = $GLOBALS['db']->query($qry_del_upg);
            $qry_del_upgh = $GLOBALS['db']->query($qry_del_upgh);
        }
    }
    
}

/**
 * Author: CC
 * Last Modified: 08/06/2013
 * [create_user_group_history Insert a new record into user_org_group_history]
 * @param  [array] $array_new_member_date [array of required info of new member: user_id, oid, gtype and date]
 * @param  [string] $table                 [description]
 * @return NULL
 */
function create_user_group_history($array_new_member_date, $table) {
    $prefix_table = strtolower($table);
    $qry_insert   = "INSERT INTO user_".$prefix_table."_group_history (FK_USER_ID,FK_".$prefix_table."_ID,FK_GROUP_ID,START_DATE) VALUES ".implode(',', $array_new_member_date);
    $rst_insert   = $GLOBALS['db']->query($qry_insert);
}

/*
 * Update the user's user_org_group_history, set the end_date
 */
function update_user_group_history($oid, $gid, $dm_val, $table) {
    $prefix_table = strtolower($table);
    $qry_del      = "UPDATE user_".$prefix_table."_group_history SET END_DATE='" . date("Y-m-d") . "' WHERE FK_USER_ID='" . $dm_val . "' AND FK_".$prefix_table."_ID='" . $oid . "' AND FK_GROUP_ID='" . $gid . "';";
    $rst_del      = $GLOBALS['db']->query($qry_del);
}

/*
 * Get all resigned employees
 */
function resigned_employee() {
    $qry_resigned_employee = "SELECT USER_ID,USER_NAME FROM user_info WHERE EMPLOYEE_END < CURRENT_DATE;";
    $rst_resigned_employee = $GLOBALS['db']->query($qry_resigned_employee);
    $resigned_employee = $rst_resigned_employee->fetch_all(MYSQLI_ASSOC);
    return $resigned_employee;
}

/*
 * Push org info into table
 */

function push_org_info_into_table($sub_org, $oid, $su_id, $gtype, $permission, $required_perm, $optype) {
    ?>
    <table class="table table-condensed table-striped table-hover">
        <caption><h4>Organization Info</h4></caption>
        <thead>
            <tr>
                <th>No</th>
                <th>Name</th>
                <th>Owner</th>
                <th colspan="2">Expected Start/End</th>
                <th colspan="2">Actual Start</th>
                <th>Status</th>
                <th>CCC</th>
                <th>BHC</th>
                <th>Comment</th>
                <?php
                if ($permission == $required_perm) {
                    echo "<th></th>";
                }
                ?>
            </tr>
        </thead>
        <?php
        foreach ($sub_org as $so_key => $so_val) {
            $short_comment = substr($so_val['COMMENT'], 0, 20);
            ?>
            <tr>
                <td><?php echo ($so_key + 1) ?></td>
                <td><?php echo $so_val['ORG_NAME'] ?></td>
                <td class="chinese"><?php echo $so_val['USER_NAME'] ?></td>
                <td><?php echo ($so_val['EXP_START'] == "0000-00-00"||$so_val['EXP_START'] == "") ? "" : date('m/d/y',strtotime($so_val['EXP_START'])); ?></td>
                <td><?php echo ($so_val['EXP_END'] == "0000-00-00"||$so_val['EXP_END'] == "") ? "" : date('m/d/y',strtotime($so_val['EXP_END'])); ?></td>
                <td><?php echo ($so_val['ACT_START'] == "0000-00-00"||$so_val['ACT_START'] == "") ? "" : date('m/d/y',strtotime($so_val['ACT_START'])); ?></td>
                <td><?php echo ($so_val['ACT_END'] == "0000-00-00")||$so_val['ACT_END'] == "" ? "" : date('m/d/y',strtotime($so_val['ACT_END'])); ?></td>
                <td><?php echo ($so_val['STATUS'] == 1 ? "Active" : "Inactive") ?></td>
                <td><?php echo $so_val['CCC'] ?></td>
                <td><?php echo isset($so_val['BHC']) ? $so_val['BHC'] : "N/A"; ?></td>
                <td title="<?php echo $so_val['COMMENT'];?>" class="chinese"><?php echo $short_comment; ?></td>

                <?php
                if ($permission == $required_perm) {
                    $parm_string = array(
                            'ORG_ID'    => $so_val['ORG_ID'],
                            'ORG_NAME'  => $so_val['ORG_NAME'],
                            'USER_NAME' => $so_val['USER_NAME'],
                            'USER_HRID' => $so_val['HR_ID'],
                            'OWNER_ID'  => $so_val['OWNER_ID'],
                            'ISNETAPP'  => $so_val['ISNETAPP'],
                            'EXP_START' => $so_val['EXP_START'],
                            'EXP_END'   => $so_val['EXP_END'],
                            'ACT_START' => $so_val['ACT_START'],
                            'ACT_END'   => $so_val['ACT_END'],
                            'STATUS'    => $so_val['STATUS'],
                            'CCC'       => $so_val['CCC'],
                            'COMMENT'   => $so_val['COMMENT'],
                            'DATA'      => $so_val['DATA']
                            );
                    ?>
                    <td>
                        <a href='../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&optype=<?php echo base64_encode($optype); ?>&url=<?php echo base64_encode("org_mgt/update_organization.php"); ?>&gtype=<?php echo base64_encode($gtype); ?>&type=edit&<?php echo http_build_query($parm_string); ?>'>
                            <img width='16' height='16' src='../../../lib/image/icons/edit.png'/>
                        </a>&nbsp;&nbsp;
                        <a onclick="return delete_bug()" href='../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&optype=<?php echo base64_encode($optype); ?>&url=<?php echo base64_encode("org_mgt/update_organization.php"); ?>&gtype=<?php echo base64_encode($gtype); ?>&ORG_ID=<?php echo base64_encode($so_val['ORG_ID']); ?>&type=delete'>
                            <img width='16' height='16' src='../../../lib/image/icons/remove.png'/>
                        </a></td><?php } ?>
            </tr>
            <?php
        }
        echo "<br>";
        ?>	
    </table>
    <?php
}

/**
 * [push_project_into_table Present the project info in a table]
 * @param  [type] $sub_org       [description]
 * @param  [type] $oid           [description]
 * @param  [type] $su_id         [description]
 * @param  [type] $gtype         [description]
 * @param  [type] $permission    [description]
 * @param  [type] $required_perm [description]
 * @return [type]                [description]
 */
function push_project_into_table($sub_project, $oid, $su_id, $gtype, $permission, $required_perm, $optype) {
    ?>
    <a id='download' href="'../../../../org_mgt/export_project_info.php?oid='<?php echo base64_encode($oid); ?>'_blank'">Click me to download project resource</a>
    <table class="table table-condensed table-striped table-hover">
        <caption><h4>Project Info</h4></caption>
        <thead>
            <tr>
                <th>No</th>
                <th>Name</th>
                <th>Owner</th>
                <th colspan="2">Expected Start/End</th>
                <th colspan="2">Actual Start/End</th>
                <th>Status</th>
                <th>CCC</th>
                <th>BHC</th>
                <th>Comment</th>
                <?php
                if ($permission == $required_perm) {
                    echo "<th></th>";
                }
                ?>
            </tr>
        </thead>
        <?php
        foreach ($sub_project as $sp_key => $sp_val) {
            $short_comment = substr($sp_val['COMMENT'], 0, 20);
            ?>
            <tr>
                <td><?php echo ($sp_key + 1) ?></td>
                <td><?php echo $sp_val['NAME'] ?></td>
                <td class="chinese"><?php echo $sp_val['USER_NAME'] ?></td>
                <td><?php echo $sp_val['EXP_START'] == "0000-00-00" ? "" : $sp_val['EXP_START']; ?></td>
                <td><?php echo $sp_val['EXP_END'] == "0000-00-00" ? "" : $sp_val['EXP_END']; ?></td>
                <td><?php echo $sp_val['ACT_START'] == "0000-00-00" ? "" : $sp_val['ACT_START']; ?></td>
                <td><?php echo $sp_val['ACT_END'] == "0000-00-00" ? "" : $sp_val['ACT_END']; ?></td>
                <td><?php echo ($sp_val['STATUS'] == 1 ? "Active" : "Inactive") ?></td>
                <td><?php echo $sp_val['CCC'] ?></td>
                <td><?php echo isset($sp_val['BHC']) ? $sp_val['BHC'] : "N/A"; ?></td>
                <td title="<?php echo $sp_val['COMMENT'];?>" class="chinese"><?php echo $short_comment; ?></td>

                <?php
                if ($permission == $required_perm) {
                        $parm_string = array(
                            'PROJECT_ID'   => $sp_val['ID'],
                            'PROJECT_NAME' => $sp_val['NAME'],
                            'USER_NAME'    => $sp_val['USER_NAME'],
                            'USER_HRID'    => $sp_val['HR_ID'],
                            'OWNER_ID'     => $sp_val['OWNER_ID'],
                            'ISNETAPP'     => $sp_val['ISNETAPP'],
                            'EXP_START'    => $sp_val['EXP_START'],
                            'EXP_END'      => $sp_val['EXP_END'],
                            'ACT_START'    => $sp_val['ACT_START'],
                            'ACT_END'      => $sp_val['ACT_END'],
                            'STATUS'       => $sp_val['STATUS'],
                            'SCOPE'        => $sp_val['SCOPE'],
                            'CCC'          => $sp_val['CCC'],
                            'COMMENT'      => $sp_val['COMMENT'],
                            'DATA'         => $sp_val['DATA']
                            );
                    ?>
                    <td>
                        <a href='../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&optype=<?php echo base64_encode($optype); ?>&url=<?php echo base64_encode("org_mgt/update_project.php"); ?>&gtype=<?php echo base64_encode($gtype); ?>&type=edit&<?php echo http_build_query($parm_string); ?>'>
                            <img width='16' height='16' src='../../../lib/image/icons/edit.png'/>
                        </a>&nbsp;&nbsp;
                        <a onclick="return delete_bug()" href='../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&optype=<?php echo base64_encode($optype); ?>&url=<?php echo base64_encode("org_mgt/update_project.php"); ?>&gtype=<?php echo base64_encode($gtype); ?>&type=delete&PROJECT_ID=<?php echo base64_encode($sp_val['ID']); ?>'>
                            <img width='16' height='16' src='../../../lib/image/icons/remove.png'/>
                        </a>
                    </td>
                    <?php } ?>
            </tr>
            <?php
        }
        echo "<br>";
        ?>  
    </table>
    <?php
}

/**
 * Author: CC
 * Date: 08/12/2013
 * [new_resign_record New resign record]
 * @param  [array] $resign_info [description]
 * @return NULL
 */
function new_resign_record($resign_info) {
    $qry_new_resign_record = "INSERT INTO resign_info (TYPE,FK_USER_ID,YEAR,MONTH,DAY,LAST_ORG,REASON,WTG,COMMENTS) VALUES (".$resign_info.");";
    $rst_new_resign_record = $GLOBALS['db']->query($qry_new_resign_record);
}

/**
 * [set_employee_end Update user info set his employee_end time]
 * @param [int] $user_id     [description]
 * @param [date] $resign_date [description]
 */
function set_employee_end($user_id, $resign_date) {
    $set_emp_end = "UPDATE user_info SET EMPLOYEE_END='$resign_date' WHERE USER_ID='$user_id'";
    $rst_emp_end = $GLOBALS['db']->query($set_emp_end);
}

/*
 *  Query existed resign info of this org
 */

function get_resign_info($oid_string) {
    $get_resign_info = "SELECT FK_USER_ID,ui.USER_NAME,ui.LEVEL,ri.YEAR,ri.MONTH,ri.DAY,LAST_ORG,oi.NAME AS ORG_NAME,REASON,WTG,ri.COMMENTS FROM resign_info AS ri, user_info AS ui,org_info AS oi WHERE ri.FK_USER_ID=ui.USER_ID AND ri.LAST_ORG=oi.ID AND LAST_ORG in ($oid_string) ORDER BY YEAR DESC, MONTH DESC, DAY DESC;";
    $rst_resign_info = $GLOBALS['db']->query($get_resign_info);
    $resign_info = $rst_resign_info->fetch_all(MYSQLI_ASSOC);
    return $resign_info;
}

/**
 * [get_resign_info_wite_date Query resign info within the date scope]
 * @param  [type] $oid_string [org string]
 * @param  [type] $date_from  [description]
 * @param  [type] $date_to    [description]
 * @return [array]             [resign info]
 */
function get_resign_info_wite_date($oid_string,$date_from,$date_to) {
    $get_resign_info = "SELECT FK_USER_ID,ui.USER_NAME,ui.HR_ID,ui.LEVEL,ri.YEAR,ri.MONTH,ri.DAY,LAST_ORG,oi.NAME,REASON,WTG,ri.COMMENTS FROM resign_info AS ri, user_info AS ui,org_info AS oi WHERE (CONCAT(ri.YEAR,'-',ri.MONTH,'-',ri.DAY) BETWEEN '$date_from' AND '$date_to') AND ri.FK_USER_ID=ui.USER_ID AND ri.LAST_ORG=oi.ID AND LAST_ORG in ($oid_string) ORDER BY YEAR DESC, MONTH DESC, DAY DESC;";
    $rst_resign_info = $GLOBALS['db']->query($get_resign_info);
    $resign_info = $rst_resign_info->fetch_all(MYSQLI_ASSOC);
    return $resign_info;
}

/*
 *  
 */
/**
 * Author: CC
 * Modified: 08/12/2013
 * [push_resign_info_into_table Push resign info into tables]
 * @param  [array] $resign_info [description]
 * @return NULL
 */
function push_resign_info_into_table($resign_info) {
    ?>
    <table class="table table-striped table-condensed table-hover">
        <caption><h4>Resign Info</h4></caption>
        <thead>
            <tr>
                <th>No</th>
                <th>Org Name</th>
                <th>Employee</th>
                <th>Date</th>
                <th>Reason</th>
                <th>Where</th>
                <th>Comment</th>
            </tr>
        </thead>
        <?php
        foreach ($resign_info as $ri_key => $ri_val) {
            $reason_array  = array();
            $short_comment = substr($ri_val['COMMENTS'], 0, 30);
            $reason        = unserialize($ri_val['REASON']);
            if($reason === NULL){
                foreach ($reason as $rea_key) {
                    $reason_array[] = $GLOBALS['RESIGN_REASON'][$rea_key];
                }
            }else{
                $reason_array = array();
            }
            ?>
            <tr>
                <td><?php echo ($ri_key + 1); ?></td>
                <td><?php echo $ri_val['ORG_NAME']; ?></td>
                <td class="chinese"><?php echo $ri_val['USER_NAME']; ?></td>
                <td><?php echo $ri_val['YEAR'] . "-" . $ri_val['MONTH'] . "-" . $ri_val['DAY']; ?></td>
                <td class="chinese"><?php echo implode(',', $reason_array); ?></td>
                <td class="chinese"><?php echo $ri_val['WTG']; ?></td>
                <td class="chinese" title="<?php echo $ri_val['COMMENTS']; ?>"><?php echo $short_comment; ?></td>
            </tr>
            <?php
        }
}

/*
 * Get billable head count of the specific org
 */
function get_bhc($oid) {
    $qry_bhc = "SELECT BHC FROM org_info WHERE ORG_ID='$oid';";
    $rst_bhc = $GLOBALS['db']->query($qry_bhc);
    $org_bhc = $rst_bhc->fetch_assoc();
    return $org_bhc['BHC'];
}

/*
 *  Calculate bhc
 */
function cal_bhc($all_childs_orgs) {
    $bhc_count = array();
    $bhc_count_temp = array();
    foreach ($all_childs_orgs as $aco_key => $aco_val) {
        foreach ($aco_val as $av_key => $av_val) {
            $bhc_count_temp[$aco_key][] = get_bhc($av_val);
        }
    }
    foreach ($bhc_count_temp as $bc_key => $bc_val) {
        $bhc_count[$bc_key] = array_sum($bc_val);
    }
    return $bhc_count;
}

/*
 * Get bhc info by org
 * e.g.
 * array(
 * 'org_id -> bch_count
 * );
 */
function get_bhc_by_org($all_childs_orgs) {
    $bhc_count = array();
    $bhc_count = cal_bhc($all_childs_orgs);
    return $bhc_count;
}

    /*
     * Prepare all available employees
     */

    function prepare_all_employee($people_count_date) {
        $available_employee = array();
        $ahc_count_temp = array();
        $total_employee_temp = array();
        foreach ($people_count_date as $pcd_key => $pcd_val) {
            foreach ($pcd_val as $pv_key => $pv_val) {
                $total_employee_temp[$pcd_key][] = $pv_val['fte'];
                $total_employee_temp[$pcd_key][] = $pv_val['intern'];
                $ahc_count_temp[$pcd_key][] = count($pv_val['fte']);
                $ahc_count_temp[$pcd_key][] = count($pv_val['intern']);
            }
        }
        $available_employee = array(
            'total' => $total_employee_temp,
            'ahc' => $ahc_count_temp
        );
        return $available_employee;
    }

    /**
     * Get ahc info by org
     * e.g.
     * array(
     * 'org_id -> ach_count
     * );
     */

    function get_ahc_by_org($ahc_count_temp) {
        $ahc_count = array();
        foreach ($ahc_count_temp as $act_key => $act_val) {
            $ahc_count[$act_key] = array_sum($act_val);
        }
        return $ahc_count;
    }

    /*
     *  Calculate billable rate
     */

    function cal_billable_rate($bhc_count, $ahc_count) {
        $brate = array();
        foreach ($bhc_count as $bc_key => $bc_val) {
            $brate[$bc_key] = round($bc_val / $ahc_count[$bc_key], 4) * 100;
        }
        return $brate;
    }

/*
 * Rearrange the array of org iteration task effort
 */
function rearrange_org_iteration_time($org_time) {
    foreach ($org_time as $ot_key => $ot_val) {
        $total_task_effort_by_iteration[$ot_key] = $ot_val['total_org_total_time'];
    }
    return $total_task_effort_by_iteration;
}

    /*
     * Split 'fte' and 'intern' out of all available employee info
     */

    function split_user_id_name($available_employee) {
        $total_employee = array();
        foreach ($available_employee as $ae_key => $ae_val) {
            foreach ($ae_val as $av_key => $av_val) {
                foreach ($av_val as $avv_key => $avv_val) {
                    $total_employee[$ae_key][$avv_val['USER_ID']] = $avv_val['USER_NAME'];
                }
            }
        }
        return $total_employee;
    }

    /*
     * Attrion info
     * Get attrion info by orgs
     */

    function get_attrition_info($all_childs_orgs, $current_month) {
        $resign_count_temp = array();
        $attrition_info = array();
        foreach ($all_childs_orgs as $aco_key => $aco_val) {
            $resign_count = 0;
            foreach ($aco_val as $av_key => $av_val) {
                $resign_count_temp = resign_info($av_val, $current_month);
                $resign_count +=$resign_count_temp;
                $attrition_info[$aco_key] = $resign_count;
            }
        }
        return $attrition_info;
    }

    /*
     * Calculate monthly ahc
     */

    function monthly_ahc($ooid, $current_year, $current_year_month, $month_int, $str_year, $last_month_people_statistics) {
        $mouthly_hc_count = array();
        /* search current org people */
        $current_org_info = current_org_info($ooid, $current_year_month);
        /*
         * entry info and count of the current year
         */
        $month_entry = entry_count($ooid, $current_year_month);
        /*
         * total headcount of current org 
         */
        $current_org_detail = current_org_detail($ooid, $current_year_month);
        $last_month_basic_date = last_month_basic_date($ooid, $current_org_info, $current_year, $current_year_month);
        $mouthly_hc_list = last_month_people_detail($ooid, $month_int, $current_org_info, $str_year, $current_org_detail, $last_month_basic_date['count_current_month_attrition'], $month_entry, $last_month_people_statistics, $current_year);
        ksort($mouthly_hc_list);
        $_SESSION['monthly_ahc_list'][$ooid] = $mouthly_hc_list;

        foreach ($mouthly_hc_list as $mhl_key => $mhl_val) {
            $str_mon = sprintf('%02d', $mhl_key);
            $mouthly_hc_count[$str_mon] = count($mhl_val);
        }
        return $mouthly_hc_count;
    }

    /*
     * Calculate monthly bhc
     */

    function monthly_bhc($ooid, $str_year, $month_int) {
        $org_bhc_final = array();
        /*
         * Get the org sting for search bug in one query using 'IN  ('1','2','3','4')'  function of MySQL
         * e.g. '1','2','3','4'
         */
        $oid_string = get_org_string($ooid);
        $org_bhc_temp = org_bhc($oid_string, $str_year, $month_int);
        $org_bhc = array();

        foreach ($org_bhc_temp as $obt_key => $obt_val) {
            $org_bhc[$obt_val['MONTH']][] = $obt_val['BHC'];
        }
        for ($m = $month_int; $m > 0; $m--) {
            $str_mo = sprintf('%02d', $m);
            if (!isset($org_bhc[$m])) {
                $org_bhc_final[$str_mo] = 0;
            } else {
                $org_bhc_final[$str_mo] = array_sum($org_bhc[$m]);
            }
        }
        ksort($org_bhc_final);
        return $org_bhc_final;
    }

/*
 * Query all the orgs that will end in the future x month
 */
function orgs_end_in_future($start_month, $end_month) {
    $qry_org_end = "SELECT ORG_ID,ORG_NAME,EXP_END,BHC FROM `org_info` WHERE (EXP_END BETWEEN '$start_month-01' AND '$end_month-31');";
    $rst_org_end = $GLOBALS['db']->query($qry_org_end);
    $orgs_will_end = $rst_org_end->fetch_all(MYSQLI_ASSOC);
    return $orgs_will_end;
}

/**
 * Author: CC
 * Date: 2012-11-13
 * Last Modified: 08/06/2013
 * Purposr: Get all valid organization list
 */
function valid_op($table) {
    $valid_op     = array();
    $prefix_table = strtolower($table);
    $qry_op_list  = "SELECT ID FROM ".$prefix_table."_info WHERE STATUS='1';";
    $rst_op       = $GLOBALS['db']->query($qry_op_list);
    $valid_op_temp     = $rst_op->fetch_all(MYSQLI_ASSOC);
    foreach ($valid_op_temp as $key => $value) {
        $valid_op[$value['ID']] = $value;
    }
    return $valid_op;
}

/**
 * Author: CC
 * Date: 2012-11-13
 * [valid_iteration Get all valid iteration list, and pointer them to their own orgs]
 * Modified: 08/11/2013     CC
 * @return [array] [description]
 */
function valid_iteration() {
    $qry_itera_list = "SELECT ITERATION_ID,FK_PROJECT_ID FROM iteration_info WHERE STATUS='1';";
    $rst_itera_list = $GLOBALS['db']->query($qry_itera_list);
    $valid_itera_list_temp = $rst_itera_list->fetch_all(MYSQLI_ASSOC);
    foreach ($valid_itera_list_temp as $vilt_key => $vilt_val) {
        $valid_itera_list[$vilt_val['FK_PROJECT_ID']][] = $vilt_val;
    }
    return $valid_itera_list;
}

/*
 * 
 * Author: CC
 * Date 2012-11-14
 * Last Modified: 08/06/2013
 * Purpose: Get all user org relationship from user_org_group and re-arranger this array
 * Comments: We just includ type kind of group: admin, emgineer, delegate, QA and custom will be excluded
 */
function op_user($table) {
    $org_user     = array();
    $prefix_table = strtolower($table);
    foreach ($GLOBALS['VALID_GROUP'] as $vg_key => $vg_val) {
        $array_valid_group[] = "'" . $vg_key . "'";
    }

    $qry_org_user  = "SELECT ug.FK_USER_ID,ug.FK_".$table."_ID,ug.FK_GROUP_ID,gi.NAME,gi.TYPE,info.NAME OP_NAME,ui.USER_NAME,ui.HR_ID,ui.LEVEL,ui.ROLE,ui.EMAIL,ui.MOBILE,ui.MSN,ui.LOCATION,ca.`OWNER`,ca.ACCOUNT,ui.NICK_NAME FROM ".$prefix_table."_group_info AS gi, user_".$prefix_table."_group AS ug,".$prefix_table."_info AS info, user_info AS ui LEFT JOIN (customer_account AS ca) ON (ui.USER_ID=ca.MAINTAINER) WHERE ug.FK_".$table."_ID = info.ID AND ug.FK_USER_ID = ui.USER_ID AND ug.FK_GROUP_ID=gi.ID AND gi.TYPE IN (".implode(',', $array_valid_group).");";
    $rst_org_user  = $GLOBALS['db']->query($qry_org_user);
    $org_user_temp = $rst_org_user->fetch_all(MYSQLI_ASSOC);
    /*
     * Rearrange this array, and pointer each employee to their own org
     */
    foreach ($org_user_temp as $out_key => $out_val) {
        $org_user[$out_val['FK_'.$table.'_ID']]['LIST'][] = $out_val;
    }
    
    return $org_user;
}

/*
 * Date 2012-11-20
 * Author: CC
 * Purpose: Get all resign info from resign_info and re-arranger this array
 */
function resign_info_in_dates($date_start, $date_end) {
    $qry_resign_info = "SELECT ID, TYPE, FK_USER_ID,RESIGN_DATE,LAST_ORG,REASON,WTG,COMMENTS FROM resign_info WHERE (RESIGN_DATE BETWEEN '$date_start' AND '$date_end')";
    $rst_resign_info = $GLOBALS['db']->query($qry_resign_info);
    $resign_info_temp = $rst_resign_info->fetch_all(MYSQLI_ASSOC);
    if (!empty($resign_info_temp)) {
        foreach ($resign_info_temp as $rit_key => $rit_val) {
            $resign_info[$rit_val['LAST_ORG']][] = $rit_val;
        }
    } else {
        $resign_info[] = array();
    }
    return $resign_info;
}

/**
 * Purpose: daily calculate org's autual head count
 * Author: CC
 * Date: 2012-11-13
 * Comment:
 * Call this script every day (including Saturday and Sunday), get the daily ahc, and store this data in DB daily_org_ahc
 * BTW: for the var $real_time_org_user, it is used to realtime display ahc info
 * Modified: 08/07/2013
 */
function calculate_op_user($table) {
    /**
     * Loop user_org_group, and point them to their own org id
     */
    $org_user_temp = op_user($table);
    /**
     * Get all valid org list
     */
    $valid_ops = valid_op($table);
    $_SESSION['valid_ops'] = $valid_ops;
    foreach ($valid_ops as $vo_val) {
        if(isset($org_user_temp[$vo_val['ID']])){
            $org_user[$vo_val['ID']] = $org_user_temp[$vo_val['ID']];
            foreach ($org_user_temp[$vo_val['ID']]['LIST'] as $out_key => $out_val) {
                $real_time_org_user[$vo_val['ID']][$out_val['FK_USER_ID']] = $out_val['FK_USER_ID'];
            }
        }else{
            $org_user[$vo_val['ID']] = array('LIST' => array());
            $real_time_org_user[$vo_val['ID']] = array();
        }
    }

    /**
     * Chekc whether today is a working day, if yes, return 1, or return 0;
     */
    $WD = getWorkingDays($GLOBALS['today'], $GLOBALS['today']);
    foreach ($org_user as $ou_key => $ou_val) {
        $ahc = count($ou_val['LIST']);
        $org_user[$ou_key]['AHC'] = $ahc;
        /*
         * Calculate man-days 
         * if today is not a working day ,$WD will be 0, and man-days will be 0. 
         */
        $org_user[$ou_key]['MAN_DAYS'] = $ahc * $WD;
    }
    $org_user_temp = array(
        'WD' => $WD, 
        'USER_ORG' => $org_user
        );
    $org_user = array(
        'DETAIL' => $org_user_temp, 
        'REALTIME_AHC_LIST' => $real_time_org_user
        );
    return $org_user;
}

/**
 * Date: 2012-11-29
 * Last Modified: 2013-06-08
 * Author: CC
 * Purpose: Get all the ahc info of the specific date scope
 * Modified: 08/10/2013     CC
 *     Add a new param: $table
 * Modified: 08/21/2013     CC
 *     Remove date scope
 */
function all_op_bhc($table) {
    $bhc_info     = array();
    $prefix_table = strtolower($table);
    $qry_bhc      = "SELECT FK_".$table."_ID,YEAR,MONTH,WEEK,DAY,BHC,WD,DATA FROM daily_".$prefix_table."_bhc;";
    $rst_bhc      = $GLOBALS['db']->query($qry_bhc);
    $bhc_temp     = $rst_bhc->fetch_all(MYSQLI_ASSOC);
    if (!empty($bhc_temp)) {
        foreach ($bhc_temp AS $bt_key => $bt_val) {
            $bhc_info[$bt_val['FK_'.$table.'_ID']][$bt_val['YEAR']][$bt_val['MONTH']][$bt_val['DAY']] = $bt_val['BHC'];
        }
    } else {
        $bhc_info = 0;
    }
    return $bhc_info;
}

/**
 * Date: 2012-12-10
 * Author:CC
 * Purpose: Get the monthly bhc info
 */
function monthly_org_bhc($all_org_bhc, $year, $month) {
    if (isset($all_org_bhc[$year][$month])) {
        $month_bhc = array_sum($all_org_bhc[$year][$month]);
    } else {
        $month_bhc = 0;
    }
    return $month_bhc;
}

/**
 * Date: 2012-11-29
 * Author: CC
 * Purpose: Calculate current month real time AHC of orgs withe given org string, year, month, 
 *              day and return the total ahc count
 * Comment: For the array '$org_user', we've pust all the member list and calculated ahc in it. 
 *              In case one employee in more one org at the same time, we cannot use the org-based 'ahc', 
 *              the duplicated employee will be calculated more than one time,
 *              so we need to user this line to exclude the duplicated names:
 *                  $current_org_emp_list[$out_val['FK_USER_ID']] = $out_val;
 */

function current_month_org_ahc($orgs, $org_user) {
    $org_user_temp           = $org_user['USER_ORG'];
    $current_month_ahc       = array();
    $current_month_ahc_count = 0;
    foreach ($orgs as $aco_key => $aco_val) {
        if(!empty($org_user_temp[$aco_key]['LIST'])){
            foreach ($org_user_temp[$aco_key]['LIST'] as $out_ke => $out_val) {
                $current_org_emp_list[$out_val['FK_USER_ID']] = $out_val;
            }
        }
    }
    if(isset($current_org_emp_list)){
        $current_month_ahc_count = count($current_org_emp_list);
        $current_month_ahc = array(
            'COUNT' => $current_month_ahc_count,
            'LIST'  => $current_org_emp_list
        );
    }else{
        $current_month_ahc = array(
            'COUNT' => 0,
            'LIST'  => array()
        );
    }
    
    return $current_month_ahc;
}

/**
 * Author: CC
 * Date: 08/20/2013
 * [current_month_project_ahc Based on realtime project user & array of projecs, get their's ahc count]
 * @param  [type] $project      [description]
 * @param  [type] $project_user [description]
 * @return [type]               [description]
 */
function current_month_project_ahc($project, $project_user) {
    $project_user_temp       = $project_user['USER_ORG'];
    $current_month_ahc       = array();
    $current_month_ahc_count = 0;
    foreach ($project as $pro_key => $pro_val) {
        if(!empty($project_user_temp[$pro_key]['LIST'])){
            foreach ($project_user_temp[$pro_key]['LIST'] as $put_key => $put_val) {
                $current_project_emp_list[$put_val['FK_USER_ID']] = $put_val;
            }
        }
    }
    if(isset($current_project_emp_list)){
        $current_month_ahc_count = count($current_project_emp_list);
        $current_month_ahc = array(
            'COUNT' => $current_month_ahc_count,
            'LIST'  => $current_project_emp_list
        );
    }else{
        $current_month_ahc = array(
            'COUNT' => 0,
            'LIST'  => array()
        );
    }
    
    return $current_month_ahc;
}

/**
 * Author: CC
 * Date: 2012-11-29
 * Last Modified: 08/07/2013
 * Author: CC
 * Purpose: Get all the ahc info of this year
 */
function all_op_ahc($table) {
    $all_op_ahc   = array();
    $prefix_table = strtolower($table);
    $qry_all_ahc  = "SELECT YEAR,MONTH,DAY,FK_".$table."_ID,AHC,DATA FROM daily_".$prefix_table."_ahc;";
    $rst_all_ahc  = $GLOBALS['db']->query($qry_all_ahc);
    $all_ahc_temp = $rst_all_ahc->fetch_all(MYSQLI_ASSOC);
    foreach ($all_ahc_temp as $aat_key => $aat_val) {
        $emp_list = json_decode($aat_val['DATA'], TRUE);
        if($emp_list !== null){
            foreach ($emp_list as $el_key => $el_val) {
                $all_op_ahc[$aat_val['YEAR']][$aat_val['MONTH']][$aat_val['DAY']][$aat_val['FK_'.$table.'_ID']]['LIST'][$el_val['FK_USER_ID']] = $el_val;
            }
        }
        $all_op_ahc[$aat_val['YEAR']][$aat_val['MONTH']][$aat_val['DAY']][$aat_val['FK_'.$table.'_ID']]['AHC'] = $aat_val['AHC'];
    }
    return $all_op_ahc;
}

/**
 * Date: 2012-12-10
 * Last Modified: 2012-12-12
 * Author:CC
 * Purpose: Get the monthly ahc info
 * Modified: 08/10/2013     CC Change function from monthly_org_ahc to month_op_ahc
 */
function monthly_op_ahc($all_org_ahc, $ops, $year, $month) {
    $op_month_ahc = array();
    $last_day_of_month = date("t", strtotime($year . "-" . $month));
    foreach ($ops as $ops_key => $ops_val) {
        if (isset($all_org_ahc[$year][$month][$last_day_of_month][$ops_val['ID']]['LIST'])) {
            foreach ($all_org_ahc[$year][$month][$last_day_of_month][$ops_val['ID']]['LIST'] as $aoa_key => $aoa_val) {
                $op_month_ahc_temp[$ops_val['ID']][$aoa_val['FK_USER_ID']] = $aoa_val;
            }
        } else {
            $op_month_ahc_temp[$ops_key][$ops_val['ID']] = array();
        }
    }

    foreach ($op_month_ahc_temp as $omat_key => $omat_val) {
        foreach ($omat_val as $ov_key => $ov_val) {
            /**
             * This if is used for  a org that had no data in the month
             */
            if (!empty($ov_val)) {
                $op_month_ahc[$ov_val['FK_USER_ID']] = $ov_val;
            }
        }
    }
    if (!isset($op_month_ahc)) {
        $op_month_ahc = array();
    }
    return $op_month_ahc;
}

/**
 * Date: 2012-12-12
 * Author: CC
 * Purpose: Get each org's bhc depend on $all_op_bhc and $orgs
 * Modified: 08/10/2013     CC Changed function from get_orgs_month_bhc to monthly_op_bhc
 * Modified: 08/20/2013     CC Reduce one level of the $ops, remove the father id 
 */
function monthly_op_bhc($all_op_bhc, $ops, $year, $month, $day) {
    $op_month_bhc = array();
    foreach ($ops as $dsco_key => $dsco_val) {
        $op_month_bhc[$dsco_key] = isset($all_op_bhc[$dsco_val['ID']][$year][$month][$day]) ? $all_op_bhc[$dsco_val['ID']][$year][$month][$day] : 0;
    }
    return $op_month_bhc;
}

/**
 * @Author CC
 * @Date 2013-03-12
 * [ds_org_ahc get org ahc with date scope]
 * @param  [string] $org_string [org string]
 * @param  [date] $date_from  [date from]
 * @param  [date] $date_to    [date to]
 * @return [array]             [return the ahc info of a org between the given date scope]
 */
function ds_op_md($org_string,$date_from, $date_to, $table){
    $prefix_table   = strtolower($table);
    $date_from_temp = date("Y-m", strtotime($date_from));
    $date_to_temp   = date("Y-m", strtotime($date_to));
    $qry_md         = "SELECT YEAR,MONTH,DAY,WEEK,FK_".$table."_ID,AHC,DATA,WD,MD FROM daily_".$prefix_table."_ahc WHERE (CONCAT(YEAR,'-',MONTH) BETWEEN '$date_from_temp' AND '$date_to_temp') AND FK_".$table."_ID IN ($org_string);";
    $rst_md         = $GLOBALS['db']->query($qry_md);
    $md_temp        = $rst_md->fetch_all(MYSQLI_ASSOC);
    $org_md         = array();
    foreach ($md_temp as $mt_key => $mt_val) {
        $emp_list = json_decode($mt_val['DATA'], TRUE);
        if(!empty($emp_list)){
            foreach ($emp_list as $el_key => $el_val) {
                $org_md[$mt_val['YEAR']][$mt_val['MONTH']][$mt_val['DAY']][$mt_val['FK_'.$table.'_ID']]['list'][$el_val['FK_USER_ID']] = $el_val;
            }
        }
        $org_md[$mt_val['YEAR']][$mt_val['MONTH']][$mt_val['DAY']][$mt_val['FK_'.$table.'_ID']]['MD'] = $mt_val['MD'];
        $org_md[$mt_val['YEAR']][$mt_val['MONTH']][$mt_val['DAY']][$mt_val['FK_'.$table.'_ID']]['WD'] = $mt_val['WD'];
    }
    return $org_md;
}

/**
 * Author: CC
 * Date: 09/13/2013
 * [ds_op_md_bhc calc man-day based on bhc for customer]
 * @param  [type] $org_string [description]
 * @param  [type] $date_from  [description]
 * @param  [type] $date_to    [description]
 * @param  [type] $table      [description]
 * @return [type]             [description]
 */
function ds_op_md_bhc($org_string,$date_from, $date_to, $table){
    $prefix_table   = strtolower($table);
    $date_from_temp = date("Y-m", strtotime($date_from));
    $date_to_temp   = date("Y-m", strtotime($date_to));
    $org_md_bhc     = array();
    $qry_md         = "SELECT YEAR,MONTH,DAY,WEEK,FK_".$table."_ID,BHC,DATA,WD FROM daily_".$prefix_table."_bhc WHERE (CONCAT(YEAR,'-',MONTH) BETWEEN '$date_from_temp' AND '$date_to_temp') AND FK_".$table."_ID IN ($org_string);";
    $rst_md         = $GLOBALS['db']->query($qry_md);
    $md_temp        = $rst_md->fetch_all(MYSQLI_ASSOC);
    foreach ($md_temp as $mt_key => $mt_val) {
        $emp_list = json_decode($mt_val['DATA'], TRUE);
        $org_md_bhc[$mt_val['YEAR']][$mt_val['MONTH']][$mt_val['DAY']][$mt_val['FK_'.$table.'_ID']]['MD'] = $mt_val['WD']*$mt_val['BHC'];
        $org_md_bhc[$mt_val['YEAR']][$mt_val['MONTH']][$mt_val['DAY']][$mt_val['FK_'.$table.'_ID']]['WD'] = $mt_val['WD'];
    }
    return $org_md_bhc;
}

/**
 * Data: 2012-12-03
 * Author: CC
 * Purpose: Query all org resign info, and return the array
 */
function org_resign_info($sub_org_string) {
    $qry_resign = "SELECT FK_USER_ID,YEAR,MONTH,DAY,LAST_ORG,REASON,WTG,LEVEL,USER_NAME FROM resign_info,user_info WHERE USER_ID=FK_USER_ID AND LAST_ORG IN ($sub_org_string);";
    $rst_resign = $GLOBALS['db']->query($qry_resign);
    $resign_info = $rst_resign->fetch_all(MYSQLI_ASSOC);
    return $resign_info;
}

/**
 * Data: 2013-05-08
 * Author: CC
 * Purpose: Only for external dahboard temporarily
 * Different with the above, data are selected in a seperated table
 * Query all org resign info, and return the array
 */
function org_resign_info_external($sub_org_string) {
    $qry_resign = "SELECT FK_USER_ID,YEAR,MONTH,DAY,LAST_ORG,REASON,WTG,LEVEL,USER_NAME FROM resign_info_external,user_info WHERE USER_ID=FK_USER_ID AND LAST_ORG IN ($sub_org_string);";
    $rst_resign = $GLOBALS['db']->query($qry_resign);
    $resign_info = $rst_resign->fetch_all(MYSQLI_ASSOC);
    return $resign_info;
}

    /**
     * Date: 2012-11-30
     * Author: CC
     * Purpose: Calculate month enroll count of orgs based the DB table: 'user_org_group'
     */

    function monthly_enroll_count($sub_org_string, $start, $end, $table) {
        $prefix_table     = strtolower($table);
        $enroll_list      = array();
        foreach ($GLOBALS['VALID_GROUP'] as $vg_key => $vg_val) {
            $array_valid_group[] = "'" . $vg_key . "'";
        }
        $qry_org_user  = "SELECT ug.FK_USER_ID,ug.FK_".$prefix_table."_ID,i.NAME,ug.FK_GROUP_ID,gi.NAME,gi.TYPE FROM ".$prefix_table."_group_info AS gi, user_".$prefix_table."_group AS ug, ".$prefix_table."_info AS i WHERE ug.FK_".$prefix_table."_ID=i.ID AND ug.FK_GROUP_ID=gi.ID AND gi.TYPE IN (".implode(',', $array_valid_group).") AND ug.FK_".$prefix_table."_ID IN ($sub_org_string);";
        $rst_org_user  = $GLOBALS['db']->query($qry_org_user);
        $org_user_temp = $rst_org_user->fetch_all(MYSQLI_ASSOC);
        foreach ($org_user_temp as $out_key => $out_val) {
            $org_user[$out_val['FK_USER_ID']] = $out_val;
        }

        $all_new_enroll = new_enroll($start, $end);
        $enroll_count = 0;
        foreach ($all_new_enroll as $ale_key => $ale_val) {
            if (isset($org_user[$ale_val['USER_ID']])) {
                $enroll_list[$ale_val['USER_ID']] = $ale_val;
                $enroll_list[$ale_val['USER_ID']]['TARGET_ORG'] = $org_user[$ale_val['USER_ID']]['NAME'];
                $enroll_count++;
            }
        }
        $enroll = array(
            'count' => $enroll_count,
            'list'  => $enroll_list
        );
        return $enroll;
    }

/**
 * Date: 2012-11-30
 * Author: CC
 * Purpose: Query all new entry user from user info
 */
function new_enroll($month_head, $end) {
    $qry_new_enroll = "SELECT USER_ID,USER_NAME,HR_ID,EMPLOYEE_START FROM user_info WHERE EMPLOYEE_START>'$month_head' AND (EMPLOYEE_END>='$end' OR EMPLOYEE_END IS NULL);";
    $rst_new_enroll = $GLOBALS['db']->query($qry_new_enroll);
    $new_enroll = $rst_new_enroll->fetch_all(MYSQLI_ASSOC);
    return $new_enroll;
}

/*
 * Date: 2012-12-05
 * Author: CC
 * Purpose: Query all sub orgs including itself.
 */
function all_childs_orgs_ws($oid, $table) {
    $orgs    = active_org();
    $sub_org = sub_org($oid, $table);
    foreach ($sub_org as $so_key => $so_val) {
        $sub_orgs = array();
        $sub_org_list = query_subOrg($so_val['ID'], $so_val['ID'], $orgs, $sub_orgs, $table);
        foreach ($sub_org_list as $sol_key => $sol_val) {
            foreach ($sol_val as $sv_key => $sv_val) {
                $all_sub_orgs[] = $sv_val;
            }
        }
    }
    $all_sub_orgs[] = $oid;
    return $all_sub_orgs;
}

/**
 * Author: CC
 * Date: 012/10/2012
 * [monthly_op_ahc_list Query all org ahc list info of this month]
 * @param  [type] $year  [description]
 * @param  [type] $month [description]
 * @param  [type] $day   [description]
 * @param  [type] $table [description]
 * @return [type]        [description]
 */
function monthly_op_ahc_list($year, $month, $day, $table) {
    $prefix_table           = strtolower($table);
    $monthly_op_ahc         = array();
    $monthly_op_member_list = array();
    $qry_monthly_ahc        = "SELECT FK_".$table."_ID,YEAR,MONTH,WEEK,DAY,AHC,DATA,WD,MD FROM daily_".$prefix_table."_ahc WHERE YEAR='$year'AND MONTH='$month' AND DAY='$day' ORDER BY DAY ASC;";
    $rst_monthly_ahc        = $GLOBALS['db']->query($qry_monthly_ahc);
    $monthly_op_ahc_temp    = $rst_monthly_ahc->fetch_all(MYSQLI_ASSOC);

    foreach ($monthly_op_ahc_temp as $moat_key => $moat_val) {
        $member_list_temp = json_decode($moat_val['DATA'], TRUE);
        if($member_list_temp !== null){
            foreach ($member_list_temp as $mlt_key => $mlt_val) {
                $monthly_op_member_list[$moat_val['FK_'.$table.'_ID']][$mlt_val['FK_USER_ID']] = $mlt_val['FK_USER_ID'];
            }
        }
        $monthly_op_ahc[$moat_val['FK_'.$table.'_ID']] = $moat_val['AHC'];
    }
    $monthly_op_ahc_list = array(
        'AHC_LIST' => $monthly_op_member_list,
        'AHC'      => $monthly_op_ahc
    );
    return $monthly_op_ahc_list;
}

/**
 * Date:2012-12-07
 * Author: CC
 * Purpose: Calculate each org's cost(By 2012-12-07, the cost only includs asset depreciation)
 * This wiil cost three main indexes: Total deprecation, depreciation per person and depreciation end this month
 */
function calculate_org_cost($year, $month, $day, $today, $valid_asset_type, $table) {
    $all_asset     = array();
    $all_asset_end = array();
    /**
     * 2. Query all org ahc info of this month
     */
    $monthly_org_ahc_list    = monthly_op_ahc_list($year, $month, $day, $table);
    $monthly_org_member_list = $monthly_org_ahc_list['AHC_LIST'];
    $monthly_org_ahc         = $monthly_org_ahc_list['AHC'];

    $valid_asset_type_temp = NULL;
    foreach ($valid_asset_type as $vg_key => $vg_val) {
        $valid_asset_type_temp = $valid_asset_type_temp . "'" . $vg_val . "',";
    }
    $valid_asset_type_string = substr($valid_asset_type_temp, 0, -1);
    /**
     * 3. Calculate each org member's asset
     * Assets will be calculated if match the following conditions:
     *      1. PC or Monitor or Notebook or Server (The type info will be included in the conatant $valid_asset_type)
     *      2. MONTHLY_DEPRECIATION is not NULL and MONTHLY_DEPRECIATION is not '0'
     */
    $qry_all_asset      = "SELECT ASSET_NO,ASSET_TYPE,CHECKINTIME,RETURNTIME,DEPRECIATION_END,PRICE,MONTHLY_DEPRECIATION,USER_ID FROM asset_info WHERE (CHECKINTIME <= '$today' AND DEPRECIATION_END >='$today') AND ASSET_TYPE IN ($valid_asset_type_string) AND (MONTHLY_DEPRECIATION IS NOT NULL AND MONTHLY_DEPRECIATION <> '0')";
    $rst_all_asset      = $GLOBALS['db']->query($qry_all_asset);
    $all_asset_temp     = $rst_all_asset->fetch_all(MYSQLI_ASSOC);

    $month_head         = $year . "-" . $month . "-01";
    $month_end          = $year . "-" . $month . "-31";

    $qry_all_asset_end  = "SELECT ASSET_NO,ASSET_TYPE,CHECKINTIME,RETURNTIME,DEPRECIATION_END,PRICE,MONTHLY_DEPRECIATION,USER_ID FROM asset_info WHERE (DEPRECIATION_END BETWEEN '$month_head' AND '$month_end') AND ASSET_TYPE IN ($valid_asset_type_string) AND (MONTHLY_DEPRECIATION IS NOT NULL AND MONTHLY_DEPRECIATION <> '0')";
    $rst_all_asset_end  = $GLOBALS['db']->query($qry_all_asset_end);
    $all_asset_end_temp = $rst_all_asset_end->fetch_all(MYSQLI_ASSOC);

    foreach ($all_asset_temp as $aat_key => $aat_val) {
        $all_asset[$aat_val['USER_ID']][] = $aat_val;
    }

    foreach ($all_asset_end_temp as $aaet_key => $aaet_val) {
        $all_asset_end[$aaet_val['USER_ID']][] = $aaet_val;
    }
    $monthly_ahc_asset = array();  
    foreach ($monthly_org_member_list as $moml_key => $moml_val) {
        foreach ($moml_val as $mvv_key => $mvv_val) {
            if (isset($all_asset[$mvv_val])) {
                foreach ($all_asset[$mvv_val] as $aa_key => $aa_val) {
                    $monthly_ahc_asset[$moml_key]['total'][] = $aa_val;
                }
            }
            if (isset($all_asset_end[$mvv_val])) {
                foreach ($all_asset_end[$mvv_val] as $aae_key => $aae_val) {
                    $monthly_ahc_asset[$moml_key]['month_end'][] = $aae_val;
                }
            }
        }
    }

    $monthly_org_cost = array();
    foreach ($monthly_ahc_asset as $maa_key => $maa_val) {
        $cost_total = 0;
        $end_total = 0;
        $cost_per_person = 0;
        if (isset($maa_val['total'])) {
            foreach ($maa_val['total'] as $mvt_key => $mvt_val) {
                $cost_total += $mvt_val['MONTHLY_DEPRECIATION'];
            }
        } else {
            $cost_total = 0;
        }

        if (isset($maa_val['month_end'])) {
            foreach ($maa_val['month_end'] as $mve_key => $mve_val) {
                $end_total += $mve_val['MONTHLY_DEPRECIATION'];
            }
        } else {
            $end_total = 0;
        }
        $depreciation_by_person = round($cost_total / $monthly_org_ahc[$maa_key], 2);
        $monthly_org_cost[$maa_key] = array(
            'depreciation_total'     => $cost_total,
            'depreciation_by_person' => $depreciation_by_person,
            'month_end'              => $end_total
        );
    }
    $org_cost_ahc = array(
        'AHC_LIST' => $monthly_org_member_list,
        'COST'     => $monthly_org_cost
    );
    
    return $org_cost_ahc;
}

    /*
     * Date: 2012-12-10
     * Author: CC
     * Purpose: split user by their employee type and level
     */

    function org_member_level_type($ahc_list) {
        $qry_user_info = "SELECT USER_NAME,USER_ID,EMPLOYEE_START,INTERN_START,LEVEL,EMPLOYEE_END,TYPE,INTERN_END FROM user_info;";
        $rst_user_info = $GLOBALS['db']->query($qry_user_info);
        $user_info_temp = $rst_user_info->fetch_all(MYSQLI_ASSOC);
        foreach ($user_info_temp as $uit_key => $uit_val) {
            $user_info[$uit_val['USER_ID']] = $uit_val;
        }
        //dump($user_info);
        $org_member_type = array();
        $org_member_level = array();
        foreach ($ahc_list as $al_key => $al_val) {
            foreach ($al_val as $av_key => $av_val) {
                //For employee type
                switch ($user_info[$av_key]['TYPE']) {
                    case 0:
                        $org_member_type[$al_key]['INTERN'][] = $user_info[$av_key];
                        break;
                    case 1:
                        $org_member_type[$al_key]['FTE'][] = $user_info[$av_key];
                        break;
                    case 2:
                        $org_member_type[$al_key]['BORROW'][] = $user_info[$av_key];
                        break;
                    case 3:
                        $org_member_type[$al_key]['VENDOR'][] = $user_info[$av_key];
                        break;
                    default :
                        $org_member_type[$al_key]['UNDEFINED'][] = $user_info[$av_key];
                        break;
                }
                //For level
                switch ($user_info[$av_key]['LEVEL']) {
                    case NULL:
                        $org_member_level[$al_key]['UNDEFINED'][] = $user_info[$av_key];
                        break;
                    default :
                        $org_member_level[$al_key][$user_info[$av_key]['LEVEL']][] = $user_info[$av_key];
                        break;
                }
            }
        }
        $org_member_level_type = array(
            'level' => $org_member_level,
            'type' => $org_member_type
        );
        return $org_member_level_type;
    }

    /**
     * [org_member_customer_account Query user info associated with their customer account]
     * @param  [array] $father_org [description]
     * @param  [array] $child_orgs [description]
     * @param  [array] $ahc_list   [description]
     * @return [array]             [description]
     * @date   2013-04-19
     */
    function org_member_customer_account($father_org, $child_orgs,$ahc_list) {
        $qry_user_info = "SELECT USER_NAME,NICK_NAME,USER_ID,EMPLOYEE_START,INTERN_START,LEVEL,EMPLOYEE_END,EMPLOYEE_TYPE,INTERN_END,`ACCOUNT`,`OWNER` FROM user_info AS ui LEFT JOIN (`customer_account` AS ca, customer_info AS ci) ON (ca.FK_CUSTOMER_ID=ci.ID AND ui.USER_ID=ca.MAINTAINER);";
        $rst_user_info = $GLOBALS['db']->query($qry_user_info);
        $user_info_temp = $rst_user_info->fetch_all(MYSQLI_ASSOC);

        foreach ($user_info_temp as $uit_key => $uit_val) {
            $user_info[$uit_val['USER_ID']] = $uit_val;
        }

        $org_member_type = array();
        foreach ($ahc_list as $al_key => $al_val) {
            foreach ($al_val as $av_key => $av_val) {
                if(isset($user_info[$av_key]['ACCOUNT'])){
                    $org_member_customer_account[$al_key]['BILLABLE'][] = $user_info[$av_key];
                }else{
                    $org_member_customer_account[$al_key]['BACKUP'][] = $user_info[$av_key];
                }
            }
        }
        return $org_member_customer_account;
    }

    /**
     * [loop_org_customer_account description]
     * @param  [array] $father_org       [description]
     * @param  [array] $child_orgs       [description]
     * @param  [array] $org_member_level [description]
     * @param  [array] $org_member_type  [description]
     * @return [array]                   [description]
     * @date   2013-04-19
     */
    function loop_org_customer_account($father_org, $child_orgs, $org_member_customer_account) {
        foreach ($child_orgs as $co_key => $co_val) {
            /**
            * For some orgs without employees [Empty Org]
            */
            $org_member_customer_account_t[$father_org][] = isset($org_member_customer_account[$co_val])?$org_member_customer_account[$co_val]:array();
        }
        //dump($org_member_customer_account_t);
        foreach ($org_member_customer_account_t as $omltt_key => $omltt_val) {
            //$omltt_key is the father org id
            foreach ($omltt_val as $oovv_key => $oovv_val) {
                //$ovl_key is 'level'
                foreach ($oovv_val as $ovl_key => $ovl_val) {
                    foreach ($ovl_val as $ovvll_key => $ovll_val) {
                            $org_member_level_type[$ovl_key][$ovll_val['USER_ID']] = $ovll_val;
                    }
                }
            }
        }
        /**
         * For some orgs without employees [Empty Org]
         */
        if(!isset($org_member_level_type)){
            $org_member_level_type = array();
        }

        return $org_member_level_type;
    }

/**
 * Date: 2012-12-10
 * Author: CC
 * Purpose:Loop each target org's member by type and level
 */
function loop_org_type_level($father_org, $child_orgs, $org_member_level, $org_member_type) {
    foreach ($child_orgs as $co_key => $co_val) {
        /**
        * For some orgs without employees [Empty Org]
        */
        if(isset($org_member_level[$co_val['ID']])){
            $org_member_level_type_t[$father_org]['level'][] = $org_member_level[$co_val['ID']];
            $org_member_level_type_t[$father_org]['type'][] = $org_member_type[$co_val['ID']];
        }else{
            $org_member_level_type_t[$father_org]['level'][] = array();
            $org_member_level_type_t[$father_org]['type'][] = array();
        }
    }

    foreach ($org_member_level_type_t as $omltt_key => $omltt_val) {
        //For level
        foreach ($omltt_val as $oovv_key => $oovv_val) {
            //$ovl_key is 'level'
            foreach ($oovv_val as $ovl_key => $ovl_val) {
                foreach ($ovl_val as $ovvll_key => $ovll_val) {
                    //$ovvllv_key is the level info
                    foreach ($ovll_val as $ovvllv_key => $ovvllv_val) {
                        $org_member_level_type[$oovv_key][$ovvll_key][$ovvllv_val['USER_ID']] = $ovvllv_val;
                    }
                }
            }
        }
    }
    
    /**
     * For some orgs without employees [Empty Org]
     */
    if(!isset($org_member_level_type)){
        $org_member_level_type = array(
            'level' => array(),
            'type'  => array()
            );
    }
    return $org_member_level_type;
}

    /*
     * Author: CC
     * Date: 2013-01-20
     * Description: arrange the array of all resign info, split them by employee type: intern, fte
     * Get the year and month that with resigned employee
     */

    function resign_by_emp_type($all_org_resign_info) {
        $attrition_data_date = array();
        $attrition_year = array();
        $attrition_year_month = array();
        foreach ($all_org_resign_info as $aori_key => $aori_val) {
            $attrition_year[$aori_key] = $aori_key;
            foreach ($aori_val as $av_key => $av_val) {
                $attrition_year_month[$aori_key][$av_key] = $av_key;
                $m = 0;
                $n = 0;
                foreach ($av_val as $aav_key => $aav_val) {
                    $employee_type_temp = str_split($aav_val['LEVEL']);
                    if ($employee_type_temp[0] == "L") {
                        $m++;
                        $monthly_org_resign_temp[$aori_key][$av_key]['FTE'] = $m;
                    } else {
                        $n++;
                        $monthly_org_resign_temp[$aori_key][$av_key]['INTERN'] = $n;
                    }
                }
                $monthly_org_resign_temp[$aori_key][$av_key]['TOTAL'] = count($av_val);
            }
            /*
             * Sort this array's key  from lower to higher
             */
            ksort($attrition_year_month[$aori_key]);
        }
        $attrition_data_date = array(
            "monthly_org_resign" => $monthly_org_resign_temp,
            'attrition_year' => $attrition_year_month,
            'attrition_year_month' => $attrition_year_month
        );
        return $attrition_data_date;
    }

    /**
     * Author: CC
     * Date: 2013-01-21
     * Description: Create a new array using the array $monthly_org_resign_info 
     * Original:
     * Array
     * (
     *     [2012] => Array
     *         (
     *             [5(month)] => Array
     *                 (
     *                     [INTERN] => 1
     *                     [FTE] => 2
     *                     [TOTAL] => 3
     *                 )
     * 
     *             [8] => Array
     *                 (
     *                     [FTE] => 2
     *                     [TOTAL] => 2
     *                 )
     *      )
     * )
     * Target:
     * Count by Type:
     * Array
     * (
     *    [2013] => Array
     *        (
     *            [Jan] => Array
     *                (
     *                    [TOTAL] => 0
     *                    [INTERN] => 0
     *                    [FTE] => 0
     *                )
     *        )
     * )
     * Attrition Rate:
     * Array
     * (
     *    [2013] => Array
     *        (
     *            [Jan] => 0
     *        )
     *
     * )
     */

    function year_month_org_resign_type_rate($monthly_org_resign, $all_org_ahc, $all_org_bhc, $all_childs_orgs, $year, $month) {
        $monthly_org_resign_target = array();
        $monthly_attrition_rate = array();
        $monthly_ahc = array();
        $monthly_bhc = array();
        for ($i = 1; $i <= $month; $i++) {
            /*
             * Change the month from '1' to '02'
             */
            $month_two_digits = date("m", mktime(0, 0, 0, $i, 10));
            //For  monthly attrition
            $month_attrition = date("M", mktime(0, 0, 0, $i, 10));
            $last_day_of_month = date("t", strtotime($year . "-" . $month_two_digits));

            $monthly_org_resign_target[$year][$month_attrition]['TOTAL'] = isset($monthly_org_resign[$year][$month_two_digits]['TOTAL']) ? $monthly_org_resign[$year][$month_two_digits]['TOTAL'] : 0;
            $monthly_org_resign_target[$year][$month_attrition]['INTERN'] = isset($monthly_org_resign[$year][$month_two_digits]['INTERN']) ? $monthly_org_resign[$year][$month_two_digits]['INTERN'] : 0;
            $monthly_org_resign_target[$year][$month_attrition]['FTE'] = isset($monthly_org_resign[$year][$month_two_digits]['FTE']) ? $monthly_org_resign[$year][$month_two_digits]['FTE'] : 0;
            //For monthly ahc
            $month_ahc_temp = monthly_op_ahc($all_org_ahc, $all_childs_orgs, $year, $month_two_digits);
            $monthly_ahc[$year][$month_two_digits] = count($month_ahc_temp);

            //For monthly bhc
            $month_bhc_temp = monthly_op_bhc($all_org_bhc, $all_childs_orgs, $year, $month_two_digits,$last_day_of_month);
            $monthly_bhc[$year][$month_two_digits] = array_sum($month_bhc_temp);

            //For monthly attrition rate
            if (($monthly_ahc[$year][$month_two_digits]) != 0) {
                $monthly_attrition_rate_temp = round($monthly_org_resign_target[$year][$month_attrition]['TOTAL'] / $monthly_ahc[$year][$month_two_digits], 2);
            } else {
                $monthly_attrition_rate_temp = 0;
            }

            $monthly_attrition_rate[$year][$month_attrition] = $monthly_attrition_rate_temp * 100;
        }
        $year_month_org_attrition_info = array(
            'resign' => $monthly_org_resign_target,
            'attriton_rate' => $monthly_attrition_rate,
            'ahc' => $monthly_ahc,
            'bhc' => $monthly_bhc);
        return $year_month_org_attrition_info;
    }

/**
 * Author: CC
 * Modified:
 * [monthly_org_attrition Depend on the  data select by the date scope, split them into month seperatly]
 * @param  [type] $monthly_org_resign [monthly resign info]
 * @param  [type] $all_org_ahc        [all org ahc]
 * @param  [type] $all_childs_orgs    [child orgs]
 * @return [type]                     [return array]
 */
function monthly_org_attrition($monthly_org_resign, $all_org_ahc, $all_childs_orgs) {
    $monthly_org_resign_target = array();
    $monthly_attrition_rate = array();
    $monthly_ahc = array();
    $count = 0;
    foreach($monthly_org_resign as $mor_key => $mor_val){
        foreach($mor_val as $mv_key => $mv_val){
            //Format the month from '05'to 'May'
            $month_name = date("M", mktime(0, 0, 0, $mv_key, 10));
            $monthly_org_resign_target[$mor_key][$month_name]['TOTAL']  = isset($mv_val['TOTAL']) ? $mv_val['TOTAL'] : 0;
            $monthly_org_resign_target[$mor_key][$month_name]['INTERN'] = isset($mv_val['INTERN']) ? $mv_val['INTERN'] : 0;
            $monthly_org_resign_target[$mor_key][$month_name]['FTE']    = isset($mv_val['FTE']) ? $mv_val['FTE'] : 0;
            $count += $mv_val['TOTAL'];
            //For monthly ahc
            $month_ahc_temp = monthly_op_ahc($all_org_ahc, $all_childs_orgs, $mor_key, $mv_key, "ORG");
            $monthly_ahc[$mor_key][$mv_key] = count($month_ahc_temp);
            if (($monthly_ahc[$mor_key][$mv_key]) != 0) {
                $monthly_attrition_rate_temp = round($monthly_org_resign_target[$mor_key][$month_name]['TOTAL'] / $monthly_ahc[$mor_key][$mv_key], 2);
            } else {
                $monthly_attrition_rate_temp = 0;
            }
    
            $monthly_attrition_rate[$mor_key][$month_name] = $monthly_attrition_rate_temp * 100;
        }
        ksort($monthly_org_resign_target[$mor_key]);
    }
    ksort($monthly_org_resign_target);
    $year_month_org_attrition_info = array(
        'resign'        => $monthly_org_resign_target,
        'attriton_rate' => $monthly_attrition_rate,
        'ahc'           => $monthly_ahc,
        'count'         => $count
        );
    return $year_month_org_attrition_info;
}

    /**
     * Author: CC
     * Date: 2013-01-20
     * Description: re arrange this array, set the type as array key
     * Original:
     * Array
     * (
     *    [2012] => Array
     *       (
     *           [Jan] => Array
     *               (
     *                   [TOTAL] => 1
     *                  [INTERN] => 0
     *                  [FTE] => 1
     *               )
     *
     *            [Feb] => Array
     *                (
     *                    [TOTAL] => 1
     *                    [INTERN] => 0
     *                    [FTE] => 1
     *                )
     *
     * Target:
     * Array
     * (
     *    [2013] => Array
     *        (
     *            [INTERN] => Array
     *                (
     *                    [Jan] => 0
     *                   [Feb] => 0
     *                )
     *            [FTE] => Array
     *                (
     *                    [Jan] => 0
     *                   [Feb] => 0
     *                )
     *        )
     * )
     */

    function year_month_attrition_type($monthly_org_resign) {
        $monthly_org_resign_total = array();
        $monthly_org_resign_type = array();
        foreach ($monthly_org_resign as $mor_key => $mor_val) {
            $monthly_org_resign_total[$mor_key] = $mor_val['TOTAL'];
            $monthly_org_resign_type['INTERN'][$mor_key] = $mor_val['INTERN'];
            $monthly_org_resign_type['FTE'][$mor_key] = $mor_val['FTE'];
        }
        $monthly_org_resign = array(
            'total' => $monthly_org_resign_total,
            'type' => $monthly_org_resign_type
        );
        return $monthly_org_resign;
    }

function monthly_attrition_by_type($monthly_org_resign) {
    $monthly_org_resign_total = array();
    $monthly_org_resign_type = array();
    foreach ($monthly_org_resign as $mor_key => $mor_val) {
        foreach($mor_val as $mv_key => $mv_val){
            $monthly_org_resign_total[$mor_key][$mv_key] = $mv_val['TOTAL'];
            $monthly_org_resign_type['INTERN'][$mor_key][$mv_key] = $mv_val['INTERN'];
            $monthly_org_resign_type['FTE'][$mor_key][$mv_key] = $mv_val['FTE'];
        }
    }
    $monthly_org_resign = array(
        'total' => $monthly_org_resign_total,
        'type' => $monthly_org_resign_type
    );
    return $monthly_org_resign;
}
?>
