<?php
require_once __DIR__ . '/../../../lib/inc/constant_monitor.php';
require_once __DIR__ . '/../../../lib/inc/bootstrap_color.php';

/**
 * [draw_task_effort_daily Draw the chart that loading by default. Including the finished effort and unfinished effort]
 * @param  [array] $sub_org_task [sub org task effort]
 * @return       [Draw the chart using Highcharts]
 */
function draw_task_effort_daily($sub_org_task,$optype,$date_from,$date_to,$gtype,$su_id,$mark) {
    $i = 0;
    foreach ($sub_org_task as $sot_key => $sot_val) {
        if ((isset($sot_val['TIME_EFFORT']['FINISHED']['TODAY']['actual_total_time'])) && ($sot_val['TIME_EFFORT']['FINISHED']['TODAY']['expected_total_time'] != 0)) {
            $schedule_deviation = round(($sot_val['TIME_EFFORT']['FINISHED']['TODAY']['actual_total_time'] - $sot_val['TIME_EFFORT']['FINISHED']['TODAY']['expected_total_time']) / $sot_val['TIME_EFFORT']['FINISHED']['TODAY']['expected_total_time'], 2) * 100;

            foreach ($GLOBALS['TASK_SCHEDULE_CRITERIA'] as $tsc_key => $tsc_val) {
                if (($tsc_val['PERCENT_START'] <= abs($schedule_deviation)) && ($tsc_val['PERCENT_END'] >= abs($schedule_deviation))) {
                    $score = $tsc_val['SCORE_MAX'] - round((( abs($schedule_deviation) - $tsc_val['PERCENT_START']) * ($tsc_val['SCORE_MAX'] - $tsc_val['SCORE_MIN'])) / ($tsc_val['PERCENT_END'] - $tsc_val['PERCENT_START']), 2);
                }
            }
        } else {
            $schedule_deviation = NULL;
            $score = "N/A";
        }
        ?>
        <div class="progress_bar">
            <div class="span2">
                <span class="label label-<?php echo $GLOBALS['BOOTSTRAP_LABEL_BADGE_COLOR'][$i]; ?>"><?php echo $sot_val['NAME']; ?></span>
            </div>
            <div class="span1">
                <span class="label label-<?php echo $GLOBALS['BOOTSTRAP_LABEL_BADGE_COLOR'][$i]; ?>"><?php echo $score; ?></span>
            </div>
            <div class="progress">
                <?php
                    if($mark == 1){?>
                        <div  rel="popover" data-original-title="Finished Tasks" 
                              data-content="Tasks Count: <?php echo $sot_val['COUNT']['FINISHED']['TODAY']; ?><br>
                              Estimated Effort Time: <?php echo round($sot_val['TIME_EFFORT']['FINISHED']['TODAY']['expected_total_time'], 2) . "h"; ?><br>
                              Actual Effort Time: <?php echo round($sot_val['TIME_EFFORT']['FINISHED']['TODAY']['actual_total_time'], 2) . "h"; ?>&nbsp;&nbsp;
                              <a href='../../general/subscribe/index.php?url=<?php echo base64_encode("task/task_status_iteration.php"); ?>&oid=<?php echo base64_encode($sot_key); ?>&optype=<?php echo base64_encode($optype); ?>&date_from=<?php echo base64_encode($date_from);?>&date_to=<?php echo base64_encode($date_to);?>&name=<?php echo base64_encode($sot_val['NAME']);?>&mark=<?php echo base64_encode($mark);?>'><i class='icon-eye-2'></i></a><br>
                              TSDR: <?php echo abs($schedule_deviation) . "%"; ?>" 
                              class="finished bar" style="width: <?php echo $sot_val['COUNT']['FINISHED_RATE'] . "%"; ?>">
                                  <?php echo $sot_val['COUNT']['FINISHED_RATE'] . "%"; ?>
                        </div>
                        <?php 
                    }else{?>
                        <div  rel="popover" data-original-title="Finished Tasks" 
                              data-content="Tasks Count: <?php echo $sot_val['COUNT']['FINISHED']['TODAY']; ?><br>
                              Estimated Effort Time: <?php echo round($sot_val['TIME_EFFORT']['FINISHED']['TODAY']['expected_total_time'], 2) . "h"; ?><br>
                              Actual Effort Time: <?php echo round($sot_val['TIME_EFFORT']['FINISHED']['TODAY']['actual_total_time'], 2) . "h"; ?>&nbsp;&nbsp;
                              <a href='../../general/home/index.php?url=<?php echo base64_encode("task/task_status_iteration.php"); ?>&oid=<?php echo base64_encode($sot_key); ?>&optype=<?php echo base64_encode($optype); ?>&gtype=<?php echo base64_encode($gtype);?>&date_from=<?php echo base64_encode($date_from);?>&date_to=<?php echo base64_encode($date_to);?>&su_id=<?php echo base64_encode($su_id);?>&mark=<?php echo base64_encode($mark);?>'><i class='icon-eye-2'></i></a><br>
                              TSDR: <?php echo abs($schedule_deviation) . "%"; ?>" 
                              class="finished bar" style="width: <?php echo $sot_val['COUNT']['FINISHED_RATE'] . "%"; ?>">
                                  <?php echo $sot_val['COUNT']['FINISHED_RATE'] . "%"; ?>
                        </div>
                    <?php
                    }
                    if ($sot_val['COUNT']['UNFINISHED'] != 0) { ?>
                    <div rel="popover" data-original-title="Tasks In Queue" 
                         data-content="Tasks Count: <?php echo $sot_val['COUNT']['UNFINISHED']; ?><br>
                         Estimated Effort Time: <?php echo round($sot_val['TIME_EFFORT']['UNFINISHED']['expected_total_time'], 2) . "h"; ?>"
                         class="unfinish bar bar-warning" style="width: <?php echo (100 - $sot_val['COUNT']['FINISHED_RATE']) . "%"; ?>">
                    </div>
                <?php } else { ?>
                    <div rel="popover" data-original-title="" data-content="" class="unfinish bar bar-info" style="width: 100%">No Tasks</div>
                <?php }
                ?>
            </div>
        </div>
        <?php
        if((array_sum($sot_val['TIME_EFFORT']['EFFORT_PER_DAY']) != 0) || (array_sum($sot_val['TIME_EFFORT']['UNFINISHED'])!= 0)){        
        ?>
        <div class="task_effort">
            <?php
            /*
             * Get the data result from the following function in monitor_mgt.php
             */
            draw_task_effort_history_comparison($i,$sot_val['TIME_EFFORT']);
            ?>
            <div id="task_effort_history_comparison_<?php echo $i; ?>" class="task_effort_history_comparison">
            </div>
            <?php
            /*
             * Get the data result from the following function in monitor_mgt.php
             */
            draw_task_effort_forecast($i,$sot_val);
            ?>
            <div id="task_effort_forecast_<?php echo $i; ?>" class="task_effort_forecast">
            </div>
        </div>
        <?php
        }
        ?>
        <hr>
        <?php
        $i++;
    }
}

/**
 * [draw_task_effort_daily Draw the chart that loading by default. Including the finished effort and unfinished effort]
 * @param  [array] $sub_org_task [sub org task effort]
 * @return       [Draw the chart using Highcharts]
 */
function draw_task_effort_daily_external($sub_org_task) {
    $i = 0;
    foreach ($sub_org_task as $sot_key => $sot_val) {
        if ((isset($sot_val['TIME_EFFORT']['FINISHED']['TODAY']['actual_total_time'])) && ($sot_val['TIME_EFFORT']['FINISHED']['TODAY']['expected_total_time'] != 0)) {
            $schedule_deviation = round(($sot_val['TIME_EFFORT']['FINISHED']['TODAY']['actual_total_time'] - $sot_val['TIME_EFFORT']['FINISHED']['TODAY']['expected_total_time']) / $sot_val['TIME_EFFORT']['FINISHED']['TODAY']['expected_total_time'], 2) * 100;

            foreach ($GLOBALS['TASK_SCHEDULE_CRITERIA'] as $tsc_key => $tsc_val) {
                if (($tsc_val['PERCENT_START'] <= abs($schedule_deviation)) && ($tsc_val['PERCENT_END'] >= abs($schedule_deviation))) {
                    $score = $tsc_val['SCORE_MAX'] - round((( abs($schedule_deviation) - $tsc_val['PERCENT_START']) * ($tsc_val['SCORE_MAX'] - $tsc_val['SCORE_MIN'])) / ($tsc_val['PERCENT_END'] - $tsc_val['PERCENT_START']), 2);
                }
            }
        } else {
            $schedule_deviation = NULL;
            $score = "N/A";
        }
        ?>
        <div class="progress_bar">
            <div class="span2">
                <span class="label label-<?php echo $GLOBALS['BOOTSTRAP_LABEL_BADGE_COLOR'][$i]; ?>"><?php echo $sot_val['ORG_NAME']; ?></span>
            </div>
            <div class="span1">
                <span class="label label-<?php echo $GLOBALS['BOOTSTRAP_LABEL_BADGE_COLOR'][$i]; ?>"><?php echo $score; ?></span>
            </div>
            <div class="progress">
                <div  rel="popover" data-original-title="Finished Tasks" 
                      data-content="Tasks Count: <?php echo $sot_val['COUNT']['FINISHED']['TODAY']; ?><br>
                      Estimated Effort Time: <?php echo round($sot_val['TIME_EFFORT']['FINISHED']['TODAY']['expected_total_time'], 2) . "h"; ?><br>
                      Actual Effort Time: <?php echo round($sot_val['TIME_EFFORT']['FINISHED']['TODAY']['actual_total_time'], 2) . "h"; ?><br>
                      TSDR: <?php echo abs($schedule_deviation) . "%"; ?>" 
                      class="finished bar" style="width: <?php echo $sot_val['COUNT']['FINISHED_RATE'] . "%"; ?>">
                          <?php echo $sot_val['COUNT']['FINISHED_RATE'] . "%"; ?>
                </div>
                <?php if ($sot_val['COUNT']['UNFINISHED'] != 0) { ?>
                    <div rel="popover" data-original-title="Tasks In Queue" 
                         data-content="Tasks Count: <?php echo $sot_val['COUNT']['UNFINISHED']; ?><br>
                         Estimated Effort Time: <?php echo round($sot_val['TIME_EFFORT']['UNFINISHED']['expected_total_time'], 2) . "h"; ?>"
                         class="unfinish bar bar-warning" style="width: <?php echo (100 - $sot_val['COUNT']['FINISHED_RATE']) . "%"; ?>">
                    </div>
                <?php } else { ?>
                    <div rel="popover" data-original-title="" data-content="" class="unfinish bar bar-info" style="width: 100%">No Tasks</div>
                <?php }
                ?>
            </div>
        </div>
        <?php
        if((array_sum($sot_val['TIME_EFFORT']['EFFORT_PER_DAY']) != 0) || (array_sum($sot_val['TIME_EFFORT']['UNFINISHED'])!= 0)){        
        ?>
        <div class="task_effort">
            <?php
            /*
             * Get the data result from the following function in monitor_mgt.php
             */
            draw_task_effort_history_comparison_external($i,$sot_val['TIME_EFFORT']);
            ?>
            <div id="task_effort_history_comparison_<?php echo $i; ?>" class="task_effort_history_comparison">
            </div>
            <?php
            /*
             * Get the data result from the following function in monitor_mgt.php
             */
            draw_task_effort_forecast($i,$sot_val);
            ?>
            <div id="task_effort_forecast_<?php echo $i; ?>" class="task_effort_forecast">
            </div>
        </div>
        <?php
        }
        ?>
        <hr>
        <?php
        $i++;
    }
}

