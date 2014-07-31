<?php
require_once __DIR__ . '/../../../lib/inc/constant_monitor.php';

/*
 * For one month  pie-donut
 * Draw the chart for draw_bug_analysis
 */

function draw_bug_analysis($sub_project_bug_status) {
    $i = 1;
    foreach ($sub_project_bug_status as $sobs_key => $sobs_val) {
        $query_life_cycle             = "";
        $max_query_life_cycle         = "";
        $min_query_life_cycle         = "";
        $array_max_query              = array();
        $array_min_query              = array();
        $max_query_no                 = "";
        $max_query_submitDate         = "";
        $max_query_modifyDate         = "";
        $min_query_no                 = "";
        $min_query_submitDate         = "";
        $min_query_modifyDate         = "";
        $max_query_submitter          = "";
        $min_query_submitter          = "";
        $query_life_cycle_display     = "";
        $max_query_life_cycle_display = "";
        $min_query_life_cycle_display = "";

        $bug_life_cycle             = "";
        $max_bug_life_cycle         = "";
        $min_bug_life_cycle         = "";
        $array_max_bug              = array();
        $array_min_bug              = array();
        $max_bug_no                 = "";
        $max_bug_submitDate         = "";
        $max_bug_modifyDate         = "";
        $min_bug_no                 = "";
        $min_bug_submitDate         = "";
        $min_bug_modifyDate         = "";
        $max_bug_submitter          = "";
        $min_bug_submitter          = "";
        $bug_life_cycle_display     = "";
        $max_bug_life_cycle_display = "";
        $min_bug_life_cycle_display = "";
        ?>
        <div class="bug_analysis">
            <div class="general_info label label-<?php echo $GLOBALS['BOOTSTRAP_LABEL_BADGE_COLOR'][$i]; ?>">
                <span class="org_name">
                    <?php echo $sobs_val['NAME']; ?>
                </span>
                <span class="general_bug_count">
                    BUG: <?php echo $sobs_val['COUNT']['BUG']; ?>
                </span>
                <span class="general_query_count">
                    QUERY: <?php echo $sobs_val['COUNT']['QUERY']; ?>
                </span>
            </div>
            <?php
                if ($sobs_val['COUNT']['QUERY'] != 0) {
                    $query_count = $sobs_val['COUNT']['QUERY'];
                    if (isset($sobs_val['COUNT']['queryTObug'])) {
                        $queryTObug_count = $sobs_val['COUNT']['queryTObug'];
                    } else {
                        $queryTObug_count = 0;
                    }
                    $qTb_rate = round($queryTObug_count / $query_count, 2) * 100;
                    foreach ($GLOBALS['BUG_CRITERIA']['queryTObug'] as $bcqb_key => $bcqb_val) {
                        if (($bcqb_val['PERCENT_START'] <= abs($qTb_rate)) && ($bcqb_val['PERCENT_END'] >= abs($qTb_rate))) {
                            $score_qtb = $bcqb_val['SCORE_MIN'] + round((( abs($qTb_rate) - $bcqb_val['PERCENT_START']) * ($bcqb_val['SCORE_MAX'] - $bcqb_val['SCORE_MIN'])) / ($bcqb_val['PERCENT_END'] - $bcqb_val['PERCENT_START']), 2);
                        }
                    }
                $qTb_rate_display = $qTb_rate . "%";
                $score_qtb_array  = explode('.', $score_qtb);
            ?>
            <div class="chart">
                <div class="chart_title"><h4>Query -> BUG</h4></div>
                <div id="queryTObug">
                    <h2>
                        <?php echo $score_qtb_array[0]; ?><sub>.<?php echo isset($score_qtb_array[1])?$score_qtb_array[1]:"00" ?></sub>
                    </h2>
                    <h5>Query -> Bug: <?php echo $queryTObug_count; ?></h5>
                    <h5>Rate: <?php echo $qTb_rate_display; ?></h5>
                </div>
            </div>
            <?php
                }
                if ($sobs_val['COUNT']['QUERY'] != 0) {
                    $query_life_cycle     = $sobs_val['LIFE']['QUERY']['AVERAGE'];
                    $max_query_life_cycle = $sobs_val['LIFE']['QUERY']['MAX'];
                    $min_query_life_cycle = $sobs_val['LIFE']['QUERY']['MIN'];
                    foreach ($GLOBALS['BUG_CRITERIA']['query_life'] as $bcql_key => $bcql_val) {
                        if (($bcql_val['DURATION_START'] <= $query_life_cycle) && ($bcql_val['DURATION_END'] >= $query_life_cycle)) {
                            $query_score_ql = $bcql_val['SCORE_MAX'] - round((($query_life_cycle - $bcql_val['DURATION_START']) * ($bcql_val['SCORE_MAX'] - $bcql_val['SCORE_MIN'])) / ($bcql_val['DURATION_END'] - $bcql_val['DURATION_START']), 2);
                        }
                    }
                    $query_life_cycle_display     = $query_life_cycle . "(h)";
                    $max_query_life_cycle_display = $max_query_life_cycle . "(h)";
                    $min_query_life_cycle_display = $min_query_life_cycle . "(h)";

                    $array_max_query      = $sobs_val['ORIGINAL_DATA']['MAX_QUERY_LIFE'];
                    $array_min_query      = $sobs_val['ORIGINAL_DATA']['MIN_QUERY_LIFE'];
                    
                    $max_query_no         = $array_max_query['BUG_ID'];
                    $max_query_submitDate = $array_max_query['SUBMIT_DATE'];
                    $max_query_modifyDate = $array_max_query['LAST_MODIFIED'];
                    
                    $min_query_no         = $array_min_query['BUG_ID'];
                    $min_query_submitDate = $array_min_query['SUBMIT_DATE'];
                    $min_query_modifyDate = $array_min_query['LAST_MODIFIED'];
                    
                    if($_SESSION['type'] == 4){
                        $max_query_submitter = isset($array_max_query['OWNER'])?$array_max_query['OWNER']:$array_max_query['NICK_NAME'];
                        $min_query_submitter = isset($array_min_query['OWNER'])?$array_min_query['OWNER']:$array_min_query['NICK_NAME'];
                    }else{
                        $max_query_submitter = $array_max_query['USER_NAME'];
                        $min_query_submitter = $array_min_query['USER_NAME'];
                    }
                $query_score_ql_array  = explode('.', $query_score_ql);
            ?>
            <div class="chart">
                <div class="chart_title"><h4>Query Life Cycle</h4></div>
                <div id="query_life">
                    <h2>
                        <?php echo $query_score_ql_array[0]; ?><sub>.<?php echo isset($query_score_ql_array[1])?$query_score_ql_array[1]:"00" ?></sub>
                    </h2>
                    <h5>Life Cycle: <?php echo $query_life_cycle_display; ?></h5>

                    <h5>Max: <?php echo $max_query_life_cycle_display; ?>
                        <a rel="popover" data-original-title="Detailed Query Info" class="detail_life_cycle" 
                           data-content="
                           Query No: <?php echo $max_query_no; ?><br>
                           Reporter: <?php echo $max_query_submitter; ?><br>
                           Submit: <?php echo $max_query_submitDate; ?><br>
                           Last Modify: <?php echo $max_query_modifyDate; ?><br>
                           ">
                            <img src='../../../lib/image/icons/flag-red.png'/>
                        </a>
                    </h5>
                    <!-- Submitter: <?php //echo $max_query_submitter; ?><br> -->
                    <h5>Min: <?php echo $min_query_life_cycle_display; ?>
                        <a rel="popover" data-original-title="Detailed Query Info" class="detail_life_cycle" 
                           data-content="
                           Query No: <?php echo $max_query_no; ?><br>
                           Reporter: <?php echo $min_query_submitter; ?><br>
                           Submit: <?php echo $max_query_submitDate; ?><br>
                           Last Modify: <?php echo $min_query_modifyDate; ?><br>
                           ">
                            <img src='../../../lib/image/icons/flag-green.png'/>
                        </a>
                    </h5>
                </div>
            </div>
            <?php
                }
                if ($sobs_val['COUNT']['BUG'] != 0) {
                    $bug_count         = $sobs_val['COUNT']['BUG'];
                    $invalid_bug_count = $sobs_val['COUNT']['INVALID_BUG'];
                    $valid_bug_count   = $bug_count - $invalid_bug_count;
                    $valid_bug_rate    = round(($bug_count - $invalid_bug_count) / $bug_count, 2) * 100;
                    foreach ($GLOBALS['BUG_CRITERIA']['valid_bug'] as $bcvb_key => $bcvb_val) {
                        if (($bcvb_val['PERCENT_START'] <= $valid_bug_rate) && ($bcvb_val['PERCENT_END'] >= $valid_bug_rate)) {
                            $score_vb = $bcvb_val['SCORE_MIN'] + round((abs($valid_bug_rate - $bcvb_val['PERCENT_START']) * ($bcvb_val['SCORE_MAX'] - $bcvb_val['SCORE_MIN'])) / ($bcvb_val['PERCENT_END'] - $bcvb_val['PERCENT_START']), 2);
                        }
                    }
                    $valid_bug_rate_display = $valid_bug_rate . "%";
                $score_vb_array = explode('.', $score_vb);
            ?>
            <div class="chart">
                <div class="chart_title"><h4>Valid Bug Rate</h4></div>
                <div id="valid_bug">
                    <h2>
                        <?php echo $score_vb_array[0]; ?><sub>.<?php echo isset($score_vb_array[1])?$score_vb_array[1]:"00" ?></sub>
                    </h2>
                    <h5>Valid Bug: <?php echo $valid_bug_count; ?></h5>
                    <h5>Rate: <?php echo $valid_bug_rate_display; ?></h5>
                </div>
            </div>
            <?php
                }
                if ($sobs_val['COUNT']['BUG'] != 0) {
                    $bug_life_cycle     = $sobs_val['LIFE']['BUG']['AVERAGE'];
                    $max_bug_life_cycle = $sobs_val['LIFE']['BUG']['MAX'];
                    $min_bug_life_cycle = $sobs_val['LIFE']['BUG']['MIN'];
                    foreach ($GLOBALS['BUG_CRITERIA']['bug_life'] as $bcql_key => $bcql_val) {
                        if (($bcql_val['DURATION_START'] <= $bug_life_cycle) && ($bcql_val['DURATION_END'] >= $bug_life_cycle)) {
                            $bug_score_ql = $bcql_val['SCORE_MAX'] - round((($bug_life_cycle - $bcql_val['DURATION_START']) * ($bcql_val['SCORE_MAX'] - $bcql_val['SCORE_MIN'])) / ($bcql_val['DURATION_END'] - $bcql_val['DURATION_START']), 2);
                        }
                    }
                    $bug_life_cycle_display     = $bug_life_cycle . "(h)";
                    $max_bug_life_cycle_display = $max_bug_life_cycle . "(h)";
                    $min_bug_life_cycle_display = $min_bug_life_cycle . "(h)";
                    
                    $array_max_bug      = $sobs_val['ORIGINAL_DATA']['MAX_BUG_LIFE'];
                    $array_min_bug      = $sobs_val['ORIGINAL_DATA']['MIN_BUG_LIFE'];
                    
                    $max_bug_no         = $array_max_bug['BUG_ID'];
                    $max_bug_submitDate = $array_max_bug['SUBMIT_DATE'];
                    $max_bug_modifyDate = $array_max_bug['LAST_MODIFIED'];
                    
                    $min_bug_no         = $array_min_bug['BUG_ID'];
                    $min_bug_submitDate = $array_min_bug['SUBMIT_DATE'];
                    $min_bug_modifyDate = $array_min_bug['LAST_MODIFIED'];

                    if($_SESSION['type'] == 4){
                        $max_bug_submitter = isset($array_max_bug['OWNER'])?$array_max_bug['OWNER']:$array_max_bug['NICK_NAME'];
                        $min_bug_submitter = isset($array_min_bug['OWNER'])?$array_min_bug['OWNER']:$array_min_bug['NICK_NAME'];
                    }else{
                        $max_bug_submitter = $array_max_bug['USER_NAME'];
                        $min_bug_submitter = $array_min_bug['USER_NAME'];
                    }
                $bug_score_ql_array = explode('.', $bug_score_ql)
            ?>
            <div class="chart">
                <div class="chart_title"><h4>Bug Life Cycle</h4></div>
                <div id="bug_life">
                    <h2>
                        <?php echo $bug_score_ql_array[0]; ?><sub>.<?php echo isset($bug_score_ql_array[1])?$bug_score_ql_array[1]:"00" ?></sub>
                    </h2>
                    <h5>Life Cycle: <?php echo $bug_life_cycle_display; ?></h5>
                    <h5>Max: <?php echo $max_bug_life_cycle_display; ?>
                        <a rel="popover" data-original-title="Detailed Bug Info" class="detail_life_cycle" 
                           data-content="
                           Bug No: <?php echo $max_bug_no; ?><br>
                           Reporter: <?php echo $max_bug_submitter; ?><br>
                           Submit: <?php echo $max_bug_submitDate; ?><br>
                           Last Modify: <?php echo $max_bug_modifyDate; ?><br>
                           ">
                            <img src='../../../lib/image/icons/flag-yellow.png'/>
                        </a>
                    </h5>
                    <h5>Min: <?php echo $min_bug_life_cycle_display; ?>
                        <a rel="popover" data-original-title="Detailed Bug Info" class="detail_life_cycle" 
                           data-content="
                           Bug No: <?php echo $min_bug_no; ?><br>
                           Reporter: <?php echo $min_bug_submitter; ?><br>
                           Submit: <?php echo $min_bug_submitDate; ?><br>
                           Last Modify: <?php echo $min_bug_modifyDate; ?><br>
                           ">
                            <img src='../../../lib/image/icons/flag-blue.png'/>
                        </a>
                    </h5>
                </div>
            </div>
            <?php
                }
            ?>
        </div>
        <hr>
        <?php
        if ($i == 5) {
            $i = 1;
        } else {
            $i++;
        }
    }
}

