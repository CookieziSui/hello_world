<?php
// ==================================================================
//
// [ Function for user management ]
// Author: CC
// Date: November 07 2013
//
// ------------------------------------------------------------------
require_once __DIR__ . '../../../mysql.php';

/**
 * Author: CC
 * Date: November 08 2013
 * [customer_account Get all the customer account(Citrite account)]
 * @return [type] [description]
 */
function customer_account(){
	$qry_ca  = "SELECT DISTINCT ID,ACCOUNT,OWNER,EXPIRE_DATE FROM 3rd_account ORDER BY ACCOUNT ASC;";
	$rst_ca  = $GLOBALS['db']->query($qry_ca);
	$ca_list_temp = $rst_ca->fetch_all(MYSQLI_ASSOC);
	foreach($ca_list_temp as $clt_key => $clt_val){
		$ca_list[$clt_val['ACCOUNT']] = $clt_val;
	}
	return $ca_list;
}

/**
 * Author: CC
 * Date: November 08 2013
 * [get_customer_account get linked customer account info]
 * @param  [int] $id [description]
 * @return [array]     [description]
 */
function get_customer_account($id){
	$get_ca_info  = "SELECT ID,ACCOUNT,OWNER FROM customer_account WHERE ID = $id;";
	$rst_ca_info  = $GLOBALS['db']->query($get_ca_info);
	$ca_info_temp = $rst_ca_info->fetch_all(MYSQLI_ASSOC);
	foreach($ca_info_temp as $cit_key => $cit_val){
		$ca_info = $cit_val;
	}
	return $ca_info;
}
?>