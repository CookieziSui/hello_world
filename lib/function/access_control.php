<?php
require_once __DIR__ . '../../../mysql.php';

/**
 * Author: CC
 * Last Modified: 07/24/2013
 * [query_current_org Get the curreny org of the logged user]
 * @param  [int] $user_id [logged user]
 * @return [array]          [orgs]
 */
function query_current_org($user_id) {
    $qry_cur_org  = "SELECT uog.FK_ORG_ID ORG_ID, ogi.TYPE GRP_TYPE,uog.FK_GROUP_ID FROM user_org_group AS uog, org_group_info AS ogi  WHERE uog.FK_USER_ID='" . $user_id . "' AND uog.FK_GROUP_ID=ogi.ID;";
    $rst_cur_org  = $GLOBALS['db']->query($qry_cur_org);
    $cur_org_temp = $rst_cur_org->fetch_all(MYSQLI_ASSOC);
    if(!empty($cur_org_temp)){
        foreach($cur_org_temp as $cot_key => $cot_val){
            $cur_org[$cot_val['ORG_ID']] = $cot_val;
        }
    }else{
        $cur_org = array();
    }
    return $cur_org;
}

/**
 * Author: CC
 * Last Modified: 07/24/2013
 * [query_current_project Get the curreny project of the logged user]
 * @param  [int] $user_id [logged user]
 * @return [array]          [projects]
 */
function query_current_project($user_id) {
    $qry_cur_project = "SELECT upg.FK_PROJECT_ID PROJECT_ID, pgi.TYPE GRP_TYPE,upg.FK_GROUP_ID FROM user_project_group AS upg, project_group_info AS pgi, project_info AS pi  WHERE upg.FK_USER_ID='" . $user_id . "' AND upg.FK_GROUP_ID=pgi.ID AND upg.FK_PROJECT_ID=pi.ID;";
    $rst_cur_project = $GLOBALS['db']->query($qry_cur_project);
    $cur_project_temp = $rst_cur_project->fetch_all(MYSQLI_ASSOC);
    if(!empty($cur_project_temp)){
        foreach ($cur_project_temp as $key => $value) {
            $cur_project[$value['PROJECT_ID']] = $value;
        }
    }else{
        $cur_project = array();
    }
    
    return $cur_project;
}

/*
 * For organizations
 * Function current_org
 * Get the current org name
 * $current_org: the org of the user
 * $orgs: array of org info
 */

function current_orgTree($current_org, $current_org_grp_type, $orgs, $optype,$current_project) {
    foreach ($orgs as $o_key => $o_val) {
        if ($o_val['ID'] == $current_org) {
            ?>
            <li class="dropdown-submenu">
                <a tabindex="-1" href='../home/index.php?oid=<?php echo base64_encode($o_val['ID']) ?>&gtype=<?php echo base64_encode($current_org_grp_type) ?>&optype=<?php echo base64_encode($optype) ?>' id='<?php echo $o_val['ID']; ?>'><?php echo $o_val['NAME'] ?></a>
                <?php $current_project = display_childOrg($current_org, $current_org_grp_type, 1, $orgs, $optype,$current_project); ?>
            </li>
            <?php
        }
    }
    return $current_project;
}

/*
 * Function dispaly_orgTree
 * list the user's org tree (including all childs)
 * $father: the current org id of the user
 * $level: just to indent the org tree to be organized
 * $org_tree: array of org tree
 */

function display_childOrg($father, $father_grp_type, $level, $org_tree, $optype,$current_project) {
    $child = array();
    foreach ($org_tree as $ot_key => $ot_val) {
        if ($ot_val['FATHER'] == $father) {
            $child[] = $ot_val;
        }
    }?>
    <ul class="dropdown-menu" role="menu" aria-labelledby="drop1">
        <?php
        foreach ($child as $child_key => $child_val) {
            if(!empty($current_project)){
                if(array_key_exists($child_val['ID'],$current_project)){
                    unset($current_project[$child_val['ID']]); 
                }
            }
            ?>
            <li class="dropdown-submenu">
                <a tabindex="-1" href='../home/index.php?oid=<?php echo base64_encode($child_val['ID']) ?>&gtype=<?php echo base64_encode($father_grp_type) ?>&optype=<?php echo base64_encode($optype) ?>' id='<?php echo $child_val['ID']; ?>'><?php echo $child_val['NAME'] ?></a>
                <?php $current_project = display_childOrg($child_val['ID'], $father_grp_type, $level + 1, $org_tree, $optype,$current_project); ?>
            </li>
        <?php }
        ?>
    </ul>
    <?php
    return $current_project;
}

/**
 * Author: CC
 * Date: 07/31/2013
 * Last Modified: 07/31/2013
 * [subscribed_projectTree List subscripted project]
 * @param  [array] $subscripted_project [subscripted project]
 */
