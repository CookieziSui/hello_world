<?php
require_once __DIR__ . '/../../../lib/inc/highcharts.php';
require_once __DIR__ . '/../../../lib/function/monitor_mgt.php';

/*
 * For one month  column basic
 * Draw the chart for draw_compare_estimation_vs_actual
 */
function draw_compare_estimation_vs_actual_month($org_time, $org_name, $current_month) {
    ?>
<script type="text/javascript">
    $(function () {
    var chart;
    $(document).ready(function() {
        chart = new Highcharts.Chart({
            chart: {
                renderTo: '<?php echo md5($org_name); ?>',
                type: 'column'
            },
            title: {
                x: 150,
                text: '<?php echo $org_name;?>'
            },
            xAxis: {
                categories: [ '<?php echo $current_month;?>']
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Time (h)'
                }
            },
            legend: {
                layout: 'vertical',
                backgroundColor: '#FFFFFF',
                align: 'left',
                verticalAlign: 'top',
                x: 50,
                y: 0,
                floating: true,
                shadow: true
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
                }
            },
                series: [{
                name: 'Estimate Setup',
                data: [<?php echo $org_time['total_org_ex_setup_time'];?>]
    
            }, {
                name: 'Estimate Execute',
                data: [<?php echo $org_time['total_org_ex_execution_time'];?>]
    
            }, {
                name: 'Actual Setup',
                data: [<?php echo $org_time['total_org_ac_setup_time'];?>]
    
            }, {
                name: 'Actual Execute',
                data: [<?php echo $org_time['total_org_ac_execution_time'];?>]
    
            }, {
                name: 'Investigate',
                data: [<?php echo $org_time['total_org_investigate_time'];?>]
    
            }]
        });
    });
    
});
</script>
<?php 
}

/*
 * For more than one month line basic
 * Draw the chart for draw_compare_estimation_vs_actual
 */

function draw_compare_estimation_vs_actual_months($org_time_by_orgid, $org_name,$months_num,$date_from,$date_to) {
    //dump($org_time_by_orgid);
    ?>
    <script type="text/javascript">
        $(function () {
            var chart;
            $(document).ready(function() {
                chart = new Highcharts.Chart({
                    chart: {
                        renderTo: '<?php echo md5($org_name); ?>',
                        type: 'line',
                        marginRight: 130,
                        marginBottom: 25
                    },
                    title: {
                        text: '<?php echo $org_name;?>',
                        x: -20 //center
                    },
                    subtitle: {
                        text: '<?php echo $date_from . "--" . $date_to; ?>',
                        x: -20
                    },
                    xAxis: {
                        categories: [
                             <?php
                            foreach ($org_time_by_orgid as $otbo_key => $otbo_val){
                                    echo "'".$otbo_key."',";
                            }
                        ?>
                        ]
                    },
                    yAxis: {
                        title: {
                            text: 'Time (h)'
                        },
                        stackLabels: {
                            enabled: false,
                            style: {
                                fontWeight: 'bold',
                                color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                            }
                        }
                    },
                    tooltip: {
                        formatter: function() {
                            return '<b>'+ this.series.name +'</b><br/>'+
                                this.x +': '+ this.y +'h';
                        }
                    },
                    plotOptions: {
                        column: {
                            stacking: 'normal',
                            dataLabels: {
                            enabled: false,
                            color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
                            }
                        },
                        line: {
                            stacking: 'normal',
                            dataLabels: {
                            enabled: false,
                            color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'black'
                            }
                        }
                        
                    },
                    legend: {
                        layout: 'vertical',
                        align: 'right',
                        verticalAlign: 'top',
                        x: -10,
                        y: 50,
                        borderWidth: 0
                    },
                    series: [
                         {
                            type: 'column',
                            name: 'Investigate',
                            data: [
                            <?php 
                                foreach ($org_time_by_orgid as $otbo_key => $otbo_val){ 
                                    echo $otbo_val['total_org_investigate_time'].",";
                                }
                            ?>
                            ]
                        },{
                            type: 'column',
                            name: 'Actual Setup',
                            data: [
                            <?php 
                                foreach ($org_time_by_orgid as $otbo_key => $otbo_val){ 
                                    echo $otbo_val['total_org_ac_setup_time'].",";
                                }
                            ?>
                            ]
                        }, {
                            type: 'column',
                            name: 'Actual Execute',
                            data: [
                            <?php 
                                foreach ($org_time_by_orgid as $otbo_key => $otbo_val){ 
                                    echo $otbo_val['total_org_ac_execution_time'].",";
                                }
                            ?>
                            ]
                        },{
                            type: 'line',
                            name: 'Estimate Setup',
                            data: [
                            <?php 
                                foreach ($org_time_by_orgid as $otbo_key => $otbo_val){ 
                                    echo $otbo_val['total_org_ex_setup_time'].",";
                                }
                            ?>
                            ]
                        }, {
                            type: 'line',
                            name: 'Estimate Execution',
                            data: [
                            <?php 
                                foreach ($org_time_by_orgid as $otbo_key => $otbo_val){ 
                                   echo $otbo_val['total_org_ex_execution_time'].",";
                                }
                            ?>
                            ]
                    }]
                });
            });
                                        
        });
    </script>
<?php }
?>