/**
 * [draw_task_effort_ajax Draw the chart that loading by ajax. Including the finished effort and day by day effort]
 * @param  [array] $sub_org_task [sub org task effort]
 * @return       [Draw the chart using Highcharts]
 */
function draw_task_effort_ajax($sub_org_task,$array_tester_name,$date_from,$date_to,$optype,$gtype,$su_id,$mark) {
    $i = 0;
    foreach ($sub_org_task as $sot_key => $sot_val) {
        if ((isset($sot_val['TIME_EFFORT']['FINISHED']['DATE_SCOPE']['GENERAL']['actual_total_time'])) && ($sot_val['TIME_EFFORT']['FINISHED']['DATE_SCOPE']['GENERAL']['expected_total_time'] != 0)) {
            $schedule_deviation = round(($sot_val['TIME_EFFORT']['FINISHED']['DATE_SCOPE']['GENERAL']['actual_total_time'] - $sot_val['TIME_EFFORT']['FINISHED']['DATE_SCOPE']['GENERAL']['expected_total_time']) / $sot_val['TIME_EFFORT']['FINISHED']['DATE_SCOPE']['GENERAL']['expected_total_time'], 2) * 100;

            foreach ($GLOBALS['TASK_SCHEDULE_CRITERIA'] as $tsc_key => $tsc_val) {
                if (($tsc_val['PERCENT_START'] <= abs($schedule_deviation)) && ($tsc_val['PERCENT_END'] >= abs($schedule_deviation))) {
                    $score = $tsc_val['SCORE_MAX'] - round((( abs($schedule_deviation) - $tsc_val['PERCENT_START']) * ($tsc_val['SCORE_MAX'] - $tsc_val['SCORE_MIN'])) / ($tsc_val['PERCENT_END'] - $tsc_val['PERCENT_START']), 2);
                }
            }
        } else {
            $schedule_deviation = NULL;
            $score = "N/A";
        }
        if(!empty($array_tester_name)){
        ?>
        <div class="span1">
            <span class="label label-info">
                <?php  echo $array_tester_name;?>
            </span>
        </div>
        <br><br>
        <?php } ?>
        <div class="progress_bar">
            <div class="span2">
                <span class="label label-<?php echo $GLOBALS['BOOTSTRAP_LABEL_BADGE_COLOR'][$i]; ?>"><?php echo $sot_val['PROJECT_NAME']; ?></span>
            </div>
            <div class="span1">
                <span class="label label-<?php echo $GLOBALS['BOOTSTRAP_LABEL_BADGE_COLOR'][$i]; ?>"><?php echo $score; ?></span>
            </div>
            <div class="progress">
                <?php if ($sot_val['COUNT']['FINISHED']['DATE_SCOPE'] != 0) { 
                        if($mark == 1){?>
                            <div  rel="popover" data-original-title="Finished Tasks" 
                                  data-content="Tasks Count: <?php echo $sot_val['COUNT']['FINISHED']['DATE_SCOPE']; ?><br>
                                  Estimated Effort Time: <?php echo round($sot_val['TIME_EFFORT']['FINISHED']['DATE_SCOPE']['GENERAL']['expected_total_time'], 2) . "h"; ?><br>
                                  Actual Effort Time: <?php echo round($sot_val['TIME_EFFORT']['FINISHED']['DATE_SCOPE']['GENERAL']['actual_total_time'], 2) . "h"; ?>&nbsp;&nbsp;
                                  <a href='../../general/subscribe/index.php?url=<?php echo base64_encode("task/task_status_iteration.php"); ?>&oid=<?php echo base64_encode($sot_key); ?>&optype=<?php echo base64_encode($optype); ?>&date_from=<?php echo base64_encode($date_from);?>&date_to=<?php echo base64_encode($date_to);?>&name=<?php echo base64_encode($sot_val['PROJECT_NAME']);?>&mark=<?php echo base64_encode($mark);?>'><i class='icon-eye-2'></i></a><br>
                                  TSDR: <?php echo abs($schedule_deviation) . "%"; ?>" 
                                  class="finished bar" style="width: 100%">
                                      <?php echo abs($schedule_deviation) . "%"; ?>
                            </div>
                            <?php 
                        }else{?>
                            <div  rel="popover" data-original-title="Finished Tasks" 
                                  data-content="Tasks Count: <?php echo $sot_val['COUNT']['FINISHED']['DATE_SCOPE']; ?><br>
                                  Estimated Effort Time: <?php echo round($sot_val['TIME_EFFORT']['FINISHED']['DATE_SCOPE']['GENERAL']['expected_total_time'], 2) . "h"; ?><br>
                                  Actual Effort Time: <?php echo round($sot_val['TIME_EFFORT']['FINISHED']['DATE_SCOPE']['GENERAL']['actual_total_time'], 2) . "h"; ?>&nbsp;&nbsp;
                                  <a href='../../general/home/index.php?url=<?php echo base64_encode("task/task_status_iteration.php"); ?>&oid=<?php echo base64_encode($sot_key); ?>&optype=<?php echo base64_encode($optype); ?>&gtype=<?php echo base64_encode($gtype);?>&su_id=<?php echo base64_encode($su_id);?>&date_from=<?php echo base64_encode($date_from);?>&date_to=<?php echo base64_encode($date_to);?>&mark=<?php echo base64_encode($mark);?>'><i class='icon-eye-2'></i></a><br>
                                  TSDR: <?php echo abs($schedule_deviation) . "%"; ?>" 
                                  class="finished bar" style="width: 100%">
                                      <?php echo abs($schedule_deviation) . "%"; ?>
                            </div>
                            <?php
                        }
                }else { ?>
                    <div rel="popover" data-original-title="" data-content="" class="unfinish bar bar-info" style="width: 100%">No Tasks</div>
                <?php }
                ?>
            </div>
        </div>
        <div class="task_effort">
            <?php
            /**
             * Get the data result from the following function in monitor_mgt.php
             */
            if($_SESSION['type'] != 4){
                if(isset($sot_val['COUNT']['MD']['DATE_SCOPE'])){
                    draw_task_effort_day_by_day($i,$sot_val);
                }
            }else{
                if(isset($sot_val['COUNT']['MD']['DATE_SCOPE'])){
                    draw_task_effort_day_by_day_3rd($i,$sot_val);
                }
            }
            ?>
            <div id="task_effort_day_by_day_<?php echo $i; ?>" class="task_effort_day_by_day">
            </div>
        </div>
        <hr>
        <?php
        $i++;
    }
}

/**
 * [draw_task_effort_history_comparison description]
 * @param  [array] $sub_org_task [sub org task effort]
 * @return [type]               [description]
 */
