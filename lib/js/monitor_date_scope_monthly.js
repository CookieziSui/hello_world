/*
 * JS for date scope
 * Monthly
 */

$(function() {
    //datepicker designed by jQuery UI
    $("#year_month_from ,#year_month_to").datepicker({
        showButtonPanel: true,
        changeMonth : true,
        changeYear : true,
        onClose: function(dateText, inst) {  
            var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val(); 
            var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val(); 
            $(this).val($.datepicker.formatDate('yy-mm', new Date(year, month, 1)));
        }
    });
            
    $("#year_month_from ,#year_month_to").focus(function () {
        $(".ui-datepicker-calendar").hide();
        $("#ui-datepicker-div").position({
            my: "center top",
            at: "center bottom",
            of: $(this)
        });    
    });
            
    $(":submit").live('click',function(){
        var date_from=0;
        var date_to=0;
        var oid=$("#org_id").val();
        date_from=$("#year_month_from").val();
        date_to=$("#year_month_to").val();
        $("#container").hide();
        if(Date.parse(date_from) > Date.parse(date_to)){
            alert("Error date scope! Date from is bigger than date to. Please modify the date.");
        }
        $.post('../../monitor/ajax_resource_task_status.php',
        {
            date_from:date_from,
            date_to:date_to,
            oid:oid
        },
        function (output){
            $('#container').html(output).fadeIn(500);
        });
    });
});


