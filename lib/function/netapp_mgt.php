<?php
require_once __DIR__ . '../../../mysql.php';
require_once __DIR__ . '/../../lib/function/org_mgt.php';
require_once __DIR__ . '/../../lib/inc/constant_org.php';
require_once __DIR__ . '/../../lib/function/get_working_days.php';
require_once __DIR__ . '/../../lib/function/log.php';
require_once __DIR__ . '/../../lib/function/escape_special_character.php';
require_once __DIR__ . '/../../lib/inc/highcharts.php';
/**
 * query $oid and sub_projects' tms and other info
 */
function query_product_info($oid,$year,$month,$today,$task_keyword){
    $product_info = array();
    /**
     * tms_info
     */
    $where = "";
    if(!empty($task_keyword)){
       $where = "AND CONCAT(ti.PO_ID,pi.NAME,nri.`RATE_NAME`) LIKE '%$task_keyword%' ";
    }
    $qry_tms_info = "SELECT pi.`NAME`,ti.`ID`,ti.`PO_ID`,ti.`JOB_TITLE`,ci.`CATEGORY_NAME`,ti.`JOB_TYPE`,ji.`JOBTYPE_NAME`,ji.`FATHER`,ji_a.`JOBTYPE_NAME` AS FATHER_JOBTYPE_NAME, ti.`ACTUAL_TOTAL`,ti.`ORIGINAL_ESTIMATE`,
                ti.`ORIGINAL_ESTIMATE_CHANGED_REASON`,ti.`DELIVERY_STATUS`,ti.`LANGUAGE` AS RI_ID,nri.`RATE_NAME` AS LANGUAGE, ti.`SCHEDULED_DUE_DATE`,ti.`COMPLETE_DATE`,ti.`PROJECT_START_DATE`,ti.`TRANS_DELIVERY_DATE`
                FROM tms_info AS ti, `category_info` AS ci,`jobtype_info` AS ji LEFT JOIN `jobtype_info` AS ji_a ON ji.`FATHER` = ji_a.`ID` ,`project_info` AS pi,`netapp_rate_info` AS nri 
                WHERE ti.`PRODUCT` = pi.`ID` AND ti.`LANGUAGE` = nri.`ID` AND ti.`JOB_TYPE` = ji.`ID` AND ji.`FK_CATEGORY_ID` = ci.`ID` AND (pi.`ID` = '$oid' OR pi.`FATHER` ='$oid') ".$where." ORDER BY ti.`PROJECT_START_DATE` DESC, pi.`ID` ASC,ti.`JOB_TYPE` ASC,`SCHEDULED_DUE_DATE` ASC;";
    $rst_qry_tms = $GLOBALS['db']->query($qry_tms_info);
    $tms_info    = $rst_qry_tms->fetch_all(MYSQLI_ASSOC);
    $product_info['tms_info'] = $tms_info;
    /**
     * other_info
     */
    $where = "";
    if(!empty($task_keyword)){
       $where = "AND CONCAT(oi.PO_ID,pi.NAME,ci.`CATEGORY_NAME`) LIKE '%$task_keyword%' ";
    }
    $qry_other_info = "SELECT pi.`NAME`,oi.`ID`,oi.`PO_ID`,oi.`JOB_TITLE`,ci.`CATEGORY_NAME`,oi.`JOB_TYPE`,ji.`JOBTYPE_NAME`,ji.`FATHER`,ji_a.`JOBTYPE_NAME` AS FATHER_JOBTYPE_NAME,oi.`WORKING_HOUR`,oi.`DATE`,oi.`HOUR_RATE`,nri.`RATE`,oi.`PM_RATE`
                FROM other_info AS oi, `category_info` AS ci,`jobtype_info` AS ji LEFT JOIN `jobtype_info` AS ji_a ON ji.`FATHER` = ji_a.`ID` ,`project_info` AS pi,`netapp_rate_info` AS nri
                WHERE oi.`PRODUCT` = pi.`ID` AND oi.`JOB_TYPE` = ji.`ID` AND ji.`FK_CATEGORY_ID` = ci.`ID` AND nri.`JOBTYPE_ID`=oi.`JOB_TYPE` AND (pi.`ID` = '$oid' OR pi.`FATHER` = '$oid') ".$where."  ORDER BY oi.`DATE` DESC, pi.`ID` ASC, oi.`JOB_TYPE` ASC,`DATE` ASC;";
    $rst_qry_other = $GLOBALS['db']->query($qry_other_info);
    $other_info    = $rst_qry_other->fetch_all(MYSQLI_ASSOC);
    $product_info['other_info'] = $other_info;
    
    return $product_info;
}
/**
 *  query task info  
 */
