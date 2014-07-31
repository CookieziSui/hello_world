<?php
require_once __DIR__ . '../../../debug.php';
require_once __DIR__ . '../../../mysql.php';
require_once __DIR__ . '/../../lib/function/org_mgt.php';
require_once __DIR__ . '/../../lib/inc/constant_asset.php';

/*
 * Query the information of user 
 */

function qry_all_user_info() {
    $query_available_employees = "SELECT USER_ID,HR_ID,USER_NAME,EMAIL FROM user_info WHERE EMPLOYEE_END IS NULL AND HR_ID != '' AND HR_ID IS NOT NULL";
    $rst_info = $GLOBALS['db']->query($query_available_employees);
    $available_employees_temp = $rst_info->fetch_all(MYSQLI_ASSOC);
    foreach ($available_employees_temp as $aet_key => $aet_val) {
        $user_id_info[$aet_val['USER_ID']] = $aet_val;
        $user_name_info[$aet_val['USER_NAME']] = $aet_val;
        $hr_id_info[$aet_val['HR_ID']] = $aet_val;
    }
    $user_info = array(
        "user_id_info" => $user_id_info,
        "user_name_info" => $user_name_info,
        "hr_id_info" => $hr_id_info,
    );
    return $user_info;
}

/**
 * push data into table when send email
 */
function push_data_into_table($asset_no,$type,$model,$ncpu,$nmemory,$nhd,$nmac,$nadditionInfo,$date_time,$comment){?>
    <table cellspacing='0'>
        <thead>
            <tr bgcolor='#dff0d9'>
                <th>Asset No</th>
                <th>Asset Type</th>
                <th>Model</th>
                <th>CPU</th>
                <th>Memory</th>
                <th>HD</th>
                <th>Mac</th>
                <th>AdditionInfo</th>
                <?php if(!empty($date_time)){?>
                    <th>Date</th>
                    <th>Comment</th>
                <?php }?>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td bgcolor='#d9edf7'><?php echo $asset_no; ?></td>
                <td bgcolor='#fcf9e4'><?php echo $type; ?></td>
                <td bgcolor='#d9edf7'><?php echo $model; ?></td>
                <td bgcolor='#fcf9e4'><?php echo $ncpu; ?></td>
                <td bgcolor='#d9edf7'><?php echo $nmemory; ?></td>
                <td bgcolor='#fcf9e4'><?php echo $nhd; ?></td>
                <td bgcolor='#d9edf7'><?php echo $nmac; ?></td>
                <td bgcolor='#fcf9e4'><?php echo $nadditionInfo; ?></td>
                <?php if(!empty($date_time)){?>
                    <td bgcolor='#d9edf7'><?php echo $date_time; ?></td>
                    <td bgcolor='#fcf9e4'><?php echo $comment; ?></td>
                <?php }?>
            </tr>
        </tbody>
    </table>
    <?php
}
/*
 * Get the asset info into array
 */

function all_asset_info() {
    $qry_asset = "SELECT ASSET_NO, ASSET_TYPE, MODEL,CPU,MEMORY,HD,MAC,ADDITIONINFO,USER_NAME,UI.USER_ID FROM asset_info AS AI, user_info AS UI WHERE AI.USER_ID = UI.USER_ID ORDER BY AI.USER_ID ASC,AI.ASSET_NO DESC ;";
    $rst_asset = $GLOBALS['db']->query($qry_asset);
    $asset_info = $rst_asset->fetch_all(MYSQLI_ASSOC);
    return $asset_info;
}

/**
 * Author: CC
 * Date: 08/06/2013
 * [query_cpu Get the CPU info]
 * @return [array] [description]
 */
function query_cpu(){
    $qry_cpu = "SELECT DISTINCT cpu FROM asset_info WHERE cpu IS NOT NULL;";
    $rst_cpu = $GLOBALS['db']->query($qry_cpu);
    $cpu     = $rst_cpu->fetch_all(MYSQLI_ASSOC);
    return $cpu;
}

/*
 * Get all request info into array
 */

function all_asset_request_info() {
    $qry_request = "SELECT a.REQUEST_ID,a.REQUEST_TYPE,a.ASSET_NO,a.ASSET_TYPE,a.REQUESTTO_ID,b.USER_NAME TO_NAME,a.REQUESTOR_ID,c.USER_NAME R_NAME,a.REQUEST_DATE,a.REQUEST_STATUS,a.OPERATOR_ID,a.OPERATE_DATE,d.USER_NAME O_NAME 
FROM (((asset_request_info AS a LEFT JOIN user_info AS b  ON a.REQUESTTO_ID=b.USER_ID) LEFT JOIN  user_info AS c  ON a.REQUESTOR_ID=c.USER_ID)  LEFT JOIN user_info AS d 
ON a.OPERATOR_ID=d.USER_ID) ORDER BY REQUEST_ID DESC";
    $rst_request = $GLOBALS['db']->query($qry_request);
    $request_info = $rst_request->fetch_all(MYSQLI_ASSOC);
    return $request_info;
}

/**
 * Date:2012-12-21
 * Last Modified: 07/31/2013
 * Author: CC
 * Purpose: Query all asset of this org. This function will only be used in module 'Asset Overview'
 * In this function, all asset owned by IDLE,IT and some other type of non-human user will be calculated
 */
