<?php
require_once __DIR__ . '/../../lib/inc/constant_bug.php';
require_once __DIR__ . '/../../lib/function/org_mgt.php';
require_once __DIR__ . '/../../mysql.php';

/*
 * Date:2013-01-24
 * Author: CC
 * Description:
 * Query all bugs in DB table: bug_info
 */

function all_bugs() {
    $query_all = "SELECT BUG_ID FROM bug_library WHERE BUG_SYSTEM <> '4' ORDER BY BUG_SYSTEM,BUG_ID;";
    $result_all = $GLOBALS['db']->query($query_all);
    $all_bug_temp = $result_all->fetch_all(MYSQLI_ASSOC);
    foreach ($all_bug_temp as $abt_key => $abt_val) {
        $all_bug[$abt_val['BUG_ID']] = $abt_val['BUG_ID'];
    }
    return $all_bug;
}

/**
 * 2013-10-17
 * SY
 * query bugs not in project which its' id is $oid
 */
function all_bugs_by_oid($oid) {
    $query_all = "SELECT BUG_ID FROM bug_library WHERE BUG_SYSTEM <> '4' and FK_PROJECT_ID not in ('$oid') ORDER BY BUG_SYSTEM,BUG_ID;";
    $result_all = $GLOBALS['db']->query($query_all);
    $all_bug_temp = $result_all->fetch_all(MYSQLI_ASSOC);
    foreach ($all_bug_temp as $abt_key => $abt_val) {
        $all_bug[$abt_val['BUG_ID']] = $abt_val['BUG_ID'];
    }
    return $all_bug;
}

/**
 * 2013-10-17
 * SY
 *  Query bugs by $oid
 */
function porject_bugs($oid) {
    $query_all = "SELECT BUG_ID FROM bug_library WHERE BUG_SYSTEM <> '4'and FK_PROJECT_ID in('$oid') ORDER BY BUG_SYSTEM,BUG_ID;";
    $result_all = $GLOBALS['db']->query($query_all);
    $all_bug_temp = $result_all->fetch_all(MYSQLI_ASSOC);
    foreach ($all_bug_temp as $abt_key => $abt_val) {
        $all_bug[$abt_val['BUG_ID']] = $abt_val['BUG_ID'];
    }
    return $all_bug;
}

/**
 * Author:CC
 * Last Modified: 08/09/2013
 * [project_bug_withdate List all bugs of the specific project id with a date scope]
 * @param  [type] $project_string [description]
 * @param  [type] $date_from      [description]
 * @param  [type] $date_to        [description]
 * @return [type]                 [description]
 */
function project_bug_withdate($projects, $date_from, $date_to) {
    $string_projects = op_string($projects);
    $qry_project_bug = "SELECT bl.ID,BUG_SYSTEM,BUG_ID,BUILD_ID,bl.FK_PROJECT_ID,bl.ITERATION_ID,ii.ITERATION_NAME,REPORTER,USER_NAME,bl.ACCOUNT,TASK_TYPE,BUG_CATEGORY,BUG_TYPE,FEATURE,RELATED_BUG,CAST(SUBMIT_DATE AS DATE) AS SUBMIT_DATE,LAST_MODIFIED,BUG_STATUS,SUB_STATUS,PACTERA_STATUS,EXECUTE_TIME,bl.COMMENTS,ca.`OWNER`,ui.NICK_NAME FROM iteration_info AS ii, bug_library AS bl,user_info AS ui LEFT JOIN (customer_account AS ca) ON (ui.USER_ID=ca.MAINTAINER) WHERE bl.FK_PROJECT_ID IN ($string_projects) AND (CAST(SUBMIT_DATE AS DATE) BETWEEN '$date_from' AND '$date_to') AND ii.ITERATION_ID = bl.ITERATION_ID AND bl.REPORTER=ui.USER_ID ORDER BY SUBMIT_DATE DESC, BUG_CATEGORY ASC,BUILD_ID DESC, BUG_ID DESC; ";
    $rst_project_bug = $GLOBALS['db']->query($qry_project_bug);
    $project_bug_withdate = $rst_project_bug->fetch_all(MYSQLI_ASSOC);
    if (empty($project_bug_withdate)) {
        $project_bug_withdate = array();
    }
    return $project_bug_withdate;
}

/*
 * check_bug_existence
 */

function check_bug_existence($bug_id) {
    $qry_check_bug_existence = "SELECT COUNT(id) COUNT FROM bug_library WHERE BUG_ID='$bug_id'";
    $rst_check_bug_existence = $GLOBALS['db']->query($qry_check_bug_existence);
    $row_check_bug_existence = $rst_check_bug_existence->fetch_assoc();
    return $row_check_bug_existence;
}

/*
 * Count the bug number of an org
 */

function count_bug($oid) {
    $query_count = "SELECT COUNT(ID) COUNT FROM bug_library WHERE FK_PROJECT_ID='$oid';";
    $result_count = $GLOBALS['db']->query($query_count);
    $row_count = $result_count->fetch_assoc();
    $record_count = $row_count['COUNT'];
    return $record_count;
}

/**
 * [bugs_of_project Query all the bugs of the specific projects]
 * @param  [type] $projects  [description]
 * @param  [type] $date_from [description]
 * @param  [type] $date_to   [description]
 * @return [type]            [description]
 */