function query_task_info_by_id($id,$table){
    $qry_tms_info_id = "SELECT * FROM $table WHERE `ID` = $id;";
    $rst_qry_task_id = $GLOBALS['db']->query($qry_tms_info_id);
    $task_info_id    = $rst_qry_task_id->fetch_all(MYSQLI_ASSOC);
    return $task_info_id;
}
/**
 *  delete task info  
 */
function del_task_info_by_id($id,$table){
    $del_tms_info_id = "DELETE FROM $table WHERE ID = $id;";
    $rst_del_task_id = $GLOBALS['db']->query($del_tms_info_id);
}
/**
 * query PO info
 */
function query_all_po_info(){
    $qry_po_info = "SELECT * FROM `po_info` AS pi ORDER BY QUARTER DESC;";
    $rst_qry_po  = $GLOBALS['db']->query($qry_po_info);
    $po_info     = $rst_qry_po->fetch_all(MYSQLI_ASSOC);
    return $po_info;
}
/**
 * query po info by po_id
 * @param type $po_id
 * @return type
 */
function query_po_info($po_id){
    $qry_po_info = "SELECT * FROM `po_info` AS pi WHERE pi.`PO_ID` = '$po_id';";
    $rst_qry_po  = $GLOBALS['db']->query($qry_po_info);
    $po_info     = $rst_qry_po->fetch_all(MYSQLI_ASSOC);
    return $po_info;
}

/**
 * delete po
 */
function del_po($po_id){
    $del_po_info = "DELETE FROM `po_info` WHERE `PO_ID` = '$po_id'; " ;
    $rst_del_po  = $GLOBALS['db']->query($del_po_info);
    $del_tms_info_by_po = "DELETE FROM tms_info WHERE `PO_ID` = '$po_id';";
    $rst_tms_info_by_no = $GLOBALS['db']->query($del_tms_info_by_po);
    $del_other_info_by_po = "DELETE FROM other_info WHERE `PO_ID` = '$po_id';";
    $rst_other_info_by_no = $GLOBALS['db']->query($del_other_info_by_po);
}

/**
 * query rate info and jobtype with ccc
 */
function query_rate_info(){
    $qry_rate_info = "SELECT nri.`ID`,nri.`RATE_NAME`,nri.`RATE`,ji.`ID` AS JI_ID,ji.`JOBTYPE_NAME`,ji.`FATHER`,ji.`CCC`,ji.`PID`,ci.`ID` AS CI_ID, ci.`CATEGORY_NAME`
                FROM `netapp_rate_info` AS nri, `jobtype_info` AS ji, `category_info` AS ci 
                WHERE nri.`JOBTYPE_ID` = ji.`ID` AND ji.`FK_CATEGORY_ID` = ci.`ID`ORDER BY ji.`FATHER` DESC,nri.`ID` ASC;";
    $rst_qry_rate  = $GLOBALS['db']->query($qry_rate_info);
    $rate_info     = $rst_qry_rate->fetch_all(MYSQLI_ASSOC);
    return $rate_info;
}

/**
 * query rate info and jobtype with ccc by id
 */
function query_rate_info_by_id($id){
    $qry_rate_info = "SELECT nri.`ID`,nri.`RATE_NAME`,nri.`RATE`,ji.`ID` AS JI_ID,ji.`FATHER`,ji.`JOBTYPE_NAME`,ji.`CCC`,ji.`PID`,ci.`ID` AS CI_ID, ci.`CATEGORY_NAME`
                FROM `netapp_rate_info` AS nri, `jobtype_info` AS ji, `category_info` AS ci 
                WHERE nri.`JOBTYPE_ID` = ji.`ID` AND ji.`FK_CATEGORY_ID` = ci.`ID`AND nri.`ID`='$id';";
    $rst_qry_rate  = $GLOBALS['db']->query($qry_rate_info);
    $rate_info     = $rst_qry_rate->fetch_all(MYSQLI_ASSOC);
    return $rate_info;
}

/**
 * query all job type info
 */
function query_job_type_info(){
    $qry_job_type_info = "SELECT ID,JOBTYPE_NAME,FATHER FROM jobtype_info";
    $rst_qry_job_type  = $GLOBALS['db']->query($qry_job_type_info);
    $job_type_info     = $rst_qry_job_type->fetch_all(MYSQLI_ASSOC);
    return $job_type_info;
}

/**
 * query all requestor
 */
