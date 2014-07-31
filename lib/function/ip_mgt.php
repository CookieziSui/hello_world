<?php
require_once __DIR__ . '../../../debug.php';
require_once __DIR__ . '../../../mysql.php';
require_once __DIR__ . '/../../lib/inc/constant_ip.php';

//add ip
function add_ip($org_name, $user_name, $ip, $comment) {
    $ip = explode(".", $ip);
    $first = $ip[0];
    $second = $ip[1];
    $third = $ip[2];
    $fourth = $ip[3];
    if ($org_name != "" && $user_name != "") {
        $query_id = "SELECT user_info.USER_ID,org_info.ID from user_info,org_info,user_org_group
    WHERE user_info.USER_ID=user_org_group.FK_USER_ID
    AND org_info.ID=user_org_group.FK_ORG_ID
    AND user_info.USER_NAME='" . $user_name . "'
    AND org_info.NAME='" . $org_name . "'";
        $rst_id = $GLOBALS['db']->query($query_id);
        $rst_id_info = $rst_id->fetch_all(MYSQLI_ASSOC);
        dump($query_id);
        foreach ($rst_id_info as $id_key => $id_value) {
            foreach ($id_value as $i_key => $i_value) {
                $result_id[$i_key] = $i_value;
            }
        }
        $org_id = $result_id['ID'];
        $user_id = $result_id['USER_ID'];
        $add_ip_info = "INSERT INTO ip_assignment(FK_ORG_ID,FK_USER_ID,FIRST,SECOND,THIRD,FOURTH,COMMENT) VALUES('" . $org_id . "','" . $user_id . "','$first','$second','$third','$fourth','$comment')";
        $rst_ip_info = $GLOBALS['db']->query($add_ip_info);
    } else {
        $add_ip = "INSERT INTO ip_assignment(FIRST,SECOND,THIRD,FOURTH) VALUES('$first','$second','$third','$fourth')";
        $rst_ip = $GLOBALS['db']->query($add_ip);
        dump($add_ip);
    }
}

function check_ip($ip) {
    $ip = explode(".", $ip);
    $first = $ip[0];
    $second = $ip[1];
    $third = $ip[2];
    $fourth = $ip[3];
    $check_ip = "SELECT ID FROM ip_assignment WHERE ID in (SELECT ID FROM ip_assignment WHERE `FIRST`='$first' AND `SECOND`='$second' AND THIRD='$third' AND FOURTH='$fourth')";
    $rst_check = $GLOBALS['db']->query($check_ip);
    $rst_info = $rst_check->fetch_all(MYSQLI_ASSOC);
    foreach ($rst_info as $r_key => $r_value) {
        if (!empty($r_value)) {
            return "true";
        } else {
            return "false";
        }
    }
}

//update ip by id
function update_ip($ip_id, $user_id, $ip, $comment) {
    $ip = explode(".", $ip);
    $first = $ip[0];
    $second = $ip[1];
    $third = $ip[2];
    $fourth = $ip[3];
    if ($user_id != "") {
        $update_ip = "UPDATE ip_assignment SET
    FK_USER_ID='" . $user_id . "',
    FIRST='" . $first . "',
    SECOND='" . $second . "',
    THIRD='" . $third . "',
    FOURTH='" . $fourth . "',
    COMMENT='" . $comment . "'
    WHERE ID='" . $ip_id . "'";
    } else {
        $org_id = "";
        $user_id = "";
        $update_ip = "UPDATE ip_assignment SET
    FIRST='" . $first . "',
    SECOND='" . $second . "',
    THIRD='" . $third . "',
    FOURTH='" . $fourth . "',
    COMMENT='" . $comment . "'
    WHERE ID='" . $ip_id . "'";
    }
    $rst_ip_info = $GLOBALS['db']->query($update_ip);
}