// function bugs_of_project($projects, $date_from, $date_to) {
//     $string_projects = op_string($projects);
//     $qry_bug = "SELECT BUG_SYSTEM, BUG_ID, bl.FK_PROJECT_ID,ITERATION_NAME,ii.ITERATION_ID,BUILD_ID, ui.USER_NAME REPORTER_NAME, ACCOUNT, TASK_TYPE, BUG_CATEGORY,BUG_TYPE, FEATURE, RELATED_BUG, EXECUTE_TIME, SUBMIT_DATE, PACTERA_STATUS,bl.BUG_STATUS, bl.SUB_STATUS,bl.COMMENTS, bl.ID, REPORTER  FROM ((bug_library AS bl left join user_info AS ui ON bl. REPORTER=ui.USER_ID) LEFT JOIN iteration_info AS ii ON bl.ITERATION_ID=ii.ITERATION_ID) WHERE bl.FK_PROJECT_ID in ($string_projects) AND (CAST(SUBMIT_DATE AS DATE) BETWEEN '$date_from' AND '$date_to') ORDER BY SUBMIT_DATE DESC";
//     $qry_project_bug = "SELECT BUG_SYSTEM,BUG_ID,BUILD_ID,FK_PROJECT_ID,ITERATION_ID,REPORTER,USER_NAME,bl.ACCOUNT,TASK_TYPE,BUG_CATEGORY,BUG_TYPE,FEATURE,RELATED_BUG,SUBMIT_DATE,LAST_MODIFIED,BUG_STATUS,SUB_STATUS,PACTERA_STATUS,EXECUTE_TIME,ca.`OWNER`,ui.NICK_NAME FROM bug_library AS bl,user_info AS ui LEFT JOIN (customer_account AS ca) ON (ui.USER_ID=ca.MAINTAINER) WHERE FK_PROJECT_ID IN ($project_string) AND (CAST(SUBMIT_DATE AS DATE) BETWEEN '$date_from' AND '$date_to') AND bl.REPORTER=ui.USER_ID; ";
//     $result = $GLOBALS['db']->query($qry_bug);
//     $bug_info = $result->fetch_all(MYSQLI_ASSOC);

//     return $bug_info;
// }


/*
 *  query all the bugs of the specific org for external view
 */

function bugs_of_org_external($oid_string, $date_from, $date_to) {
    $qry_bug = "SELECT BUG_SYSTEM, BUG_ID, bl.FK_PROJECT_ID,ITERATION_NAME,ii.ITERATION_ID,BUILD_ID, ui.USER_NAME REPORTER_NAME,ui.NICK_NAME NICK_NAME, bl.ACCOUNT, TASK_TYPE, BUG_CATEGORY,BUG_TYPE, FEATURE, RELATED_BUG, EXECUTE_TIME, CAST(SUBMIT_DATE AS DATE) SUBMIT_DATE, PACTERA_STATUS,bl.BUG_STATUS, bl.SUB_STATUS,bl.COMMENTS, bl.ID, REPORTER,ca.`OWNER`,ca.ACCOUNT CITRITE_ACCOUNT FROM ((bug_library AS bl left join user_info AS ui ON bl. REPORTER=ui.USER_ID) LEFT JOIN iteration_info AS ii ON bl.ITERATION_ID=ii.ITERATION_ID) LEFT JOIN (customer_account AS ca) ON (ca.MAINTAINER = ui.USER_ID) WHERE bl.FK_PROJECT_ID in ($oid_string) AND (CAST(SUBMIT_DATE AS DATE) BETWEEN '$date_from' AND '$date_to') ORDER BY SUBMIT_DATE DESC";
    $result = $GLOBALS['db']->query($qry_bug);
    $bug_info_temp = $result->fetch_all(MYSQLI_ASSOC);

    $qry_bug_note = "SELECT bn.ISSUE_ID,bl.BUG_ID,bl.REPORTER,bn.OWNER,bl.FK_PROJECT_ID,CAST(bn.SUBMIT_DATE AS DATE) SUBMIT_DATE,bn.EXECUTE_TIME,ui.USER_NAME,ui.NICK_NAME NICK_NAME,ca.`OWNER`,ca.ACCOUNT CITRITE_ACCOUNT FROM (bug_library AS bl, user_info AS ui, bug_note AS bn) LEFT JOIN (customer_account AS ca) ON (ca.MAINTAINER = ui.USER_ID) WHERE bl.FK_PROJECT_ID in ($oid_string) AND (CAST(bn.SUBMIT_DATE AS DATE) BETWEEN '$date_from' AND '$date_to') AND bl.ID = bn.ISSUE_ID AND bn.`OWNER`=ui.USER_ID ORDER BY bn.SUBMIT_DATE DESC";
    $result_note = $GLOBALS['db']->query($qry_bug_note);
    $bug_info_note_temp = $result_note->fetch_all(MYSQLI_ASSOC);

    if(!empty($bug_info_note_temp)){
        foreach($bug_info_note_temp as $bint_key => $bint_Val){
            $bug_note[$bint_Val['FK_PROJECT_ID']][] = $bint_Val;
        }
    }

    if(!empty($bug_info_temp)){
        foreach($bug_info_temp as $bit_key => $bit_val){
            $bug_note[$bit_val['FK_PROJECT_ID']][] = $bit_val;
        }
    }

    if(empty($bug_note)){
        $bug_note =array();
    }

    return $bug_note;
}

/*
 * query the detailed bug info through the bug no
 */

