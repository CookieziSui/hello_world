<?php
require_once __DIR__ . '/../../../lib/inc/highcharts.php';
require_once __DIR__ . '/../../../lib/function/monitor_mgt.php';
/*
 * draw resource status hisoft_experience table
 * 
 */

function org_employee_pactera_experience($org_info, $sub_year, $sub_org_year, $oid, $year, $month) {
    ?>
    <script type="text/javascript">
        $(function () {
            var chart;
            $(document).ready(function() {
                chart = new Highcharts.Chart({
                    chart: {
                        renderTo: 'pactera_experience',
                        type: 'column'
                    },
                    title: {
                        text: 'Pactera Experience'
                    },
                    xAxis: {
                        categories: [
                            <?php
                            foreach ($sub_year as $sy_key => $sy_val) {
                                echo "'" . $sy_key . " Year',";
                            }
                            ?>
                            ]
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Head Count'
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
                        x: -100,
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
                            return '<b>'+ this.x +'</b><br/>'+
                                this.series.name +': '+ this.y +'<br/>'+
                                'Total: '+ this.point.stackTotal;
                        }
                    },
                    plotOptions: {
                        column: {
                            stacking: 'normal',
                                dataLabels: {
                                    enabled: true,
                                    color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
                                }
                        }
                    },
                    series: [
                        <?php foreach ($sub_org_year as $soy_key => $soy_val) { ?>
                            {
                                name:'<?php echo $org_info[$soy_key]['NAME']; ?>',
                                data:[
                                    <?php
                                    foreach ($soy_val as $sv_key => $sv_val) {
                                        echo $sv_val . ",";
                                    }
                                    ?>
                                    ]
                            }, 
                        <?php }
                        ?>]
                    });
                });
            });
    </script>
    <?php
}

/*
 *  draw resource_status_employee_compare table
 */

function org_employee_statistics($org_info, $directly_org_member_typecount, $oid, $year, $month) {
    $month_name = date("F", mktime(0, 0, 0, $month, 10));
    $i = 0;
    ?>
    <script type="text/javascript">
            $(function () {
                var chart;
                $(document).ready(function() {                    
                    var colors = Highcharts.getOptions().colors,
                    categories = [
                        <?php
                        foreach ($directly_org_member_typecount as $domt_key => $domt_val) {
                            echo "'" . $org_info[$domt_key]['NAME'] . "',";
                        }
                        ?>
                                ],
                    name = 'Orgnanization Name',
                    data = [
                        <?php foreach ($directly_org_member_typecount as $domtt_key => $domtt_val) { ?>
                                {
                                    y: <?php echo array_sum($domtt_val); ?>,
                                    color: colors[<?php echo $i; ?>],
                                    drilldown: {
                                        name: '<?php echo $domtt_key; ?>',
                                        categories: [
                                            <?php
                                            foreach ($domtt_val as $dv_key => $dv_val) {
                                                echo "'" . $dv_key . "',";
                                            }
                                            ?>
                                        ],
                                        data: [
                                            <?php
                                            foreach ($domtt_val as $dv_key => $dv_val) {
                                                echo $dv_val . ",";
                                            }
                                            ?>
                                        ],
                                        color: colors[<?php echo $i; ?>]
                                    }
                                },       
                            <?php
                            $i++;
                        }
                        ?>
                    ];
                    // Build the data arrays
                    var orgData = [];
                    var typeData = [];
                    for (var i = 0; i < data.length; i++) {
                    
                        // add browser data
                        orgData.push({
                            name: categories[i],
                            y: data[i].y,
                            color: data[i].color
                        });
                                            
                        // add version data
                        for (var j = 0; j < data[i].drilldown.data.length; j++) {
                            var brightness = 0.2 - (j / data[i].drilldown.data.length) / 5 ;
                            typeData.push({
                                name: data[i].drilldown.categories[j],
                                y: data[i].drilldown.data[j],
                                color: Highcharts.Color(data[i].color).brighten(brightness).get()
                            });
                        }
                    }
                                        
                    // Create the chart
                    $('#org_employee_statistics').highcharts({
                        chart: {
                            type: 'pie'
                        },
                        title: {
                            text: '<?php echo $org_info[$oid]['NAME'] . "," . $month_name . "," . $year; ?> (Exclude <?php echo $org_info[$oid]['NAME']; ?>)'
                        },
                        yAxis: {
                            title: {
                                text: ''
                            }
                        },
                        plotOptions: {
                            pie: {
                                shadow: false,
                                center: ['50%', '50%']
                            }
                        },
                        tooltip: {
                            valueSuffix: ''
                        },
                        series: [{
                                name: 'Org',
                                data: orgData,
                                size: '60%',
                                dataLabels: {
                                    formatter: function() {
                                        return this.y > 5 ? this.point.name : null;
                                    },
                                    color: 'white',
                                    distance: -30
                                }
                            }, {
                                    name: 'Type',
                                    data: typeData,
                                    size: '80%',
                                    innerSize: '60%',
                                    dataLabels: {
                                        formatter: function() {
                                            // display only if larger than 1
                                            return this.y > 1 ? '<b>'+ this.point.name +':</b> '+ this.y  : null;
                                        }
                                    }
                                }]
                            });
                        });
                    });                               
    </script>
    <?php
}