function draw_task_effort_history_comparison($i,$task_effort) {
    ?>
    <script type="text/javascript">
        $(function () {
            var chart;
            $(document).ready(function() {            
                chart = new Highcharts.Chart({
                    chart: {
                        renderTo: 'task_effort_history_comparison_<?php echo $i; ?>',
                        type: 'column'
                    },
                    title: {
                        text: 'Compare with History'
                    },
                    xAxis: {
                        categories: [
                            'This Week',
                            'Last Week',
                            'Last Month'
                        ]
                    },
                    yAxis: [{
                        title: {
                            text: 'Finished Effort',
                            style: {
                                color: '#F9A329'
                            }
                        },
                        labels: {
                            formatter: function() {
                                return this.value +'h';
                            },
                            style: {
                                color: '#F9A329'
                            }
                        }
                    },{
                        title: {
                            text: 'Effort Per Workday',
                            style: {
                                color: '#5DB75D'
                            }
                        },
                        labels: {
                            formatter: function() {
                                return this.value +'h';
                            },
                            style: {
                                color: '#5DB75D'
                            }
                        },
                        opposite: true
                    },{
                        title: {
                            text: 'Effort Per Man * Day',
                            style: {
                                color: '#2F7ED8'
                            }
                        },
                        labels: {
                            formatter: function() {
                                return this.value +'h';
                            },
                            style: {
                                color: '#2F7ED8'
                            }
                        },
                        opposite: true
                    }],
                    legend: {
                        shadow: true,
                        backgroundColor: '#FFFFFF',
                        itemStyle: {
                            fontSize: '12px'
                        },
                        reversed: true
                    },
                    tooltip: {
                        formatter: function() {
                            return ''+
                                this.x +': '+ this.y +' h';
                        }
                    },
                    plotOptions: {
                        column: {
                            pointPadding: 0.2,
                            borderWidth: 0
                        },
                        area: {
                            fillOpacity: 0.7
                        }
                    },
                    series: [{
                            name: 'Finished Task',
                            color: '#F9A329',
                            type: 'area',
                            data: [
                                    <?php
                                        echo $task_effort['FINISHED']['THIS_WEEK']['GENERAL']['expected_total_time'];
                                    ?>,
                                    <?php
                                        echo $task_effort['FINISHED']['LAST_WEEK']['expected_total_time'];
                                    ?>,
                                    null
                            ],
                            yAxis: 0
                        },{
                            name: 'BUG',
                            color: '#ED5A4B',
                            type: 'area',
                            data: [
                                    <?php
                                        echo $task_effort['FINISHED']['THIS_WEEK']['GENERAL']['bug_time'];
                                    ?>,
                                    <?php
                                        echo $task_effort['FINISHED']['LAST_WEEK']['bug_time'];
                                    ?>,
                                    null,
                            ],
                            yAxis: 0
                        }, {
                            name: 'Effort / Workday',
                            color: '#5DB75D',
                            data: [
                                    <?php
                                        echo $task_effort['EFFORT_PER_DAY']['THIS_WEEK'];
                                    ?>,
                                    <?php
                                        echo $task_effort['EFFORT_PER_DAY']['LAST_WEEK'];
                                    ?>,
                                    <?php
                                        echo $task_effort['EFFORT_PER_DAY']['LAST_MONTH'];
                                    ?>,
                            ],
                            dataLabels: {
                                enabled: true,
                                rotation: -45,
                                color: '#274B6D',
                                align: 'center',
                                formatter: function() {
                                    return this.y;
                                },
                                style: {
                                    fontSize: '12px'
                                }
                            },
                            yAxis: 1
                        }, {
                            name: 'Effort / Man*Day',
                            color: '#2F7ED8',
                            data: [
                                    <?php
                                        echo $task_effort['EFFORT_PER_ENGINEER_DAY']['THIS_WEEK'];
                                    ?>,
                                    <?php
                                        echo $task_effort['EFFORT_PER_ENGINEER_DAY']['LAST_WEEK'];
                                    ?>,
                                    <?php
                                        echo $task_effort['EFFORT_PER_ENGINEER_DAY']['LAST_MONTH'];
                                    ?>
                                ],
                            dataLabels: {
                                enabled: true,
                                rotation: -45,
                                color: '#274B6D',
                                align: 'center',
                                formatter: function() {
                                    return this.y;
                                },
                                style: {
                                    fontSize: '12px'
                                }
                            },
                            yAxis: 2
                        }]
                });
            });
                
        });
    </script>
    <?php
}

/**
 * [draw_task_effort_history_comparison description]
 * @param  [array] $sub_org_task [sub org task effort]
 * @return [type]               [description]
 */
function draw_task_effort_history_comparison_external($i,$task_effort) {
    ?>
    <script type="text/javascript">
        $(function () {
            var chart;
            $(document).ready(function() {
                chart = new Highcharts.Chart({
                    chart: {
                        renderTo: 'task_effort_history_comparison_<?php echo $i; ?>',
                        type: 'column'
                    },
                    title: {
                        text: 'Compare with History'
                    },
                    xAxis: {
                        categories: [
                            'This Week',
                            'Last Week',
                            'Last Month'
                        ]
                    },
                    yAxis: [{
                        title: {
                            text: 'Finished Effort',
                            style: {
                                color: '#F9A329'
                            }
                        },
                        labels: {
                            formatter: function() {
                                return this.value +'h';
                            },
                            style: {
                                color: '#F9A329'
                            }
                        }
                    },{
                        title: {
                            text: 'Effort Per Workday',
                            style: {
                                color: '#5DB75D'
                            }
                        },
                        labels: {
                            formatter: function() {
                                return this.value +'h';
                            },
                            style: {
                                color: '#5DB75D'
                            }
                        },
                        opposite: true
                    },{
                        title: {
                            text: 'Effort Per Man * Day',
                            style: {
                                color: '#2F7ED8'
                            }
                        },
                        labels: {
                            formatter: function() {
                                return this.value +'h';
                            },
                            style: {
                                color: '#2F7ED8'
                            }
                        },
                        opposite: true
                    }],
                    legend: {
                        shadow: true,
                        backgroundColor: '#FFFFFF',
                        itemStyle: {
                            fontSize: '12px'
                        },
                        reversed: true
                    },
                    tooltip: {
                        formatter: function() {
                            return ''+
                                this.x +': '+ this.y +' h';
                        }
                    },
                    plotOptions: {
                        column: {
                            pointPadding: 0.2,
                            borderWidth: 0
                        },
                        area: {
                            fillOpacity: 0.7
                        }
                    },
                    series: [{
                            name: 'Finished',
                            color: '#F9A329',
                            type: 'area',
                            data: [
                                    <?php
                                        echo $task_effort['FINISHED']['THIS_WEEK']['GENERAL']['actual_total_time'];
                                    ?>,
                                    <?php
                                        echo $task_effort['FINISHED']['LAST_WEEK']['actual_total_time'];
                                    ?>,
                                    null
                            ],
                            yAxis: 0
                        }, {
                            name: 'Effort / Workday',
                            color: '#5DB75D',
                            data: [
                                    <?php
                                        echo $task_effort['EFFORT_PER_DAY']['THIS_WEEK'];
                                    ?>,
                                    <?php
                                        echo $task_effort['EFFORT_PER_DAY']['LAST_WEEK'];
                                    ?>,
                                    <?php
                                        echo $task_effort['EFFORT_PER_DAY']['LAST_MONTH'];
                                    ?>,
                            ],
                            dataLabels: {
                                enabled: true,
                                rotation: -45,
                                color: '#274B6D',
                                align: 'center',
                                formatter: function() {
                                    return this.y;
                                },
                                style: {
                                    fontSize: '12px'
                                }
                            },
                            yAxis: 1
                        }, {
                            name: 'Effort / Man*Day',
                            color: '#2F7ED8',
                            data: [
                                    <?php
                                        echo $task_effort['EFFORT_PER_ENGINEER_DAY']['THIS_WEEK'];
                                    ?>,
                                    <?php
                                        echo $task_effort['EFFORT_PER_ENGINEER_DAY']['LAST_WEEK'];
                                    ?>,
                                    <?php
                                        echo $task_effort['EFFORT_PER_ENGINEER_DAY']['LAST_MONTH'];
                                    ?>
                                ],
                            dataLabels: {
                                enabled: true,
                                rotation: -45,
                                color: '#274B6D',
                                align: 'center',
                                formatter: function() {
                                    return this.y;
                                },
                                style: {
                                    fontSize: '12px'
                                }
                            },
                            yAxis: 2
                        }]
                });
            });
                
        });
    </script>
    <?php
}

