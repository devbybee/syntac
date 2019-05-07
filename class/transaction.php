<?php

class Transaction {

	private $_table = '';
	private $_ws_user = '';
	private $_ws_pass = '';
	private $_ws_uri = '';
	private $_flight_type = '';

	private function get_date_param() {
		$date['end'] = date('d-M-Y H:i');
		$date['start'] = date('d-M-Y H:i', (strtotime( $date['end'] ) - 60) );

		return $date;
	}

	public function set_flight_type($ft) {
		$return = 'N';

		if ( strtolower($ft) === 'j' ) {
			$return = 'J';
		}

		$this->_flight_type = $return; 
	}

	public function get_flight_type() {
		return $this->_flight_type;
	}

	public function is_deleted_flight($status) {
		$return = FALSE;

		if ( strtolower($status) === 'd' ) {
			$return = TRUE;
		}

		return $return;
	}
	
	public function is_no_registration($reg) {
		if (is_null($reg) || empty($reg) || strlen($reg) == 0) {
			return TRUE;
		}
		return FALSE;
	}	

	public function set_table($table) {
		$this->_table =  $table;
	}

	public function set_ws_user($user) {
		$this->_ws_user =  $user;
	}

	public function set_ws_pass($pass) {
		$this->_ws_pass =  $pass;
	}

	public function set_ws_uri($uri) {
		$this->_ws_uri =  $uri;
	}

	public function is_exist_flight($key, $conn) {
		$query =  mssql_query("SELECT COL_FLAG  
			FROM " . $this->_table . " WHERE
				CONVERT (VARCHAR (MAX), [COL_KEY]) = '" . $key . "'
			", $conn);

		$result = mssql_fetch_array($query);
		return $result;
	}

	public function get_data_movement($param, $conn) {
		$query = mssql_query("SELECT A.COL_CARRIER_CODE, A.COL_FLIGHT_NUMBER, A.COL_DEPARTURE_NUMBER, 
			A.COL_DEPARTURE_STATION, A.COL_PLAN_DEPARTURE_DATE, A.COL_AIRCRAFT_REGISTRATION,
			A.COL_CHOX_OFF_DATE, A.COL_CHOX_OFF_TIME,
			A.COL_WHEELS_OFF_DATE, A.COL_WHEELS_OFF_TIME,
			A.COL_EST_DEP_DATE, A.COL_EST_DEP_TIME,
			A.COL_EST_WHEELS_OFF_DATE, A.COL_EST_WHEELS_OFF_TIME,
			A.COL_CHOX_ON_DATE, A.COL_CHOX_ON_TIME,
			A.COL_WHEELS_ON_DATE, A.COL_WHEELS_ON_TIME,
			A.COL_EST_ARR_DATE, A.COL_EST_ARR_TIME,
			A.COL_EST_WHEELS_ON_DATE, A.COL_EST_WHEELS_ON_TIME,
			A.COL_ARR_STATION, A.COL_FLIGHT_TYPE, A.COL_CANCEL_INDICATOR
		FROM TBL_AC_MOVEMENT_PROD1 A
		WHERE 
			A.COL_PLAN_DEPARTURE_DATE IN (" . $param . ") AND
			A.COL_CARRIER_CODE IN ('IN', 'SJ')", $conn
		);
		
		$result = array();

		while ($rows = mssql_fetch_array($query)) {
			$result[ $rows['COL_CARRIER_CODE'] . '|'
				. $rows['COL_FLIGHT_NUMBER'] . '|'
				. $rows['COL_DEPARTURE_NUMBER'] . '|'
				. $rows['COL_DEPARTURE_STATION'] . '|'
				. $rows['COL_PLAN_DEPARTURE_DATE'] . '|'
				. $rows['COL_AIRCRAFT_REGISTRATION'] . '|' 
				. empty_date_time($rows['COL_CHOX_OFF_DATE']) . '|' 
				. empty_date_time($rows['COL_CHOX_OFF_DATE'], remove_zulu($rows['COL_CHOX_OFF_TIME'])) . '|' 
				. empty_date_time($rows['COL_WHEELS_OFF_DATE']) . '|' 
				. empty_date_time($rows['COL_WHEELS_OFF_DATE'], remove_zulu($rows['COL_WHEELS_OFF_TIME'])) . '|' 
				. empty_date_time($rows['COL_EST_DEP_DATE']) . '|' 
				. empty_date_time($rows['COL_EST_DEP_DATE'], remove_zulu($rows['COL_EST_DEP_TIME'])) . '|' 
				. empty_date_time($rows['COL_EST_WHEELS_OFF_DATE']) . '|' 
				. empty_date_time($rows['COL_EST_WHEELS_OFF_DATE'], remove_zulu($rows['COL_EST_WHEELS_OFF_TIME'])) . '|' 
				. empty_date_time($rows['COL_CHOX_ON_DATE']) . '|' 
				. empty_date_time($rows['COL_CHOX_ON_DATE'], remove_zulu($rows['COL_CHOX_ON_TIME'])) . '|' 
				. empty_date_time($rows['COL_WHEELS_ON_DATE']) . '|' 
				. empty_date_time($rows['COL_WHEELS_ON_DATE'], remove_zulu($rows['COL_WHEELS_ON_TIME'])) . '|' 
				. empty_date_time($rows['COL_EST_ARR_DATE']) . '|' 
				. empty_date_time($rows['COL_EST_ARR_DATE'], remove_zulu($rows['COL_EST_ARR_TIME'])) . '|' 
				. empty_date_time($rows['COL_EST_WHEELS_ON_DATE']) . '|' 
				. empty_date_time($rows['COL_EST_WHEELS_ON_DATE'], remove_zulu($rows['COL_EST_WHEELS_ON_TIME'])) . '|' 
				. $rows['COL_ARR_STATION'] . '|'
				. $rows['COL_FLIGHT_TYPE'] . '|'
				. rem_space($rows['COL_CANCEL_INDICATOR'])
			] = '';
		}
		return $result;
	}

	public function get_last_sequence($conn) {
		$query = mssql_query("SELECT MAX(COL_IDX) AS last FROM " . $this->_table, $conn);
		$result = mssql_fetch_array($query);

		if ( empty($result['last']) OR !isset($result['last']) ) {
			$return = 900000006939;
		}
		else {
			$return = $result['last'] + 1;
		}

		return $return;
	}

	public function insert_data($data, $conn_db) {
		$return = FALSE;
		$query = "INSERT INTO " . $this->_table . " (COL_IDX, COL_CARRIER_CODE, COL_FLIGHT_NUMBER, 							COL_DEPARTURE_NUMBER, COL_DEPARTURE_STATION, COL_PLAN_DEPARTURE_DATE,
				COL_AIRCRAFT_REGISTRATION, COL_CHOX_OFF_DATE, COL_CHOX_OFF_TIME, COL_WHEELS_OFF_DATE, COL_WHEELS_OFF_TIME,
				COL_EST_DEP_DATE, COL_EST_DEP_TIME, COL_EST_WHEELS_OFF_DATE, COL_EST_WHEELS_OFF_TIME, COL_CHOX_ON_DATE,
				COL_CHOX_ON_TIME, COL_WHEELS_ON_DATE, COL_WHEELS_ON_TIME, COL_EST_ARR_DATE, COL_EST_ARR_TIME,
				COL_EST_WHEELS_ON_DATE, COL_EST_WHEELS_ON_TIME, COL_ARR_STATION, COL_FLIGHT_TYPE, COL_ENTRY_DATE,
				COL_CANCEL_INDICATOR, COL_DUPL, COL_KEY, COL_FLAG, REFERENCE_NUMBER)
			VALUES ('".$data['idx']."', '".$data['code']."', '".$data['fNumber']."',
				'".$data['depNumber']."', '".$data['depStat']."', '".$data['planDep']."',
				'".$data['acReg']."', '".$data['choxOffDate']."', '".$data['choxOffTime']."', '".$data['wheelsOffDate']."', '".$data['wheelsOffTime']."',
				'".$data['estDepDate']."', '".$data['estDepTime']."', '".$data['estWheelsOffDate']."', '".$data['estWheelsOffTime']."', '".$data['choxOnDate']."',
				'".$data['choxOnTime']."', '".$data['wheelsOnDate']."', '".$data['wheelsOnTime']."', '".$data['estArrDate']."', '".$data['estArrTime']."',
				'".$data['estWheelsOnDate']."', '".$data['estWheelsOnTime']."', '".$data['arrStat']."', '".$data['flightType']."', '".$data['entryDate'] . "',
				'".$data['cancellation']."', '".$data['dupKey']."', '".$data['key']."', '0', '".$data['refNum']."'
					)
				";

		$insert = mssql_query($query, $conn_db);
		if ($insert) {
			$return = TRUE;	
		}
		else {
			//logging(mssql_get_last_message());
			$return = FALSE;
		}
		
		return $return;
	}