/* draw  employee_type_distribution     $reason = unserialize($ri_val['REASON']);
 */

function resource_status_employee_type_distribution($current_org_info, $org_owner, $oid, $current_month) {
    ?> <script type="text/javascript">
                $(function () {
                    var chart;
                    $(document).ready(function() {
                        chart = new Highcharts.Chart({
                                                                                                                                                           
                            chart: {
                                renderTo: 'emplpoyee_type_distribution',
                                type: 'column'
                            },
                            title: {
                                text: '<?php echo $org_owner['NAME'] ?>'
                            },
                            subtitle: {
                                text: ''
                            },
                            xAxis: {
                                categories: [
                                    'FTE',
                                    'Intern',
                                    'Borrow'
                                ]
                            },
                            yAxis: {
                                min: 0,
                                title: {
                                    text: 'Percent (%)'
                                }
                            },
                            legend: {
                                layout: 'vertical',
                                backgroundColor: '#FFFFFF',
                                align: 'left',
                                verticalAlign: 'top',
                                x: 680,
                                y: 70,
                                floating: true,
                                shadow: true
                            },
                            tooltip: {
                                formatter: function() {
                                    return ''+
                                        this.x +': '+ this.y +' %';
                                }
                            },
                            plotOptions: {
                                                                                                                                                           
                                column: {                                  
                                    dataLabels: {
                                        enabled: true
                                    },
                                    pointPadding: 0.2,
                                    borderWidth: 0
                                }
                            },
                            series: [
    <?php
    foreach ($current_org_info as $oi_key => $oi_val) {
        $people_count_date = all_people_count($oi_val['ORG_ID'], $current_month);
        foreach ($people_count_date as $cd_key => $cd_val) {
            $fte = $cd_val['fte'];
            $intern = $cd_val['intern'];
            $quit = $cd_val['quit'];
            $borrow = $cd_val['borrow'];
        }
        ?>  
                                    {
                                        name: '<?php echo $oi_val["NAME"]; ?>',
                                        data: [<?php echo round((count($fte) / (count($fte) + count($intern) + count($borrow))) * 100, 2) ?>, <?php echo round((count($intern) / (count($fte) + count($intern) + count($borrow))) * 100, 2) ?>, <?php echo round((count($borrow) / (count($fte) + count($intern) + count($borrow))) * 100, 2) ?>]
                                                                                                                                                                                                                                                                                                                                                        
                                    },
                                                                                                                                                                                                                                        
    <?php }
    ?>
                                                                                                                                                                                            
                        ]
                    });
                });
                                                                                                                                                                                    
            });
    </script>
    <?php
}

/* draw resource_status_attrition_statistics table
 */