function draw_task_effort_forecast($i,$task_effort) {
    ?>
    <script type="text/javascript">
        $(function () {
            var chart;
            $(document).ready(function() {
                chart = new Highcharts.Chart({
                    chart: {
                        renderTo: 'task_effort_forecast_<?php echo $i; ?>',
                        zoomType: 'xy'
                    },
                    title: {
                        text: 'Daily Task Effort'
                    },
                    subtitle: {
                        text: ''
                    },
                    xAxis: [{
                            categories: [
                                <?php
                                    foreach($task_effort['TIME_EFFORT']['FINISHED']['THIS_WEEK']['DETAILED'] as $te_key => $te_val){
                                        echo "'".$te_key."',";
                                    }
                                ?>
                            ]
                        },
                        {
                            categories: [
                                <?php
                                    foreach($task_effort['TIME_EFFORT']['STANDARD']['THIS_WEEK']['DETAILED'] as $tes_key => $tes_val){
                                        echo "'".$tes_key."',";
                                    }
                                ?>
                            ],
                            title: {
                                text: 'Standard Estimate',
                                style: {
                                    color: '#D84D47'
                                }
                            },
                            labels: {
                                style: {
                                    color: '#D84D47'
                                }
                            },
                            opposite: true
                        }],
                    yAxis: [{ // Primary yAxis
                            min: 0,
                            title: {
                                text: 'Actual Effort',
                                style: {
                                    color: '#5DB75D'
                                }
                            },
                            labels: {
                                formatter: function() {
                                    return this.value +'h';
                                },
                                style: {
                                    color: '#5DB75D'
                                }
                            },
                            linkedTo: 1
                        }, { // Secondary yAxis
                            min: 0,
                            title: {
                                text: 'Estimate Effort',
                                style: {
                                    color: '#F9A329'
                                }
                            },
                            labels: {
                                formatter: function() {
                                    return this.value +'h';
                                },
                                style: {
                                    color: '#F9A329'
                                }
                            }
                        },{ // Third yAxis
                            min: 0,
                            minTickInterval: 5,
                            title: {
                                text: 'Actual Head Count',
                                style: {
                                    color: '#40A7C5'
                                }
                            },
                            labels: {
                                style: {
                                    color: '#40A7C5'
                                }
                            },
                            opposite: true
                        },{ // Fourth yAxis
                            min: 0,
                            minTickInterval: 5,
                            title: {
                                text: 'Standard Estimate',
                                style: {
                                    color: '#D84D47'
                                }
                            },
                            labels: {
                                style: {
                                    color: '#D84D47'
                                }
                            },
                            opposite: true
                        }],
                    tooltip: {
                        formatter: function() {
                            return ''+
                                this.x +': '+ this.y +
                                (this.series.name == 'AHC' ? '' : ' h');
                        }
                    },
                    legend: {
                        shadow: true,
                        backgroundColor: '#FFFFFF',
                        itemStyle: {
                            fontSize: '12px'
                        },
                        reversed: true
                    },
                    series: [{
                            name: 'Actual',
                            color: '#5DB75D',
                            type: 'column',
                            yAxis: 0,
                            data: [
                                <?php
                                    foreach($task_effort['TIME_EFFORT']['FINISHED']['THIS_WEEK']['DETAILED'] as $te_key => $te_val){
                                        echo $te_val['actual_total_time'].",";
                                    }
                                ?>
                            ],
                            dataLabels: {
                                enabled: true,
                                rotation: 0,
                                color: '#274B6D',
                                align: 'center',
                                formatter: function() {
                                    return this.y;
                                },
                                style: {
                                    fontSize: '11px'
                                }
                            }            
                        }, {
                            name: 'Estimate',
                            color: '#F9A329',
                            type: 'spline',
                            dashStyle: 'ShortDot',
                            yAxis: 1,
                            data: [
                                <?php
                                    foreach($task_effort['TIME_EFFORT']['FINISHED']['THIS_WEEK']['DETAILED'] as $te_key => $te_val){
                                        echo $te_val['expected_total_time'].",";
                                    }
                                ?>
                            ]
                        }, {
                            name: 'AHC',
                            color: '#40A7C5',
                            type: 'line',
                            dashStyle: 'Dot',
                            yAxis: 2,
                            data: [
                                <?php
                                    foreach($task_effort['COUNT']['MD']['THIS_WEEK'] as $te_key => $te_val){
                                        echo $te_val.",";
                                    }
                                ?>

                            ]
                        }, {
                            name: 'Standard',
                            color: '#D84D47',
                            type: 'line',
                            dashStyle: 'LongDot',
                            yAxis: 3,
                            xAxis: 1,
                            data: [
                                <?php
                                    foreach($task_effort['TIME_EFFORT']['STANDARD']['THIS_WEEK']['DETAILED'] as $tesd_key => $tesd_val){
                                        echo $tesd_val['expected_total_time'].",";
                                    }
                                ?>

                            ]
                        }]
                });
            });
            
        });
    </script>
    <?php
}

/**
 * Author: CC
 * Date: 09/09/2013
 * [draw_task_effort_day_by_day For internal]
 * @param  [type] $i           [description]
 * @param  [type] $task_effort [description]
 * @return [type]              [description]
 */
function draw_task_effort_day_by_day($i,$task_effort) {
    ?>
    <script type="text/javascript">
        $(function () {
            var chart;
            $(document).ready(function() {
                chart = new Highcharts.Chart({
                    chart: {
                        renderTo: 'task_effort_day_by_day_<?php echo $i; ?>',
                        zoomType: 'xy'
                    },
                    title: {
                        text: 'Daily Task Effort'
                    },
                    subtitle: {
                        text: ''
                    },
                    xAxis: [{
                            categories: [
                                <?php
                                    foreach($task_effort['TIME_EFFORT']['FINISHED']['DATE_SCOPE']['DETAILED'] as $te_key => $te_val){
                                        echo "'".$te_key."',";
                                    }
                                ?>
                            ]
                        },],
                    yAxis: [{ // Primary yAxis
                            min: 0,
                            title: {
                                text: 'Actual Effort',
                                style: {
                                    color: '#5DB75D'
                                }
                            },
                            labels: {
                                formatter: function() {
                                    return this.value +'h';
                                },
                                style: {
                                    color: '#5DB75D'
                                }
                            }
                        }, { // Secondary yAxis
                            min: 0,
                            title: {
                                text: 'Estimate Effort',
                                style: {
                                    color: '#F9A329'
                                }
                            },
                            labels: {
                                formatter: function() {
                                    return this.value +'h';
                                },
                                style: {
                                    color: '#F9A329'
                                }
                            },
                            linkedTo: 0
                        },{ // Third yAxis
                            min: 0,
                            minTickInterval: 5,
                            title: {
                                text: 'Head Count',
                                style: {
                                    color: '#40A7C5'
                                }
                            },
                            labels: {
                                style: {
                                    color: '#40A7C5'
                                }
                            },
                            opposite: true
                        }],
                    tooltip: {
                        formatter: function() {
                            return ''+
                                this.x +': '+ this.y +
                                (this.series.name == 'HC' ? '' : ' h');
                        }
                    },
                    legend: {
                        shadow: true,
                        backgroundColor: '#FFFFFF',
                        itemStyle: {
                            fontSize: '12px'
                        },
                        reversed: true
                    },
                    series: [{
                            name: 'Actual',
                            color: '#5DB75D',
                            type: 'column',
                            yAxis: 0,
                            data: [
                                <?php
                                    foreach($task_effort['TIME_EFFORT']['FINISHED']['DATE_SCOPE']['DETAILED'] as $te_key => $te_val){
                                        echo $te_val['actual_total_time'].",";
                                    }
                                ?>
                            ],
                            dataLabels: {
                                enabled: true,
                                rotation: 0,
                                color: '#274B6D',
                                align: 'center',
                                formatter: function() {
                                    return this.y;
                                },
                                style: {
                                    fontSize: '11px'
                                }
                            }            
                        }, {
                            name: 'Estimate',
                            color: '#F9A329',
                            type: 'spline',
                            dashStyle: 'ShortDot',
                            yAxis: 1,
                            data: [
                                <?php
                                    foreach($task_effort['TIME_EFFORT']['FINISHED']['DATE_SCOPE']['DETAILED'] as $te_key => $te_val){
                                        echo $te_val['expected_total_time'].",";
                                    }
                                ?>
                            ]
                        }, {
                            name: 'HC',
                            color: '#40A7C5',
                            type: 'line',
                            dashStyle: 'Dot',
                            yAxis: 2,
                            data: [
                                <?php
                                    foreach($task_effort['COUNT']['MD']['DATE_SCOPE'] as $te_key => $te_val){
                                        echo $te_val.",";
                                    }
                                ?>

                            ]
                        }]
                });
            });
            
        });
    </script>
    <?php
}

/**
 * Author: CC
 * Date: 09/09/2013
 * [draw_task_effort_day_by_day_subscribe For Customer]
 * @param  [type] $i           [description]
 * @param  [type] $task_effort [description]
 * @return [type]              [description]
 */
function draw_task_effort_day_by_day_3rd($i,$task_effort) {
    ?>
    <script type="text/javascript">
        $(function () {
            var chart;
            $(document).ready(function() {
                chart = new Highcharts.Chart({
                    chart: {
                        renderTo: 'task_effort_day_by_day_<?php echo $i; ?>',
                        zoomType: 'xy'
                    },
                    title: {
                        text: 'Daily Task Effort'
                    },
                    subtitle: {
                        text: ''
                    },
                    xAxis: [{
                            categories: [
                                <?php
                                    foreach($task_effort['TIME_EFFORT']['FINISHED']['DATE_SCOPE']['DETAILED'] as $te_key => $te_val){
                                        echo "'".$te_key."',";
                                    }
                                ?>
                            ]
                        },],
                    yAxis: [{ // Primary yAxis
                            min: 0,
                            title: {
                                text: 'Task Effort',
                                style: {
                                    color: '#5DB75D'
                                }
                            },
                            labels: {
                                formatter: function() {
                                    return this.value +'h';
                                },
                                style: {
                                    color: '#5DB75D'
                                }
                            }
                        },{ // Second yAxis
                            min: 0,
                            minTickInterval: 5,
                            title: {
                                text: 'Head Count',
                                style: {
                                    color: '#40A7C5'
                                }
                            },
                            labels: {
                                style: {
                                    color: '#40A7C5'
                                }
                            },
                            opposite: true
                        }],
                    tooltip: {
                        formatter: function() {
                            return ''+
                                this.x +': '+ this.y +
                                (this.series.name == 'HC' ? '' : ' h');
                        }
                    },
                    legend: {
                        shadow: true,
                        backgroundColor: '#FFFFFF',
                        itemStyle: {
                            fontSize: '12px'
                        },
                        reversed: true
                    },
                    series: [{
                            name: 'Task Effort',
                            color: '#5DB75D',
                            type: 'column',
                            yAxis: 0,
                            data: [
                                <?php
                                    foreach($task_effort['TIME_EFFORT']['FINISHED']['DATE_SCOPE']['DETAILED'] as $te_key => $te_val){
                                        echo $te_val['total_effort'].",";
                                    }
                                ?>
                            ],
                            dataLabels: {
                                enabled: true,
                                rotation: 0,
                                color: '#274B6D',
                                align: 'center',
                                formatter: function() {
                                    return this.y;
                                },
                                style: {
                                    fontSize: '11px'
                                }
                            }            
                        },{
                            name: 'HC',
                            color: '#40A7C5',
                            type: 'line',
                            dashStyle: 'Dot',
                            yAxis: 1,
                            data: [
                                <?php
                                    foreach($task_effort['COUNT']['MD']['DATE_SCOPE'] as $te_key => $te_val){
                                        echo $te_val.",";
                                    }
                                ?>

                            ]
                        }]
                });
            });
            
        });
    </script>
    <?php
}