function detailed_bug_info($bug_no) {
    $qry_bug_info = "SELECT bl.ID,BUG_SYSTEM,bl.EXPLORING, ii.ITERATION_ID ITERATION_ID,ii.ITERATION_NAME ITERATION_NAME,BUG_ID,BUILD_ID, USER_NAME, REPORTER, ACCOUNT, TASK_TYPE, BUG_CATEGORY,BUG_TYPE, FEATURE, 
RELATED_BUG, EXECUTE_TIME, SUBMIT_DATE, PACTERA_STATUS,bl.BUG_STATUS,SUB_STATUS, bl.COMMENTS,bl.FK_PROJECT_ID FROM bug_library AS bl ,user_info AS ui,iteration_info AS ii 
WHERE bl.REPORTER=ui.USER_ID AND bl.ITERATION_ID=ii.ITERATION_ID AND bl.ID='$bug_no';";
    $rst_bug_info = $GLOBALS['db']->query($qry_bug_info);
    $detailed_bug_info = $rst_bug_info->fetch_assoc();
    return $detailed_bug_info;
}

/*
 * query the detailed bug info through the bug no
 */

function detailed_bug_info_external($bug_no) {
    $qry_bug_info = "SELECT bl.ID,BUG_SYSTEM, ii.ITERATION_ID ITERATION_ID,ii.ITERATION_NAME ITERATION_NAME,BUG_ID,BUILD_ID, USER_NAME, REPORTER, bl.ACCOUNT, TASK_TYPE, BUG_CATEGORY,BUG_TYPE, FEATURE, RELATED_BUG, EXECUTE_TIME, SUBMIT_DATE, PACTERA_STATUS, bl.BUG_STATUS,SUB_STATUS, bl.COMMENTS, ca.OWNER 
FROM bug_library AS bl ,user_info AS ui,iteration_info AS ii, customer_account AS ca WHERE bl.REPORTER=ui.USER_ID AND bl.ITERATION_ID=ii.ITERATION_ID AND ca.MAINTAINER=bl.REPORTER AND bl.ID='$bug_no';";
    $rst_bug_info = $GLOBALS['db']->query($qry_bug_info);
    $detailed_bug_info = $rst_bug_info->fetch_assoc();
    return $detailed_bug_info;
}

/*
 * Search bug info through the bug id or keywords or reporter
 */

function search_bug_info($bug_keyword) {
    $qry_bug_info = "SELECT ID,BUG_SYSTEM, BUG_ID, bl.FK_PROJECT_ID,ITERATION_NAME,ii.ITERATION_ID,BUILD_ID, USER_NAME, REPORTER,ACCOUNT, TASK_TYPE, BUG_CATEGORY,BUG_TYPE, FEATURE, RELATED_BUG, EXECUTE_TIME, SUBMIT_DATE, PACTERA_STATUS,bl.BUG_STATUS, bl.SUB_STATUS,bl.COMMENTS, bl.ID, REPORTER  
FROM ((bug_library AS bl left join user_info AS ui ON bl. REPORTER=ui.USER_ID) LEFT JOIN iteration_info AS ii ON bl.ITERATION_ID=ii.ITERATION_ID) 
WHERE CONCAT(BUG_ID,USER_NAME,IFNULL(bl.COMMENTS,'')) LIKE '%$bug_keyword%' ORDER BY SUBMIT_DATE DESC";
    $rst_bug_info = $GLOBALS['db']->query($qry_bug_info);
    $bug_info_result = $rst_bug_info->fetch_all(MYSQLI_ASSOC);
    return $bug_info_result;
}

/*
 * Push bug info into table
 */
