<?php

class Xml {

	public function validate_xml($msg) {
		$xml = simplexml_load_string($msg);

		if ($xml === false) {
			echo "Failed loading XML: ";
			foreach(libxml_get_errors() as $error) {
				logging($error->message);
			}
		}
		else {
			$xml->registerXPathNamespace('SOAP-ENV', 'http://schemas.xmlsoap.org/soap/envelope/');
			$xml->registerXPathNamespace('NS', 'urn:SINTAC_WSDL');
			$nodes = $xml->xpath('//SOAP-ENV:Envelope/SOAP-ENV:Body/NS:MVT_PERIODE_UPDATEResponse/return');
			return $nodes[0];
		}
	}

}