/* draw personal task effort
*  2013-8-21
*  shiyan
*/

function draw_personal_task_effort_ajax($sub_org_task,$user_name) {
    $i = 0;
    foreach ($sub_org_task as $sot_key => $sot_val) {
        if ((isset($sot_val['TIME_EFFORT']['FINISHED']['DATE_SCOPE']['GENERAL']['actual_total_time'])) && ($sot_val['TIME_EFFORT']['FINISHED']['DATE_SCOPE']['GENERAL']['expected_total_time'] != 0)) {
            $schedule_deviation = round(($sot_val['TIME_EFFORT']['FINISHED']['DATE_SCOPE']['GENERAL']['actual_total_time'] - $sot_val['TIME_EFFORT']['FINISHED']['DATE_SCOPE']['GENERAL']['expected_total_time']) / $sot_val['TIME_EFFORT']['FINISHED']['DATE_SCOPE']['GENERAL']['expected_total_time'], 2) * 100;

            foreach ($GLOBALS['TASK_SCHEDULE_CRITERIA'] as $tsc_key => $tsc_val) {
                if (($tsc_val['PERCENT_START'] <= abs($schedule_deviation)) && ($tsc_val['PERCENT_END'] >= abs($schedule_deviation))) {
                    $score = $tsc_val['SCORE_MAX'] - round((( abs($schedule_deviation) - $tsc_val['PERCENT_START']) * ($tsc_val['SCORE_MAX'] - $tsc_val['SCORE_MIN'])) / ($tsc_val['PERCENT_END'] - $tsc_val['PERCENT_START']), 2);
                }
            }
        } else {
            $schedule_deviation = NULL;
            $score = "N/A";
        }
        ?>
        <div class="span1">
            <span class="label label-info">
                <?php  echo $user_name;?>
            </span>
        </div>
        <br><br>
        <div class="progress_bar">
            <div class="span2">
                <span class="label label-<?php echo $GLOBALS['BOOTSTRAP_LABEL_BADGE_COLOR'][$i]; ?>">
                    <?php echo $sot_val['PROJECT_NAME']; ?>
                </span>
            </div>
            <div class="span1">
                <span class="label label-<?php echo $GLOBALS['BOOTSTRAP_LABEL_BADGE_COLOR'][$i]; ?>"><?php echo $score; ?></span>
            </div>
            <div class="progress">
                <?php 
                if ($sot_val['COUNT']['FINISHED']['DATE_SCOPE'] != 0) { ?>
                <div  rel="popover" data-original-title="Finished Tasks" 
                      data-content="Tasks Count: <?php echo $sot_val['COUNT']['FINISHED']['DATE_SCOPE']; ?><br>
                      Estimated Effort Time: <?php echo round($sot_val['TIME_EFFORT']['FINISHED']['DATE_SCOPE']['GENERAL']['expected_total_time'], 2) . "h"; ?><br>
                      Actual Effort Time: <?php echo round($sot_val['TIME_EFFORT']['FINISHED']['DATE_SCOPE']['GENERAL']['actual_total_time'], 2) . "h"; ?><br>
                      TSDR: <?php echo abs($schedule_deviation) . "%"; ?>" 
                      class="finished bar" style="width: 100%">
                        <?php echo abs($schedule_deviation) . "%"; ?>
                </div>
                <?php } else { ?>
                    <div rel="popover" data-original-title="" data-content="" class="unfinish bar bar-info" style="width: 100%">No Personal Tasks</div>
                <?php }
                ?>
            </div>
        </div>
        <div class="task_effort">
            <?php
            /*
             * Get the data result from the following function in monitor_mgt.php
             */
            draw_personal_task_effort_day_by_day($i,$sot_val);
            ?>
            <div id="personal_task_effort_day_by_day_<?php echo $i; ?>" class="task_effort_day_by_day">
            </div>
        </div>
        <hr>
        <?php
        $i++;
    }
}

function draw_personal_task_effort_day_by_day($i,$task_effort) {
  ?>
    <script type="text/javascript">
        $(function () {
            var chart;
            $(document).ready(function() {
                chart = new Highcharts.Chart({
                    chart: {
                        renderTo: 'personal_task_effort_day_by_day_<?php echo $i; ?>',
                        zoomType: 'xy'
                    },
                    title: {
                        text: 'Personal Daily Task Effort'
                    },
                    subtitle: {
                        text: ''
                    },
                    xAxis: [{
                            categories: [
                                <?php
                                    foreach($task_effort['TIME_EFFORT']['FINISHED']['DATE_SCOPE']['DETAILED'] as $te_key => $te_val){
                                        echo "'".$te_key."',";
                                    }
                                ?>
                            ]
                        },],
                    yAxis: [{ // Primary yAxis
                            min: 0,
                            title: {
                                text: 'Actual Effort',
                                style: {
                                    color: '#5DB75D'
                                }
                            },
                            labels: {
                                formatter: function() {
                                    return this.value +'h';
                                },
                                style: {
                                    color: '#5DB75D'
                                }
                            }
                        }, { // Secondary yAxis
                            min: 0,
                            title: {
                                text: 'Estimate Effort',
                                style: {
                                    color: '#F9A329'
                                }
                            },
                            labels: {
                                formatter: function() {
                                    return this.value +'h';
                                },
                                style: {
                                    color: '#F9A329'
                                }
                            },
                            linkedTo: 0
                        },{ // Third yAxis
                            min: 0,
                            minTickInterval: 5,
                            title: {
                                text: 'Head Count',
                                style: {
                                    color: '#40A7C5'
                                }
                            },
                            labels: {
                                style: {
                                    color: '#40A7C5'
                                }
                            },
                            opposite: true
                        }],
                    tooltip: {
                        formatter: function() {
                            return ''+
                                this.x +': '+ this.y +
                                (this.series.name == 'HC' ? '' : ' h');
                        }
                    },
                    legend: {
                        shadow: true,
                        backgroundColor: '#FFFFFF',
                        itemStyle: {
                            fontSize: '12px'
                        },
                        reversed: true
                    },
                    series: [{
                            name: 'Actual',
                            color: '#5DB75D',
                            type: 'column',
                            yAxis: 0,
                            data: [
                                <?php
                                    foreach($task_effort['TIME_EFFORT']['FINISHED']['DATE_SCOPE']['DETAILED'] as $te_key => $te_val){
                                        echo $te_val['total_effort'].",";
                                    }
                                ?>
                            ],
                            dataLabels: {
                                enabled: true,
                                rotation: 0,
                                color: '#274B6D',
                                align: 'center',
                                formatter: function() {
                                    return this.y;
                                },
                                style: {
                                    fontSize: '11px'
                                }
                            }            
                        }, {
                            name: 'Estimate',
                            color: '#F9A329',
                            type: 'spline',
                            dashStyle: 'ShortDot',
                            yAxis: 1,
                            data: [
                                <?php
                                    foreach($task_effort['TIME_EFFORT']['FINISHED']['DATE_SCOPE']['DETAILED'] as $te_key => $te_val){
                                        echo $te_val['expected_total_time'].",";
                                    }
                                ?>
                            ]
                        }, {
                            name: 'HC',
                            color: '#40A7C5',
                            type: 'line',
                            dashStyle: 'Dot',
                            yAxis: 2,
                            data: [
                                <?php
                                    foreach($task_effort['COUNT']['MD']['DATE_SCOPE'] as $te_key => $te_val){
                                        echo $te_val.",";
                                    }
                                ?>

                            ]
                        }]
                });
            });
            
        });
    </script>
    <?php
}