function push_bug_into_table($bug_info, $record_count, $oid, $su_id, $gtype, $permission, $required_perm, $optype) {
    ?>
    <div id="div_table_total"><h4 class="chinese">Total:<?php echo $record_count; ?></h4></div>
    <table class="table table-striped table-condensed table-hover">
        <thead>
            <tr>
                <th class='sortth' style="cursor:pointer" id='ID'>#</th>
                <th>No.</th>
                <th class='sortth' style="cursor:pointer" id='BUILD_ID'>Build</th>
                <th class='sortth' style="cursor:pointer" id='ITERATION_NAME'>Iteration</th>
                <th>Title</th>
                <th class='sortth' style="cursor:pointer" id='USER_NAME'>Reporter</th>
                <th class='sortth' style="cursor:pointer" id='BUG_CATEGORY'>Category</th>
                <th class='sortth' style="cursor:pointer" id='BUG_STATUS'>Status</th>
                <th class='sortth' style="cursor:pointer" id='SUB_STATUS'>Sub Status</th>
                <th class='sortth' style="cursor:pointer" id="SUBMIT_DATE">Date</th>
                <th></th>
            </tr>
        </thead>
        <?php
        $i = 0;
        foreach ($bug_info as $bi_key => $bi_val) {
            $short_comments = substr($bi_val['COMMENTS'], 0, 40);
            $short_iteration = substr($bi_val['ITERATION_NAME'], 0, 20);
            ?>
            <tr>
                <input type="hidden" id="id_<?php echo $i;?>" value="<?php echo $bi_val ['ID']; ?>">
                <input type="hidden" id="bug_system_<?php echo $i;?>" value="<?php echo $GLOBALS['BUG_SYSTEM'][$bi_val ['BUG_SYSTEM']][0]; ?>">
                <input type="hidden" id="bug_type_<?php echo $i;?>" value="<?php echo $GLOBALS['BUG_TYPE'][$bi_val ['BUG_TYPE']][0]; ?>">
                <input type="hidden" id="feature_<?php echo $i;?>" value="<?php echo $bi_val ['FEATURE']; ?>">
                <input type="hidden" id="related_bug_<?php echo $i;?>" value="<?php echo $bi_val ['RELATED_BUG']; ?>">
                <input type="hidden" id="execute_time_<?php echo $i;?>" value="<?php echo round($bi_val ['EXECUTE_TIME']/60,2); ?>">
                <input type="hidden" id="account_<?php echo $i;?>" value="<?php echo $bi_val ['ACCOUNT']; ?>">
                <input type="hidden" id="nick_name_<?php echo $i;?>" value="<?php echo $bi_val ['NICK_NAME']; ?>">
                <input type="hidden" id="task_type_<?php echo $i;?>" value="<?php echo $GLOBALS['TASK_TYPE'][$bi_val ['TASK_TYPE']][0]; ?>">
                <input type="hidden" id="last_modified_<?php echo $i;?>" value="<?php echo !empty($bi_val ['LAST_MODIFIED'])?date("m/d/y", strtotime($bi_val ['LAST_MODIFIED'])):null; ?>">
                <td><?php echo ($bi_key + 1); ?></td>
                <td class="bug_detail_info" id="<?php echo $i;?>" value="<?php echo $bi_val['BUG_ID'];?>"><a><?php echo $bi_val['BUG_ID']; ?></a></td>
                <td id="build_id_<?php echo $i;?>"><?php echo $bi_val ['BUILD_ID']; ?></td>
                <td id="iteration_name_<?php echo $i;?>" title="<?php echo $bi_val ['ITERATION_NAME']; ?>"><?php echo $short_iteration; ?></td>
                <td id="comments_<?php echo $i;?>" title="<?php echo htmlspecialchars($bi_val ['COMMENTS'], ENT_QUOTES); ?>">
                    <?php echo $short_comments; ?>
                </td>
                <td id="user_name_<?php echo $i;?>" class="chinese">
                    <?php echo ($bi_val['USER_NAME'] != null) ? $bi_val['USER_NAME'] : NULL; ?>
                </td>
                <td id="bug_category_<?php echo $i;?>">
                    <?php echo isset($GLOBALS['BUG_CATEGORY'][$bi_val['BUG_CATEGORY']][0]) ? $GLOBALS['BUG_CATEGORY'][$bi_val['BUG_CATEGORY']][0] : ""; ?>
                </td>
                <td id="bug_status_<?php echo $i;?>">
                    <?php echo isset($GLOBALS['BUG_STATUS'][$bi_val ['BUG_STATUS']][0]) ? $GLOBALS['BUG_STATUS'][$bi_val ['BUG_STATUS']][0] : "" ;?>
                </td>
                <td id="bug_sub_status_<?php echo $i;?>">
                    <?php echo isset($GLOBALS['BUG_SUB_STATUS'][$bi_val ['SUB_STATUS']][0]) ? $GLOBALS['BUG_SUB_STATUS'][$bi_val ['SUB_STATUS']][0] : "" ;?>
                </td>
                <td id="submit_date_<?php echo $i;?>"> 
                    <span class="span1"><?php echo date("m/d/y", strtotime($bi_val['SUBMIT_DATE'])); ?></span>
                </td>
                <?php
                $parm_string = $bi_val ['ID'];
                $parm = base64_encode($parm_string);
                ?>
                <?php
                /*
                 * For the bug's onwer or the owner's manager, they can modify all the bugs
                 */
                if ($bi_val['REPORTER'] == $_SESSION ['user_id'] || $permission >= $required_perm) {
                    ?>
                    <td> 
                        <a title='Update' href='../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&optype=<?php echo base64_encode($optype); ?>&url=<?php echo base64_encode("bug_mgt/update_bug.php"); ?>&gtype=<?php echo base64_encode($gtype); ?>&parm=<?php echo $parm; ?>&editable=<?php echo base64_encode("1"); ?>' ><img width='14' height='14' src='../../../lib/image/icons/edit.png'/></a>
                        <a title='Delete' onclick="return delete_bug()" href='../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&optype=<?php echo base64_encode($optype); ?>&url=<?php echo base64_encode("bug_mgt/delete_bug.php"); ?>&gtype=<?php echo base64_encode($gtype); ?>&parm=<?php echo $parm; ?>#tabs_ops' ><img width='16' height='16' src='../../../lib/image/icons/remove.png'/></a>
                    </td>    
                <?php }?>                
            </tr>

            <?php
            $i++;
        }
        ?>
    </table>
    <?php
}