function get_op_asset_info($year, $month, $day, $today, $valid_asset_type, $table) {
    /**
     * 1. Query all org ahc info of this month
     */
    $monthly_org_ahc_list    = monthly_op_ahc_list($year, $month, $day, $table);
    $monthly_org_member_list = $monthly_org_ahc_list['AHC_LIST'];
    $monthly_org_ahc         = $monthly_org_ahc_list['AHC'];
    $valid_asset_type_temp   = NULL;
    $monthly_ahc_asset       = array();
    foreach ($valid_asset_type as $vg_key => $vg_val) {
        $valid_asset_type_temp = $valid_asset_type_temp . "'" . $vg_val . "',";
    }
    $valid_asset_type_string = substr($valid_asset_type_temp, 0, -1);
    /**
     * 2. Get each org member's asset
     * Assets will be included if match the following conditions:
     *      1. PC or Monitor or Notebook or Server (The type info will be included in the conatant $valid_asset_type)
     */
    $qry_all_asset  = "SELECT ASSET_NO,ASSET_TYPE,CPU,MAC,MODEL,MEMORY,HD,CHECKINTIME,RETURNTIME,DEPRECIATION_END,PRICE,MONTHLY_DEPRECIATION,USER_ID,ADDITIONINFO FROM asset_info WHERE ASSET_TYPE IN ($valid_asset_type_string)";
    $rst_all_asset  = $GLOBALS['db']->query($qry_all_asset);
    $all_asset_temp = $rst_all_asset->fetch_all(MYSQLI_ASSOC);
    foreach ($all_asset_temp as $aat_key => $aat_val) {
        $all_asset[$aat_val['USER_ID']][] = $aat_val;
    }

    foreach ($monthly_org_member_list as $moml_key => $moml_val) {
        foreach ($moml_val as $mvv_key => $mvv_val) {
            if (isset($all_asset[$mvv_val])) {
                $monthly_ahc_asset[$moml_key]['total'][] = $all_asset[$mvv_val];
            }
        }
    }
    $monthly_asset = array();
    foreach ($monthly_ahc_asset as $maa_key => $maa_val) {
        $monthly_asset_count_temp = 0;
        foreach ($maa_val['total'] as $mvt_key => $mvt_val) {
            foreach ($mvt_val as $mv_key => $mv_val) {
                $monthly_asset[$maa_key]['list'][$mv_val['ASSET_TYPE']][] = $mv_val;
            }
            $monthly_asset_count_temp += count($mvt_val);
        }
        $monthly_asset[$maa_key]['count'] = $monthly_asset_count_temp;
    }
    
    /**
     * 3. Check whether this org had a group which group type is 13 (idle asset)
     */
    $qry_idle_asset        = "SELECT ogi.ID,ogi.NAME,ogi.TYPE,uog.FK_ORG_ID,uog.FK_USER_ID,ui.USER_NAME FROM org_group_info AS ogi, user_org_group AS uog,user_info AS ui WHERE ogi.TYPE='" . $GLOBALS['IDLE_ASSET_GROUP_TYPE'] . "' AND ogi.ID=uog.FK_GROUP_ID AND uog.FK_USER_ID=ui.USER_ID";
    $rst_idle_asset        = $GLOBALS['db']->query($qry_idle_asset);
    $idle_asset_group_temp = $rst_idle_asset->fetch_all(MYSQLI_ASSOC);
    foreach ($idle_asset_group_temp as $iagt_key => $iagt_val) {
        $idle_asset_group[$iagt_val['FK_ORG_ID']][$iagt_val['FK_USER_ID']] = $iagt_val;
    }

    foreach ($idle_asset_group as $iag_key => $iag_val) {
        foreach ($iag_val as $iv_key => $iv_val) {
            $idle_asset_count = 0;
            if (isset($all_asset[$iv_key])) {
                foreach ($all_asset[$iv_key] as $aa_key => $aa_val) {
                    $monthly_idle_asset[$iag_key][$iv_val['USER_NAME']]['list'][$aa_val['ASSET_TYPE']][] = $aa_val;
                }
                $idle_asset_count += count($all_asset[$iv_key]);
            }
            $monthly_idle_asset[$iag_key][$iv_val['USER_NAME']]['count'] = $idle_asset_count;
        }
    }
    $monthly_asset_info = array(
        'IN_USE' => $monthly_asset,
        'IDLE'   => $monthly_idle_asset
    );
    return $monthly_asset_info;
}

/*
 * Date:07/31/2013
 * Last Modified: 07/31/2013
 * Author: CC
 * Purpose: Query all asset of the subscribed projects. This function will only be used in module 'Asset Overview'
 * In this function, all asset owned by IDLE,IT and some other type of non-human user will be calculated
 */
function get_project_asset_info($year, $month, $day, $today, $valid_asset_type) {
    /*
     * 1. Query all project ahc info of this month
     */
    $monthly_project_ahc_list    = monthly_op_ahc_list($year, $month, $day);
    $monthly_project_member_list = $monthly_project_ahc_list['ahc_list'];
    $monthly_project_ahc         = $monthly_project_ahc_list['ahc'];
    $valid_asset_type_temp       = NULL;
    foreach ($valid_asset_type as $vg_key => $vg_val) {
        $valid_asset_type_temp = $valid_asset_type_temp . "'" . $vg_val . "',";
    }
    $valid_asset_type_string = substr($valid_asset_type_temp, 0, -1);
    /*
     * 2. Get each project member's asset
     * Assets will be included if match the following conditions:
     *      1. PC or Monitor or Notebook or Server (The type info will be included in the conatant $valid_asset_type)
     */
    $qry_all_asset  = "SELECT ASSET_NO,ASSET_TYPE,CPU,MAC,MODEL,MEMORY,HD,CHECKINTIME,RETURNTIME,DEPRECIATION_END,PRICE,MONTHLY_DEPRECIATION,USER_ID,ADDITIONINFO FROM asset_info WHERE ASSET_TYPE IN ($valid_asset_type_string)";
    $rst_all_asset  = $GLOBALS['db']->query($qry_all_asset);
    $all_asset_temp = $rst_all_asset->fetch_all(MYSQLI_ASSOC);
    foreach ($all_asset_temp as $aat_key => $aat_val) {
        $all_asset[$aat_val['USER_ID']][] = $aat_val;
    }
    foreach ($monthly_project_member_list as $mpml_key => $mpml_val) {
        foreach ($mpml_val as $mvv_key => $mvv_val) {
            if (isset($all_asset[$mvv_val])) {
                $monthly_ahc_asset[$mpml_key]['total'][] = $all_asset[$mvv_val];
            }
        }
    }
    $monthly_asset = array();
    foreach ($monthly_ahc_asset as $maa_key => $maa_val) {
        $monthly_asset_count_temp = 0;
        foreach ($maa_val['total'] as $mvt_key => $mvt_val) {
            foreach ($mvt_val as $mv_key => $mv_val) {
                $monthly_asset[$maa_key]['list'][$mv_val['ASSET_TYPE']][] = $mv_val;
            }
            $monthly_asset_count_temp += count($mvt_val);
        }
        $monthly_asset[$maa_key]['count'] = $monthly_asset_count_temp;
    }
    
    $monthly_asset_info = $monthly_asset;
    return $monthly_asset_info;
}

