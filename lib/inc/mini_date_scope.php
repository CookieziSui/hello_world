<link rel="stylesheet" type="text/css" href="../../../lib/css/3rd_party/css/datepicker.css">
<script type="text/javascript" src="../../../lib/css/3rd_party/scripts/bootstrap-datepicker.js"></script>
<?php
$today     = $GLOBALS['today'];
$date_from = $GLOBALS['today'];
$date_to   = $GLOBALS['today'];
?>
<!--Dialog: Customize date scope box  -->
<div id="mini_customize_date" class="modal message hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header"  align="left">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h3>Set Date Scope</h3>
    </div>
    <div class="modal-body" align="left">
        <form class="form-horizontal">
            <div class="control-group">
                <div class="controls">
                    <div data-date-format="yyyy-mm-dd" data-date="<?php echo $today; ?>" class="input-append date">
                        <input type="text" id="mini_cus_date_from" class="span2" name="cus_date_from" placeholder="Date From" required>
                        <span class="add-on"><i class="icon-calendar"></i></span>
                    </div>
                    <span class="form-title">--</span>
                    <div data-date-format="yyyy-mm-dd" data-date="<?php echo $today; ?>" class="input-append date">
                        <input type="text" id="mini_cus_date_to" class="span2" name="cus_date_to" placeholder="Date To" required>
                        <span class="add-on"><i class="icon-calendar"></i></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" id="mini_cus_date_confirm">Confirm</button>
            </div>
        </form>
    </div>
</div>
<!--Dialog: Customize date scope box  -->
<div id="mini_invald_date" class="modal message hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-body">
        <span class="form-title">Wrong date scope, please a choose a valid date scope!</span>
        <div class="modal-footer">
            <button type="button" class="btn-warning" id="mini_btn_invald_date">Close</button>
        </div>
    </div>
</div>
<script>
    $(function() {
        $('.date').datepicker();
        $("#mini_btn_invald_date").live("click",function(){
            $("#mini_invald_date").modal("hide");
        });
    });
 
</script>
<div id="task_status_date">
    <input type="hidden" id="org_id" value="<?php echo isset($oid)?$oid:""; ?>" >
    <input type="hidden" id="su_id" value="<?php echo isset($su_id)?$su_id:""; ?>" >
    <input type="hidden" id="optype" value="<?php echo isset($optype)?$optype:""; ?>" >
    <input type="hidden" id="gtype" value="<?php echo isset($gtype)?$gtype:""; ?>" >
    
    <input type="hidden" id="this_month" value="<?php echo  date("Y-m",strtotime(date( 'Y-m-01' ))); ?>">   
    <input type="hidden" id="last_month" value="<?php echo date("Y-m",strtotime(date( 'Y-m-01' )." -1 month")); ?>">
    <input type="hidden" id="last_month_last_day" value="<?php echo date("Y-m-d",strtotime(date( 'Y-m-01' )." -1 day")); ?>">
    <input type="hidden" id="today" value="<?php echo $today; ?>">
    <select id="mini_date_scope_view" style="width: 30%;">
        <option value="0">Date Scope</option>
        <option value="1">This Month</option>
        <option value="2">Last Month</option>
        <option value="3">Customize</option>
    </select>
</div>