function resource_status_attrition_statistics($count_attrition, $org_owner, $current_year, $re_sum_attrition_rate) {
    ?>

    <script type="text/javascript">
            $(function () {
                var chart;
                $(document).ready(function() {
                    chart = new Highcharts.Chart({
                        chart: {
                            renderTo: 'attrition_statistics',
                            zoomType: 'xy'
                        },
                        title: {
                            text: '<?php echo $org_owner["ORG_NAME"]; ?>'
                        },
                        subtitle: {
                            text: ''
                        },
                        xAxis: [{
                                categories: [<?php
    foreach ($count_attrition as $ca_key => $ca_val) {
        echo "'" . $ca_key . "'" . ",";
    }
    ?>]
                                }],
                            yAxis: [{ // Primary yAxis
                                    labels: {
                                        formatter: function() {
                                            return this.value +'%';
                                        },
                                        style: {
                                            color: '#89A54E'
                                        }
                                    },
                                    min: 0,
                                    title: {
                                        text: 'Rate',
                                        style: {
                                            color: '#89A54E'
                                        }
                                    }
                                }, { // Secondary yAxis
                                    title: {
                                        text: 'Number',
                                        style: {
                                            color: '#4572A7'
                                        }
                                    },
                                    labels: {
                                        formatter: function() {
                                            return this.value +' ';
                                        },
                                        style: {
                                            color: '#4572A7'
                                        }
                                    },
                                    opposite: true
                                }],
                            tooltip: {
                                formatter: function() {
                                    return ''+
                                        this.x +': '+ this.y +
                                        (this.series.name == 'Number' ? ' ' : '%');
                                }
                            },
                            legend: {
                                layout: 'vertical',
                                align: 'left',
                                x: 120,
                                verticalAlign: 'top',
                                y: 100,
                                floating: true,
                                backgroundColor: '#FFFFFF'
                            },
                            series: [{
                                    name: 'Number',
                                    color: '#4572A7',
                                    type: 'column',
                                    yAxis: 1,
                                    data: [<?php
    foreach ($count_attrition as $ca_key => $ca_val) {
        echo $ca_val . ",";
    }
    ?>]
                                                                                                                                                    
                                    }, {
                                        name: 'Rate',
                                        color: '#89A54E',
                                        type: 'spline',
                                        data: [<?php
    foreach ($re_sum_attrition_rate as $rsar_key => $rsar_val) {
        echo $rsar_val . ",";
    }
    ?>]
                                        }]
                                });
                            });
                                                                                                                                                    
                        });
    </script>
    <?php
}

/*
 * draw resource status level Distribution table
 */

