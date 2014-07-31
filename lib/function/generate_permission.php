<?php
// ==================================================================
//
// Author: CC
// Last Modified: 08/05/2013
// 
// This page used for generate permissions
// Including three main procedures:
// 1. Create state schema group
// 2. Create state unit schema
// 3. Create permission unit
//
// ------------------------------------------------------------------


/**
 * Author: CC
 * Last Modified: 08/05/2013
 * [create_state_schema_group Insert into state_schema_group]
 * @param  [type] $ss_id        [description]
 * @param  [type] $group_id     [description]
 * @param  [type] $prefix_table [prefix of a table: org or project]
 * @return [array]               [description]
 */
function create_state_schema_group($ss_id, $group_id, $table) {
    $prefix_table = strtolower($table);
    $qry_ssg = "INSERT INTO ".$prefix_table."_state_schema_group (FK_SS_ID,FK_GROUP_ID) VALUES ('$ss_id','$group_id')";
    $rst_ssg = $GLOBALS['db']->query($qry_ssg);
    return get_ssg_id($ss_id, $group_id, $prefix_table);
}

/**
 * Author: CC
 * Last Modified: 08/05/2013
 * [get_ssg_id Get the new created ssg_id]
 * @param  [type] $ss_id        [description]
 * @param  [type] $group_id     [description]
 * @param  [type] $prefix_table [description]
 * @return [type]               [description]
 */
function get_ssg_id($ss_id, $group_id, $table) {
    $prefix_table = strtolower($table);
    $qry_ssg_id = "SELECT SSG_ID FROM ".$prefix_table."_state_schema_group WHERE FK_SS_ID='$ss_id' AND FK_GROUP_ID='$group_id';";
    $rst_ssg_id = $GLOBALS['db']->query($qry_ssg_id);
    $row_ssg_id = $rst_ssg_id->fetch_assoc();
    $ss_ssg_id[] = array(
        'ss_id' => $ss_id,
        'ssg_id' => $row_ssg_id['SSG_ID']
    );
    return $ss_ssg_id;
}


/*
 * GET the relationship of state_schema_id and state_schema '$ss_ssg_id'
 * Re-arrange the array to conbiane state_schema_id and state_unit
 */
function reArrange_ssi_su($ss_ssg_id, $new_perm) {
    $array_ssg_su = array();
    foreach ($ss_ssg_id as $ssi_key => $ssi_val) {
        foreach ($ssi_val as $ssv_key => $ssv_val) {
            foreach ($new_perm as $np_key => $np_val) {
                if ($np_val['state_schema'] == $ssv_val['ss_id']) {
                    $array_ssg_su[] = array(
                        'state_unit' => $np_val['state_unit'],
                        'ssg_id' => $ssv_val['ssg_id']);
                }
            }
        }
    }
    return $array_ssg_su;
}

/**
 * Author: CC
 * Date: 
 * Last Modified: 08/05/2013
 * [create_state_unit_schema Insert into state_unit_schema]
 * @param  [type] $su           [description]
 * @param  [type] $ssg          [description]
 * @param  [type] $prefix_table [description]
 * @return [type]               [description]
 */
function create_state_unit_schema($su, $ssg, $table) {
    $prefix_table = strtolower($table);
    $qry_sus = "INSERT INTO ".$prefix_table."_state_unit_schema (FK_SU_ID,FK_SSG_ID) VALUES ('$su','$ssg');";
    $rst_sus = $GLOBALS['db']->query($qry_sus);
    return get_sus_id($su, $ssg, $prefix_table);
}

/*
 * Purpuse:Get the new created sus_id 
 */
function get_sus_id($su_id, $ssg_id, $table) {
    $prefix_table = strtolower($table);
    $qry_sus_id = "SELECT SUS_ID FROM ".$prefix_table."_state_unit_schema WHERE FK_SU_ID='$su_id' AND FK_SSG_ID='$ssg_id';";
    $rst_sus_id = $GLOBALS['db']->query($qry_sus_id);
    $row_sus_id = $rst_sus_id->fetch_assoc();
    $sus_su[] = array(
        'sus_id' => $row_sus_id['SUS_ID'],
        'su_id' => $su_id
    );
    return $sus_su;
}

/**
 * [get_susp_id Get the new created susp id]
 * @param  [int] $su_id   [description]
 * @param  [int] $ssgp_id [description]
 * @return [array]          [description]
 */
function get_susp_id($su_id, $ssgp_id) {
    $qry_susp_id = "SELECT SUSP_ID FROM state_unit_schema_project WHERE FK_SU_ID='$su_id' AND FK_SSGP_ID='$ssgp_id';";
    $rst_susp_id = $GLOBALS['db']->query($qry_susp_id);
    $row_susp_id = $rst_susp_id->fetch_assoc();
    $susp_su[] = array(
        'sus_id' => $row_susp_id['SUSP_ID'],
        'su_id' => $su_id
    );
    return $susp_su;
}