/**
 * Author: CC
 * Date: 2013-06-25
 * Last Modified: 2013-06-25
 * [get_org_asset Query all asset info of the father org. The asset will be calculated based on the org member queried from
 * daily_org_ahc.
 * The non-human user will not be excluded.
 * This function is used in Dashboard for internal
 * ]
 * @param  [type] $orgs  [description]
 * @param  [type] $year             [description]
 * @param  [type] $month            [description]
 * @param  [type] $day              [description]
 * @param  [type] $today            [description]
 * @param  [type] $valid_asset_type [description]
 * @return [type]                   [description]
 */
function org_asset($orgs, $year, $month, $day, $valid_asset_type) {
    /**
     * 1. Query all org ahc info of this month
     */
    $monthly_org_ahc_list         = monthly_op_ahc_list($year, $month, $day, "ORG");
    $monthly_org_member_list_temp = $monthly_org_ahc_list['AHC_LIST'];
    $valid_asset_type_temp        = NULL;
    $monthly_org_member_list      = array(); 
    $monthly_ahc_asset            = array();
    foreach($orgs as $aco_key => $aco_val){
        if(isset($monthly_org_member_list_temp[$aco_val['ID']])){
            $monthly_org_member_list[$aco_val['ID']] = $monthly_org_member_list_temp[$aco_val['ID']];
        }
    }

    foreach ($valid_asset_type as $vg_key => $vg_val) {
        $valid_asset_type_temp = $valid_asset_type_temp . "'" . $vg_val . "',";
    }
    $valid_asset_type_string = substr($valid_asset_type_temp, 0, -1);
    /**
     * 2. Get each org member's asset
     * Assets will be included if match the following conditions:
     *      1. PC or Monitor or Notebook or Server (The type info will be included in the conatant $valid_asset_type)
     */
    $qry_all_asset  = "SELECT ASSET_NO,ASSET_TYPE,CPU,MAC,MODEL,MEMORY,HD,CHECKINTIME,RETURNTIME,DEPRECIATION_END,PRICE,MONTHLY_DEPRECIATION,USER_ID,ADDITIONINFO FROM asset_info WHERE ASSET_TYPE IN ($valid_asset_type_string)";
    $rst_all_asset  = $GLOBALS['db']->query($qry_all_asset);
    $all_asset_temp = $rst_all_asset->fetch_all(MYSQLI_ASSOC);
    foreach ($all_asset_temp as $aat_key => $aat_val) {
        $all_asset[$aat_val['USER_ID']][] = $aat_val;
    }
    foreach ($monthly_org_member_list as $moml_key => $moml_val) {
        foreach ($moml_val as $mvv_key => $mvv_val) {
            if (isset($all_asset[$mvv_val])) {
                foreach($all_asset[$mvv_val] as $aamv_key => $aamv_val){
                    $monthly_ahc_asset[$aamv_val['ASSET_NO']] = $aamv_val;
                }
            }
        }
    }
    $monthly_asset       = array();
    $asset_count_by_type = array();
    foreach ($monthly_ahc_asset as $maa_key => $maa_val) {
        $monthly_asset[$maa_val['ASSET_TYPE']][] = $maa_val;
        if(!isset($asset_count_by_type[$maa_val['ASSET_TYPE']])){
            $asset_count_by_type[$maa_val['ASSET_TYPE']] = 0;
        }
        $asset_count_by_type[$maa_val['ASSET_TYPE']]++;
    }
    $asset_count_by_type['GENERAL'] = count($monthly_ahc_asset);
    $monthly_asset_info = array(
        'LIST'  => $monthly_asset,
        'COUNT' => $asset_count_by_type
        );
    return $monthly_asset_info;
}

function get_org_asset_external($all_childs_orgs, $year, $month, $day, $valid_asset_type) {
    /*
     * 1. Query all org ahc info of this month
     */
    $monthly_org_ahc_list         = monthly_org_ahc_list($year, $month, $day);
    $monthly_org_member_list_temp = $monthly_org_ahc_list['ahc_list'];
    $valid_asset_type_temp        = NULL;
    foreach($all_childs_orgs as $aco_key => $aco_val){
        if(isset($monthly_org_member_list_temp[$aco_val])){
            $monthly_org_member_list[$aco_val] = $monthly_org_member_list_temp[$aco_val];
        }
    }

    foreach ($valid_asset_type as $vg_key => $vg_val) {
        $valid_asset_type_temp = $valid_asset_type_temp . "'" . $vg_val . "',";
    }
    $valid_asset_type_string = substr($valid_asset_type_temp, 0, -1);
    /*
     * 2. Get each org member's asset
     * Assets will be included if match the following conditions:
     *      1. PC or Monitor or Notebook or Server (The type info will be included in the conatant $valid_asset_type)
     */
    $qry_all_asset = "SELECT ASSET_NO,ASSET_TYPE,CPU,MAC,MODEL,MEMORY,HD,CHECKINTIME,RETURNTIME,DEPRECIATION_END,PRICE,MONTHLY_DEPRECIATION,USER_ID,ADDITIONINFO FROM asset_info WHERE ASSET_TYPE IN ($valid_asset_type_string)";
    $rst_all_asset = $GLOBALS['db']->query($qry_all_asset);
    $all_asset_temp = $rst_all_asset->fetch_all(MYSQLI_ASSOC);
    foreach ($all_asset_temp as $aat_key => $aat_val) {
        $all_asset[$aat_val['USER_ID']][] = $aat_val;
    }
    foreach ($monthly_org_member_list as $moml_key => $moml_val) {
        foreach ($moml_val as $mvv_key => $mvv_val) {
            if (isset($all_asset[$mvv_val])) {
                foreach($all_asset[$mvv_val] as $aamv_key => $aamv_val){
                    $monthly_ahc_asset[$aamv_val['ASSET_NO']] = $aamv_val;
                }
            }
        }
    }
    $monthly_asset       = array();
    $asset_count_by_type = array();
    foreach ($monthly_ahc_asset as $maa_key => $maa_val) {
        $monthly_asset[$maa_val['ASSET_TYPE']][] = $maa_val;
        if(!isset($asset_count_by_type[$maa_val['ASSET_TYPE']])){
            $asset_count_by_type[$maa_val['ASSET_TYPE']] = 0;
        }
        $asset_count_by_type[$maa_val['ASSET_TYPE']]++;
    }
    $asset_count_by_type['GENERAL'] = count($monthly_ahc_asset);
    $monthly_asset_info = array(
        'LIST'  => $monthly_asset,
        'COUNT' => $asset_count_by_type
        );
    return $monthly_asset_info;
}

