<?php

require_once __DIR__ . '/../../../lib/inc/highcharts.php';

/*
 * Date: 2012-11-28
 * Author: CC
 * Purpose: Draw chart in dashboard
 */

function dashboard_cost_chart($year_monthly_org_cost, $year) {
    ?>
    <script type="text/javascript">
        $(function () {
            var chart;
            $(document).ready(function() {
                chart = new Highcharts.Chart({
                    chart: {
                        renderTo: 'cost_chart',
                        type: 'column'
                    },
                    title: {
                        text: 'Monthly Asset Depreciation'
                    },
                    xAxis: {
                        categories: [
                            <?php 
                                foreach($year_monthly_org_cost[$year] as $mory_key => $mor_val){
                                    echo "'".$mory_key."',";
                                }
                            ?>
                        ]
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Value (RMB)'
                        }
                    },
                    tooltip: {
                        formatter: function() {
                            return ''+
                                this.x +': '+ this.y +' RMB';
                        }
                    },
                    plotOptions: {
                        column: {
                            pointPadding: 0.2,
                            borderWidth: 0
                        }
                    },
                    series: [{
                            name: 'Depreciation',
                            data: [
                                <?php
                                foreach($year_monthly_org_cost[$year] as $mory_key => $mor_val){
                                    echo $mor_val.",";
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
?>