function push_bug_into_table_subscribe($bug_info, $record_count, $user_type) {
    ?>
    <div id="div_table_total"><h4 class="chinese">Total:<?php echo $record_count; ?></h4></div>
    <table class="table table-striped table-condensed table-hover">
        <thead>
            <tr>
                <th class='sortth' style="cursor:pointer" id='ID'>#</th>
                <th>No.</th>
                <th class='sortth' style="cursor:pointer" id='BUILD_ID'>Build</th>
                <th class='sortth' style="cursor:pointer" id='ITERATION_NAME'>Iteration</th>
                <th>Title</th>
                <th class='sortth' style="cursor:pointer" id='USER_NAME'>Reporter</th>
                <th class='sortth' style="cursor:pointer" id='BUG_CATEGORY'>Category</th>
                <th class='sortth' style="cursor:pointer" id='BUG_STATUS'>Status</th>
                <th class='sortth' style="cursor:pointer" id='SUB_STATUS'>Sub Status</th>
                <th class='sortth' style="cursor:pointer" id="SUBMIT_DATE">Date</th>
            </tr>
        </thead>
        <?php
        $i = 0;
        foreach ($bug_info as $bi_key => $bi_val) {
            $short_comments = substr($bi_val['COMMENTS'], 0, 40);
            $short_iteration = substr($bi_val['ITERATION_NAME'], 0, 20);
            ?>
            <tr>
                <input type="hidden" id="id_<?php echo $i;?>" value="<?php echo $bi_val ['ID']; ?>">
                <input type="hidden" id="bug_system_<?php echo $i;?>" value="<?php echo $GLOBALS['BUG_SYSTEM'][$bi_val ['BUG_SYSTEM']][0]; ?>">
                <input type="hidden" id="bug_type_<?php echo $i;?>" value="<?php echo $GLOBALS['BUG_TYPE'][$bi_val ['BUG_TYPE']][0]; ?>">
                <input type="hidden" id="feature_<?php echo $i;?>" value="<?php echo $bi_val ['FEATURE']; ?>">
                <input type="hidden" id="related_bug_<?php echo $i;?>" value="<?php echo $bi_val ['RELATED_BUG']; ?>">
                <input type="hidden" id="execute_time_<?php echo $i;?>" value="<?php echo round($bi_val ['EXECUTE_TIME']/60,2); ?>">
                <input type="hidden" id="reporter_<?php echo $i;?>" value="<?php echo $bi_val ['REPORTER']; ?>">
                <input type="hidden" id="account_<?php echo $i;?>" value="<?php echo $bi_val ['ACCOUNT']; ?>">
                <input type="hidden" id="nick_name_<?php echo $i;?>" value="<?php echo $bi_val ['NICK_NAME']; ?>">
                <input type="hidden" id="task_type_<?php echo $i;?>" value="<?php echo $GLOBALS['TASK_TYPE'][$bi_val ['TASK_TYPE']][0]; ?>">
                <input type="hidden" id="last_modified_<?php echo $i;?>" value="<?php echo !empty($bi_val ['LAST_MODIFIED'])?date("m/d/y", strtotime($bi_val ['LAST_MODIFIED'])):null; ?>">
                <td><?php echo ($bi_key + 1) ?></td>
                <td class="bug_detail_info" id="<?php echo $i;?>" value="<?php echo $bi_val['BUG_ID'];?>"><a><?php echo $bi_val['BUG_ID']; ?></a></td>
                <td id="build_id_<?php echo $i;?>"><?php echo $bi_val ['BUILD_ID']; ?></td>
                <td id="iteration_name_<?php echo $i;?>" title="<?php echo $bi_val ['ITERATION_NAME']; ?>"><?php echo $short_iteration; ?></td>
                <td id="comments_<?php echo $i;?>" title="<?php echo htmlspecialchars($bi_val ['COMMENTS'], ENT_QUOTES); ?>">
                    <?php echo $short_comments; ?>
                </td>
                <?php
                    if($user_type != 4){?>
                        <td id="user_name_<?php echo $i;?>" class="chinese"><?php echo $bi_val['USER_NAME'];?></td>
                        <?php
                    } else{?>
                        <td id="user_name_<?php echo $i;?>" class='chinese'><?php echo (isset($bi_val['OWNER']) ? $bi_val['OWNER']: $bi_val['NICK_NAME']);?></td>
                        <?php
                    }   
                ?>
                <td id="bug_category_<?php echo $i;?>">
                    <?php echo isset($GLOBALS['BUG_CATEGORY'][$bi_val['BUG_CATEGORY']][0]) ? $GLOBALS['BUG_CATEGORY'][$bi_val['BUG_CATEGORY']][0] : "" ?>
                </td>
                <td id="bug_status_<?php echo $i;?>">
                    <?php echo isset($GLOBALS['BUG_STATUS'][$bi_val ['BUG_STATUS']][0]) ? $GLOBALS['BUG_STATUS'][$bi_val ['BUG_STATUS']][0] : "" ?>
                </td>
                <td id="bug_sub_status_<?php echo $i;?>">
                    <?php echo isset($GLOBALS['BUG_SUB_STATUS'][$bi_val ['SUB_STATUS']][0]) ? $GLOBALS['BUG_SUB_STATUS'][$bi_val ['SUB_STATUS']][0] : "" ?>
                </td>
                <td id="submit_date_<?php echo $i;?>"> 
                    <span class="span1"><?php echo date("m/d/y", strtotime($bi_val['SUBMIT_DATE'])); ?></span>
                </td>                      
            </tr>
            <?php
            $i++;
        }
        ?>
    </table>
    <?php
}

/**
 * [push_bug_into_table_external Push bug info table for external view]
 * @param  [array] $bug_info      [description]
 * @param  [int] $record_count  [description]
 * @param  [int] $oid           [description]
 * @param  [int] $su_id         [description]
 * @param  [int] $gtype         [description]
 * @param  [int] $permission    [description]
 * @param  [int] $required_perm [description]
 * @return [type]                [description]
 */