/*
 * Query all asset request to owner
 */

function request_to_owner($owner) {
    $qry_request_to_owner = "SELECT a.REQUEST_ID,a.REQUEST_TYPE,a.ASSET_NO,a.ASSET_TYPE,a.REQUESTTO_ID,b.USER_NAME TO_NAME,a.REQUESTOR_ID,c.USER_NAME R_NAME,a.REQUEST_DATE,a.REQUEST_STATUS,a.OPERATOR_ID,a.OPERATE_DATE,d.USER_NAME O_NAME FROM (((asset_request_info AS a LEFT JOIN user_info AS b ON a.REQUESTTO_ID=b.USER_ID) LEFT JOIN  user_info AS c ON a.REQUESTOR_ID=c.USER_ID) LEFT JOIN user_info AS d ON a.OPERATOR_ID=d.USER_ID) WHERE a.REQUEST_STATUS!='Approved' and a.REQUEST_STATUS!='Deny' AND a.REQUESTTO_ID='" . $owner . "' ORDER BY REQUEST_ID DESC;";
    $rst_request_to_onwer = $GLOBALS['db']->query($qry_request_to_owner);
    $request_to_owner = $rst_request_to_onwer->fetch_all(MYSQLI_ASSOC);
    return $request_to_owner;
}

/*
 * Search asset info through keywords
 */
function search_asset_info($asset_kewords) {
    $qry_asset_info = "SELECT ASSET_NO, ASSET_TYPE, MODEL,CPU,MEMORY,HD,MAC,ADDITIONINFO,USER_NAME,UI.USER_ID FROM asset_info AS AI, user_info AS UI WHERE AI.USER_ID = UI.USER_ID AND CONCAT(ASSET_NO,USER_NAME,MODEL) LIKE '%$asset_kewords%';";
    $rst_asset_info = $GLOBALS['db']->query($qry_asset_info);
    $asset_info = $rst_asset_info->fetch_all(MYSQLI_ASSOC);
    return $asset_info;
}

/*
 * Search asset history through asset request info
 */

function search_asset_history($asset_no) {
    $qry_asset_history = "SELECT REQUEST_ID,REQUESTOR_ID,REQUEST_TYPE,ASSET_NO,ASSET_TYPE,ASSET_MODEL,NUSER_ID,REQUESTTO_ID,REQUEST_DATE,REQUESTOR_ID,REQUEST_STATUS,OPERATOR_ID,OPERATE_DATE,`COMMENT` FROM asset_request_info WHERE ASSET_NO='$asset_no' ORDER BY REQUEST_ID DESC";
    $rst_asset_history = $GLOBALS['db']->query($qry_asset_history);
    $asset_history = $rst_asset_history->fetch_all(MYSQLI_ASSOC);
    return $asset_history;
}

/*
 * Push asset info into table
 */

function push_asset_info_into_table($asset_info, $available_emp, $record_count, $permission, $required_perm, $oid, $su_id, $gtype, $optype) {
    ?>
    <div id="div_table_total"><h4>Total:<?php echo $record_count; ?></h4></div>
    <table class="table table-striped table-condensed table-hover">
        <thead>
            <tr>
                <th>Asset No</th>
                <th>Type</th>
                <th>Model</th>
                <th>CPU</th>
                <th>Mem</th>
                <th>HDD</th>
                <th>Note</th>
                <th>Owner</th>
                <?php
                if ($permission >= $required_perm) {
                    echo "<th></th>";
                }
                ?>
            </tr>
        </thead>
        <?php
        foreach ($asset_info as $ai_key => $ai_val) {
            $short_addition_info = substr($ai_val['ADDITIONINFO'], 0, 30);
            ?>
            <tr>
                <td><?php echo $ai_val ['ASSET_NO']; ?></td>
                <td><?php echo $ai_val ['ASSET_TYPE']; ?></td>
                <td><?php echo $ai_val ['MODEL']; ?></td>
                <td><?php echo $ai_val ['CPU']; ?></td>
                <td><?php echo $ai_val ['MEMORY']; ?></td>
                <td><?php echo $ai_val ['HD']; ?></td>
                <td title="<?php echo $ai_val['ADDITIONINFO']; ?>" class="chinese"><?php echo $short_addition_info; ?></td>
                <td class="chinese"><?php echo $available_emp[$ai_val ['USER_ID']]; ?></td>
                <?php
                if ($permission >= $required_perm) {
                    $parm_string = $ai_val ['ASSET_NO'] . "@@@" . $ai_val ['ASSET_TYPE'] . "@@@" . $ai_val ['MODEL'] . "@@@" . $ai_val ['CPU'] . "@@@" . $ai_val ['MEMORY'] . "@@@" . $ai_val ['HD'] . "@@@" . $ai_val ['MAC'] . "@@@" . $ai_val ['ADDITIONINFO'];
                    $parm = base64_encode(urlencode($parm_string));
                    if ($ai_val ['ASSET_TYPE'] == 'PC') {
                        ?>
                        <td>
                            <a title='Edit' href='../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&optype=<?php echo base64_encode($optype); ?>&url=<?php echo base64_encode("asset/asset_update.php"); ?>&gtype=<?php echo base64_encode($gtype); ?>&parm=<?php echo $parm; ?>' ><img width='16' height='16' src='../../../lib/image/icons/edit.png'/></a>&nbsp;&nbsp;
                            <a title='Transfer' href='../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&optype=<?php echo base64_encode($optype); ?>&url=<?php echo base64_encode("asset/asset_transfer.php"); ?>&gtype=<?php echo base64_encode($gtype); ?>&parm=<?php echo $parm; ?>' ><img width='16' height='16' src='../../../lib/image/icons/transfer.png'/></a>
                        </td>
                    <?php } else { ?>
                        <td>
                            <a title='Transfer' href='../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&optype=<?php echo base64_encode($optype); ?>&url=<?php echo base64_encode("asset/asset_transfer.php"); ?>&gtype=<?php echo base64_encode($gtype); ?>&parm=<?php echo $parm; ?>' ><img width='16' height='16' src='../../../lib/image/icons/transfer.png'/></a>
                        </td>
                        <?php
                    }
                }
                ?>
            </tr>
            <?php
        }
        ?>
    </table>      
    <?php
}