/*
 * For one month  pie-donut
 * Draw the chart for draw_bug_analysis external
 */

function draw_bug_analysis_external($sub_org_bug_status) {
    $i = 1;
    foreach ($sub_org_bug_status as $sobs_key => $sobs_val) {
        ?>
        <div class="bug_analysis">
            <div class="general_info label label-<?php echo $GLOBALS['BOOTSTRAP_LABEL_BADGE_COLOR'][$i]; ?>">
                <span class="org_name">
                    <?php echo $sobs_val['ORG_NAME']; ?>
                </span>
                <span class="general_bug_count">
                    BUG: <?php echo $sobs_val['COUNT']['BUG']; ?>
                </span>
                <span class="general_query_count">
                    QUERY: <?php echo $sobs_val['COUNT']['QUERY']; ?>
                </span>
            </div>
            <?php
                if ($sobs_val['COUNT']['QUERY'] != 0) {
                    $query_count = $sobs_val['COUNT']['QUERY'];
                    if (isset($sobs_val['COUNT']['queryTObug'])) {
                        $queryTObug_count = $sobs_val['COUNT']['queryTObug'];
                    } else {
                        $queryTObug_count = 0;
                    }
                    $qTb_rate = round($queryTObug_count / $query_count, 2) * 100;
                    $qTb_rate_display = $qTb_rate . "%";
            ?>
            <div class="chart">
                <div class="chart_title"><h3>Query -> BUG</h3></div>
                <div id="queryTObug">
                    <br>
                    <h3>Query -> Bug: <?php echo $queryTObug_count; ?></h3>
                    <h3>Rate: <?php echo $qTb_rate_display; ?></h3>
                </div>
            </div>
            <?php
                }
                if ($sobs_val['COUNT']['QUERY'] != 0) {
                    $query_life_cycle     = $sobs_val['LIFE']['QUERY']['AVERAGE'];
                    $max_query_life_cycle = $sobs_val['LIFE']['QUERY']['MAX'];
                    $min_query_life_cycle = $sobs_val['LIFE']['QUERY']['MIN'];

                    $query_life_cycle_display     = $query_life_cycle . "(h)";
                    $max_query_life_cycle_display = $max_query_life_cycle . "(h)";
                    $min_query_life_cycle_display = $min_query_life_cycle . "(h)";
                    $max_query_submitter          = $sobs_val['ORIGINAL_DATA']['MAX_QUERY_LIFE']['USER_NAME'];
                    $max_query_no                 = $sobs_val['ORIGINAL_DATA']['MAX_QUERY_LIFE']['BUG_ID'];
                    $max_query_submitDate         = $sobs_val['ORIGINAL_DATA']['MAX_QUERY_LIFE']['SUBMIT_DATE'];
                    $max_query_modifyDate         = $sobs_val['ORIGINAL_DATA']['MAX_QUERY_LIFE']['LAST_MODIFIED'];
                    $min_query_submitter          = $sobs_val['ORIGINAL_DATA']['MIN_QUERY_LIFE']['USER_NAME'];
                    $min_query_no                 = $sobs_val['ORIGINAL_DATA']['MIN_QUERY_LIFE']['BUG_ID'];
                    $min_query_submitDate         = $sobs_val['ORIGINAL_DATA']['MIN_QUERY_LIFE']['SUBMIT_DATE'];
                    $min_query_modifyDate         = $sobs_val['ORIGINAL_DATA']['MIN_QUERY_LIFE']['LAST_MODIFIED'];
            ?>
            <div class="chart">
                <div class="chart_title"><h3>Query Life Cycle</h3></div>
                <div id="query_life">
                    
                    <br>
                    <h3>Life Cycle: <?php echo $query_life_cycle_display; ?></h3>

                    <h4>Max Life Cycle: <?php echo $max_query_life_cycle_display; ?>
                        <a rel="popover" data-original-title="Detailed Query Info" class="detail_life_cycle" 
                           data-content="
                           Query No: <?php echo $max_query_no; ?><br>
                           Submit: <?php echo $max_query_submitDate; ?><br>
                           Last Modify: <?php echo $max_query_modifyDate; ?><br>
                           ">
                            <img src='../../../lib/image/icons/flag-red.png'/>
                        </a>
                    </h4>
                    <!--  
                    Submitter: <?php //echo $max_query_submitter; ?><br>
                    -->
                    <h4>Min Life Cycle: <?php echo $min_query_life_cycle_display; ?>
                        <a rel="popover" data-original-title="Detailed Query Info" class="detail_life_cycle" 
                           data-content="
                           Query No: <?php echo $max_query_no; ?><br>
                           Submit: <?php echo $max_query_submitDate; ?><br>
                           Last Modify: <?php echo $min_query_modifyDate; ?><br>
                           ">
                            <img src='../../../lib/image/icons/flag-green.png'/>
                        </a>
                    </h4>
                </div>
            </div>
            <?php
                }
                if ($sobs_val['COUNT']['BUG'] != 0) {
                    $bug_count         = $sobs_val['COUNT']['BUG'];
                    $invalid_bug_count = $sobs_val['COUNT']['INVALID_BUG'];
                    $valid_bug_count   = $bug_count - $invalid_bug_count;
                    $valid_bug_rate    = round(($bug_count - $invalid_bug_count) / $bug_count, 2) * 100;

                    $valid_bug_rate_display = $valid_bug_rate . "%";
            ?>
            <div class="chart">
                <div class="chart_title"><h3>Valid Bug Rate</h3></div>
                <div id="valid_bug">
                    
                    <br>
                    <h3>Valid Bug: <?php echo $valid_bug_count; ?></h3>
                    <h3>Rate: <?php echo $valid_bug_rate_display; ?></h3>
                </div>
            </div>
            <?php
                }
                if ($sobs_val['COUNT']['BUG'] != 0) {
                    $bug_life_cycle     = $sobs_val['LIFE']['BUG']['AVERAGE'];
                    $max_bug_life_cycle = $sobs_val['LIFE']['BUG']['MAX'];
                    $min_bug_life_cycle = $sobs_val['LIFE']['BUG']['MIN'];

                    $bug_life_cycle_display     = $bug_life_cycle . "(h)";
                    $max_bug_life_cycle_display = $max_bug_life_cycle . "(h)";
                    $min_bug_life_cycle_display = $min_bug_life_cycle . "(h)";
                    $max_bug_submitter          = $sobs_val['ORIGINAL_DATA']['MAX_BUG_LIFE']['USER_NAME'];
                    $max_bug_no                 = $sobs_val['ORIGINAL_DATA']['MAX_BUG_LIFE']['BUG_ID'];
                    $max_bug_submitDate         = $sobs_val['ORIGINAL_DATA']['MAX_BUG_LIFE']['SUBMIT_DATE'];
                    $max_bug_modifyDate         = $sobs_val['ORIGINAL_DATA']['MAX_BUG_LIFE']['LAST_MODIFIED'];
                    $min_bug_submitter          = $sobs_val['ORIGINAL_DATA']['MIN_BUG_LIFE']['USER_NAME'];
                    $min_bug_no                 = $sobs_val['ORIGINAL_DATA']['MIN_BUG_LIFE']['BUG_ID'];
                    $min_bug_submitDate         = $sobs_val['ORIGINAL_DATA']['MIN_BUG_LIFE']['SUBMIT_DATE'];
                    $min_bug_modifyDate         = $sobs_val['ORIGINAL_DATA']['MIN_BUG_LIFE']['LAST_MODIFIED'];
            ?>
            <div class="chart">
                <div class="chart_title"><h3>Bug Life Cycle</h3></div>
                <div id="bug_life">
                    <br>
                    <h3>Life Cycle: <?php echo $bug_life_cycle_display; ?></h3>
                    <h4>Max Life Cycle: <?php echo $max_bug_life_cycle_display; ?>
                        <a rel="popover" data-original-title="Detailed Bug Info" class="detail_life_cycle" 
                           data-content="
                           Bug No: <?php echo $max_bug_no; ?><br>
                           Submit: <?php echo $max_bug_submitDate; ?><br>
                           Last Modify: <?php echo $max_bug_modifyDate; ?><br>
                           ">
                            <img src='../../../lib/image/icons/flag-yellow.png'/>
                        </a>
                    </h4>
                    <h4>Min Life Cycle: <?php echo $min_bug_life_cycle_display; ?>
                        <a rel="popover" data-original-title="Detailed Bug Info" class="detail_life_cycle" 
                           data-content="
                           Bug No: <?php echo $min_bug_no; ?><br>
                           Submit: <?php echo $min_bug_submitDate; ?><br>
                           Last Modify: <?php echo $min_bug_modifyDate; ?><br>
                           ">
                            <img src='../../../lib/image/icons/flag-blue.png'/>
                        </a>
                    </h4>
                </div>
            </div>
            <?php
                }
            ?>
        </div>
        <hr>
        <?php
        if ($i == 5) {
            $i = 1;
        } else {
            $i++;
        }
    }
}