	public function get_data_wsdl() {
		$date_param = $this->get_date_param();

		$start = $date_param['start'];
		$end = $date_param['end'];
		//$start = '30-MAR-2019 19:37';
		//$end = '30-MAR-2019 19:40';

		$post_string = '
			<soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:SINTAC_WSDL">
				<soapenv:Header/>
				<soapenv:Body>
					<urn:MVT_PERIODE_UPDATE soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
						<param xsi:type="urn:INPUTS_ARRAY_MVT_PERIODE_UPDATE">
							<!--You may enter the following 4 items in any order-->
							<USERNAME xsi:type="xsd:string">' . $this->_ws_user . '</USERNAME>
							<PASSWORD xsi:type="xsd:string">' . $this->_ws_pass . '</PASSWORD>
							<UPDATE_FROM xsi:type="xsd:string">' . $start . '</UPDATE_FROM>
							<UPDATE_TO xsi:type="xsd:string">' . $end . '</UPDATE_TO>
						</param>
					</urn:MVT_PERIODE_UPDATE>
				</soapenv:Body>
			</soapenv:Envelope>';

		$ch = curl_init();
  		
  		curl_setopt($ch, CURLOPT_URL, $this->_ws_uri);
  		curl_setopt($ch, CURLOPT_FAILONERROR, true);
  		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
  		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: text/xml; charset=utf-8',
                'Connection: Keep-Alive'
            ));    
  		
  		$result = curl_exec($ch);

  		if (curl_error($ch)) {
    		$error_msg = curl_error($ch);
    		logging($error_msg);
		}

  		curl_close($ch);

  		return $result;
	}
}