/*
 * Push asset info into table
 */
/**
 * [push_asset_info_into_table_subscribe Display asset for subscribed project]
 * @param  [type] $asset_info    [description]
 * @param  [type] $available_emp [description]
 * @param  [type] $user_type     [description]
 * @param  [type] $record_count  [description]
 * @return [type]                [description]
 */
function push_asset_info_into_table_subscribe($asset_info, $available_emp, $user_type, $record_count) {
    ?>
    <div id="div_table_total"><h4>Total:<?php echo $record_count; ?></h4></div>
    <table class="table table-striped table-condensed table-hover sortable">
        <thead>
            <tr>
                <th>Asset No</th>
                <th>Type</th>
                <th>Model</th>
                <th>CPU</th>
                <th>Mem</th>
                <th>HDD</th>
                <?php if($user_type != 4){ ?>
                <th>Note</th>
                    <?php }
                ?>
                <th>Owner</th>
            </tr>
        </thead>
        <?php
        foreach ($asset_info as $ai_key => $ai_val) {
            $short_addition_info = substr($ai_val['ADDITIONINFO'], 0, 30);
            ?>
            <tr>
                <td><?php echo $ai_val ['ASSET_NO']; ?></td>
                <td><?php echo $ai_val ['ASSET_TYPE']; ?></td>
                <td><?php echo $ai_val ['MODEL']; ?></td>
                <td><?php echo $ai_val ['CPU']; ?></td>
                <td><?php echo $ai_val ['MEMORY']; ?></td>
                <td><?php echo $ai_val ['HD']; ?></td>
                <?php
                if($user_type == 4){ ?>
                    <td class="chinese">
                    <?php
                        echo isset($available_emp[$ai_val['USER_ID']]['OWNER'])?$available_emp[$ai_val ['USER_ID']]['OWNER']:$available_emp[$ai_val ['USER_ID']]['NICK_NAME'];
                    ?>
                    </td>
                <?php 
                }else{ ?>
                    <td title="<?php echo $ai_val['ADDITIONINFO']; ?>" class="chinese"><?php echo $short_addition_info; ?></td>
                    <td class='chinese'>
                    <?php
                        echo $available_emp[$ai_val ['USER_ID']]['USER_NAME'];
                    ?>
                    </td>
                <?php }
                ?>
            </tr>
            <?php
        }
        ?>
    </table>      
    <?php
}

/*
 * Push the search result of asset into table
 */

function push_asset_search_result_into_table($asset_info, $record_count, $permission, $required_perm, $oid, $su_id, $gtype, $optype) {
    ?>
    <div id="div_table_total"><h4><?php echo $record_count; ?></h4></div>
    <table class="table table-striped table-condensed table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Asset No</th>
                <th>Type</th>
                <th>Model</th>
                <th>CPU</th>
                <th>Mem</th>
                <th>HDD</th>
                <th>Note</th>
                <th>Owner</th>
                <?php
                if ($permission >= $required_perm) {
                    echo "<th></th>";
                }
                ?>
            </tr>
        </thead>
        <?php
        foreach ($asset_info as $ai_key => $ai_val) {
            $short_addition_info = substr($ai_val['ADDITIONINFO'], 0, 30);
            ?>
            <tr>
                <td><?php echo ($ai_key + 1); ?></td>
                <td><?php echo $ai_val ['ASSET_NO']; ?></td>
                <td><?php echo $ai_val ['ASSET_TYPE']; ?></td>
                <td><?php echo $ai_val ['MODEL']; ?></td>
                <td><?php echo $ai_val ['CPU']; ?></td>
                <td><?php echo $ai_val ['MEMORY']; ?></td>
                <td><?php echo $ai_val ['HD']; ?></td>
                <td title="<?php echo $ai_val['ADDITIONINFO']; ?>" class="chinese"><?php echo $short_addition_info; ?></td>
                <td class="chinese"><?php echo $ai_val ['USER_NAME']; ?></td>
                <?php
                if ($permission >= $required_perm) {
                    $parm_string = $ai_val ['ASSET_NO'] . "@@@" . $ai_val ['ASSET_TYPE'] . "@@@" . $ai_val ['MODEL'] . "@@@" . $ai_val ['CPU'] . "@@@" . $ai_val ['MEMORY'] . "@@@" . $ai_val ['HD'] . "@@@" . $ai_val ['MAC'] . "@@@" . $ai_val ['ADDITIONINFO'];
                    $parm = base64_encode(urlencode($parm_string));
                    if ($ai_val ['ASSET_TYPE'] == 'PC') {
                        ?>
                        <td>
                            <a title='Edit' href='../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&optype=<?php echo base64_encode($optype); ?>&url=<?php echo base64_encode("asset/asset_update.php"); ?>&gtype=<?php echo base64_encode($gtype); ?>&parm=<?php echo $parm; ?>' ><img width='16' height='16' src='../../../lib/image/icons/edit.png'/></a>&nbsp;&nbsp;
                            <a title='Transfer' href='../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&optype=<?php echo base64_encode($optype); ?>&url=<?php echo base64_encode("asset/asset_transfer.php"); ?>&gtype=<?php echo base64_encode($gtype); ?>&parm=<?php echo $parm; ?>' ><img width='16' height='16' src='../../../lib/image/icons/transfer.png'/></a>
                        </td>
                    <?php } else { ?>
                        <td>
                            <a title='Transfer' href='../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&optype=<?php echo base64_encode($optype); ?>&url=<?php echo base64_encode("asset/asset_transfer.php"); ?>&gtype=<?php echo base64_encode($gtype); ?>&parm=<?php echo $parm; ?>' ><img width='16' height='16' src='../../../lib/image/icons/transfer.png'/></a>
                        </td>
                        <?php
                    }
                }
                ?>
            </tr>
            <?php
        }
        ?>
    </table>      
    <?php
}