/*
 * draw bug category pie
 */

function draw_bug_report_by_pie($i,$title,$rendto,$name,$bug_report_info,$report){
    foreach($bug_report_info as $bri_key => $bri_val){
        $bug_report_info_count[$bri_key] = count($bri_val);
    }
    ?>
    <script type="text/javascript">
    $(function () {
        var chart;
        $(document).ready(function() {
            chart = new Highcharts.Chart({
                chart: {
                    renderTo: '<?php echo $rendto.$i;?>',
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false
                },
                title: {
                    text: '<?php echo $title;?>'
                },
                tooltip: {
                     pointFormat: '{point.percentage:.2f}%'
                },
                plotOptions: {
                    pie: {
                        size:'80%',
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '{point.name}: {point.percentage:.2f} %'
                        },
                        showInLegend: false
                    }
                },
                series: [{
                    type: 'pie',
                    name: '<?php echo $name; ?>',
                    data: [
                        <?php
                            foreach ($bug_report_info_count as $bric_key => $bric_val) {
                                switch ($report){
                                    case "0":
                                        echo "{name:'".$GLOBALS['BUG_CATEGORY'][$bric_key][0]."',y:".$bric_val.",color:'".$GLOBALS['BUG_CATEGORY_COLORS'][$bric_key]."'},";
                                        break;
                                    case "2":
                                        echo "{name:'".$bric_key."',y:".$bric_val.",color:'".$GLOBALS['BUG_SEVERITY_COLORS'][$bric_key]."'},";
                                        break;
                                    case "3":
                                        echo "{name:'".$bric_key."',y:".$bric_val.",color:'".$GLOBALS['BUG_PRIORITY_COLORS'][$bric_key]."'},";
                                        break;
                                }
                            }
                        ?>
                     ],
                }]
            });
        });
    });
    </script>
    <?php 
}