function push_bug_into_table_external($bug_info, $record_count, $oid, $su_id, $gtype, $permission, $required_perm) {
    ?>
    <div id="div_table_total"><h4 class="chinese">Total:<?php echo $record_count; ?></h4></div>
    <table class="table table-striped table-condensed table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>No.</th>
                <th>Build</th>
                <th>Iteration</th>
                <th>Title</th>
                <th>Reporter</th>
                <th>Category</th>
                <!-- <th>Related</th> -->
                <th>Status</th>
                <th>Sub Status</th>
                <th>Date</th>
                <th></th>
            </tr>
        </thead>
        <?php
        foreach ($bug_info as $bi_key => $bi_val) {
            $short_comments = substr($bi_val['COMMENTS'], 0, 40);
            $short_iteration = substr($bi_val['ITERATION_NAME'], 0, 20);
            ?>
            <tr>
                <td><?php echo ($bi_key + 1) ?></td>
                <td><a href='#'><?php echo $bi_val['BUG_ID'] ?></a></td>
                <td><?php echo $bi_val ['BUILD_ID']; ?></td>
                <td title="<?php echo $bi_val ['ITERATION_NAME']; ?>"><?php echo $short_iteration; ?></td>
                <td title="<?php echo htmlspecialchars($bi_val ['COMMENTS'], ENT_QUOTES); ?>">
                    <?php echo $short_comments; ?>
                </td>
                <td class="chinese">
                    <?php echo (isset($bi_val['OWNER'])) ? $bi_val['OWNER'] : $bi_val['NICK_NAME']; ?>
                </td>
                <td>
                    <?php echo isset($GLOBALS['BUG_CATEGORY'][$bi_val['BUG_CATEGORY']][0]) ? $GLOBALS['BUG_CATEGORY'][$bi_val['BUG_CATEGORY']][0] : "" ?>
                </td>

                                <!-- <td title="<?php //echo htmlspecialchars($bi_val ['RELATED_BUG'], ENT_QUOTES);   ?>">
                                    <span class="span1"><?php //echo substr($bi_val ['RELATED_BUG'], 0, 10);   ?></span>
                                </td> -->
                <td>
                    <?php echo isset($GLOBALS['BUG_STATUS'][$bi_val ['BUG_STATUS']][0]) ? $GLOBALS['BUG_STATUS'][$bi_val ['BUG_STATUS']][0] : "" ?>
                </td>
                <td>
                    <?php echo isset($GLOBALS['BUG_SUB_STATUS'][$bi_val ['SUB_STATUS']][0]) ? $GLOBALS['BUG_SUB_STATUS'][$bi_val ['SUB_STATUS']][0] : "" ?>
                </td>
                <td>
                    <span class="span1"><?php echo date("m/d/y", strtotime($bi_val['SUBMIT_DATE'])); ?></span>
                </td>
                <?php
                $parm_string = $bi_val ['ID'];
                $parm = base64_encode($parm_string);
                ?>
                <?php
                /*
                 * For the bug's onwer or the owner's manager, they can modify all the bugs
                 */
                if ($bi_val['REPORTER'] == $_SESSION ['user_id'] || $permission >= $required_perm) {
                    ?>
                    <td> 
                        <a title='Update' href='../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&url=<?php echo base64_encode("bug_mgt/update_bug_external.php"); ?>&gtype=<?php echo base64_encode($gtype); ?>&parm=<?php echo $parm; ?>&editable=<?php echo base64_encode("1"); ?>' ><img width='14' height='14' src='../../../lib/image/icons/edit.png'/></a>
                        <a title='Delete' onclick="return delete_bug()" href='../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&url=<?php echo base64_encode("bug_mgt/delete_bug.php"); ?>&gtype=<?php echo base64_encode($gtype); ?>&parm=<?php echo $parm; ?>#tabs_ops' ><img width='16' height='16' src='../../../lib/image/icons/remove.png'/></a>
                    </td>    
                <?php } else { ?>
                    <td> 
                        <a title='View' href='../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&url=<?php echo base64_encode("bug_mgt/update_bug.php"); ?>&gtype=<?php echo base64_encode($gtype); ?>&parm=<?php echo $parm; ?>&editable=<?php echo base64_encode("0"); ?>' ><img width='14' height='14' src='../../../lib/image/icons/eye.png'/></a>
                    </td>           
                <?php } ?>                
            </tr>

            <?php
        }
        ?>
    </table>
    <?php
}

/**
 * Query all bug not info
 */
function all_bug_note_info() {
    $qry_bug_note = "SELECT bl.BUG_ID,bl.BUILD_ID,bl.ACCOUNT,ui.USER_NAME,ui.NICK_NAME,ca.`OWNER`,uin.USER_NAME NOTE_OWNER,uin.NICK_NAME NOTE_OWNER_NICK, can.`OWNER` NOTE_OWNER_CUS,bl.BUG_STATUS,bn.OWNER BUG_NOTE_OWNER,bn.CONTENT,bn.EXECUTE_TIME NOTE_TIME,bn.SUBMIT_DATE SUBMIT_DATE,ii.ITERATION_ID,ii.ITERATION_NAME FROM bug_library AS bl, bug_note AS bn, iteration_info AS ii, (user_info AS ui LEFT JOIN customer_account AS ca ON ui.USER_ID=ca.MAINTAINER), (user_info AS uin LEFT JOIN customer_account AS can ON uin.USER_ID=can.MAINTAINER) WHERE bl.REPORTER=ui.USER_ID AND bn.OWNER=uin.USER_ID AND bl.ID=bn.ISSUE_ID AND bl.ITERATION_ID=ii.ITERATION_ID ORDER BY BUILD_ID DESC;";
    $rst_bug_note = $GLOBALS['db']->query($qry_bug_note);
    $all_bug_note = $rst_bug_note->fetch_all(MYSQLI_ASSOC);
    return $all_bug_note;
}