/*
 * Re-arrange the array to conbine state_unit_schema_id and permission
 */
function reArrange_susi_perm($sus_su, $new_perm) {
    $array_sus_pu = array();
    foreach ($sus_su as $sus_su_key => $sus_su_val) {
        foreach ($sus_su_val as $ssv_key => $ssv_val) {
            foreach ($new_perm as $np_key => $np_val) {
                if ($np_val['state_unit'] == $ssv_val['su_id']) {
                    $array_sus_pu[] = array(
                        'permission' => $np_val['permission'],
                        'sus_id' => $ssv_val['sus_id']);
                }
            }
        }
    }
    return $array_sus_pu;
}

/**
 * Author: CC
 * Last Modified: 08/05/2013
 * [create_permission_unit Create permission of org or project]
 * @param  [int] $permission   [description]
 * @param  [int] $sus_id       [description]
 * @param  [string] $prefix_table [prefix of table]
 */
function create_permission_unit($permission, $sus_id, $table) {
    $prefix_table = strtolower($table);
    $qry_pu = "INSERT INTO ".$prefix_table."_permission_unit (PERMISSION,FK_SUS_ID) VALUES ('$permission','$sus_id');";
    $rst_pu = $GLOBALS['db']->query($qry_pu);
}



/*
 * Purpuse:Update the existed permission
 */
function update_existed_permission($existed_permission, $table) {
    $prefix_table = strtolower($table);
    foreach ($existed_permission as $ep_key => $ep_val) {
        $qry_update_perm = "UPDATE ".$prefix_table."_permission_unit SET PERMISSION='" . $ep_val['permission'] . "' WHERE FK_SUS_ID='" . $ep_val['fk_sus_id'] . "';";
        $rst_update_perm = $GLOBALS['db']->query($qry_update_perm);
    } 
}

/*
 * Purpuse: Generate permissions for the new state_unit under an existed state_schema
 */
function new_perm_noSS($new_perm_noSS, $table) {
    /*
     * Insert state_unit into state_unit_schema
     */
    foreach ($new_perm_noSS as $npn_key => $npn_val) {
        $sus_su[] = create_state_unit_schema($npn_val['state_unit'], $npn_val['state_schema_group_id'], $table);
    }

    /*
     * Re-arrange the array to conbine state_unit_schema_id and permission
     */
    $array_sus_pu = reArrange_susi_perm($sus_su, $new_perm_noSS, $table);

    /*
     * Insert into permission unit
     */
    foreach ($array_sus_pu as $asp_key => $asp_val) {
        create_permission_unit($asp_val['permission'], $asp_val['sus_id'], $table);
    }
    
}

/*
 * Purpuse: Generate permissions for the new state_shcema and state_unit, both of them are need to be added into permission_unit for each group
 */
function new_perm_withSS($new_perm_withSS,$group_id, $table) {
    $prefix_table = strtolower($table);
    $state_schema = array();
    $ss_ssg_id    = array();
    $array_ssg_su = array();
    $sus_su       = array();
    /*
     * unique the state_schema into an array
     * This new array will be used to store in DB  in function 'create_state_schema_group'
     */
    $flag = NULL;
    foreach ($new_perm_withSS as $ne_key => $np_val) {
        if ($np_val['state_schema'] != $flag) {
            $state_schema[] = $np_val['state_schema'];
            $flag = $np_val['state_schema'];
        }
    }
    /*
     * Insert state_schema into state_schema_group
     */
    foreach ($state_schema as $state_s_key => $state_s_val) {
        $ss_ssg_id[] = create_state_schema_group($state_s_val, $group_id, $prefix_table);
    }

    /*
     * GET the relationship of state_schema_id and state_schema '$ss_ssg_id'
     * Re-arrange the array to conbiane state_schema_id and state_unit
     */

    $array_ssg_su = reArrange_ssi_su($ss_ssg_id, $new_perm_withSS);

    /*
     * Insert state_unit into state_unit_schema
     */
    foreach ($array_ssg_su as $ass_key => $ass_val) {
        $susp_su[] = create_state_unit_schema($ass_val['state_unit'], $ass_val['ssg_id'], $prefix_table);
    }

    /*
     * Re-arrange the array to conbine state_unit_schema_id and permission
     */
    $array_susp_pu = reArrange_susi_perm($susp_su, $new_perm_withSS);

    /*
     * Insert into permission unit
     */
    foreach ($array_susp_pu as $asp_key => $asp_val) {
        create_permission_unit($asp_val['permission'], $asp_val['sus_id'], $prefix_table);
    }
}
?>
