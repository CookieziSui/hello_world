/*
 * JS for date scope
 * Month to month
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
            
    
});