/*
 * Query org bug note info
 */

function project_bug_note_info($projects, $date_from, $date_to) {
    $string_projects    = op_string($projects);
    $members_of_project = members_of_project($string_projects);
    $all_bug_note_info  = all_bug_note_info();
    $org_bug_note_info = array();
    foreach ($all_bug_note_info as $abni_key => $abni_val) {
        foreach ($members_of_project as $moo_key => $moo_val) {
            $submit_date = date("Y-m-d", strtotime($abni_val['SUBMIT_DATE']));
            if (($abni_val['BUG_NOTE_OWNER'] == $moo_val['USER_ID']) && ($submit_date >= $date_from) && ($submit_date <= $date_to)) {
                $org_bug_note_info[] = $abni_val;
            }
        }
    }
    return $org_bug_note_info;
}

/*
 * Devide bug info into different array by bug_category
 */

function devide_bug_by_category($bug_info_temp) {
    foreach ($GLOBALS['BUG_CATEGORY'] as $bc_key => $bc_val) {
        foreach ($bug_info_temp as $bit_key => $bit_val) {
            if ($bit_val['BUG_CATEGORY'] == $bc_key) {
                $bug_info_by_category[$bc_val[0]][] = $bit_val;
            }
        }
    }
    return $bug_info_by_category;
}

/*
 * Push bug info into table just for dispaly 
 * readonly
 */

function push_bug_into_table_ro($bug_info) {
    ?>
    <thead>
        <tr>
            <th>#</th>
            <th>System</th>
            <th>No.</th>
            <th>Build</th>
            <th>Iteration</th>
            <th>Reporter</th>
            <th>Category</th>
            <th>Feature</th>
            <th>Related</th>
            <th>Status</th>
            <th>Date</th>
        </tr>
    </thead>
    <?php
    foreach ($bug_info as $bi_key => $bi_val) {
        $short_iteration_name = substr($bi_val['ITERATION_NAME'], 0, 20);
        ?>
        <tr>
            <td><?php echo ($bi_key + 1) ?></td>
            <td><?php echo $GLOBALS['BUG_SYSTEM'][$bi_val['BUG_SYSTEM']][0] ?></td>
            <td><?php echo $bi_val['BUG_ID'] ?></a></td>
        <td><?php echo $bi_val ['BUILD_ID']; ?></td>
        <td title="<?php echo htmlspecialchars($bi_val ['ITERATION_NAME'], ENT_QUOTES); ?> "><?php echo $short_iteration_name; ?></td>
        <td class="chinese"><?php echo ($bi_val['REPORTER_NAME'] != null) ? $bi_val['REPORTER_NAME'] : NULL; ?></td>
        <td><?php echo $GLOBALS['BUG_CATEGORY'][$bi_val['BUG_CATEGORY']][0] ?></td>
        <!--                    <td><?php //echo $GLOBALS['TASK_TYPE'][$ai_val['BUG_TYPE']][0]                                   ?></td>-->
        <td title="<?php echo htmlspecialchars($bi_val ['FEATURE'], ENT_QUOTES); ?>"><?php echo substr($bi_val ['FEATURE'], 0, 10); ?></td>
        <td title="<?php echo htmlspecialchars($bi_val ['RELATED_BUG'], ENT_QUOTES); ?>"><?php echo substr($bi_val ['RELATED_BUG'], 0, 10); ?></td>
        <td><?php echo $GLOBALS['BUG_STATUS'][$bi_val ['BUG_STATUS']][0] ?></td>
        <td><?php echo $bi_val['SUBMIT_DATE']; ?></td>
        </tr>
        <?php
    }
}

/*
 * Purpose: Caculate the max local query id of table bug info
 */

function max_query_id() {
    $qry_max_id = "SELECT MAX(BUG_ID) AS MAX_ID FROM bug_library WHERE BUG_ID LIKE 'QRY%'";
    $rst_max_id = $GLOBALS['db']->query($qry_max_id);
    $row_max_id = $rst_max_id->fetch_assoc();
    /*
     * Explod old query id, 
     * e.g. 
     * QRY0000001 TO QRY, 0000001
     */
    $old_query_id_temp = explode("QRY", $row_max_id ['MAX_ID']);
    $old_query_id = $old_query_id_temp[1];
    $new_query_id = $old_query_id + 1;

    $query_id_temp = str_pad($new_query_id, 7, 0, STR_PAD_LEFT);
    $query_id = "QRY" . $query_id_temp;
    return $query_id;
}

/**
 * Date: 2012-11-12
 * Author: CC
 * Purpose: Get all valid employees' bug info
 */