//release IP
function release_ip($id) {
    $release_ip = "UPDATE ip_assignment SET  FK_ORG_ID=NULL,FK_USER_ID=NULL,`COMMENT`=NULL WHERE ID='" . $id . "'";
//    dump($delete_ip);
    $rst_release_info = $GLOBALS['db']->query($release_ip);
}

//delete IP
function delete_ip($id) {
    $delete_ip = "DELETE FROM ip_assignment WHERE ID='" . $id . "'";
//    dump($delete_ip);
    $rst_delete_info = $GLOBALS['db']->query($delete_ip);
}

//All inquires IP record
function ip_info($rst_info, $IP_ADMIN) {
    //dump($rst_info);
    $i = 0;
    $length = sizeof($rst_info);
    $row = $length / 2;
    if ($length % 2 == 0) {
        $sub_first = array_slice($rst_info, 0, $row);
        $sub_second = array_slice($rst_info, -$row);
        first_section($sub_first, $i, $IP_ADMIN);
        second_section($sub_second, $i + $row, $IP_ADMIN);
    } else if ($length == 1) {
        first_section($rst_info, $i, $IP_ADMIN);
    } else {
        $row = round($row);
        $sub_first = array_slice($rst_info, 0, $row);
        $sub_second = array_slice($rst_info, -($row - 1));
        first_section($sub_first, $i, $IP_ADMIN);
        second_section($sub_second, $i + $row + 1, $IP_ADMIN);
    }
    ?>
    <?php
}

function first_section($sub_first, $i, $IP_ADMIN) {
    ?>
    <table id="left_table" class="table table-striped table-condensed table-hover">

        <thead>
            <tr>
                <th>Tester name</th>
                <th>IP</th>
                <th>The Name of Team</th>
                <?php
                if (isset($IP_ADMIN[$_SESSION['user_id']])) {
                    ?>
                    <th>Operate</th>
                    <?php
                }
                ?>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($sub_first as $db_key => $db_value) {
                ?>
                <tr>
            <input type="hidden" id="ip_id_<?php echo $i; ?>" name="ip_id" value="<?php echo $db_value['ID']; ?>">
            <input type="hidden" id="org_id_<?php echo $i; ?>" value="<?php echo $db_value['FK_ORG_ID']; ?>">
            <input type="hidden" id="user_id_<?php echo $i; ?>" value="<?php echo $db_value['FK_USER_ID']; ?>">
            <input type="hidden" id="comment_<?php echo $i; ?>"  value="<?php echo $db_value['COMMENT']; ?>">
            <td id="org_name_<?php echo $i; ?>"><?php echo $db_value['COMMENT']; ?></td>
            <td id="ip_<?php echo $i; ?>"><?php echo $db_value['FIRST']; ?>.<?php echo $db_value['SECOND']; ?>.<?php echo $db_value['THIRD']; ?>.<?php echo $db_value['FOURTH']; ?></td>
            <td id="user_name_<?php echo $i; ?>" class="chinese"><?php echo $db_value['USER_NAME']; ?></td>
            <?php
            if (isset($IP_ADMIN[$_SESSION['user_id']])) {
                $parm_id = base64_encode($db_value['ID']);
                ?>
                <td>
                    <a class="update_ip" href="#update_ip"  data-toggle="modal" id="<?php echo $i; ?>" ><img width="16" height="16" src="../../../lib/image/icons/edit.png"></a>&nbsp;&nbsp;
                    <a onclick="return delete_ip();" href="../ip/index.php?url=<?php echo base64_encode("ip/delete_ip.php"); ?>&id=<?php echo $parm_id; ?>" ><img width="16" height="16" src="../../../lib/image/icons/remove.png"></a>&nbsp;&nbsp;
                    <a onclick="return release_ip();" href="../ip/index.php?url=<?php echo base64_encode("ip/release_ip.php"); ?>&id=<?php echo $parm_id; ?>" ><img width="16" height="16" src="../../../lib/image/icons/transfer.png"></a>&nbsp;&nbsp;

                </td>
                <?php
            }
            ?>
        </tr>
        <?php
        $i++;
    }
    ?>
    </tbody>
    </table>
    <?php
}

