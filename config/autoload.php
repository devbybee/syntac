<?php
date_default_timezone_set("Asia/Jakarta");

require_once  'config/helper.php';
require_once  'config/database.php';
require_once  'config/wsdl.php';
require_once  'class/xml.php';
require_once  'class/transaction.php';

/*
create connection database
*/
$conn_db = mssql_connect($db['mro']['host'], $db['mro']['user'], $db['mro']['pass']);
if ( ! $conn_db ) {
	echo 'Can\t access database server';
	logging (mssql_get_last_message());
}
else {
	$select_db = mssql_select_db($db['mro']['db'], $conn_db);
	if ( ! $select_db ) {
		echo 'is database exist or active?';
		logging (mssql_get_last_message());
	}
}
