<?php

require_once __DIR__ . '/../../../lib/inc/highcharts.php';

function draw_asset_by_type($org_asset_info,$oid,$su_id,$gtype) {
    ?>
    <script type="text/javascript">
        $(function () {
            var chart;
            $(document).ready(function() {
                var colors = Highcharts.getOptions().colors,
                chart = new Highcharts.Chart({
                    chart: {
                        renderTo: 'asset_count_by_type',
                        type: 'column'
                    },
                    title: {
                        text: 'Asset Count By Type'
                    },
                    xAxis: {
                        categories: [
                            <?php 
                                foreach($org_asset_info['COUNT'] as $oai_key => $oai_val){
                                    if($oai_key !== "GENERAL"){
                                        echo "'".$oai_key."',";
                                    }
                                }
                            ?>
                        ],
                        labels: {
                            rotation: 0,
                            align: 'right'
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Asset Count'
                        }
                    },
                    legend: {
                        enabled: false
                    },
                    tooltip: {
                        enabled: false,
                        formatter: function() {
                            return '<b>'+ this.x + ':</b>'+this.y+'<br/>';
                        }
                    },
                    plotOptions: {
                                    column: {
                                        shadow: false,
                                        cursor: 'pointer',
                                        dataLabels: {
                                            enabled: true,
                                            style: {
                                                fontSize: '13px',
                                                fontWeight: 'bold',
                                                fontFamily: 'Verdana, sans-serif'
                                            }
                                        }//,
                                        // point: {
                                        //     events: {
                                        //     click: function() {
                                        //     //alert(this.y);
                                        //     window.location="../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&url=<?php echo base64_encode("monitor/asset_info_by_org.php"); ?>&gtype=<?php echo base64_encode($gtype); ?>&atype="+this.category+"&count="+this.y+"";
                                        //                     }
                                        //                 }
                                        //         }
                                            }
                                },
                    series: [{
                            name: 'Count',
                            data: [<?php 
                                foreach($org_asset_info['COUNT'] as $oai_key => $oai_val){
                                    if($oai_key !== 'GENERAL'){
                                        echo $oai_val.",";
                                    }
                                }
                            ?>
                            ],
                            color: colors[4]
                        }]
                });
            });
            
        });
    </script>
<?php }

function draw_asset_by_owner($asset_owner_count,$oid,$su_id,$gtype) { 
    ?>
<script type="text/javascript">
$(function () {
    var chart;
    $(document).ready(function() {
        chart = new Highcharts.Chart({
            chart: {
                renderTo: 'asset_count_by_owner',
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: 'Asset Count By Owner'
            },
            tooltip: {
                formatter: function() {
                    return '<b>'+ this.point.name +'</b>: '+ this.y;
                }
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: false
                    },
                    point: {
                        events: {
                        click: function() {
                        //alert(this.name);
                        window.location="../../general/home/index.php?oid=<?php echo base64_encode($oid); ?>&su_id=<?php echo base64_encode($su_id); ?>&url=<?php echo base64_encode("monitor/asset_info_by_org.php"); ?>&gtype=<?php echo base64_encode($gtype); ?>&otype="+this.name+"&count="+this.y+"";
                                                  }
                                    }
                             },
                    showInLegend: true
                }
            },
            series: [{
                type: 'pie',
                name: 'Browser share',
                data: [
                    <?php 
                    foreach($asset_owner_count as $aoc_key => $aoc_val){
                        echo "['".$aoc_key."',".$aoc_val['total_count']."],";
                    }
                    ?>
//                    {
//                        name: 'Chrome',
//                        y: 12.8,
//                        sliced: true,
//                        selected: true
//                    }
                ]
            }]
        });
    });
    
});
</script>
<?php }
?>
