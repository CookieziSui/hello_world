<?php
require_once __DIR__ . '../../../debug.php';
require_once __DIR__ . '../../../mysql.php';
require_once __DIR__ . '/../../lib/inc/constant_device.php';

/*
 * Query available orgs
 */

function query_available_orgs() {
    $query_available_orgs = "SELECT ID,NAME FROM org_info WHERE `STATUS`<>2";
    $rst_info = $GLOBALS['db']->query($query_available_orgs);
    $available_orgs_temp = $rst_info->fetch_all(MYSQLI_ASSOC);
    foreach ($available_orgs_temp as $aot_key => $aot_value) {
        $available_orgs[$aot_value['NAME']] = $aot_value['ID'];
    }
    return $available_orgs;
}

/*
 * Query the information of user 
 */

function qry_user_info() {
    $query_available_employees = "SELECT USER_ID,HR_ID,USER_NAME,EMAIL FROM user_info WHERE USER_ID>4 AND EMPLOYEE_END IS NULL AND HR_ID != '' AND HR_ID IS NOT NULL";
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

/*
 * Query the rest of information about request to the maintainer of device
 */

function all_requests_to_maintainer($device_id, $maintainer) {
    $qry_requests_to_maintainer = "SELECT di.ID,dbi.BORROW_ID FROM device_info AS di,device_borrow_info AS dbi WHERE di.ID=dbi.DEVICE_ID 
        AND di.ID='$device_id'
        AND dbi.RETURN_DATE IS NULL 
        AND dbi.HOLDER IS NULL
        AND dbi.REQUESTTO_ID='$maintainer' 
        AND dbi.REQUEST_TYPE=1 
        AND dbi.REQUEST_STATUS = 1";
    $rst_qry_info = $GLOBALS['db']->query($qry_requests_to_maintainer);
    $rst_info = $rst_qry_info->fetch_all(MYSQLI_ASSOC);
    //dump($qry_requests_to_maintainer);
    return $rst_info;
}

/*
 * The rest of information about request to the maintainer of device
 * update the requestto_id column
 */

function update_requestto_id_maintainer($device_id, $requestor_id) {
    $update_info = "UPDATE device_borrow_info AS dbi SET dbi.REQUESTTO_ID = '$requestor_id' WHERE DEVICE_ID = '$device_id' AND dbi.RETURN_DATE is NULL AND dbi.REQUEST_TYPE = 1 AND dbi.REQUEST_STATUS = 1";
    $rst_qry = $GLOBALS['db']->query($update_info);
}

/*
 * query all borrow_info
 */
function qry_all_borrow_info(){
    $qry_borrow_info = "SELECT a.BORROW_ID,a.DEVICE_ID AS ID,a.COUNT AS NUMBER,c.OS,c.CONFIGURATION,c.COUNT,c.DEVICE_NO,c.CATEGORY,c.MANUFACTURER,c.MODEL,c.DEVICE_TYPE,c.`OWNER`,c.PURCHASER AS PURCHASER_ID,
        e.NAME AS PURCHASER,c.COMMENT,a.COMMENTS AS ADDINFO,c.MAINTAINER AS MAINTAINER_ID,f.USER_NAME AS MAINTAINER,NULLIF(a.HOLDER,NULL) AS HOLDER_ID,d.USER_NAME AS HOLDER,c.`STATUS`,
        a.BORROW_DATE,NULLIF(a.RETURN_DATE,NULL) AS RETURN_DATE,a.REQUESTOR_ID,b.USER_NAME AS REQUESTOR,a.REQUESTTO_ID,a.REQUEST_STATUS,a.REQUEST_TYPE,c.CHECKINTIME,c.LOCATION,
        NULLIF(a.RECIEVER_ID,NULL) AS RECIEVER_ID,a.TRANSFER_DATE
        FROM (((((device_borrow_info AS a LEFT JOIN user_info AS b ON a.REQUESTOR_ID=b.USER_ID)  
        LEFT JOIN device_info as c ON a.DEVICE_ID=c.ID)
        LEFT JOIN user_info AS d ON a.HOLDER=d.USER_ID)
        LEFT JOIN org_info AS e ON e.ID=c.PURCHASER AND e.`STATUS`<>2) 
        LEFT JOIN user_info AS f ON c.MAINTAINER=f.USER_ID)
        WHERE a.RETURN_DATE IS NULL
        ORDER BY c.DEVICE_TYPE DESC,a.BORROW_ID DESC";
    $rst_qry = $GLOBALS['db']->query($qry_borrow_info);
    $request_to_holder = $rst_qry->fetch_all(MYSQLI_ASSOC);
    if (!empty($request_to_holder)) {
        return $request_to_holder;
    } else {
        return NULL;
    }
}

/*
 * Query request status
 */

function qry_request_status($request_type, $requestor_id, $device_id) {
    $qry_info = "SELECT dbi.REQUEST_STATUS FROM device_borrow_info AS dbi,device_info AS di 
        WHERE dbi.DEVICE_ID = di.ID AND dbi.REQUEST_TYPE = '$request_type' AND dbi.DEVICE_ID = '$device_id' ";
    if(!empty($requestor_id)){
        $where_info =$qry_info."AND dbi.REQUESTOR_ID = '$requestor_id' ORDER BY dbi.BORROW_ID DESC" ;
    }else{
        $where_info =$qry_info."ORDER BY dbi.BORROW_ID DESC" ;
    }
    $rst_info = $GLOBALS['db']->query($where_info);
    $rst_borrow_id = $rst_info->fetch_assoc();
    return $rst_borrow_id;
}

/*
 * Query the basic information of the device
 */

function device_base_info() {
    $qry_device_info = "SELECT a.ID,a.DEVICE_NO,a.CATEGORY,a.MANUFACTURER,a.MODEL,a.DEVICE_TYPE,a.COUNT,a.`STATUS`,a.`OWNER`,a.PURCHASER,a.CHECKINTIME,a.LOCATION,a.`COMMENT`
            FROM device_info AS a";
    $rst_qry = $GLOBALS['db']->query($qry_device_info);
    $device_base_info = $rst_qry->fetch_all(MYSQLI_ASSOC);
    return $device_base_info;
}

/*
 * Purpose: Caculate the max device_no of table device_info
 */

function max_device_no($device_pattern,$device_type) {
    $qry_max_id = "SELECT MAX(SUBSTRING(DEVICE_NO,9,3)) AS MAX_ID FROM device_info WHERE DEVICE_NO LIKE '" .$device_pattern. "%';";
    $rst_max_id = $GLOBALS['db']->query($qry_max_id);
    $row_max_id = $rst_max_id->fetch_assoc();
    if($device_type == 1){
        $device_no = $device_pattern.str_pad($row_max_id ['MAX_ID']+1, 3, 0, STR_PAD_LEFT).'D';  
    }else{
        $device_no = $device_pattern.str_pad($row_max_id ['MAX_ID']+1, 3, 0, STR_PAD_LEFT).'C';  
    }
    return $device_no;
}

/*
 * Query all devices info(including the basic info and all borrow records)
 */

function device_info($scope,$device_type,$device_keyword) {
    $query_device_info = "SELECT a.ID,a.DEVICE_NO,NULLIF(c.BORROW_ID,NULL) AS BORROW_ID,c.COUNT AS NUMBER,a.CATEGORY,a.MANUFACTURER,a.MODEL,a.OS,a.CONFIGURATION,a.DEVICE_TYPE,a.VALUE,a.INVOICE,
            a.USABILITY,a.COUNT,a.`STATUS`,a.`OWNER`,a.PURCHASER AS PURCHASER_ID,f.NAME AS PURCHASER,a.CHECKINTIME,a.LOCATION,c.REQUESTOR_ID,e.USER_NAME AS REQUESTOR,NULLIF(c.HOLDER,NULL) AS HOLDER_ID,
            NULLIF(d.USER_NAME,NULL) AS HOLDER,a.MAINTAINER AS MAINTAINER_ID,b.USER_NAME AS MAINTAINER,a.COMMENT,NULLIF(c.COMMENTS,NULL) AS ADDINFO,c.BORROW_DATE,c.RETURN_DATE,
            ca.`OWNER` AS FAKE_BORROWER,d.NICK_NAME
            FROM ((((((device_info AS a LEFT JOIN user_info AS b ON a.MAINTAINER=b.USER_ID) 
            LEFT JOIN  device_borrow_info AS c ON a.ID=c.DEVICE_ID  AND c.RETURN_DATE IS NULL AND c.HOLDER IS NOT NULL)
            LEFT JOIN user_info as d ON c.HOLDER=d.USER_ID)
            LEFT JOIN user_info AS e ON c.REQUESTOR_ID=e.USER_ID)
            LEFT JOIN customer_account AS ca ON ca.MAINTAINER = c.HOLDER)
            LEFT JOIN org_info AS f ON a.PURCHASER=f.ID AND f.`STATUS`<>2) 
            WHERE a.DEVICE_TYPE ='$device_type'";
    if($scope == "internal"){
        if(!empty($device_keyword)){
            $where_info ="AND CONCAT(a.DEVICE_NO,a.CATEGORY,a.MANUFACTURER,b.USER_NAME) LIKE '%$device_keyword%' ORDER BY a.`STATUS` DESC,a.DEVICE_NO ASC ";
        }else{
            $where_info ="ORDER BY a.`STATUS` DESC,a.DEVICE_NO ASC ";
        }
    }else{
        if(!empty($device_keyword)){
            $where_info ="AND CONCAT(a.CATEGORY) LIKE '%$device_keyword%' AND a.OWNER='3' ORDER BY a.`STATUS` DESC,a.DEVICE_NO ASC ";
        }else{
            $where_info ="AND a.OWNER='3' ORDER BY a.`STATUS` DESC,a.DEVICE_NO ASC ";
        }
    }
    $qry_device_info = $query_device_info.$where_info;
    $rst_info = $GLOBALS['db']->query($qry_device_info);
    $device_info_temp = $rst_info->fetch_all(MYSQLI_ASSOC);
    $device_info = array();
    foreach ($device_info_temp as $di_key => $di_value) {
        $device_info[$di_value['ID']] = $di_value;
    }
    return $device_info;
}

function query_all_device_history() {
    $search_info = "SELECT di.ID,di.DEVICE_NO,di.CATEGORY,di.MODEL,di.DEVICE_TYPE,di.`STATUS`,t_ui.USER_NAME AS REQUESTOR,dbi.REQUEST_TYPE,dbi.REQUEST_STATUS,dbi.COUNT,
        s_ui.USER_NAME AS HOLDER,f_ui.USER_NAME AS MAINTAINER,fo_ui.USER_NAME AS RECIEVER,dbi.TRANSFER_DATE,dbi.BORROW_DATE,dbi.RETURN_DATE,dbi.OPERATE_DATE,dbi.COMMENTS
        FROM (((((device_info AS di LEFT JOIN device_borrow_info AS dbi ON dbi.DEVICE_ID=di.ID)
        LEFT JOIN user_info AS f_ui ON f_ui.USER_ID=di.MAINTAINER)
        LEFT JOIN user_info as s_ui ON dbi.HOLDER=s_ui.USER_ID)
        LEFT JOIN user_info AS t_ui ON dbi.REQUESTOR_ID=t_ui.USER_ID)
        LEFT JOIN user_info AS fo_ui ON fo_ui.USER_ID=dbi.RECIEVER_ID)
        ORDER BY di.`STATUS` DESC,di.DEVICE_NO ASC ";
    $qry_info = $GLOBALS['db']->query($search_info);
    $rst_info = $qry_info->fetch_all(MYSQLI_ASSOC);
    $device_info = array();
    foreach ($rst_info as $di_key => $di_value) {
        $device_info[$di_value['ID']][] = $di_value;
    }
    return $device_info;
}
/*
 * Search device info through device no
 */

function search_device_history($device_no) {
    $search_info = "SELECT di.DEVICE_NO,di.CATEGORY,di.MODEL,di.DEVICE_TYPE,di.`STATUS`,t_ui.USER_NAME AS REQUESTOR,dbi.REQUEST_TYPE,dbi.REQUEST_STATUS,dbi.COUNT,
        s_ui.USER_NAME AS HOLDER,f_ui.USER_NAME AS MAINTAINER,fo_ui.USER_NAME AS RECIEVER,dbi.TRANSFER_DATE,dbi.BORROW_DATE,dbi.RETURN_DATE,dbi.OPERATE_DATE,dbi.COMMENTS
        FROM (((((device_info AS di LEFT JOIN device_borrow_info AS dbi ON dbi.DEVICE_ID=di.ID)
        LEFT JOIN user_info AS f_ui ON f_ui.USER_ID=di.MAINTAINER)
        LEFT JOIN user_info as s_ui ON dbi.HOLDER=s_ui.USER_ID)
        LEFT JOIN user_info AS t_ui ON dbi.REQUESTOR_ID=t_ui.USER_ID)
        LEFT JOIN user_info AS fo_ui ON fo_ui.USER_ID=dbi.RECIEVER_ID)
        WHERE di.DEVICE_NO = '$device_no'
        ORDER BY dbi.BORROW_DATE DESC";
    $qry_info = $GLOBALS['db']->query($search_info);
    $rst_info = $qry_info->fetch_all(MYSQLI_ASSOC);
    return $rst_info;
}

/*
 * Display the holder of all devices
 */

function push_my_devices_info_into_table($my_devices_info, $request_to_holder, $DEVICE_ADMIN, $user_id) {
    ?>
    <table class="table table-condensed table-striped table-hover">
        <thead>
            <tr>
                <th>Device No</th>
                <th>Category</th>
                <th colspan="3">Manufacture/OS/Config</th>
                <th>Model</th>
                <th>Type</th>
                <th>Count</th>
                <th>Maintainer</th>
                <th>Comments</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $j=0;
            if(!empty($request_to_holder)){
                foreach($request_to_holder as $rth_key => $rth_val){
                    $short_model = substr($rth_val['MODEL'], 0,20);
                    $comments = substr($rth_val['COMMENT'], 0, 15);
                    ?>
                     <tr>
                        <td><?php echo $rth_val['DEVICE_NO']; ?></td>
                        <td><?php echo $rth_val['CATEGORY']; ?></td>
                        <td><?php echo $rth_val['MANUFACTURER']; ?></td>
                        <td><?php echo $rth_val['OS']; ?></td>
                        <td><?php echo $rth_val['CONFIGURATION']; ?></td>
                        <td title="<?php echo htmlspecialchars($rth_val['MODEL'],ENT_QUOTES); ?>"><?php echo $short_model; ?></td>
                        <td><?php echo ($rth_val['DEVICE_TYPE'] == 1) ? "Device" : "Consumable"; ?></td>
                        <td><?php echo ($rth_val['DEVICE_TYPE'] == 1)?"1":$rth_val['NUMBER'];?></td>
                        <td class="chinese"><?php echo $rth_val['MAINTAINER']; ?></td>
                        <td class="chinese" title="<?php echo $rth_val['COMMENT']; ?>"><?php echo $comments; ?></td>
                        <td>
                            <?php
                            $rst_borrow = qry_request_status(1, $_SESSION['user_id'], $rth_val['ID']);
                            if ($rst_borrow['REQUEST_STATUS'] == 1) {
                                ?> <span class="label label-warning">Pending</span> <?php
                            }
                            $j++;
                        ?>
                    </td>
                </tr>
                <?php 
                }
            }
            $i = 0;
            if(!empty($my_devices_info)){
                foreach ($my_devices_info as $di_key => $di_value) {
                    $short_model = substr($di_value['MODEL'], 0,20);
                    $comments = substr($di_value['COMMENT'], 0, 15);
                    ?>
                    <tr>
                        <td><?php echo $di_value['DEVICE_NO']; ?></td>
                        <td><?php echo $di_value['CATEGORY']; ?></td>
                        <td><?php echo $di_value['MANUFACTURER']; ?></td>
                        <td title="<?php echo $di_value['OS']; ?>"><?php echo substr($di_value['OS'],0,15); ?></td>
                        <td title="<?php echo $di_value['CONFIGURATION']; ?>"><?php echo substr($di_value['CONFIGURATION'],0,15); ?></td>
                        <td title="<?php echo htmlspecialchars($di_value['MODEL'],ENT_QUOTES); ?>"><?php echo $short_model; ?></td>
                        <td><?php echo ($di_value['DEVICE_TYPE'] == 1) ? "Device" : "Consumable"; ?></td>
                        <td><?php echo ($di_value['DEVICE_TYPE'] == 1) ?'1':$di_value['NUMBER'];?></td>
                        <td class="chinese"><?php echo $di_value['MAINTAINER']; ?></td>
                        <td class="chinese" title="<?php echo $di_value['COMMENT']; ?>"><?php echo $comments; ?></td>
                    </tr>
                    <?php
                    $i++;
                }
            }
            ?>
        </tbody>
    </table>
    <?php
}

/*
 * Push request to maintainer info into table
 */

function push_request_maintainer_into_table($request_to_maintainer) {
    $i = 0;
    foreach ($request_to_maintainer as $rtm_key => $rtm_value) {
        $request_type = $rtm_value['REQUEST_TYPE'];
        $short_model = substr($rtm_value['MODEL'], 0,20);
        $comments = substr($rtm_value['COMMENT'], 0, 15);
        $addinfo = substr($rtm_value['ADDINFO'], 0, 15);
        ?>
        <tr>
        <input id="borrow_borrow_id_<?php echo $i; ?>" type="hidden" value="<?php echo $rtm_value['BORROW_ID']; ?>">
        <input id="borrow_requestor_id_<?php echo $i; ?>" type="hidden" value="<?php echo $rtm_value['REQUESTOR_ID']; ?>">
        <input id="borrow_maintainer_id_<?php echo $i; ?>" type="hidden" value="<?php echo $rtm_value['MAINTAINER_ID']; ?>">
        <input id="borrow_device_id_<?php echo $i; ?>" type="hidden" value="<?php echo $rtm_value['ID']; ?>">
        <input id="borrow_device_type_<?php echo $i; ?>" type="hidden" value="<?php echo $rtm_value['DEVICE_TYPE']; ?>">
        <?php
        if ($rtm_value['DEVICE_TYPE'] == 0) {
            ?>
            <input id="borrow_device_number_<?php echo $i; ?>" type="hidden" value="<?php echo ($rtm_value['COUNT'] - $rtm_value['NUMBER']); ?>">
            <?php
        } else {
            ?>
            <input id="borrow_device_number_<?php echo $i; ?>" type="hidden" value="1">
            <?php
        }
        ?>
        <td id="borrow_device_no_<?php echo $i; ?>" value="<?php echo $rtm_value['DEVICE_NO']; ?>"><?php echo $rtm_value['DEVICE_NO']; ?></td>
        <td id="borrow_category_<?php echo $i; ?>"  value="<?php echo $rtm_value['CATEGORY']; ?>"><?php echo $rtm_value['CATEGORY']; ?></td>
        <td id="borrow_manufacturer_<?php echo $i; ?>" value="<?php echo $rtm_value['MANUFACTURER']; ?>" class="chinese"><?php echo $rtm_value['MANUFACTURER']; ?></td>
        <td id="borrow_model_<?php echo $i; ?>" title="<?php echo htmlspecialchars($rtm_value['MODEL'],ENT_QUOTES); ?>"><?php echo $short_model; ?></td>
        <td class="chinese"><?php echo $rtm_value['MAINTAINER']; ?></td>
        <td id="borrow_request_date_<?php echo $i; ?>" value="<?php echo ($rtm_value['BORROW_DATE'] != NULL)? $rtm_value['BORROW_DATE']:$rtm_value['RETURN_DATE'];?>">
            <?php echo ($rtm_value['BORROW_DATE'] != NULL)? $rtm_value['BORROW_DATE']:$rtm_value['RETURN_DATE'];?></td>
        <td class="chinese"><?php echo $rtm_value['REQUESTOR']; ?></td>
        <td><?php echo ($rtm_value['DEVICE_TYPE'] == 1)?'1':($rtm_value['COUNT'] . " - " . $rtm_value['NUMBER']);?></td>
        <?php
        if ($request_type == 1) {
            ?>
            <td id="borrow_comments_<?php echo $i; ?>" class="chinese" title="<?php echo $rtm_value['COMMENT']; ?>"><?php echo $comments; ?></td>
            <td>
                <a id="<?php echo $i; ?>" class="borrow_denied" href="#borrow_denied" data-toggle="modal"><span class="label">Deny</span></a>
                <a id="<?php echo $i; ?>" class="borrow_confirmed" href="#borrow_confirmed" data-toggle="modal"><span class="label label-info">Borrow</span></a>
            </td>
            <?php
        } if ($request_type == 2) {
            ?>
            <td id="borrow_comments_<?php echo $i; ?>"class="chinese" title="<?php echo $rtm_value['ADDINFO']; ?>"><?php echo $addinfo; ?></td>
            <td>
                <a id="<?php echo $i; ?>" class="return_denied" href="#return_denied" data-toggle="modal"><span class="label">Deny</span></a>
                <a id="<?php echo $i; ?>" class="return_confirmed" href="#return_confirmed" data-toggle="modal"><span class="label label-success">Return</span></a>
            </td>
            <?php
        }
        $i++;
    }
    ?>
    </tr>
    <?php
}

/*
 * Push transfer request  to the reciever of device info into table
 */

function push_recieve_device_info_into_table($recieve_device_info) {
    $i = 0;
    foreach ($recieve_device_info as $rth_key => $rth_value) {
        $short_model = substr($rth_value['MODEL'], 0,20);
        $comments = substr($rth_value['ADDINFO'], 0, 15);
        ?>
        <tr>
            <input id="recieve_requestor_id_<?php echo $i; ?>" type="hidden" value="<?php echo $rth_value['REQUESTOR_ID']; ?>">
            <input id="recieve_borrow_id_<?php echo $i; ?>" type="hidden" value="<?php echo $rth_value['BORROW_ID']; ?>">
            <input id="maintainer_id_<?php echo $i; ?>" type="hidden" value="<?php echo $rth_value['MAINTAINER_ID']; ?>">
            <td id="recieve_device_no_<?php echo $i; ?>" value="<?php echo $rth_value['DEVICE_NO']; ?>"><?php echo $rth_value['DEVICE_NO']; ?></td>
            <td><?php echo $rth_value['CATEGORY']; ?></td>
            <td><?php echo $rth_value['MANUFACTURER']; ?></td>
            <td title="<?php echo htmlspecialchars($rth_value['MODEL'],ENT_QUOTES); ?>"><?php echo $short_model; ?></td>
            <td class="chinese"><?php echo $rth_value['MAINTAINER']; ?></td>
            <td><?php echo $rth_value['TRANSFER_DATE']; ?></td>
            <td class="chinese"><?php echo $rth_value['REQUESTOR']; ?></td>
            <td>1</td>
            <td class="chinese" title="<?php echo $rth_value['COMMENTS']; ?>"><?php echo $comments; ?></td>
            <td>
                <a id="<?php echo $i; ?>" class="recieve_denied" href="#recieve_denied" data-toggle="modal"><span class="label">Deny</span></a>
                <a id="<?php echo $i; ?>" class="recieve_confirmed" href="#recieve_confirmed" data-toggle="modal"><span class="label label-important">Receive</span></a>
            </td>
        </tr>
        <?php
        $i++;
    }
}

/*
 * Push device info into table
 */

function push_device_info_into_table($device_info, $DEVICE_ADMIN, $total_records,$device_type) {
    ?>
    <div id="div_table_total"><h4>Total:<?php echo $total_records; ?></h4></div>
    <table id="device_table" class="table table-condensed table-striped table-hover">
        <thead>
            <tr>
                <th class='sortth' style="cursor:pointer" id="DEVICE_NO">Device No</th>
                <th class='sortth' style="cursor:pointer" id="CATEGORY">Category</th>
                <th class='sortth' style="cursor:pointer" id="MANUFACTURER" colspan="3">Manufacture/OS/Config</th>
                <th class='sortth' style="cursor:pointer" id="MODEL">Model</th>
                <?php
                if (isset($_SESSION['user_id']) && isset($DEVICE_ADMIN[$_SESSION['user_id']])) {?>
                    <th class='sortth' style="cursor:pointer" id='OWNER'>Owner</th>
                    <th class='sortth' style="cursor:pointer" id='PURCHASER'>Purchaser</th>
                    <?php
                }
                if($device_type == 1){?>
                    <th class='sortth' style="cursor:pointer" id='BORROW_DATE' colspan='2'>Borrow Date/Holder</th>
                    <th class='sortth' style="cursor:pointer" id='FREQUENCY'>Freq</th>
                    <?php
                }else{?>
                    <th class='sortth' style="cursor:pointer" id='COUNT'>Remaining</th>
                    <th class='sortth' style="cursor:pointer" id='FREQUENCY'>Used</th>
                    <?php
                }?>
                
                <th class='sortth' style="cursor:pointer" id="MAINTAINER">Maintainer</th>
                <th class='sortth' style="cursor:pointer" id="COMMENT">Comments</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 0;
            foreach ($device_info as $di_key => $di_value) {
                $short_model = substr($di_value['MODEL'], 0,20);
                $comments = substr($di_value['COMMENT'], 0, 15);
                ?>
                <tr>
                    <input id="id_<?php echo $i; ?>" type="hidden" value="<?php echo $di_value['ID']; ?>">
                    <input id="holder_id_<?php echo $i; ?>" type="hidden" value="<?php echo $di_value['HOLDER_ID']; ?>" name="<?php echo $di_value['HOLDER'];?>">
                    <input id="maintainer_id_<?php echo $i; ?>" type="hidden" value="<?php echo $di_value['MAINTAINER_ID']; ?>" name="<?php echo $di_value['MAINTAINER'];?>">
                    <input id="purchaser_id_<?php echo $i; ?>" type="hidden" value="<?php echo $di_value['PURCHASER_ID']; ?>">
                    <input id="location_<?php echo $i; ?>" type="hidden" value="<?php echo $di_value['LOCATION']; ?>">
                    <input id="checkintime_<?php echo $i; ?>" type="hidden" value="<?php echo $di_value['CHECKINTIME']; ?>">
                    <input id="addinfo_<?php echo $i; ?>" type="hidden" value="<?php echo $di_value['ADDINFO']; ?>">
                    <input id="borrow_id_<?php echo $i; ?>" type="hidden" value="<?php echo $di_value['BORROW_ID']; ?>">
                    <input id="device_perm_<?php echo $i; ?>" type="hidden" value="<?php echo $di_value['USABILITY']; ?>">
                    <input id="device_status_<?php echo $i; ?>" type="hidden" value="<?php echo $di_value['STATUS']; ?>">
                    <input id="device_number_<?php echo $i; ?>" type="hidden" value="<?php echo $di_value['COUNT']; ?>">
                    <input id="value_<?php echo $i; ?>" type="hidden" value="<?php echo $di_value['VALUE']; ?>">
                    <input id="invoice_<?php echo $i; ?>" type="hidden" value="<?php echo $di_value['INVOICE']; ?>">
                    <input id="device_type_<?php echo $i; ?>" type="hidden" value="<?php echo ($di_value['DEVICE_TYPE'] == 1) ? "Device" : "Consumable"; ?>">
                    <td id="device_no_<?php echo $i; ?>" value="<?php echo $di_value['DEVICE_NO'];?>"><?php echo $di_value['DEVICE_NO']; ?></td>
                    <td id="category_<?php echo $i; ?>" value="<?php echo $di_value['CATEGORY'];?>"><?php echo $di_value['CATEGORY']; ?></td>
                    <td id="manufacturer_<?php echo $i; ?>" value="<?php echo $di_value['MANUFACTURER'];?>"><?php echo $di_value['MANUFACTURER']; ?></td>
                    <td id="os_<?php echo $i; ?>" title="<?php echo $di_value['OS']; ?>"><?php echo substr($di_value['OS'],0,15); ?></td>
                    <td id="config_<?php echo $i; ?>" title="<?php echo $di_value['CONFIGURATION']; ?>"><?php echo substr($di_value['CONFIGURATION'],0,15); ?></td>
                    <td id="model_<?php echo $i; ?>" value="<?php echo $di_value['MODEL'];?>" title="<?php echo htmlspecialchars($di_value['MODEL'],ENT_QUOTES); ?>"><?php echo $short_model; ?></td>
                    <?php
                    if (isset($_SESSION['user_id'])&&isset($DEVICE_ADMIN[$_SESSION['user_id']])) {
                        ?>
                        <td id="owner_<?php echo $i; ?>" value="<?php echo $di_value['OWNER'];?>"><?php echo ($di_value['OWNER'] == 1) ?"Pactera" :(($di_value['OWNER'] == 3)?"Citrix":"");?></td>
                        <td id="purchaser_<?php echo $i; ?>"><?php echo $di_value['PURCHASER']; ?></td>
                        <?php
                    } ?>
                    <?php 
                    if($device_type == 1){?>
                        <td id="borrow_date_<?php echo $i;?>" value="<?php echo $di_value['BORROW_DATE'];?>"><?php echo empty($di_value['BORROW_DATE'])?'':date('m/d/y',strtotime($di_value['BORROW_DATE'])); ?></td>
                        <td class='chinese'><?php echo $di_value['HOLDER'];?></td>
                        <?php
                    }else{?>
                        <td><?php echo $di_value['COUNT']; ?></td>
                        <?php
                    }?>
                    <td><?php echo $di_value['FREQUENCY']; ?></td>
                    <td class="chinese"><?php echo $di_value['MAINTAINER']; ?></td>
                    <td id="comments_<?php echo $i; ?>" class="chinese" title="<?php echo $di_value['COMMENT']; ?>"><?php echo $comments; ?></td>
                    <td>
                        <?php
                        if($di_value['USABILITY']!='1'){
                            $rst_borrow_id = qry_request_status(1,'',$di_value['ID']);
                            if ($di_value['DEVICE_TYPE'] == 1) {
                                if($di_value['STATUS']==1){
                                    if(($di_value['HOLDER_ID'] != NULL) || ($di_value['MAINTAINER_ID'] != NULL)){
                                        if (($rst_borrow_id['REQUEST_STATUS'] == 0) || ($rst_borrow_id['REQUEST_STATUS'] != 1)) {
                                            if(isset($_SESSION['user_id'])){
                                                if (($di_value['HOLDER_ID'] == NULL) && $di_value['HOLDER_ID'] != $_SESSION['user_id']) {
                                                    if (isset($DEVICE_ADMIN[$_SESSION['user_id']])) {
                                                        ?> <a id="<?php echo $i; ?>" class="borrow" title="Borrow" href="#borrow_device" data-toggle="modal"><img src='../../../lib/image/icons/borrow.png' width="16" height="16"/></a> <?php
                                                    }else{
                                                        ?> <i class="icon-check-alt"title="Borrow"></i> <?php                                                
                                                    }
                                                }else{
                                                    if (isset($DEVICE_ADMIN[$_SESSION['user_id']])){?>
                                                        <a id="<?php echo $i; ?>" class="email" title="Alert" href="#alert_email"><img width='16' height='16' src='../../../lib/image/icons/email.png'/></i></a>
                                                        <a id="<?php echo $i; ?>" class="return" title="Return" href="#return_device" data-toggle="modal"><img width='16' height='16' src='../../../lib/image/icons/return.png'/></a>
                                                        <?php
                                                    }else{
                                                        ?> <i class="icon-minus-alt" title="Occupied"></i> <?php
                                                    }
                                                }
                                            }else{
                                                if ($di_value['HOLDER_ID'] == NULL) {
                                                    ?> <a id="<?php echo $i; ?>" class="borrow" title="Borrow" href="#borrow_device" data-toggle="modal"><img src='../../../lib/image/icons/borrow.png' width="16" height="16"/></a> <?php
                                                }else{
                                                    ?><a id="<?php echo $i; ?>" class="return" title="Return" href="#return_device" data-toggle="modal"><img width='16' height='16' src='../../../lib/image/icons/return.png'/></a><?php
                                                }
                                            }
                                        }
                                    }
                                }else{
                                    ?><i class="icon-cancel" title="Broken"></i><?php
                                }
                            }else {
                                if(!isset($_SESSION['user_id']) || (isset($_SESSION['user_id'])&&isset($DEVICE_ADMIN[$_SESSION['user_id']]))){
                                    ?> <a id="<?php echo $i; ?>" class="borrow" title="Borrow" href="#borrow_device" data-toggle="modal"><img src='../../../lib/image/icons/borrow.png' width="16" height="16"/></a> <?php
                                }else{
                                    ?> <i class="icon-check-alt" title="Borrow"></i> <?php
                                }
                            }
                        }
                        if (isset($_SESSION['user_id'])&&isset($DEVICE_ADMIN[$_SESSION['user_id']])&&$_SESSION['user_id'] == $di_value['MAINTAINER_ID']) {
                            ?> <a id="<?php echo $i; ?>" class="update" title="Update" href="#update_device" data-toggle="modal"><img width='16' height='16' src='../../../lib/image/icons/edit.png'/></a> <?php
                        }
                        ?>
                    </td>
                </tr>
            <?php
            $i++;
        }?>
    </tbody>
    </table>
    <?php
}


/*
 * Push device info into table
 */

function push_device_info_into_table_test($device_info, $DEVICE_ADMIN, $total_records,$device_type) {
    ?>
    <div id="div_table_total"><h4>Total:<?php echo $total_records; ?></h4></div>
    <table id="device_table" class="table table-condensed table-striped table-hover sortable">
        <thead>
            <tr>
                <th>NO</th>
                <th>Category</th>
                <th>Brand</th>
                <th>Model</th>
                <th>OS</th>
                <th>Config</th>
                <th>Status</th>
                <th>Owner</th>
                <th>Maintainer</th>
                <th>Holder</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 0;
            foreach ($device_info as $di_key => $di_value) {
                $short_model         = substr($di_value['MODEL'], 0,20);
                $short_os            = substr($di_value['OS'], 0,20);
                $short_configuration = substr($di_value['CONFIGURATION'], 0,30);
                $comments            = substr($di_value['COMMENT'], 0, 15);
                ?>
                <tr>
                    <input id="id_<?php echo $i; ?>" type="hidden" value="<?php echo $di_value['ID']; ?>">
                    <td id="device_no_<?php echo $i; ?>"><?php echo $di_value['DEVICE_NO']; ?></td>
                    <td id="category_<?php echo $i; ?>"><?php echo $di_value['CATEGORY']; ?></td>
                    <td id="manufacturer_<?php echo $i; ?>" class="chinese"><?php echo $di_value['MANUFACTURER']; ?></td>
                    <td id="model_<?php echo $i; ?>" title="<?php echo htmlspecialchars($di_value['MODEL'],ENT_QUOTES); ?>"><?php echo $short_model; ?></td>
                    <td id="os_<?php echo $i; ?>" title="<?php echo htmlspecialchars($di_value['OS'],ENT_QUOTES); ?>"><?php echo $short_os; ?></td>
                    <td id="os_<?php echo $i; ?>" title="<?php echo htmlspecialchars($di_value['CONFIGURATION'],ENT_QUOTES); ?>">
                        <?php echo $short_configuration; ?>
                    </td>
                    <td id="status_<?php echo $i; ?>"><?php echo ($di_value['STATUS']==1)?"OK":"Bad"; ?></td>
                    <td id="owner_<?php echo $i; ?>">
                        <?php
                            if ($di_value['OWNER'] == 1) {
                                echo "Pactera";
                            } else if ($di_value['OWNER'] == 3) {
                                echo "Citrix";
                            }
                        ?>
                    </td>
                    <td id="purchaser_<?php echo $i; ?>"><?php echo $di_value['PURCHASER']; ?></td>
                    <td id="purchaser_<?php echo $i; ?>"><?php echo ($di_value['FAKE_BORROWER']=="")?$di_value['NICK_NAME']:$di_value['FAKE_BORROWER']; ?></td>
                </tr>
            <?php
            $i++;
        }?>
    </tbody>
    </table>
    <?php
}

/*
 * Push device info into table
 */

function push_device_info_external($device_info,$total_records) {
    ?>
    <div id="div_table_total"><h4>Total:<?php echo $total_records; ?></h4></div>
    <table id="device_table" class="table table-condensed table-striped table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Device No</th>
                <th>Category</th>
                <th>Manufacture</th>
                <th>Model</th>
                <th>Holder</th>
                <th>Status</th>
                <th>Comments</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
            foreach ($device_info as $di_key => $di_value) {
                $short_model = substr($di_value['MODEL'], 0,20);
                $comments = substr($di_value['COMMENT'], 0, 15);
                ?>
                <tr>
                    <td><?php echo $i; ?></td>
                    <td><a id="<?php echo $i; ?>" class="device_detail"><?php echo $di_value['DEVICE_NO']; ?></a></td>
                    <td><?php echo $di_value['CATEGORY']; ?></td>
                    <td class="chinese"><?php echo $di_value['MANUFACTURER']; ?></td>
                    <td title="<?php echo htmlspecialchars($di_value['MODEL'],ENT_QUOTES); ?>"><?php echo $short_model; ?></td>
                    <td><?php echo $di_value['NAME']; ?></td>
                    <td><?php echo $di_value['STATUS']=1?"OK":"Bad"; ?></td>
                    <td class="chinese" title="<?php echo $di_value['COMMENT']; ?>"><?php echo $comments; ?></td>
                </tr>
                <?php
                $i++;
            }
            ?>
        </tbody>
    </table>
    <?php
}


/*
 * Push device history info into table
 */

function push_device_history_into_table($device_history, $device_no) {
    $device_type = $device_history[0]['DEVICE_TYPE'];
    ?>
    <div id="div_table_total">
        <h4><?php echo $device_no; ?></h4>
    </div>
    </br>
    <table id="device_table" class="table table-striped table-hover">
        <thead>
        <th>Category</th>
        <th>Model</th>
        <th>Requestor</th>
        <?php
        if ($device_type == 1) {
            ?> <th colspan="2">Borrow/Return Date</th> <?php
        } else {
            ?> <th>Borrow Date</th> <?php
        }
        ?>
        <th colspan="2">Request Type/Status</th>
        <th colspan="2">Operator/Date</th>
        <?php echo ($device_type == 1)?"<th>Status</th>":"<th>Count</th>";?>
        <th>Comment</th>
    </thead>
    <tbody>
        <?php
        foreach ($device_history as $dh_key => $dh_value) {
            $short_model = substr($dh_value['MODEL'], 0,20);
            $comments = substr($dh_value['COMMENTS'], 0, 15);
            $borrow_date = (!empty($dh_value['BORROW_DATE']))?date("m/d/y", strtotime($dh_value['BORROW_DATE'])):null;
            $return_date = (!empty($dh_value['RETURN_DATE']))?date("m/d/y", strtotime($dh_value['RETURN_DATE'])):null;
            $operate_date = (!empty($dh_value['OPERATE_DATE']))?date("m/d/y", strtotime($dh_value['OPERATE_DATE'])):null;
            $transfer_date = (!empty($dh_value['TRANSFER_DATE']))?date("m/d/y", strtotime($dh_value['TRANSFER_DATE'])):null;
            ?>
            <tr>
                <td><?php echo $dh_value['CATEGORY']; ?></td>
                <td title="<?php echo htmlspecialchars($dh_value['MODEL'],ENT_QUOTES); ?>"><?php echo $short_model; ?></td>
                <td class="chinese"><?php echo $dh_value['REQUESTOR']; ?></td>
                <td><?php echo $borrow_date; ?></td>
                <?php
                if ($device_type == 1) {
                    ?> <td><?php echo $return_date; ?></td> <?php
                }
                ?>
                <td><?php echo ($dh_value['REQUEST_TYPE'] == 1)?"Borrow":(($dh_value['REQUEST_TYPE'] == 2)?"Return":"Transfer");?></td>
                <td><?php echo ($dh_value['REQUEST_STATUS'] == 0)?"Denied":(($dh_value['REQUEST_STATUS'] == 1)?"Pending":"Confirmed");?></td>
                <td class="chinese"><?php echo ($dh_value['REQUEST_TYPE'] == 1 || $dh_value['REQUEST_TYPE'] == 2)?$dh_value['MAINTAINER']:$dh_value['RECIEVER'];?></td>
                <td><?php echo $operate_date; ?></td>
                <?php if ($device_type == 1) {?>
                    <td> <?php echo ($dh_value['STATUS'] == 0)?"<span class='label label-important'>Inactive</span>":"<span class='label label-success'>Active</span>";?></td>
                    <?php
                }else{
                    ?> <td><?php echo $dh_value['COUNT']; ?></td> <?php
                }?>
                <td class="chinese" title="<?php echo $dh_value['COMMENTS']; ?>"><?php echo $comments; ?></td>
            </tr>
            <?php
        }
        ?>
    </tbody>
    </table>
    <?php
}
?>