function bug_in_dates($date_start, $date_end) {
    /**
     * 1. Query all bugs
     */
    $qry_bug = "SELECT ID,BUG_ID,ITERATION_ID,REPORTER,ACCOUNT,EXECUTE_TIME FROM bug_library WHERE (SUBMIT_DATE BETWEEN '$date_start' AND '$date_end');";
    $rst_bug = $GLOBALS['db']->query($qry_bug);
    $bug_temp = $rst_bug->fetch_all(MYSQLI_ASSOC);

    //re-arrange the array, make the uid as the array KEY
    foreach ($bug_temp as $bt_key => $bt_val) {
        $bug[$bt_val['REPORTER']][] = $bt_val;
    }

    /**
     * 2. Query all bug notes
     */
    $qry_bug_note = "SELECT ID,ISSUE_ID,OWNER,SUBMIT_DATE,EXECUTE_TIME FROM bug_note WHERE (SUBMIT_DATE BETWEEN '$date_start' AND '$date_end');";
    $rst_bug_note = $GLOBALS['db']->query($qry_bug_note);
    $bug_note_temp = $rst_bug_note->fetch_all(MYSQLI_ASSOC);
    //re-arrange the array, make the uid as the array KEY
    foreach ($bug_note_temp as $bnt_key => $bnt_val) {
        $bug_note[$bnt_val['OWNER']][] = $bnt_val;
    }

    /**
     * 3. Put the two array in one array to trans
     */
    $bug_info = array(
        'bug' => isset($bug) ? $bug : array(),
        'bug_note' => isset($bug_note) ? $bug_note : array()
    );

    return $bug_info;
}

/**
 * Auhtor: SY
 * Date: 09/04/2013
 * [list_bug_status_count description]
 * @param  [type] $pid_string       [project id string]
 * @param  [type] $date_from [description]
 * @param  [type] $date_to   [description]
 * @return [type]            [description]
 */
function list_bug_status_count($pid_string,$date_from,$date_to){
    $bc_array = array();
    $bs_array = array();
    if(($date_from == "")||($date_to == "")){
        $date_from = "2007-01-01";
        $date_to   = $GLOBALS['today'];
    }
    foreach($GLOBALS['BUG_CATEGORY'] as $bc_key => $bc_val){
        $bc_array[] = "'{$bc_key}'"; 
    }
    foreach($GLOBALS['BUG_STATUS'] as $bs_key => $bs_val){
        $bs_array[] = "'{$bs_key}'"; 
    }
    $bc_string = implode(',', $bc_array);
    $bs_string = implode(',', $bs_array);
    $qry_bug_status_count = "SELECT bl.FK_PROJECT_ID,pi.NAME,bl.BUG_ID,bl.BUG_CATEGORY,bl.BUG_STATUS,bl.SUB_STATUS,CAST(bl.SUBMIT_DATE AS DATE) AS OPEN_DATE,bl.LAST_MODIFIED,bl.REPORTER,ui.USER_NAME ,ui.NICK_NAME,ca.ACCOUNT,ca.`OWNER` FROM bug_library AS bl,project_info as pi,(user_info AS ui LEFT JOIN customer_account AS ca ON ui.USER_ID=ca.MAINTAINER)
                    WHERE bl.FK_PROJECT_ID = pi.ID AND bl.REPORTER = ui.USER_ID AND bl.FK_PROJECT_ID in ($pid_string) AND CAST(bl.SUBMIT_DATE AS DATE) BETWEEN '$date_from' AND '$date_to' AND bl.BUG_CATEGORY IN ($bc_string) AND bl.BUG_STATUS IN ($bs_string)
                    ORDER BY bl.BUG_CATEGORY,bl.BUG_STATUS,bl.SUB_STATUS;";
    $rst_bug_status_count = $GLOBALS['db']->query($qry_bug_status_count);
    $all_bug_status_count = $rst_bug_status_count->fetch_all(MYSQLI_ASSOC);
    $bug_status_count     = array();
    if(!empty($all_bug_status_count)){
        foreach ($all_bug_status_count as $absc_key => $absc_value) {
            $bug_status_count[$absc_value['FK_PROJECT_ID']][$absc_value['BUG_ID']] = $absc_value;
        }
    }
    return $bug_status_count;
}

/**
 * Author: SY
 * Date: 
 * Last Modified: CC    09/24/2013
 * [list_iteration_bug_status_count description]
 * @param  [type] $iteration_id [description]
 * @param  [type] $date_from    [description]
 * @param  [type] $date_to      [description]
 * @return [type]               [description]
 */
function list_iteration_bug_status_count($iteration_id,$date_from,$date_to){
    $bc_array = array();
    $bs_array = array();
    foreach($GLOBALS['BUG_CATEGORY'] as $bc_key => $bc_val){
        $bc_array[] = "'{$bc_key}'"; 
    }
    foreach($GLOBALS['BUG_STATUS'] as $bs_key => $bs_val){
        $bs_array[] = "'{$bs_key}'"; 
    }
    $bc_string = implode(',', $bc_array);
    $bs_string = implode(',', $bs_array);
     $qry_bug_status_count = "SELECT bl.ITERATION_ID,bl.BUG_ID,bl.BUG_CATEGORY,bl.BUG_STATUS,bl.SUB_STATUS FROM bug_library AS bl 
                    WHERE bl.ITERATION_ID in ($iteration_id) AND CAST(bl.SUBMIT_DATE AS DATE) BETWEEN '$date_from' AND '$date_to' AND bl.BUG_CATEGORY IN ($bc_string) AND bl.BUG_STATUS IN ($bs_string)
                    ORDER BY bl.BUG_CATEGORY,bl.BUG_STATUS,bl.SUB_STATUS;";
    $rst_bug_status_count = $GLOBALS['db']->query($qry_bug_status_count);
    $all_bug_status_count = $rst_bug_status_count->fetch_all(MYSQLI_ASSOC);
    $bug_status_count     = array();
    if(!empty($all_bug_status_count)){
        foreach ($all_bug_status_count as $absc_key => $absc_value) {
            $bug_status_count[$absc_value['ITERATION_ID']][$absc_value['BUG_ID']] = $absc_value;
        }
    }
    return $bug_status_count;
}
?>
