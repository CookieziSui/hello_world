<?php

/*
 *  Forbid Login session using IE
 */

function check_browser() {
    $browser = $_SERVER['HTTP_USER_AGENT'];
    if (strpos($browser, "MSIE")) {
        header("Location: browser_error.php");
    }
}
?>
