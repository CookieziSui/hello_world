<?php

/*
 * Check severity depend on the value
 *  and then do different thing, e.g. different background colors
 */

/*
 * Check billable rate
 */
function billable_rate($value) {
    if($value >=80 ){
        return "normal";
    }else if(($value<80)&&($value>=60)){
        return "warn";
    }else{
        return "critical";
    }
}

/*
 * Check ur 
 */
function ur($value) {
    if($value >=80 ){
        return "normal";
    }else if(($value<80)&&($value>=60)){
        return "warn";
    }else{
        return "critical";
    }
}

?>
