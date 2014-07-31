<?php

/*
 * Asset type
 */

$ASSET_TYPE = array(
    1 => array("PC", "background:hsla(190,90%,80%,0.8)"),
    2 => array("Monitor", "background:hsla(60,90%,50%,0.7)"),
    3 => array("Notebook", "background:hsla(100,80%,70%,0.7)"),
    4 => array("HD", "background:hsla(10,90%,50%,0.7)")
);

/*
 * Asset type in this array will be calculated for cost
 */
$VALID_ASSET_TYPE_DEPRECIATION = array("Monitor","PC","Notebook","Server");
$VALID_ASSET_TYPE_LIST = array("Monitor","PC","Notebook","Server","Printer","RAID","Switch","HD");
?>
