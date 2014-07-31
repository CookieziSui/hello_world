<?php

/*
 * Escapes special characters in a string for use in an SQL statement
 */

function escape_special_character($sql_string) {
    $search = array(
        "'<script[^>]*?>.*?</script>'si",
        "'<[\/\!]*?[^<>]*?>'si",
        "\x00", 
        "\\", 
        "'", 
        "\"", 
        "\x1a", 
        "'([\r\n])[\s]+'");
    $replace = array(
        "",
        "",
        "\\x00", 
        "\\\\", 
        "\'", 
        "\\\"", 
        "\\\x1a", 
        "\\1");

    return str_replace($search, $replace, $sql_string);
}

?>