/*
 * draw bug status donut
 */

function draw_bug_status_donut($i,$bug_status,$bug_sub_status){
    $status_count = 0;
    foreach($bug_status as $bi_key => $bu_val){
        $bug_status_count[$bi_key] = count($bu_val);
    }
    foreach ($bug_status_count as $bsc_val){
        $status_count += $bsc_val;
    }
    foreach($bug_sub_status as $bss_key => $bss_val){
        if(empty($bss_key)){
            $bug_sub_status_count[0] = count($bss_val);
        }else{
            $bug_sub_status_count[$bss_key] = count($bss_val);
        }
    }
    ?>
    <script type="text/javascript">
    $(function () {
        var chart;
        $(document).ready(function() {
            var categories = ['Open', 'Closed'],
                name = 'Bug Status',
                bug_status = <?php echo json_encode($GLOBALS['BUG_STATUS_COLOR']); ?>,
                bug_sub_status = <?php echo json_encode($GLOBALS['BUG_SUB_STATUS_COLOR']); ?>,
                data = [{
                        y: <?php echo (round($bug_status_count['3']/$status_count,4)*100);?>,
                        color: bug_status['3'],
                        drilldown: {
                            name: 'Open',
                            categories: [  
                                <?php echo "'".$GLOBALS['BUG_STATUS']['3'][0]."',";?>],
                            stat: ['3'],
                            data: [  
                                <?php echo (round($bug_status_count['3']/$status_count,4)*100).",";?>],
                            color: bug_status['3']
                        }
                    }, {
                        y: <?php echo (round($bug_status_count['5']/$status_count,4)*100);?>,
                        color: bug_status['5'],
                        drilldown: {
                            name: 'Closed',
                            categories:[   
                                <?php
                                    foreach ($bug_sub_status_count as $bssc_key => $bssc_value) {
                                        if($bssc_key !== ""){
                                            echo "'".$GLOBALS['BUG_SUB_STATUS'][$bssc_key][0]."',";
                                        }
                                 }?>],
                            stat:[   
                                <?php
                                    foreach ($bug_sub_status_count as $bssc_key => $bssc_value) {
                                        if($bssc_key !== ""){
                                            echo "'".$bssc_key."',";
                                        }
                                 }?>],
                            data:[   
                                <?php
                                    foreach ($bug_sub_status_count as $bssc_key => $bssc_value) {
                                        echo (round($bssc_value/$status_count,4)*100).",";
                                 }?>]
                        }
                    }];
            var ScheduleData = [];
            var StatusData = [];
            for (var i = 0; i < data.length; i++) {
                ScheduleData.push({
                    name: categories[i],
                    y: data[i].y,
                    color: data[i].color
                });
                if(i === 0){
                    StatusData.push({
                                name: data[i].drilldown.categories[0],
                                y: data[i].drilldown.data[0],
                                color:data[i].drilldown.color
                            });
                }else{
                    for (var j = 0; j < data[i].drilldown.data.length; j++) {
                        StatusData.push({
                                    name: data[i].drilldown.categories[j],
                                    y: data[i].drilldown.data[j],
                                    color:bug_sub_status[data[i].drilldown.stat[j]]
                                });
                    }
                }
            }
            chart = new Highcharts.Chart({
                chart: {
                    renderTo: 'bug_status_<?php echo $i;?>',
                    type: 'pie'
                },
                title: {
                    text: name
                },
                yAxis: {
                    title: {
                        text: 'Total percent'
                    }
                },
                plotOptions: {
                    pie: {
                        shadow: false,
                        size:'80%'
                    }
                },
                tooltip: {
                    formatter: function() {
                        return '<b>'+ this.point.name +'</b>: '+ this.y +' %';
                    }
                },
                series: [{
                    name: 'Schedule',
                    data: ScheduleData,
                    size: '60%',
                    dataLabels: {
                        formatter: function() {
                            return this.point.name + ':</b> '+ this.y +'%';
                        },
                        color: 'white',
                        distance: -30
                    }
                }, {
                    name: 'Status',
                    data: StatusData,
                    size: '80%',
                    innerSize: '60%',
                    dataLabels: {
                        formatter: function() {
                            return this.point.name +':</b> '+ this.y +'%';
                        }
                    }
                }]
            });
        });

    });
    </script>
    <?php   
}