function second_section($sub_second, $i, $IP_ADMIN) {
    ?>
    <table id="right_table" class="table table-striped table-condensed table-hover">

        <thead>
            <tr>
                <th>The Name of Team</th>
                <th>IP</th>
                <th>Tester name</th>
                <?php
                if (isset($IP_ADMIN[$_SESSION['user_id']])) {
                    ?>
                    <th>Operate</th>
                    <?php
                }
                ?>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($sub_second as $db_key => $db_value) {
                ?>
                <tr>
            <input type="hidden" id="ip_id_<?php echo $i; ?>" name="ip_id" value="<?php echo $db_value['ID']; ?>">
            <input type="hidden" id="org_id_<?php echo $i; ?>" value="<?php echo $db_value['FK_ORG_ID']; ?>">
            <input type="hidden" id="user_id_<?php echo $i; ?>" value="<?php echo $db_value['FK_USER_ID']; ?>">
            <input type="hidden" id="comment_<?php echo $i; ?>"  value="<?php echo $db_value['COMMENT']; ?>">
            <td id="org_name_<?php echo $i; ?>"><?php echo $db_value['NAME']; ?></td>
            <td id="ip_<?php echo $i; ?>"><?php echo $db_value['FIRST']; ?>.<?php echo $db_value['SECOND']; ?>.<?php echo $db_value['THIRD']; ?>.<?php echo $db_value['FOURTH']; ?></td>
            <td id="user_name_<?php echo $i; ?>" class="chinese"><?php
        if ($db_value['USER_NAME'] == NULL) {
            echo $db_value['COMMENT'];
        }
        echo $db_value['USER_NAME'];
                ?></td>
            <?php
            if (isset($IP_ADMIN[$_SESSION['user_id']])) {
                $parm_id = base64_encode($db_value['ID']);
                ?>
                <td>
                    <a class="update_ip" href="#update_ip"  data-toggle="modal" id="<?php echo $i; ?>" ><img width="16" height="16" src="../../../lib/image/icons/edit.png"></a>&nbsp;&nbsp;
                    <a onclick="return delete_ip();" href="../ip/index.php?url=<?php echo base64_encode("ip/delete_ip.php"); ?>&id=<?php echo $parm_id; ?>" ><img width="16" height="16" src="../../../lib/image/icons/remove.png"></a>&nbsp;&nbsp;
                    <a onclick="return release_ip();" href="../ip/index.php?url=<?php echo base64_encode("ip/release_ip.php"); ?>&id=<?php echo $parm_id; ?>" ><img width="16" height="16" src="../../../lib/image/icons/transfer.png"></a>&nbsp;&nbsp;

                </td>
                <?php
            }
            ?>
        </tr>
        <?php
        $i++;
    }
    ?>
    </tbody>
    </table>
    <?php
}

//select ip
function select_ip($ip_info) {
    $i = 0;
    foreach ($ip_info as $first_key => $first_value) {
        $f_key[$first_key] = $first_key;
    }
    ksort($f_key, SORT_NUMERIC);
    ?>
    <select id="first" class="span3">
        <option id="ip_first_<?php echo $i++; ?>" name="ip_first" value=""></option>
        <?php
        foreach ($f_key as $first_key => $first_value) {
            ?>
            <option id="ip_first_<?php echo $i++; ?>" name="ip_first" value="<?php echo $first_key ?>"><?php echo $first_key; ?></option>
            <?php
        }
        ?>
    </select>
    <select id="second" class="span3">
        <option id="ip_second_<?php echo $i++; ?>" name="ip_second" value=""></option>
    </select>
    <select id="third" class="span3">
        <option id="ip_third_<?php echo $i++; ?>" name="ip_third" value=""></option>
    </select>
    <?php
}
?>