/*
 * Push the search result of asset into table
 */

function push_asset_search_result_into_table_subscribe($asset_info, $available_emp, $record_count) {
    ?>
    <div id="div_table_total"><h4><?php echo $record_count; ?></h4></div>
    <table class="table table-striped table-condensed table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Asset No</th>
                <th>Type</th>
                <th>Model</th>
                <th>CPU</th>
                <th>Mem</th>
                <th>HDD</th>
                <?php if($_SESSION['type'] != 4){ ?>
                <th>Note</th>
                    <?php }
                ?>
                <th>Owner</th>
            </tr>
        </thead>
        <?php
        foreach ($asset_info as $ai_key => $ai_val) {
            $short_addition_info = substr($ai_val['ADDITIONINFO'], 0, 30);
            ?>
            <tr>
                <td><?php echo ($ai_key + 1); ?></td>
                <td><?php echo $ai_val ['ASSET_NO']; ?></td>
                <td><?php echo $ai_val ['ASSET_TYPE']; ?></td>
                <td><?php echo $ai_val ['MODEL']; ?></td>
                <td><?php echo $ai_val ['CPU']; ?></td>
                <td><?php echo $ai_val ['MEMORY']; ?></td>
                <td><?php echo $ai_val ['HD']; ?></td>
                <?php
                if($_SESSION['type'] == 4){ ?>
                    <td class="chinese">
                    <?php
                        echo isset($available_emp[$ai_val['USER_ID']]['OWNER'])?$available_emp[$ai_val ['USER_ID']]['OWNER']:$available_emp[$ai_val ['USER_ID']]['NICK_NAME'];
                    ?>
                    </td>
                <?php 
                }else{ ?>
                    <td title="<?php echo $ai_val['ADDITIONINFO']; ?>" class="chinese"><?php echo $short_addition_info; ?></td>
                    <td class='chinese'>
                    <?php
                        echo $available_emp[$ai_val ['USER_ID']]['USER_NAME'];
                    ?>
                    </td>
                <?php }
                ?>
            </tr>
            <?php
        }
        ?>
    </table>      
    <?php
}


/*
 * Check asset status (requested or not)
 */
function asset_status($asset_no) {
    $check_as     = "SELECT REQUEST_ID FROM asset_info WHERE ASSET_NO='" . $asset_no . "';";
    $rst_check_as = $GLOBALS['db']->query($check_as);
    $row_as       = $rst_check_as->fetch_assoc();
    if ($row_as['REQUEST_ID'] != NULL) {
        error('101');
        exit();
    }
}

/*
 * Query the requesto's owner (for transfer )
 * In this situation, the approver is the manager of the requesto
 */

function requesto_approver($db, $asset_no) {
    $qry_requesto = "SELECT UI.USER_ID,UI.USER_NAME FROM asset_info AS AI, user_info AS UI, user_org_group AS UOG, org_info AS OI, org_info as OI_FATHER WHERE AI.USER_ID=UOG.FK_USER_ID AND UOG.FK_ORG_ID=OI.ORG_ID AND OI.FATHER=OI_FATHER.ORG_ID AND UI.USER_ID=OI_FATHER.OWNER_ID AND AI.ASSET_NO='$asset_no';";
    $rst_to = $db->query($qry_requesto);
    $requesto_approver = $rst_to->fetch_all(MYSQLI_ASSOC);
    return $requesto_approver;
}

/**
 * Author: CC
 * Modified: 08/10/2013
 * [requestor_approver Query the requesto's owner, In this situation, the approver is the manager of the current requestor]
 * @param  [int] $requestor [requesto id]
 * @return [type]            [description]
 * Modified: 08/19/2013     CC
 * Make the direct resource org owner as the approver
 */
function requestor_approver($requestor) {
    //$qry_requestor = "SELECT ui.USER_ID,ui.USER_NAME FROM user_info AS ui, user_org_group AS ug, org_info AS i , org_info as i_father WHERE ug.FK_ORG_ID=i.ID AND i.FATHER=i_father.ID AND i_father.OWNER_ID=ui.USER_ID AND ug.FK_USER_ID='$requestor';";
    $qry_requestor = "SELECT ui.USER_ID,ui.USER_NAME FROM user_info AS ui, user_org_group AS ug, org_info AS i WHERE ug.FK_ORG_ID=i.ID AND i.OWNER_ID=ui.USER_ID AND ug.FK_USER_ID='$requestor';";
    $rst_tor = $GLOBALS['db']->query($qry_requestor);
    $requestor_approver = $rst_tor->fetch_all(MYSQLI_ASSOC);
    return $requestor_approver;
}

/*
 * Purpose: Caculate the max request_id of table asset_request
 */