function org_employee_level_distribution($org_info, $oid, $org_member_father_levelcount, $org_member_levelcount, $year, $month) {
    $month_name = date("F", mktime(0, 0, 0, $month, 10));
    ?>
    <script type="text/javascript">
                        $(function () {
                            var chart;
                            $(document).ready(function() {
                                chart = new Highcharts.Chart({
                                    chart: {
                                        renderTo: 'level_distribution',
                                        type: 'column'
                                    },
                                    title: {
                                        text: '<?php echo $org_info[$oid]['NAME'] . "," . $month_name . "," . $year; ?>'
                                    },
                                    subtitle: {
                                        text: ''
                                    },
                                    xAxis: {
                                        categories: [
    <?php
    foreach ($org_member_father_levelcount[$oid] as $omfl_key => $omflval) {
        echo "'" . $omfl_key . "',";
    }
    ?>
                                                                                    ]
                                                                                },
                                                                                yAxis: {
                                                                                    min: 0,
                                                                                    title: {
                                                                                        text: 'Head Count'
                                                                                    }
                                                                                },
                                                                                legend: {
                                                                                    layout: 'vertical',
                                                                                    backgroundColor: '#FFFFFF',
                                                                                    align: 'left',
                                                                                    verticalAlign: 'top',
                                                                                    x: 100,
                                                                                    y: 70,
                                                                                    floating: true,
                                                                                    shadow: true
                                                                                },
                                                                                tooltip: {
                                                                                    formatter: function() {
                                                                                        return ''+
                                                                                            this.x +': '+ this.y + '%';
                                                                                    }
                                                                                },
                                                                                plotOptions: {
                                                                                    column: {
                                                                                        pointPadding: 0.2,
                                                                                        borderWidth: 0
                                                                                    }
                                                                                },
                                                                                series: [
    <?php foreach ($org_member_levelcount as $oml_key => $oml_val) { ?>
                                                                                        {
                                                                                            name: '<?php echo $org_info[$oml_key]['NAME']; ?>',
                                                                                            data: [
        <?php
        foreach ($oml_val as $ov_key => $ov_val) {
            echo $ov_val['PERCENT'] . ",";
        }
        ?>
                                                                                                        ]
                                                                                                    },
    <?php }
    ?> {
                                                                                name: 'BaseLine',
                                                                                color: '#89A54E',
                                                                                type: 'spline',
                                                                                data: [ 
    <?php
    foreach ($org_member_father_levelcount[$oid] as $omfl_key => $omflval) {
        echo $omflval['PERCENT'] . ",";
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

/*
 * draw resource status attrition statistics by level
 */

function org_attrition_statistics_by_level($resign_info_by_level) {
    ?>
    <script type="text/javascript">
$(function () {
    var chart;
    $(document).ready(function() {
        var colors = Highcharts.getOptions().colors,
        chart = new Highcharts.Chart({
            chart: {
                renderTo: 'attrition_chart',
                type: 'column'
            },
            title: {
                text: 'Attrition by Level'
            },
            xAxis: {
                categories: [
                    <?php
                    foreach ($resign_info_by_level as $rirl_key => $rirl_val) {
                        echo "'" . $rirl_key . "',";
                    }
                    ?>
                ]
            },
            legend: {
                layout: 'vertical',
                backgroundColor: '#FFFFFF',
                align: 'left',
                verticalAlign: 'top',
                x: 100,
                y: 70,
                floating: true,
                shadow: true
            },
            tooltip: {
                formatter: function() {
                    return ''+
                        this.x +': '+ this.y;
                }
            },
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0
                }
            },
                series: [{
                name: 'Level',
                data: [
                    <?php
                        $i = 0;
                        foreach($resign_info_by_level as $rirll_key => $rirll_val){ 
                            $i++;
                            ?>
                            {
                                y:<?php echo $rirll_val; ?>,
                                color:colors[<?php echo $i; ?>]
                            },
                        <?php }
                    ?>
                ]
    
            }]
        });
    });
    
});
        </script>
    <?php
}

/*
 * draw resource status attrition statistics by reason
 */

function org_attrition_statistics_by_reason($all_org_resign_reason) {
    ?>
    <script type="text/javascript">
    $(function() {
        var chart;
        $(document).ready(function() {
            chart = new Highcharts.Chart({
                chart : {
                    renderTo : 'attrition_chart',
                    plotBackgroundColor : null,
                    plotBorderWidth : null,
                    plotShadow : false
                },
                title : {
                    text : 'Attrition by Reason'
                },
                tooltip : {
                    formatter : function() {
                        return '<b>' + this.point.name + '</b>: ' + this.y  + ' %';
                    }
                },
                plotOptions : {
                    pie : {
                        allowPointSelect : true,
                        cursor : 'pointer',
                        dataLabels : {
                            enabled : true,
                            color : '#ffffff',
                            connectorColor : '#ffffff',
                            formatter : function() {
                                return '<b>' + this.point.name + '</b>: ' + this.y  + ' %';
                            }
                        }
                    }
                },
                series : [{
                        type : 'pie',
                        name : 'Reason weight',
                        data : [<?php foreach ($all_org_resign_reason as $aorr_key => $aorr_val) {
                                    ?>
                                    ['<?php echo $aorr_key ?>', <?php echo $aorr_val ?>],
                                 <?php } ?>]
                            }]
                });
            });

        });
    </script>
    <?php
}

function monthly_attrition_chart($monthly_org_resign,$monthly_org_resign_type,$monthly_attrition_rate) {
    ?>
    <script type="text/javascript">
            $(function () {
                var chart;
                $(document).ready(function() {
                    chart = new Highcharts.Chart({
                        chart: {
                            renderTo: 'attrition_chart_default',
                            type: 'column'
                        },
                        title: {
                            text: 'Monthly Attrition'
                        },
                        xAxis: [{
                            categories: [
                                <?php
                                    foreach($monthly_org_resign as $mor_key => $mor_val){
                                        foreach($mor_val as $mv_key => $mv_val){
                                            echo "'".$mor_key."-".$mv_key."',";
                                        }
                                    }
                                ?>
                            ]
                        }],
                        yAxis: [{
                            min: 0,
                            title: {
                                text: 'Head Count',
                                style: {
                                        color: '#4572A7'
                                        }
                            },
                            stackLabels: {
                                enabled: true,
                                style: {
                                    fontWeight: 'bold',
                                    color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                                }
                            }
                            },{
                            min: 0,
                            title: {
                                text: 'Attrition Rate',
                                style: {
                                        color: '#89A54E'
                                        }
                            },
                            stackLabels: {
                                enabled: true,
                                style: {
                                    fontWeight: 'bold',
                                    color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                                }
                            },
                            opposite: true
                        }],
                        legend: {
                            align: 'right',
                            x: -100,
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
                                return '<b>'+ this.x +'</b><br/>'+
                                    this.series.name +': '+ this.y +'%<br/>'
                            }
                        },
                        plotOptions: {
                            column: {
                                stacking: 'normal',
                                dataLabels: {
                                    enabled: true,
                                    color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
                                }
                            }
                        },
                        series: [
                            <?php
                                foreach($monthly_org_resign_type as $mort_key => $mort_val){ ?>
                                    {
                                           name: '<?php echo $mort_key; ?>',
                                           data: [
                                               <?php
                                                foreach($mort_val as $mv_key => $mv_val){
                                                    foreach($mv_val as $mvv_key => $mvv_val){
                                                        echo $mvv_val.",";
                                                    }
                                                }
                                               ?>
                                            ]
                                    },
                                <?php } ?> 
                                    {
                                            name: 'Attrition Rate',
                                            color: '#89A54E',
                                            type: 'spline',
                                            data: [ 
                                    <?php
                                        foreach ($monthly_attrition_rate as $mar_key => $mar_val) {
                                            foreach($mar_val as $mmv_key => $mmv_val){
                                                echo $mmv_val . ",";
                                            }
                                        } ?>
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
 * Date: 
 * [month_hc Line chart for hc compare]
 * @param  [type] $owned_project       [description]
 * @param  [type] $monthly_project_ahc [description]
 * @param  [type] $monthly_project_bhc [description]
 * @return [type]                      [description]
 */
function month_hc($owned_project, $monthly_project_ahc, $monthly_project_bhc) {
    ?>
    <script type="text/javascript">
        $(function () {
            var chart;
            $(document).ready(function() {
                chart = new Highcharts.Chart({
                    chart: {
                        renderTo: 'monthly_hc',
                        type: 'spline'
                    },
                    title: {
                        text: 'Head Count'
                    },
                    xAxis: {
                        categories: [
                            <?php
                            foreach ($monthly_project_ahc as $ma_key => $ma_val) {
                                echo "'" . $ma_key . "',";
                            }
                            ?>]
                        },
                    yAxis: {
                        title: {
                            text: 'Head Count'
                        }
                    },
                    plotOptions: {
                        line: {
                            dataLabels: {
                                enabled: true
                            },
                            enableMouseTracking: false
                        }
                    },
                    series: [{
                            name: 'Billable HC',
                            data: [<?php
                                    foreach ($monthly_project_bhc as $mb) {
                                        echo $mb . ",";
                                    }
                                    ?>]
                            }, {
                            name: 'Actual HC',
                            data: [<?php
                                    foreach ($monthly_project_ahc as $ma) {
                                        echo $ma . ",";
                                    }
                                ?>]
                        }]
                    });
                });
            });
    </script>
    <?php
}

/**
 * [month_hc_subscribe chart for hc of customer view]
 * @param  [type] $owned_project       [description]
 * @param  [type] $monthly_project_ahc [description]
 * @param  [type] $monthly_project_bhc [description]
 * @return [type]                      [description]
 */
function month_hc_curstomer($owned_project, $monthly_project_ahc, $monthly_project_bhc) {
    ?>
    <script type="text/javascript">
        $(function () {
            var chart;
            $(document).ready(function() {
                chart = new Highcharts.Chart({
                    chart: {
                        renderTo: 'monthly_hc',
                        type: 'spline'
                    },
                    title: {
                        text: 'Head Count'
                    },
                    xAxis: {
                        categories: [
                            <?php
                            foreach ($monthly_project_bhc as $mb_key => $mb_val) {
                                echo "'" . $mb_key . "',";
                            }
                            ?>]
                        },
                    yAxis: {
                        title: {
                            text: 'Head Count'
                        }
                    },
                    plotOptions: {
                        line: {
                            dataLabels: {
                                enabled: true
                            },
                            enableMouseTracking: false
                        }
                    },
                    series: [{
                            name: 'Billable HC',
                            data: [<?php
                                    foreach ($monthly_project_bhc as $mb) {
                                        echo $mb . ",";
                                    }
                                    ?>]
                            }]
                    });
                });
            });
    </script>
    <?php
}

/*
 * Draw chart of ur: Internal
 */

function monthly_or_ur_internal($org_info,$oid,$UR,$UR_org,$year) {
    ?>
    <script type="text/javascript">
            $(function () {
                var chart;
                $(document).ready(function() {
                    chart = new Highcharts.Chart({
                        chart: {
                            renderTo: 'monthly_org_ur',
                            type: 'line'
                        },
                        title: {
                            text: 'Internal UR of <?php echo $org_info[$oid]['NAME']."-".$year; ?>'
                        },
                        xAxis: {
                            categories: [
                                <?php
                                    foreach($UR as $ur_key => $ur_val){
                                        echo "'".$ur_key."',";
                                    }
                                ?>
                            ]
                        },
                        yAxis: {
                            title: {
                                text: 'Rate (%)'
                            }
                        },
                        tooltip: {
                            enabled: false,
                            formatter: function() {
                                return '<b>'+ this.series.name +'</b><br/>'+
                                    this.x +': '+ this.y +'%';
                            }
                        },
                        plotOptions: {
                            line: {
                                dataLabels: {
                                    enabled: true
                                },
                                enableMouseTracking: false
                            }
                        },
                        series: [
                            <?php
                                foreach($UR_org as $uo_key => $uo_val){ ?>
                                  {
                                       name: '<?php echo $org_info[$uo_key]['NAME']; ?>',
                                        data: [
                                            <?php
                                                foreach($uo_val['internal'] as $uv_key => $uv_val){
                                                    echo $uv_val.",";
                                                }
                                            ?>
                                        ]
                                  },
                                    <?php }
                            ?>]
                    });
                });
    
            });
        </script>
    <?php
}
/*
 * Draw chart of ur: External
 */

function monthly_or_ur_external($org_info,$oid,$UR,$UR_org,$year) {
    ?>
    <script type="text/javascript">
            $(function () {
                var chart;
                $(document).ready(function() {
                    chart = new Highcharts.Chart({
                        chart: {
                            renderTo: 'monthly_org_ur',
                            type: 'line'
                        },
                        title: {
                            text: 'External UR of <?php echo $org_info[$oid]['NAME']."-".$year; ?>'
                        },
                        xAxis: {
                            categories: [
                                <?php
                                    foreach($UR as $ur_key => $ur_val){
                                        echo "'".$ur_key."',";
                                    }
                                ?>
                            ]
                        },
                        yAxis: {
                            title: {
                                text: 'Rate (%)'
                            }
                        },
                        tooltip: {
                            enabled: false,
                            formatter: function() {
                                return '<b>'+ this.series.name +'</b><br/>'+
                                    this.x +': '+ this.y +'%';
                            }
                        },
                        plotOptions: {
                            line: {
                                dataLabels: {
                                    enabled: true
                                },
                                enableMouseTracking: false
                            }
                        },
                        series: [
                            <?php
                                foreach($UR_org as $uo_key => $uo_val){ ?>
                                  {
                                       name: '<?php echo $org_info[$uo_key]['NAME']; ?>',
                                        data: [
                                            <?php
                                                foreach($uo_val['external'] as $uv_key => $uv_val){
                                                    echo $uv_val.",";
                                                }
                                            ?>
                                        ]
                                  },
                                    <?php }
                            ?>]
                    });
                });
    
            });
        </script>
    <?php
}
?>