function draw_personal_task_effort_history_comparison($i,$task_effort) {
    ?>
    <script type="text/javascript">
        $(function () {
            var chart;
            $(document).ready(function() {
                 chart = new Highcharts.Chart({
                    chart: {
                        renderTo: 'personal_task_effort_history_comparison_<?php echo $i; ?>',
                        type: 'column'
                    },
                    title: {
                        text: 'Compare with History'
                    },
                    xAxis: {
                        categories: [
                            'This Week',
                            'Last Week',
                            'Last Month'
                        ]
                    },
                    yAxis: [{
                        title: {
                            text: 'Finished Effort',
                            style: {
                                color: '#F9A329'
                            }
                        },
                        labels: {
                            formatter: function() {
                                return this.value +'h';
                            },
                            style: {
                                color: '#F9A329'
                            }
                        }
                    },{
                        title: {
                            text: 'Effort Per Workday',
                            style: {
                                color: '#5DB75D'
                            }
                        },
                        labels: {
                            formatter: function() {
                                return this.value +'h';
                            },
                            style: {
                                color: '#5DB75D'
                            }
                        },
                        opposite: true
                    },{
                        title: {
                            text: 'Effort Per Man * Day',
                            style: {
                                color: '#2F7ED8'
                            }
                        },
                        labels: {
                            formatter: function() {
                                return this.value +'h';
                            },
                            style: {
                                color: '#2F7ED8'
                            }
                        },
                        opposite: true
                    }],
                    legend: {
                        shadow: true,
                        backgroundColor: '#FFFFFF',
                        itemStyle: {
                            fontSize: '12px'
                        },
                        reversed: true
                    },
                    tooltip: {
                        formatter: function() {
                            return ''+
                                this.x +': '+ this.y +' h';
                        }
                    },
                    plotOptions: {
                        column: {
                            pointPadding: 0.2,
                            borderWidth: 0
                        },
                        area: {
                            fillOpacity: 0.7
                        }
                    },
                    series: [{
                            name: 'Finished Task',
                            color: '#F9A329',
                            type: 'area',
                            data: [
                                    <?php
                                        echo $task_effort['FINISHED']['THIS_WEEK']['GENERAL']['actual_total_time'];
                                    ?>,
                                    <?php
                                        echo $task_effort['FINISHED']['LAST_WEEK']['actual_total_time'];
                                    ?>,
                                    null
                            ],
                            yAxis: 0
                        },{
                            name: 'BUG',
                            color: '#ED5A4B',
                            type: 'area',
                            data: [
                                    <?php
                                        echo $task_effort['FINISHED']['THIS_WEEK']['GENERAL']['bug_time'];
                                    ?>,
                                    <?php
                                        echo $task_effort['FINISHED']['LAST_WEEK']['bug_time'];
                                    ?>,
                                    null,
                            ],
                            yAxis: 0
                        }, {
                            name: 'Effort / Workday',
                            color: '#5DB75D',
                            data: [
                                    <?php
                                        echo $task_effort['EFFORT_PER_DAY']['THIS_WEEK'];
                                    ?>,
                                    <?php
                                        echo $task_effort['EFFORT_PER_DAY']['LAST_WEEK'];
                                    ?>,
                                    <?php
                                        echo $task_effort['EFFORT_PER_DAY']['LAST_MONTH'];
                                    ?>,
                            ],
                            dataLabels: {
                                enabled: true,
                                rotation: -45,
                                color: '#274B6D',
                                align: 'center',
                                formatter: function() {
                                    return this.y;
                                },
                                style: {
                                    fontSize: '12px'
                                }
                            },
                            yAxis: 1
                        }, {
                            name: 'Effort / Man*Day',
                            color: '#2F7ED8',
                            data: [
                                    <?php
                                        echo $task_effort['EFFORT_PER_ENGINEER_DAY']['THIS_WEEK'];
                                    ?>,
                                    <?php
                                        echo $task_effort['EFFORT_PER_ENGINEER_DAY']['LAST_WEEK'];
                                    ?>,
                                    <?php
                                        echo $task_effort['EFFORT_PER_ENGINEER_DAY']['LAST_MONTH'];
                                    ?>
                                ],
                            dataLabels: {
                                enabled: true,
                                rotation: -45,
                                color: '#274B6D',
                                align: 'center',
                                formatter: function() {
                                    return this.y;
                                },
                                style: {
                                    fontSize: '12px'
                                }
                            },
                            yAxis: 2
                        }]
                });
            });                
        });
    </script>
    <?php
    }
    
function draw_personal_task_effort_forecast($i,$task_effort) {
    ?>
    <script type="text/javascript">
        $(function () {
            var chart;
            $(document).ready(function() {
                chart = new Highcharts.Chart({
                    chart: {
                        renderTo: 'personal_task_effort_forecast_<?php echo $i; ?>',
                        zoomType: 'xy'
                    },
                    title: {
                        text: 'Daily Task Effort'
                    },
                    subtitle: {
                        text: ''
                    },
                    xAxis: [{
                            categories: [
                                <?php
                                    foreach($task_effort['TIME_EFFORT']['FINISHED']['THIS_WEEK']['DETAILED'] as $te_key => $te_val){
                                        echo "'".$te_key."',";
                                    }
                                ?>
                            ]
                        },
                        {
                            categories: [
                                <?php
                                    foreach($task_effort['TIME_EFFORT']['STANDARD']['THIS_WEEK']['DETAILED'] as $tes_key => $tes_val){
                                        echo "'".$tes_key."',";
                                    }
                                ?>
                            ],
                            title: {
                                text: 'Standard Estimate',
                                style: {
                                    color: '#D84D47'
                                }
                            },
                            labels: {
                                style: {
                                    color: '#D84D47'
                                }
                            },
                            opposite: true
                        }],
                    yAxis: [{ // Primary yAxis
                            min: 0,
                            title: {
                                text: 'Actual Effort',
                                style: {
                                    color: '#5DB75D'
                                }
                            },
                            labels: {
                                formatter: function() {
                                    return this.value +'h';
                                },
                                style: {
                                    color: '#5DB75D'
                                }
                            },
                            linkedTo: 1
                        }, { // Secondary yAxis
                            min: 0,
                            title: {
                                text: 'Estimate Effort',
                                style: {
                                    color: '#F9A329'
                                }
                            },
                            labels: {
                                formatter: function() {
                                    return this.value +'h';
                                },
                                style: {
                                    color: '#F9A329'
                                }
                            }
                        },{ // Third yAxis
                            min: 0,
                            minTickInterval: 5,
                            title: {
                                text: 'Actual Head Count',
                                style: {
                                    color: '#40A7C5'
                                }
                            },
                            labels: {
                                style: {
                                    color: '#40A7C5'
                                }
                            },
                            opposite: true
                        },{ // Fourth yAxis
                            min: 0,
                            minTickInterval: 5,
                            title: {
                                text: 'Standard Estimate',
                                style: {
                                    color: '#D84D47'
                                }
                            },
                            labels: {
                                style: {
                                    color: '#D84D47'
                                }
                            },
                            opposite: true
                        }],
                    tooltip: {
                        formatter: function() {
                            return ''+
                                this.x +': '+ this.y +
                                (this.series.name == 'AHC' ? '' : ' h');
                        }
                    },
                    legend: {
                        shadow: true,
                        backgroundColor: '#FFFFFF',
                        itemStyle: {
                            fontSize: '12px'
                        },
                        reversed: true
                    },
                    series: [{
                            name: 'Actual',
                            color: '#5DB75D',
                            type: 'column',
                            yAxis: 0,
                            data: [
                                <?php
                                    foreach($task_effort['TIME_EFFORT']['FINISHED']['THIS_WEEK']['DETAILED'] as $te_key => $te_val){
                                        echo $te_val['actual_total_time'].",";
                                    }
                                ?>
                            ],
                            dataLabels: {
                                enabled: true,
                                rotation: 0,
                                color: '#274B6D',
                                align: 'center',
                                formatter: function() {
                                    return this.y;
                                },
                                style: {
                                    fontSize: '11px'
                                }
                            }            
                        }, {
                            name: 'Estimate',
                            color: '#F9A329',
                            type: 'spline',
                            dashStyle: 'ShortDot',
                            yAxis: 1,
                            data: [
                                <?php
                                    foreach($task_effort['TIME_EFFORT']['FINISHED']['THIS_WEEK']['DETAILED'] as $te_key => $te_val){
                                        echo $te_val['expected_total_time'].",";
                                    }
                                ?>
                            ]
                        }, {
                            name: 'AHC',
                            color: '#40A7C5',
                            type: 'line',
                            dashStyle: 'Dot',
                            yAxis: 2,
                            data: [
                                <?php
                                    foreach($task_effort['COUNT']['MD']['THIS_WEEK'] as $te_key => $te_val){
                                        echo $te_val.",";
                                    }
                                ?>

                            ]
                        }, {
                            name: 'Standard',
                            color: '#D84D47',
                            type: 'line',
                            dashStyle: 'LongDot',
                            yAxis: 3,
                            xAxis: 1,
                            data: [
                                <?php
                                    foreach($task_effort['TIME_EFFORT']['STANDARD']['THIS_WEEK']['DETAILED'] as $tesd_key => $tesd_val){
                                        echo $tesd_val['expected_total_time'].",";
                                    }
                                ?>

                            ]
                        }]
                });
            });
            
        });
    </script>
    <?php
}

//2013-8-30
function draw_task_status_pie($i,$plan_working_day,$actual_working_day){
?>
<script type="text/javascript">
$(function () {
    var chart;
    $(document).ready(function() {
        chart = new Highcharts.Chart({
            chart: {
                renderTo: 'task_uncompleted_pie_<?php echo $i;?>',
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: '<?php 
                    echo 'Plan Schedule';
                ?>'
            },
            tooltip: {
                 pointFormat: '{point.percentage:.2f}%'
            },
            plotOptions: {
                pie: {
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
                name: 'Task Status',
                data: [['Completed',<?php echo $actual_working_day;?>],
                        ['Uncompleted',<?php echo ($plan_working_day-$actual_working_day);?>]
                 ],
            }]
        });
    });
    
});
</script>
<?php 
}
/**
 * Author: SY
 * Date: 09/25/2013
 * [draw_task_status_donut description]
 * @param  [type] $i                      [description]
 * @param  [type] $task_bug_by_category   [description]
 * @return [type]                         [description]
 */