function max_request_id($date) {
    $qry_max_id = "SELECT MAX(REQUEST_ID) AS MAX_ID FROM asset_request_info WHERE REQUEST_ID LIKE '" . $date . "%';";
    $rst_max_id = $GLOBALS['db']->query($qry_max_id);
    $row_max_id = $rst_max_id->fetch_assoc();
    if ($row_max_id ['MAX_ID'] == 0) {
        $request_id = str_pad($date, 12, 0, STR_PAD_RIGHT) + 1;
    } else {
        $request_id = $row_max_id ['MAX_ID'] + 1;
    }
    return $request_id;
}

/*
 * Insert a new record into table (asset_request)
 */

function new_asset_request($request_param) {
    $qry_new_request = "INSERT INTO asset_request_info (REQUEST_ID,REQUEST_TYPE,ASSET_NO,ASSET_TYPE,ASSET_MODEL,NASSET_CPU,NASSET_MEMORY,NASSET_HD,NASSET_MAC,NADDITIONINFO,NUSER_ID,REQUESTTO_ID,REQUEST_DATE,REQUESTOR_ID,REQUEST_STATUS,COMMENT) 
VALUES ('" . $request_param['request_id'] . "','" . $request_param['type'] . "','" . $request_param['asset_no'] . "','" . $request_param['asset_type'] . "',NULLIF('" . $request_param['asset_model'] . "',''),NULLIF('" . $request_param['asset_cpu'] . "',''),NULLIF('" . $request_param['asset_mem'] . "',''),NULLIF('" . $request_param['asset_hd'] . "',''),NULLIF('" . $request_param['asset_mac'] . "',''),NULLIF('" . $request_param['asset_info'] . "',''),NULLIF('" . $request_param['requesto_id'] . "',''),NULLIF('" . $request_param['approver'] . "',''),NULLIF('" . $request_param['request_date'] . "',''),NULLIF('" . $request_param['requestor_id'] . "',''),'" . $request_param['request_status'] . "',NULLIF('" . $request_param['comment'] . "',''));";
    $rst_new_request = $GLOBALS['db']->query($qry_new_request);
}

/*
 * Update asset_info, set upate the value of column request_id to $request_id
 */

function update_asset_info_qid($request_id, $asset_no) {
    $qry_ai_qid = "UPDATE asset_info SET REQUEST_ID='" . $request_id . "' WHERE ASSET_NO='" . $asset_no . "';";
    $rst_ai_qi = $GLOBALS['db']->query($qry_ai_qid);
}

/*
 * Push asset history info into table
 */

function push_asset_history_into_table($asset_history, $emp, $asset_no) {
    ?>
    <div id="div_table_total"><h4><?php echo $asset_no; ?></h4></div>
    <table class="table table-striped">
        <thead>
        <th>ID</th>
        <th>Type</th>
        <th colspan="2">Asset Info</th>
        <th>Target</th>
        <th colspan="2">Requestor/Date</th>
        <th>Request To</th>
        <th>Status</th>
        <th colspan="2">Operator/Date</th>
        <th>Comment</th>
    </thead>
    <?php
    foreach ($asset_history as $ah_key => $as_val) {
        $short_comment = substr($as_val['COMMENT'], 0, 30);
        $short_request_date = substr($as_val['REQUEST_DATE'], 0, 10);
        $short_operate_date = substr($as_val['OPERATE_DATE'], 0, 10);
        ?>
        <tr>
            <td><?php echo $as_val['REQUEST_ID']; ?></td>
            <td><?php echo $as_val['REQUEST_TYPE']; ?></td>
            <td><?php echo $as_val['ASSET_TYPE']; ?></td>
            <td><?php echo $as_val['ASSET_MODEL']; ?></td>
            <td class="chinese"><?php echo (isset($emp[$as_val['NUSER_ID']]['USER_NAME'])) ? $emp[$as_val['NUSER_ID']]['USER_NAME'] : ""; ?></td>
            <td class="chinese"><?php echo (isset($emp[$as_val['REQUESTOR_ID']]['USER_NAME']) ? $emp[$as_val['REQUESTOR_ID']]['USER_NAME'] : ""); ?></td>
            <td><?php echo date("m/d/y",strtotime($short_request_date)); ?></td>
            <td class="chinese"><?php echo (isset($emp[$as_val['REQUESTTO_ID']]['USER_NAME']) ? $emp[$as_val['REQUESTTO_ID']]['USER_NAME'] : ""); ?></td>
            <td><?php echo $as_val['REQUEST_STATUS']; ?></td>
            <td class="chinese"><?php echo (isset($emp[$as_val['OPERATOR_ID']]['USER_NAME']) ? $emp[$as_val['OPERATOR_ID']]['USER_NAME'] : ""); ?></td>
            <td><?php echo date("m/d/y",strtotime($short_operate_date)); ?></td>
            <td class="chinese" title="<?php echo $as_val['COMMENT']; ?>"><?php echo $short_comment; ?></td>
        </tr>
        <?php
    }
    ?>
    </table>
    <?php
}

/*
 * Arrange asset info by asset type into array
 * e.g.
 * array(
 * 'PC'=>,
 * 'Monitor'=>
 * )
 */

function arrange_asset($asset_info) {
    $asset_by_type = array();
    //dump($asset_info);
    foreach ($asset_info as $ai_key => $ai_val) {
        foreach ($GLOBALS['ASSET_TYPE'] as $at_key => $at_val) {
            if ($ai_val['ASSET_TYPE'] == $at_val[0]) {
                $asset_by_type[$at_val[0]][] = $ai_val;
            }
        }
    }
    return $asset_by_type;
}

/*
 * Seperate asset info by owner
 * idle:XXX
 * Return 2F: XXX
 * Suspicious: XXX
 * IT: XXX
 * In use : XXX
 */

