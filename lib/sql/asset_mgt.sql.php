<?php

/*
 * SQLs for asset management
 * 
 */
require_once __DIR__ . '/../../mysql.php';

/*
 * Query all the CPU model
 */
  $qry_cpu= "SELECT DISTINCT cpu FROM asset_info WHERE cpu IS NOT NULL;";
  
?>