function draw_task_status_donut($i,$task_bug_by_category){
    ?>
<script type="text/javascript">
$(function () {
    var chart;
    $(document).ready(function() {
        var colors = Highcharts.getOptions().colors,
            categories = ['Completed', 'Uncompleted'],
            name = 'Completed vs Uncompleted',
            task_status = <?php echo json_encode($GLOBALS['TASK_STATUS_COLOR']); ?>,
            data = [{
                    y: <?php echo (round($task_bug_by_category['task_completed_count']/$task_bug_by_category['task_total_count'],4)*100);?>,
                    color: colors[7],
                    drilldown: {
                        name: 'Completed',
                        categories: [  
                            <?php
                                foreach ($task_bug_by_category['task_completed_status_info'] as $ts_key => $ts_value) {
                                    echo "'".$GLOBALS['TASK_STATUS'][$ts_key][0]."',";
                             }?>],
                        stat: [  
                            <?php
                                foreach ($task_bug_by_category['task_completed_status_info'] as $ts_key => $ts_value) {
                                    echo "'".$ts_key."',";
                             }?>],
                        data: [  
                            <?php
                                foreach ($task_bug_by_category['task_completed_status_info'] as $ts_key => $ts_value) {
                                    echo (round(count($ts_value)/$task_bug_by_category['task_total_count'],4)*100).",";
                             }?>],
                        color: colors[7]
                    }
                }, {
                    y: <?php echo (round($task_bug_by_category['task_uncompleted_count']/$task_bug_by_category['task_total_count'],4)*100);?>,
                    color: colors[6],
                    drilldown: {
                        name: 'Uncompleted',
                        categories:[   
                            <?php
                                foreach ($task_bug_by_category['task_uncompleted_status_info'] as $ts_key => $ts_value) {
                                    if($ts_key !== ""){
                                        echo "'".$GLOBALS['TASK_STATUS'][$ts_key][0]."',";
                                    }else{
                                        echo "'Unassign',";
                                    }
                                        
                             }?>],
                        stat:[   
                            <?php
                                foreach ($task_bug_by_category['task_uncompleted_status_info'] as $ts_key => $ts_value) {
                                    echo "'".$ts_key."',";                        
                             }?>],
                        data:[   
                            <?php
                                foreach ($task_bug_by_category['task_uncompleted_status_info'] as $ts_key => $ts_value) {
                                    echo (round(count($ts_value)/$task_bug_by_category['task_total_count'],4)*100).",";
                             }?>],
                        color: colors[6]
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
            for (var j = 0; j < data[i].drilldown.data.length; j++) {
                StatusData.push({
                            name: data[i].drilldown.categories[j],
                            y: data[i].drilldown.data[j],
                            color:task_status[data[i].drilldown.stat[j]]
                        });
            }
        }
        chart = new Highcharts.Chart({
            chart: {
                renderTo: 'task_completed_pie_<?php echo $i;?>',
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

/**
 * Author: SY
 * Date:
 * Last Modified: CC    09/25/2013
 * [draw_task_bug_iteration description]
 * @param  [type] $project_iteration_info   [description]
 * @param  [type] $iteration_name [description]
 * @param  [type] $date_from      [description]
 * @param  [type] $date_to        [description]
 * @return [type]                 [description]
 */
function draw_task_bug_statistics($project_iteration_info,$task_bug_by_category){
?>
    <div>
         <span class="label label-info"><?php echo $project_iteration_info['NAME']; ?></span>
         <span class="label label-warning"><?php echo date("m/d/y",strtotime($project_iteration_info['START'])).'-'. date("m/d/y",strtotime($project_iteration_info['END'])); ?></span>
    </div><br>
    <div style="height: <?php echo $task_bug_by_category['height'];?>px;margin-bottom:25px">
        <div style="height: <?php echo $task_bug_by_category['height'];?>px;float:left;width: 40%;margin:auto 10% auto 5%">
            <table class="table table-bordered table-condensed table-hover">
                <caption><h4>Task</h4></caption>
                <thead>
                   <tr>
                       <th>Item</th>
                       <th>Detail</th>
                       <th>Count</th>
                   </tr>
                </thead>
                <tr>
                    <td rowspan="3"><b>Schedule</b></td>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td>Scenario Count</td>
                    <td><?php echo ($task_bug_by_category['task_total_count']);?></td>
                </tr>
                <tr>
                    <td>Work Day</td>
                    <td><?php echo $task_bug_by_category['actual_working_day'];?></td>
                </tr>
                <tr>
                    <td rowspan="<?php echo (count($task_bug_by_category['task_completed_status_info'])+count($task_bug_by_category['task_uncompleted_status_info'])+2);?>">
                        <b>Status</b>
                    </td>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <?php
                    foreach($task_bug_by_category['task_completed_status_info'] as $tcsi_key => $tcsi_value) {
                        ?><tr>
                            <td><?php echo $GLOBALS['TASK_STATUS'][$tcsi_key][0];?></td><td><?php echo count($tcsi_value);?></td>
                        </tr>
                    <?php }
                    foreach($task_bug_by_category['task_uncompleted_status_info'] as $tusi_key => $tusi_value) {
                        ?><tr>
                            <td><?php echo isset($GLOBALS['TASK_STATUS'][$tusi_key])?$GLOBALS['TASK_STATUS'][$tusi_key][0]:'Unassign';?></td>
                            <td><?php echo count($tusi_value);?></td>
                        </tr>
                    <?php }
                ?>
            </table>
        </div>
        <div style="height: <?php echo $task_bug_by_category['height'];?>px;float:left;width: 40%;margin-right: 5%;">
            <table class="table table-bordered table-condensed table-hover">
                <caption><h4>Bug</h4></caption>
                <thead>
                   <tr>
                       <th>Item</th>
                       <th colspan="2">Detail</th>
                       <th>Count</th>
                   </tr>
                </thead>
                <?php 
                if(!empty($task_bug_by_category['bug'])){ ?>
                    <tbody>
                    <tr>
                        <td rowspan="<?php echo count($task_bug_by_category['bug']['CATEGORY'])+1; ?>">
                            <b>Category</b>
                        </td>
                        <td colspan="2">&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                        <?php
                        foreach ($task_bug_by_category['bug']['CATEGORY'] as $bc_key => $bc_val){?>
                            <tr>
                                <td colspan="2"><?php echo $GLOBALS['BUG_CATEGORY'][$bc_key][0];?></td>
                                <td><?php echo $bc_val['COUNT'];?></td>
                            </tr>
                            <?php
                        }
                        ?>
                    <tr>
                        <td rowspan="<?php echo (count($task_bug_by_category['bug']['STATUS'])+(isset($task_bug_by_category['bug']['SUB_STATUS'])?count($task_bug_by_category['bug']['SUB_STATUS']):0)+2); ?>">
                            <b>Status</b>
                        </td>
                        <td colspan="3">&nbsp;</td>
                    </tr> 
                    <tr>
                        <td>Open</td>
                        <td>&nbsp;</td>
                        <td><?php echo isset($task_bug_by_category['bug']['STATUS'][3])?$task_bug_by_category['bug']['STATUS'][3]['COUNT']:''; ?></td>
                    </tr>
                    <tr>
                        <td rowspan="<?php echo ((isset($task_bug_by_category['bug']['SUB_STATUS'])?count($task_bug_by_category['bug']['SUB_STATUS']):0)+1); ?>">Close</td>
                        <td>&nbsp;</td>
                        <td><?php echo isset($task_bug_by_category['bug']['STATUS'][5])?$task_bug_by_category['bug']['STATUS'][5]['COUNT']:''; ?></td>
                    </tr>
                    <?php
                    if(isset($task_bug_by_category['bug']['SUB_STATUS'])){
                        foreach ($task_bug_by_category['bug']['SUB_STATUS'] as $oss_key => $oss_val){
                            ?>
                            <tr>
                                <td><?php echo ($oss_key === "")?"No Given Reason":$GLOBALS['BUG_SUB_STATUS'][$oss_key][0];?></td>
                                <td><?php echo $oss_val['COUNT'];?></td>
                            </tr>
                        <?php
                        }
                    }
                    ?>
                </tbody>
                <?php }else{ ?>
                    <tr>
                        <td colspan="4">No bugs reported during this date scope!
                        </td>
                    </tr>
                <?php }
                ?>
            </table>
        </div>
    </div>
<?php
}

//2013-9-9
//draw remaining count about plan and actual
function draw_task_remain_count_line($i,$project_remain_count,$actual_remain_count){
    ?>
    <script type='text/javascript'>
    $(function () {
        var chart;
        $(document).ready(function() {
             chart = new Highcharts.Chart({
                chart: {
                    renderTo: 'task_remaining_status_<?php echo $i;?>',
                    type: 'line'
                },
                title: {
                    text: ''
                },
                subtitle: {
                    text: ''
                },
                xAxis: {
                    categories: [
                        <?php
                        $i = 0;
                        $gap = (count($project_remain_count)/6+1);
                        foreach($project_remain_count as $prc_key => $prc_val){
                            if(count($project_remain_count)>6){
                                if($i%$gap == 0){
                                    echo "'".$prc_key."',";
                                }else{
                                    echo "' ',";
                                }
                            }else{
                                echo "'".$prc_key."',";
                            }
                            $i++;
                        }
                        ?>
                    ]
                },
                yAxis: {
                    title: {
                        text: 'Number of Scenarios'
                    }
                },
                tooltip: {
                    enabled: true,
                    formatter: function() {
                            return '<b>'+ this.series.name +': </b>'+ this.y;
                    }
                },
                plotOptions: {
                    line: {
                        dataLabels: {
                            enabled: false
                        },
                        enableMouseTracking: true
                    }
                },
                series: [{
                    name: 'Planed Remain',
                    data: [
                        <?php
                            foreach($project_remain_count as $prc_key => $prc_val){
                                echo "".$prc_val[0].",";
                            }
                        ?>
                    ]
                }, {
                    name: 'Actual Remain',
                    data: [
                        <?php
                            foreach($actual_remain_count as $arc_key => $arc_val){
                                echo "".$arc_val[0].",";
                            }
                        ?>
                    ]
                }]
            });
        });

    });
    </script>
    <?php
}

/**
 * Author: CC
 * Date: 09/11/2013
 * [draw_task_remain_count_stock draw remaining count about plan and actual stock chart]
 * @param  [type] $i                [description]
 * @param  [type] $task_by_category [description]
 * @return [type]                   [description]
 */
function draw_task_remain_count_stock($i,$task_by_category){
    ?>
<script type='text/javascript'>
$(function() {
	var seriesOptions = [],
            seriesCounter = 0,
            names = ['Projected Remain', 'Actual Remain'],
            datas = [[
                    <?php  
                         foreach($task_by_category['project'] as $prc_key => $prc_val){
                             $date_array = explode('-',$prc_key);
                             echo '[Date.UTC('.$date_array[0].','.($date_array[1]-1).','.$date_array[2].'),'.$prc_val[0]."],";
                         }
                    ?>],  
                    [
                    <?php  
                        foreach($task_by_category['actual'] as $arc_key => $arc_val){
                            $date_array = explode('-',$arc_key);
                            echo '[Date.UTC('.$date_array[0].','.($date_array[1]-1).','.$date_array[2].'),'.$arc_val[0]."],";
                        }
                    ?>
                    ]];
	$.each(names, function(i, name) {
        seriesOptions[i] = {
		  name: name,
		  data: datas[i]
        };
        seriesCounter++;
        if (seriesCounter === names.length) {
            createChart();
        }
	});
	// create the chart when all data is loaded
	function createChart() {
            $(document).ready(function() {
                $('#task_remaining_status_<?php echo $i;?>').highcharts('StockChart',{
                    chart: {
                    },
                    scrollbar : {
			enabled : false
		    },
                    rangeSelector: {
                        selected:5,
                        inputEnabled:false
                    },
                    legend: {
                        enabled: true
                    },
		    yAxis: {
                        title: {
                            text: 'Number of Scenarios'
                        }
		    },
		    series: seriesOptions
		});
            });
        }
    });
    </script>
    <?php
}
/**
 * 2013-9-27
 * Shiyan
 * push_man_day_into_table
 */
function push_man_day_into_table($i,$oid,$date_from,$date_to,$count){
    /**
     * 2013-9-23
     * shiyan
     * get all iterations' man*day of project
     */
    //list all iteration information
    $iteration_info = array();
    $iteration_man_info = array();
    if(empty($date_from)||empty($date_to)){
        $date_from = '2000-01-01';
        $date_to   = $GLOBALS['today'];
    }
    $all_iteration_info = list_iteration_info($oid,$date_from,$date_to);
    if (!empty($all_iteration_info)) {
        foreach ($all_iteration_info as $aii_val) {
            $iteration_info[$aii_val['ITERATION_ID']][$aii_val['FINISH_DATE']][$aii_val['TESTER']][]=$aii_val; 
            $iteration_man_info[$aii_val['ITERATION_ID']]['ITERATION_NAME'] = $aii_val['ITERATION_NAME'];
        }
    }
    foreach ($iteration_info as $ii_key => $ii_val) {
        foreach ($ii_val as $iv_key => $iv_val) {
            $iteration_man_info[$ii_key]['DETAIL'][$iv_key] = count($iv_val);
            foreach ($iv_val as $value) {
                if(empty($value[0]['ACTUAL_START'])||$value[0]['ACTUAL_START'] == '0000-00-00'){
                    if(empty($value[0]['ESTIMATED_START'])||$value[0]['ESTIMATED_START'] == '0000-00-00'){
                        $iteration_man_info[$ii_key]['ACTUAL_START'] = date("m/d/y",strtotime($value[0]['EXPECTED_START']));
                    }else{
                        $iteration_man_info[$ii_key]['ACTUAL_START'] = date("m/d/y",strtotime($value[0]['ESTIMATED_START']));
                    }
                }else{
                    $iteration_man_info[$ii_key]['ACTUAL_START'] = date("m/d/y",strtotime($value[0]['ACTUAL_START']));
                }
                if($value[0]['ACTUAL_END'] == '0000-00-00'){
                    $iteration_man_info[$ii_key]['ACTUAL_END']  = '';
                }else{
                    $iteration_man_info[$ii_key]['ACTUAL_END']  = date("m/d/y",strtotime($value[0]['ACTUAL_END']));
                }
            }
        }
        $iteration_man_info[$ii_key]['WD'] = count($ii_val);
    }
    foreach ($iteration_man_info as $imi_key => $imi_value) {
        if(!empty($imi_value['DETAIL'])){
            $iteration_man_info[$imi_key]['GENERAL'] = array_sum($imi_value['DETAIL']);
        }else{
            $iteration_man_info[$imi_key]['GENERAL'] = 0;
        }
    }
    ?>
    <input type="hidden" id="org_id_<?php echo $i;?>" value="<?php echo $oid; ?>" >
    <div id="task_iteration_<?php echo $i;?>" style="float: left;width: 48%;margin-right: 2%">
        <h4 align="center"><b>Man-Day by Iteration</b></h4>
        <?php if($count == 1){?>
            <div align="right">
                <?php require_once __DIR__ . '../../../inc/mini_date_scope.php';?>
            </div>
       <?php }?>
        <div id="task_iteration_manday_<?php echo $i;?>">
        <table class="table table-bordered table-condensed table-hover">
            <thead>
                <tr>
                    <th>Iteration Name</th>
                    <th colspan="2">Actual Start/End</th>
                    <th>Man-Day</th>
                    <th>Avg Man-Day</th>
                </tr>
            </thead> 
            <?php 
                $m=0;
                foreach($iteration_man_info as $imi_value) {
                    if($m <= 9){?>
                        <tr>
                            <td><?php echo $imi_value['ITERATION_NAME'];?></td>
                            <td><?php echo $imi_value['ACTUAL_START'];?></td>
                            <td><?php echo $imi_value['ACTUAL_END'];?></td>
                            <td><?php echo $imi_value['GENERAL'];?></td>
                            <td><?php echo round($imi_value['GENERAL']/$imi_value['WD'],2);?></td>
                        </tr>
                        <?php
                    }
                    $m++;
                }
                if($m > 10){?>
                    <tr><td colspan="3"><input type="button" id="iteration_man_day_view_<?php echo $i;?>" class="btn-mini btn-link" value=">>>"></td></tr>
                    <?php
                } 
            ?>
        </table>
        </div>
    </div>
    <?php
    if(count($iteration_man_info)>10){
    ?>
    <div id="customize_iteration_man_day_<?php echo $i;?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        </div>
        <div class="modal-body">
            <table class="table table-bordered table-condensed table-hover">
                <caption><h4>Iterations' Man*Day</h4></caption>
                <thead>
                    <tr>
                        <th>Iteration Name</th>
                        <th colspan="2">Actual Start/End</th>
                        <th>Man*Day</th>
                        <th>Work Day</th>
                        <th>Average No.</th>
                    </tr>
                </thead> 
                <?php 
                    foreach($iteration_man_info as $imi_key => $imi_value) {?>
                        <tr>
                           <td><?php echo $imi_value['ITERATION_NAME'];?></td>
                           <td><?php echo $imi_value['ACTUAL_START'];?></td>
                           <td><?php echo $imi_value['ACTUAL_END'];?></td>
                           <td><?php echo $imi_value['GENERAL'];?></td>
                           <td><?php echo $imi_value['WD'];?></td>
                           <td><?php echo round($imi_value['GENERAL']/$imi_value['WD'],2);?></td>
                        </tr>
                    <?php
                    }
                ?>
            </table>
        </div>
    </div>
    <?php
    }
    ?>
    <script type="text/javascript">
        $("#iteration_man_day_view_<?php echo $i;?>").live('click',function(){ 
            $("#customize_iteration_man_day_<?php echo $i;?>").modal("show");
        });    
         // For date scope change of different view
        $("#mini_date_scope_view").live("change",function(){
            var date_scope   = $(this).val();
            var oid          = $('#org_id_<?php echo $i;?>').val();
            switch(date_scope){
                case "0":
                    $("#mini_invald_date").modal("show");
                    break;
                case "1":
                    var date_from = $("#this_month").val()+'-01';
                    var date_to   = $("#today").val();
                    $.post('../../monitor/task/ajax_iteration_manday_with_date.php',
                    {
                        date_from:date_from,
                        date_to:date_to,
                        optype:optype,
                        oid:oid,
                        i:<?php echo $i;?>
                    },
                    function (output){
                        $('#task_iteration_manday_<?php echo $i;?>').html(output).fadeIn(500);
                    });
                    break;
                case "2":   
                    var date_from_temp = $("#last_month").val();
                    var date_from      = date_from_temp + '-01';
                    var date_to        = $("#last_month_last_day").val();
                    $.post('../../monitor/task/ajax_iteration_manday_with_date.php',
                    {
                        date_from:date_from,
                        date_to:date_to,
                        optype:optype,
                        oid:oid,
                        i:<?php echo $i;?>
                    },
                    function (output){
                        $('#task_iteration_manday_<?php echo $i;?>').html(output).fadeIn(500);
                    });
                    break;
                case "3":
                    $("#mini_customize_date").modal("show");
                    break;
            }
        });

        $("#mini_cus_date_confirm").live("click",function(){
            var cus_date_from = $("#mini_cus_date_from").val();
            var cus_date_to   = $("#mini_cus_date_to").val();
            if(cus_date_from === 0||cus_date_to === 0){
                $("#mini_invald_date").modal('show');
                return false;
            }else{
                if(Date.parse(cus_date_from) > Date.parse(cus_date_to)){
                    $("#mini_invald_date").modal('show');
                    return false;
                }
                $.post('../../monitor/task/ajax_iteration_manday_with_date.php',
                {
                    date_from:cus_date_from,
                    date_to:cus_date_to,
                    optype:optype,
                    oid:oid,
                    i:<?php echo $i;?>
                },
                function (output){
                    $('#task_iteration_manday_<?php echo $i;?>').html(output).fadeIn(500);
                });
            }
            $("#mini_customize_date").modal("hide");
        });
    </script>
    <?php
    
}