function separate_asset_by_owner($all_asset_info) {
    $asset_by_owner = array();
    foreach ($all_asset_info as $aai_key => $aai_val) {
        switch ($aai_val['USER_ID']) {
            case 1:
                $asset_by_owner['Idle'][] = $aai_val;
                break;
            case 2:
                $asset_by_owner['Return_2F'][] = $aai_val;
                break;
            case 3:
                $asset_by_owner['Suspicious'][] = $aai_val;
                break;
            case 4:
                $asset_by_owner['IT'][] = $aai_val;
                break;
            default:
                $asset_by_owner['In_Use'][] = $aai_val;
                break;
        }
    }
    return $asset_by_owner;
}

/*
 * Push asset request info into table
 */

function push_asset_request_into_table($request_info, $record_count, $oid, $su_id, $optype, $gtype, $permission, $required_perm) {
    ?>
    <div id="div_table_total"><h4>Total:<?php echo $record_count; ?></h4></div>
    <table class="table table-striped table-condensed table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Asset No</th>
                <th>Type</th>
                <th>Requestor</th>
                <th>Request To</th>
                <th>Request</th>
                <th>Status</th>
                <th>Operator</th>
                <th>Operate</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($request_info as $re_key => $re_val) {
                $short_request_date = substr($re_val ['REQUEST_DATE'], 0, 10);
                $short_operate_date = substr($re_val ['OPERATE_DATE'], 0, 10);
                ?>
                <tr>
                    <?php
                    $parm_string = $re_val ['REQUEST_ID'];
                    if ($re_val ['REQUEST_TYPE'] == 'Update') {
                        ?>
                        <!-- if the user is a tester, it will redirect to the detail page without any operate privilege -->
                        <td>
                            <a href='../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&optype=<?php echo base64_encode($optype); ?>&url=<?php echo base64_encode("asset/asset_request_detail_u.php"); ?>&gtype=<?php echo base64_encode($gtype); ?>&parm=<?php echo base64_encode($parm_string); ?>#tabs_ops' ><?php echo $re_val ['REQUEST_ID'] ?></a>
                        </td>
                    <?php } else { ?>
                        <td>
                            <a href='../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&optype=<?php echo base64_encode($optype); ?>&url=<?php echo base64_encode("asset/asset_request_detail_dt.php"); ?>&gtype=<?php echo base64_encode($gtype); ?>&parm=<?php echo base64_encode($parm_string); ?>#tabs_ops' ><?php echo $re_val ['REQUEST_ID'] ?></a>
                        </td>
                    <?php } ?>
                    <td><?php echo $re_val ['REQUEST_TYPE'] ?></td>
                    <td><?php echo $re_val ['ASSET_NO'] ?></td>
                    <td><?php echo $re_val ['ASSET_TYPE'] ?></td>
                    <td class="chinese"><?php echo $re_val ['R_NAME'] ?></td>
                    <td class="chinese"><?php echo $re_val ['TO_NAME'] ?></td>
                    <td title="<?php echo $re_val ['REQUEST_DATE'] ?>"><?php echo date("m/d/y",strtotime($short_request_date)); ?></td>
                    <td><?php echo $re_val ['REQUEST_STATUS'] ?></td>
                    <td class="chinese"><?php echo $re_val ['O_NAME'] ?></td>
                    <td title="<?php echo $re_val ['OPERATE_DATE'] ?>"><?php echo ($short_operate_date!=NULL)?date("m/d/y",strtotime($short_operate_date)):""; ?></td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
    <?php
}

/*
 * Push asset request  to owner info into table
 */

function push_request_owner_into_table($request_to_owner, $record_count, $oid, $su_id, $gtype, $optype) {
    ?>
    <div id="div_table_total"><h4>Total:<?php echo $record_count; ?></h4></div>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Asset No</th>
                <th>Type</th>
                <th>Requestor</th>
                <th>Request To</th>
                <th>Request Date</th>
                <th>Status</th>
                <th>Operator</th>
                <th>Operate Date</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($request_to_owner as $rtm_key => $rtm_val) {
                $short_request_date = substr($rtm_val ['REQUEST_DATE'], 0, 10);
                $short_operate_date = substr($rtm_val ['OPERATE_DATE'], 0, 10);
                ?>
                <tr>
                    <?php
                    $parm_string = $rtm_val ['REQUEST_ID'];
                    if ($rtm_val ['REQUEST_TYPE'] == 'Update') {
                        ?>
                        <td>
                            <a title='Return' href='../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&optype=<?php echo base64_encode($optype); ?>&url=<?php echo base64_encode("asset/asset_request_operate_u.php"); ?>&gtype=<?php echo base64_encode($gtype); ?>&parm=<?php echo base64_encode($parm_string); ?>' ><?php echo $rtm_val ['REQUEST_ID'] ?></a>
                        </td>
                    <?php } else {
                        ?>
                        <td> 
                            <a title='Return' href='../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&optype=<?php echo base64_encode($optype); ?>&url=<?php echo base64_encode("asset/asset_request_operate_dt.php"); ?>&gtype=<?php echo base64_encode($gtype); ?>&parm=<?php echo base64_encode($parm_string); ?>' ><?php echo $rtm_val ['REQUEST_ID'] ?></a>
                        </td>
                    <?php } ?>
                    <td><?php echo $rtm_val['REQUEST_TYPE'] ?></td>
                    <td><?php echo $rtm_val['ASSET_NO'] ?></td>
                    <td><?php echo $rtm_val['ASSET_TYPE'] ?></td>
                    <td class='chinese'><?php echo $rtm_val['R_NAME'] ?></td>
                    <td class="chinese"><?php echo $rtm_val ['TO_NAME'] ?></td>
                    <td title="<?php echo $rtm_val ['REQUEST_DATE'] ?>"><?php echo $short_request_date ?></td>
                    <td><?php echo $rtm_val['REQUEST_STATUS'] ?></td>
                    <td class='chinese'><?php echo $rtm_val['O_NAME'] ?></td>
                    <td title="<?php echo $rtm_val ['OPERATE_DATE'] ?>"><?php echo $short_operate_date ?></td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
    <?php
}
?>
