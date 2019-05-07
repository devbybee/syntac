<?php

function logging($message) {
	$datetime = date('Y-m-d H:i:s');
	error_log($datetime . ' ' . $message . "\r\n", 3, "./error.log" );
	exit('stopped the process');
}

function change_date($date) {
	if ( strlen($date) == 0 ) {
		$newDate = '';
	}
	else {
		$replace = str_replace('/', '-', $date);
		$newDate = date("Y-m-d", strtotime($replace));
	}

	return $newDate;
}

function change_time($time) {
	if ( strlen($time) == 0 ) {
		$newDate = '';
	}
	else {
		$newDate = date("H:i:s", strtotime($time));
	}
	
	return $newDate;
}

function change_date_time($datetime) {
	$minus7 = 7 * 60 * 60;
	$newDate = date("Y-m-d H:i:s", (strtotime($datetime) - $minus7) );
	//$newDate = date("Y-m-d H:i:s", strtotime($datetime));
	return $newDate;
}

function new_ac_reg($acreg) {
	$prefix = 'PK-';
	return $prefix . $acreg;
}

function generate_file($msg) {
	$xml = new SimpleXMLElement($msg);
	$xml_file = $xml->asXML('./response/' . uniqid('gmf_') . '.xml');
}

function rem_space($string) {
	return str_replace(' ', '', $string);
}

function empty_date_time($date, $time = '') {
	if ( empty($time) ) {
		if ($date == '1900-01-01') {
			return '';
		}

		return $date;
	}

	else {
		if ($date == '1900-01-01') {
			return '';
		}
		else {
			return $time;
		}
	}
}

function remove_zulu($param) {
	$result = preg_replace('/^([^.]*).*$/', '$1', $param);
	return $result;
}