function query_requestor_info(){
    $qry_requestor_info = "SELECT DISTINCT REQUESTOR FROM tms_info";
    $rst_qry_requestor  = $GLOBALS['db']->query($qry_requestor_info);
    $requestor_info     = $rst_qry_requestor->fetch_all(MYSQLI_ASSOC);
    return $requestor_info;
}

/**
 * query all product by oid
 */
function query_project_info($oid){
    $qry_project_info = "SELECT ID,NAME FROM project_info WHERE ID = '$oid' OR FATHER = '$oid'";
    $rst_qry_project  = $GLOBALS['db']->query($qry_project_info);
    $project_info     = $rst_qry_project->fetch_all(MYSQLI_ASSOC);
    return $project_info;
}

/**
 * query job type info by job_type
 */
function query_job_type_info_by_id($id){
    $qry_job_type_info = "SELECT ji.ID,ji.JOBTYPE_NAME,ji.CCC,ji.PID,ji.FATHER,ci.ID as CI_ID,ci.CATEGORY_NAME FROM jobtype_info as ji,category_info as ci WHERE ji.`FK_CATEGORY_ID` = ci.`ID` AND ji.ID = '$id'";
    $rst_qry_job_type  = $GLOBALS['db']->query($qry_job_type_info);
    $job_type_info     = $rst_qry_job_type->fetch_all(MYSQLI_ASSOC);
    return $job_type_info;
}

/**
 * query all category info
 */
function query_category_info(){
    $qry_category_info = "SELECT ID,CATEGORY_NAME FROM category_info";
    $rst_qry_category  = $GLOBALS['db']->query($qry_category_info);
    $category_info     = $rst_qry_category->fetch_all(MYSQLI_ASSOC);
    return $category_info;
}

/**
 * query all category info
 */
function query_category_info_by_job_type_id($job_type_id){
    $qry_category_info = "SELECT CATEGORY_NAME,JOBTYPE,CCC,PID FROM category_jobtype_ccc WHERE JOBTYPE = '$job_type_id'";
    $rst_qry_category  = $GLOBALS['db']->query($qry_category_info);
    $category_info     = $rst_qry_category->fetch_all(MYSQLI_ASSOC);
    return $category_info;
}

/**
 * query all category info
 */
function del_rate($id){
    $del_rate_info = "DELETE FROM netapp_rate_info WHERE ID = '$id'";
    $rst_del_rate  = $GLOBALS['db']->query($del_rate_info);
}

/**
 * draw_product_spend_by_month
 */
function draw_product_spend_by_month($product_spend_month,$container,$title){
    $category_spend = array();
    foreach($product_spend_month as $psm_key => $psm_val){
        foreach($psm_val as $pv_key => $pv_val){
            $category_spend[$pv_key][] = $pv_val;
        }
    }
    ?>
    <script type="text/javascript">
    $(function () {
        $('#<?php echo $container; ?>').highcharts({
            chart: {
                type: 'column'
            },
            title: {
                text: '<?php echo $title;?>'
            },
            xAxis: {
                categories: [<?php
                    foreach($product_spend_month as $pso_key=> $pso_val){
                        echo "'".date('m/Y',strtotime($pso_key))."',";
                    }
                ?>]
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Spent'
                },
                stackLabels: {
                    enabled: true,
                    style: {
                        fontWeight: 'bold',
                        color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                    }
                }
            },
            legend: {
                align: 'right',
                x: -70,
                verticalAlign: 'top',
                y: 20,
                floating: true,
                backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColorSolid) || 'white',
                borderColor: '#CCC',
                borderWidth: 1,
                shadow: false
            },
            tooltip: {
                formatter: function() {
                    return this.series.name +': '+ this.y +'<br/>';
                }
            },
            plotOptions: {
                column: {
                    stacking: 'normal',
                    dataLabels: {
                        enabled: true,
                        color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white',
                        style: {
                            textShadow: '0 0 3px black, 0 0 3px black'
                        }
                    }
                }
            },
            series: [<?php 
                foreach($category_spend as $cs_key=> $cs_val){
                     echo "{name:'".$cs_key."',data:[".implode(',', $cs_val)."]},";
                }
            ?>]
        });
    });
    </script>
    <?php
}
/**
 * draw_working_hours
 */