/*
 * draw bug report by columns
 */
function draw_bug_report_by_columns($i,$title,$name,$rendto,$bug_report_info){
    foreach($bug_report_info as $bri_key => $bri_val){
        $bug_report_info_count[$bri_key] = count($bri_val);
    }
    ?>
    <script>
        $(function () {
            $('#<?php echo $rendto.$i; ?>').highcharts({
                chart: {
                    type: 'column'
                },
                title: {
                    text: '<?php echo $title; ?>'
                },
                xAxis: {
                    categories: [<?php
                        foreach ($bug_report_info_count as $bric_key => $bric_val) {
                            echo "'".$bric_key."',";
                        }
                        ?>],
                    labels: {
                        rotation: -45,
                        align: 'right'
                    }
                },
                yAxis: {
                    min: 0,
                    title:{
                        text: ''
                    }
                },
                tooltip: {
                   formatter: function() {
                            return this.x +': '+ this.y ;
                        }
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.2,
                        borderWidth: 0
                    }
                },
                series: [{
                    name:'<?php echo $name; ?>',
                    data: [<?php
                        foreach ($bug_report_info_count as $bric_key => $bric_val) {
                            echo $bric_val.",";
                        }
                    ?>]

                }]
            });
        });

    </script>
    <?php
}
?>
