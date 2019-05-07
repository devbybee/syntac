<?php
require 'config/autoload.php';



$xml = new Xml();
$tr = new Transaction();
$tr->set_table($db['mro']['table']);
$tr->set_ws_user($ws['syntac']['user']);
$tr->set_ws_pass($ws['syntac']['pass']);
$tr->set_ws_uri($ws['syntac']['uri']);

/*
  get data from wsdl
  dev from file
*/
$response = $tr->get_data_wsdl();
//$response = file_get_contents('response/dummy.xml');

/*save response as a file*/
//generate_file($response);
/*save response as a file*/

$obj_xml = $xml->validate_xml($response);

/**
 * mapping clause where with plan_date parameter
 */
$where_param = "";
$exist_param = array();
//echo $num_response; exit();
foreach ($obj_xml->DATA->item as $key => $val) {
  $plan_date = change_date($val->COL_PLAN_DEPARTURE_DATE);
  
  if (!isset($exist_param[$plan_date])) {
    $where_param .= "'" . $plan_date . "'";
    $where_param .= ",";
    $exist_param[$plan_date] = '';
  }
}
//echo $where_param; exit();

/**
 * get data from master data movement
 */
$data_movement  = $tr->get_data_movement(rtrim($where_param, ','), $conn_db);
//var_dump($data_movement); exit();

//parsing data object & mapping to an array
foreach ($obj_xml->DATA->item as $key => $val) {

  $data = array();

  /*don't get flight within status D*/
  $is_deleted = $tr->is_deleted_flight($val->COL_CANCEL_INDICATOR);
  $is_no_reg = $tr->is_no_registration($val->COL_AIRCRAFT_REGISTRATION); 

  if ($is_deleted || $is_no_reg) {
    continue;
  }

  $tr->set_flight_type($val->COL_FLIGHT_TYPE);

  $data['code'] = $val->COL_CARRIER_CODE;
  $data['fNumber'] = $val->COL_FLIGHT_NUMBER;
  $data['depStat'] = $val->COL_DEPARTURE_STATION;
  $data['planDep'] = change_date($val->COL_PLAN_DEPARTURE_DATE);
  $data['acReg'] = new_ac_reg($val->COL_AIRCRAFT_REGISTRATION);
  $data['estDepDate'] = change_date($val->COL_EST_DEP_DATE);
  $data['estDepTime'] = change_time($val->COL_EST_DEP_TIME);
  $data['estWheelsOffDate'] = change_date($val->COL_EST_WHEELS_OFF_DATE);
  $data['estWheelsOffTime'] = change_time($val->COL_EST_WHEELS_OFF_TIME);
  $data['estWheelsOnDate'] = change_date($val->COL_EST_WHEELS_ON_DATE);
  $data['estWheelsOnTime'] = change_time($val->COL_EST_WHEELS_ON_TIME);
  $data['estArrDate'] = change_date($val->COL_EST_ARR_DATE);
  $data['estArrTime'] = change_time($val->COL_EST_ARR_TIME);
  $data['choxOffDate'] = change_date($val->COL_CHOX_OFF_DATE);
  $data['choxOffTime'] = change_time($val->COL_CHOX_OFF_TIME);
  $data['wheelsOffDate'] = change_date($val->COL_WHEELS_OFF_DATE);
  $data['wheelsOffTime'] = change_time($val->COL_WHEELS_OFF_TIME);
  $data['wheelsOnDate'] = change_date($val->COL_WHEELS_ON_DATE);
  $data['wheelsOnTime'] = change_time($val->COL_WHEELS_ON_TIME);
  $data['choxOnDate'] = change_date($val->COL_CHOX_ON_DATE);
  $data['choxOnTime'] = change_time($val->COL_CHOX_ON_TIME);
  $data['arrStat'] = $val->COL_ARR_STATION;
  $data['flightType'] = $tr->get_flight_type();
  $data['cancellation'] = $val->COL_CANCEL_INDICATOR;

  /*additional 26-02-2019*/
  $data['refNum'] = $val->COL_ID;
  $data['entryDate'] = change_date_time($val->COL_LOG_UPDATE);
  /*additional 26-02-2019*/

  /*departune number being hard code cus no data from resource*/
  $data['depNumber'] = '02';

  /*keys for others system*/
  $data['dupKey'] = $data['code'] . '|' . $data['fNumber'] . '|' . $data['depNumber'] . '|' . $data['depStat'] . '|' . $data['planDep'] . '|' . $data['arrStat'];

  $data['key'] = $data['code'] . '|' . $data['fNumber'] . '|' . $data['depNumber'] . '|' . $data['depStat'] . '|' . $data['planDep'] . '|' . $data['acReg'] . '|' . $data['choxOffDate'] . '|' . $data['choxOffTime'] . '|' . $data['wheelsOffDate'] . '|' . $data['wheelsOffTime'] . '|' . $data['estDepDate'] . '|' . $data['estDepTime'] . '|' . $data['estWheelsOffDate'] . '|' . $data['estWheelsOffTime'] . '|' . $data['choxOnDate'] . '|' . $data['choxOnTime'] . '|' . $data['wheelsOnDate'] . '|' . $data['wheelsOnTime'] . '|' . $data['estArrDate'] . '|' . $data['estArrTime'] . '|' . $data['estWheelsOnDate'] . '|' . $data['estWheelsOnTime'] . '|' . $data['arrStat'] . '|' . $data['flightType'] . '|' . $data['cancellation'];
  //var_dump($data['key']); exit();
  //var_dump($data); exit();
  /*check is record already exist*/
  $is_exist =  isset($data_movement[ $data['key'] ]) ? true : false;
  //$is_exist = $tr->is_exist_flight($data['key'], $conn_db);

  if ( ! $is_exist ) {
    do_transaction($data, $conn_db, $db);
  }
  //var_dump($data); exit();
} //end foreach

/*
process until success
*/

function do_transaction($data, $conn_db, $db){
    $tr = new Transaction();
    $tr->set_table($db['mro']['table']);
    //get last sequence for unique id
    $data['idx'] = $tr->get_last_sequence($conn_db);
    //var_dump($data['idx']); exit();
    $is_success = $tr->insert_data($data, $conn_db);

    if ($is_success) {
      echo 'success: 1 rows affected';
    }
    else {
      do_transaction($data, $conn_db, $db);   
    }
}