function draw_working_hours($product_hours,$container){
    foreach($product_hours as $ph_key => $ph_val){
        foreach($ph_val as $pv_key => $pv_val){
            $working_hours[$pv_key][] = $pv_val;
        }
    }
    ?>
    <script type="text/javascript">
        $(function () {
            $('#<?php echo $container ?>').highcharts({
                chart: {
                    type: 'column'
                },
                title: {
                    text: 'Working Hours by Product'
                },
                xAxis: {
                    categories: [<?php
                        foreach($product_hours as $ph_key => $ph_val){
                            echo "'".date('m/Y',strtotime($ph_key))."',";
                        }
                    ?>
                    ]
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Hours'
                    }
                },
                tooltip: {
                    headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                    pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                        '<td style="padding:0"><b>{point.y} hours</b></td></tr>',
                    footerFormat: '</table>',
                    shared: true,
                    useHTML: true
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.2,
                        borderWidth: 0,
                        dataLabels: {
                            enabled: true
                        }
                    }
                },
                series: [
                    <?php 
                        foreach($working_hours as $wh_key=> $wh_val){
                             echo "{name:'".$wh_key."',data:[".implode(',', $wh_val)."]},";
                        }
                    ?>]
            });
        });
    </script>
    <?php
}

/**
 * put tms_info/other_info into table
 */