function subscribed_projectTree($subscripted_project) {
    foreach ($subscripted_project as $sp_key => $sp_val) {
        ?>
        <li>
            <a tabindex="-1" href='../subscribe/index.php?oid=<?php echo base64_encode($sp_val['ID']); ?>&url=<?php echo base64_encode("asset/subscribe/asset.php") ?>&name=<?php echo base64_encode($sp_val['NAME']); ?>' id='<?php echo $sp_val['ID']; ?>'><?php echo $sp_val['NAME'] ?></a>
            <?php //display_childOrg($current_org, $current_org_grp_type, 1, $orgs); ?>
        </li>
        <?php
    }
}

/*
 * Function query_subOrg
 * list the sub tree (including all childs)
 * $original: the current org id of the user
 * $father: the current org id of the user
 * $org_tree: array of org tree
 * $sub_org: array to store the result
 */

function query_subOrg($original, $father, $org_tree, &$sub_org) {
    $child = array();
    $i = 0;
    foreach ($org_tree as $ot_key => $ot_val) {
        if ($ot_val['FATHER'] == $father) {
            $child[] = $ot_val;
            $i++;
        }
    }
    $sub_org[$original][] = $father;
    foreach ($child as $child_key => $child_val) {
        query_subOrg($original, $child_val['ID'], $org_tree, $sub_org);
    }
    return $sub_org;
}

/**
 * Author: CC
 * Last Modified: 08/06/2013
 * [display_operations Get all available Operations without the item which permission is 'N']
 * @param  [int] $grp_id  [description]
 * @param  [string] $op_name [description]
 * @param  [string] $table   [prefix of table name: org or project]
 * @return [array]          [description]
 */
function display_operations($grp_id, $op_name, $table) {
    $prefix_table = strtolower($table);
    $qry_ss_su = "SELECT ssgn.FK_SS_ID,ssgn.SSG_ID,ssgn.STATE_SCHEMA_NAME,susn.SUS_ID,susn.FK_SU_ID,susn.STATE_UNIT_NAME,pu.PERMISSION_ID,pu.PERMISSION,pu.FK_SUS_ID
FROM (((".$prefix_table."_permission_unit AS pu LEFT JOIN ".$prefix_table."_state_unit_schema_name AS susn ON susn.SUS_ID=pu.FK_SUS_ID) LEFT JOIN ".$prefix_table."_state_schema_group_name AS ssgn ON ssgn.SSG_ID=susn.FK_SSG_ID)
LEFT JOIN ".$prefix_table."_group_info AS ogi ON ogi.ID=ssgn.FK_GROUP_ID)
WHERE ogi.ID ='" . $grp_id . "' AND pu.PERMISSION <>1;";
    $rst_task = $GLOBALS['db']->query($qry_ss_su);
    $operations = $rst_task->fetch_all(MYSQLI_ASSOC);
    $operations_orgName = array('operations' => $operations, 'org_name' => $op_name);
    return $operations_orgName;
}

/**
 * Author: CC
 * Last Modified: 08/06/2013
 * [get_operations Get  group_id through user_id and org_id]
 * @param  [int] $id         [description]
 * @param  [int] $group_type [description]
 * @param  [string] $table      [prefix of table name: org or project]
 * @return [type]             [description]
 */
function get_operations($id, $group_type, $table) {
    $prefix_table = strtolower($table);
    $qry_grpID = "SELECT gi.ID GRP_ID, i.NAME OP_NAME FROM ".$prefix_table."_group_info AS gi,".$prefix_table."_info AS i WHERE gi.TYPE='" . $group_type . "' AND gi.FK_".$table."_ID = '" . $id . "' AND i.ID='" . $id . "' limit 0,1;";
    $rst_grp = $GLOBALS['db']->query($qry_grpID);
    $num_grp = $rst_grp->num_rows;
    if ($num_grp == 0) {
        return FALSE;
        exit();
    }
    $row_grp = $rst_grp->fetch_assoc();
    $grp_id  = $row_grp['GRP_ID'];
    $op_name = $row_grp['OP_NAME'];
    return display_operations($grp_id, $op_name, $prefix_table);
}
function is_netapp($id, $group_type, $table) {
    $prefix_table = strtolower($table);
    $qry_grpID = "SELECT gi.ID GRP_ID, i.NAME OP_NAME,i.ISNETAPP FROM ".$prefix_table."_group_info AS gi,".$prefix_table."_info AS i WHERE gi.TYPE='" . $group_type . "' AND gi.FK_".$table."_ID = '" . $id . "' AND i.ID='" . $id . "' limit 0,1;";
    $rst_grp = $GLOBALS['db']->query($qry_grpID);
    $netapp_info = $rst_grp->fetch_all(MYSQLI_ASSOC);
    return $netapp_info;
}
/*
 * Get all available Operations , including the item which permission is 'N'
 * This function is used for update privilege
 */

function display_operations_all($grp_id, $table) {
    $prefix_table = strtolower($table);
    $qry_ss_su = "SELECT ssgn.FK_SS_ID,ssgn.SSG_ID,ssgn.STATE_SCHEMA_NAME,susn.SUS_ID,susn.FK_SU_ID,susn.STATE_UNIT_NAME,pu.PERMISSION_ID,pu.PERMISSION,pu.FK_SUS_ID
FROM (((".$prefix_table."_permission_unit AS pu
LEFT JOIN ".$prefix_table."_state_unit_schema_name AS susn ON susn.SUS_ID=pu.FK_SUS_ID)
LEFT JOIN ".$prefix_table."_state_schema_group_name AS ssgn ON ssgn.SSG_ID=susn.FK_SSG_ID)
LEFT JOIN ".$prefix_table."_group_info AS ogi ON ogi.ID=ssgn.FK_GROUP_ID)
WHERE ogi.ID ='" . $grp_id . "';";
    $rst_task   = $GLOBALS['db']->query($qry_ss_su);
    $operations = $rst_task->fetch_all(MYSQLI_ASSOC);
    return $operations;
}

/*
 * Get state unit permission of each group
 */

function state_unit_permission($permission, $su_id, $table) {
    $prefix_table = strtolower($table);
    $query = "SELECT FK_SU_ID FROM ".$prefix_table."_state_unit_schema WHERE SUS_ID='$su_id';";
    $result = $GLOBALS['db']->query($query);
    $row = $result->fetch_assoc();
    foreach ($permission as $perm_key => $perm_val) {
        if ($perm_val['su_id'] == $row['FK_SU_ID']) {
            $perm = $perm_val['permission'];
        }
    }
    return $perm;
}

/**
 * Author: CC
 * Date: 08/06/2013
 * Last Modified: 08/06/2013
 * [state_schema Query all original state schema into a array,this array will be used in breadcrumb fo each page]
 * @param  [string] $table [prefix of table name: org or project]
 * @return [array]        [state schema]
 */
function state_schema($table){
    $prefix_table = strtolower($table);
    $qry_ss       = "SELECT SUS.SUS_ID,SS.ID,SS.STATE_SCHEMA_NAME FROM state_schema  AS SS, ".$prefix_table."_state_schema_group AS SSG, ".$prefix_table."_state_unit_schema AS SUS WHERE SS.ID=SSG.FK_SS_ID AND SUS.FK_SSG_ID=SSG.SSG_ID;";
    $rst_ss       = $GLOBALS['db']->query($qry_ss);
    $ss_temp      = $rst_ss->fetch_all(MYSQLI_ASSOC);
    foreach ($ss_temp as $st_key => $st_val) {
        $ss[$st_val['SUS_ID']] = $st_val['STATE_SCHEMA_NAME'];
    }
    return $ss;
}

function original_state_schema(){
    $qry_ss = "SELECT ID, STATE_SCHEMA_NAME FROM state_schema";
    $rst_ss = $GLOBALS['db']->query($qry_ss);
    $state_schema_temp = $rst_ss->fetch_all(MYSQLI_ASSOC);
    foreach ($state_schema_temp as $sst_key => $sst_val) {
        $state_schema[$sst_val['ID']] = $sst_val;
    }
    return $state_schema;
}

/**
 * Author: CC
 * Date: 08/06/2013
 * Last Modified: 08/06/2013
 * [state_unit Query all state unit by su_id, used in index.php for redirect]
 * @param  [int] $su_id [state unit id]
 * @param  [string] $table [prefix of table name: org or project]
 * @return [array] $state_unitID       [description]
 */
function state_unit($su_id,$table){
    $prefix_table = strtolower($table);
    $qry_suID     = "SELECT ID,STATE_UNIT_NAME,URL FROM ".$prefix_table."_state_unit_schema,state_unit WHERE FK_SU_ID=ID AND SUS_ID='" . $su_id . "';";
    $rst_suID     = $GLOBALS['db']->query($qry_suID);
    $state_unitID = $rst_suID->fetch_all(MYSQLI_ASSOC);
    return $state_unitID;
}

/**
 * Author: CC
 * Date: 08/06/2013
 * Last Modified: 08/06/2013
 * [ss_su Query all original state_schema and state_unit]
 * @return [type] [description]
 */
function ss_su(){
    $qry_ss_su = "SELECT SS.ID SSID,SS.STATE_SCHEMA_NAME,SU.ID SUID,SU.STATE_UNIT_NAME FROM state_unit AS SU,state_schema AS SS WHERE SU.FK_SS_ID=SS.ID AND SS.TYPE=1";
    $rst_ss_su = $GLOBALS['db']->query($qry_ss_su);
    $ss_su     = $rst_ss_su->fetch_all(MYSQLI_ASSOC);
    return $ss_su;
}
?>