function put_tms_other_info_into_table($product_info,$ei,$oid,$su_id,$optype,$gtype,$keywords){
    if($ei == 'TMS'){
        $tms_info = $product_info['tms_info'];
        if(!empty($tms_info)){
            foreach($tms_info as $ti_key => $ti_val){
                $tms[$ti_val['PO_ID']][$ti_val['PROJECT_START_DATE']][] = $ti_val;
            }
            ?>
            <table class="table table-condensed table-striped table-hover">
                <thead>
                    <!--<th>#</th>-->
                    <th><h6><b>PO</b></h6></th>
                    <th><h6><b>Product</b></h6></th>
                    <th><h6><b>Projects</b></h6></th>
                    <th colspan="2"><h6><b>Sub Total / Original estimate</b></h6></th>
                    <th><h6><b>Change Reason</b></h6></th>
                    <th><h6><b>Status</b></h6></th>
                    <th><h6><b>Overall Delay</b></h6></th>
                    <th><h6><b>LANG</b></h6></th>
                    <th colspan="2"><h6><b>Start/Scheduled Date</b></h6></th>
                    <th colspan="2"><h6><b>Trans Delivery/Complete Date</b></h6></th>
                    <th></th>
                </thead>
                <?php
//                $m = 0;
                foreach($tms as $t_key => $t_val){
                    foreach($t_val as $tv_key => $tv_val){
                        foreach($tv_val as $tvv_key => $tvv_val){
                            $short_title = substr($tvv_val['JOB_TITLE'], 0, 20);
                            ?>
                            <tr>
                                <!--<td><?php echo ++$m;?></td>-->
                                <td><?php echo $tvv_val['PO_ID'];?></td>
                                <td><?php echo $tvv_val['NAME'];?></td>
                                <td title='<?php echo $tvv_val['JOB_TITLE']; ?>'><?php echo $short_title;?></td>
                                <td><?php echo "&#36;".round($tvv_val['ACTUAL_TOTAL']);?></td>    
                                <td><?php echo "&#36;".round($tvv_val['ORIGINAL_ESTIMATE']);?></td>
                                <td><?php echo $tvv_val['ORIGINAL_ESTIMATE_CHANGED_REASON'];?></td>
                                <td><?php echo $tvv_val['DELIVERY_STATUS'];?></td>
                                <td style="color:<?php echo ((empty($tvv_val['COMPLETE_DATE'])?date('Y-m-d'):$tvv_val['COMPLETE_DATE'])>(empty($tvv_val['SCHEDULED_DUE_DATE'])?date('Y-m-d'):$tvv_val['SCHEDULED_DUE_DATE'])); ?>"><?php echo ((empty($tvv_val['COMPLETE_DATE'])?date('Y-m-d'):$tvv_val['COMPLETE_DATE']) >(empty($tvv_val['SCHEDULED_DUE_DATE'])?date('Y-m-d'):$tvv_val['SCHEDULED_DUE_DATE']))?'Y':'N';?></td>
                                <td><?php echo $tvv_val['LANGUAGE'];?></td>
                                <td><?php echo !(($tvv_val['PROJECT_START_DATE'] == '0000-00-00')||(empty($tvv_val['PROJECT_START_DATE'])))?date('m/d/y',strtotime($tvv_val['PROJECT_START_DATE'])):"";?></td>
                                <td><?php echo !(($tvv_val['SCHEDULED_DUE_DATE'] == '0000-00-00')||(empty($tvv_val['SCHEDULED_DUE_DATE'])))?date('m/d/y',strtotime($tvv_val['SCHEDULED_DUE_DATE'])):"";?></td>
                                <td><?php echo !(($tvv_val['TRANS_DELIVERY_DATE'] == '0000-00-00')||(empty($tvv_val['TRANS_DELIVERY_DATE'])))?date('m/d/y',strtotime($tvv_val['TRANS_DELIVERY_DATE'])):"";?></td>
                                <td><?php echo !(($tvv_val['COMPLETE_DATE'] == '0000-00-00')||(empty($tvv_val['COMPLETE_DATE'])))?date('m/d/y',strtotime($tvv_val['COMPLETE_DATE'])):"";?></td>
                                <td>
                                    <a href='../../general/home/index.php?url=<?php echo base64_encode('task/netapp_update_task.php');?>&oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&optype=<?php echo base64_encode($optype); ?>&gtype=<?php echo base64_encode($gtype); ?>&ei=<?php echo base64_encode($ei);?>&id=<?php echo base64_encode($tvv_val['ID']);?>&type=edit&keywords=<?php echo base64_encode($keywords);?>'>
                                        <img width='16' height='16' src='../../../lib/image/icons/edit.png'/>
                                    </a>&nbsp;&nbsp;
                                    <a onclick="return delete_task()" href='../../general/home/index.php?url=<?php echo base64_encode('task/netapp_update_task.php');?>&oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&optype=<?php echo base64_encode($optype); ?>&ei=<?php echo base64_encode($ei);?>&gtype=<?php echo base64_encode($gtype); ?>&id=<?php echo base64_encode($tvv_val['ID']);?>&type=delete&keywords=<?php echo base64_encode($keywords);?>'>
                                        <img width='16' height='16' src='../../../lib/image/icons/remove.png'/>
                                    </a>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                }
                ?>
            </table>
            <?php
        }else{
            error('204');
        }
    }else{
        $other_info = $product_info['other_info'];
        if(!empty($other_info)){
            foreach($other_info as $oi_key => $oi_val){
                $other[$oi_val['PO_ID']][$oi_val['DATE']][] = $oi_val;
            }
            ?>
            <table class="table table-condensed table-striped table-hover">
                <thead>
                    <!--<th>#</th>-->
                    <th>PO</th>
                    <th>Product</th>
                    <th>Projects</th>
                    <th>Category</th>
                    <th>Job type</th>
                    <th>Date</th>
                    <th>Working hour</th>
                    <th>Rate</th>
                    <th>PM rate</th>
                    <th></th>
                </thead>
                <?php
//                $m = 0;
                foreach($other as $o_key => $o_val){
                    foreach($o_val as $ov_key => $ov_val){
                        foreach($ov_val as $ovv_key => $ovv_val){?>
                            <tr>
                                <!--<td><?php echo ++$m;?></td>-->
                                <td><?php echo $ovv_val['PO_ID'];?></td>
                                <td><?php echo $ovv_val['NAME'];?></td>
                                <td><?php echo $ovv_val['JOB_TITLE'];?></td>
                                <td><?php echo $ovv_val['CATEGORY_NAME'];?></td>    
                                <td><?php echo $ovv_val['JOBTYPE_NAME'];?></td>
                                <td><?php echo date('m/d/y',strtotime($ovv_val['DATE']));?></td>
                                <td><?php echo $ovv_val['WORKING_HOUR'];?></td>
                                <td><?php echo "&#36;".$ovv_val['HOUR_RATE'];?></td>
                                <td><?php echo $ovv_val['PM_RATE'];?></td>
                                <td>
                                    <a href='../../general/home/index.php?url=<?php echo base64_encode('task/netapp_update_task.php');?>&oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&optype=<?php echo base64_encode($optype); ?>&gtype=<?php echo base64_encode($gtype); ?>&ei=<?php echo base64_encode($ei);?>&id=<?php echo base64_encode($ovv_val['ID']);?>&type=edit&keywords=<?php echo base64_encode($keywords);?>'>
                                        <img width='16' height='16' src='../../../lib/image/icons/edit.png'/>
                                    </a>&nbsp;&nbsp;
                                    <a onclick="return delete_task()" href='../../general/home/index.php?url=<?php echo base64_encode('task/netapp_update_task.php');?>&oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&optype=<?php echo base64_encode($optype); ?>&gtype=<?php echo base64_encode($gtype); ?>&ei=<?php echo base64_encode($ei);?>&id=<?php echo base64_encode($ovv_val['ID']);?>&type=delete&keywords=<?php echo base64_encode($keywords);?>'>
                                        <img width='16' height='16' src='../../../lib/image/icons/remove.png'/>
                                    </a>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                }
                ?>
            </table>
            <?php
        }else{
            error('204');
        }
    }
}
?>