<?php

class sc_tkp_provider_dellin extends sc_tkp_provider {

    var $name		 = 'dellin';
    var $title		 = 'Деловые линии';
    var $siteTrackingUrl	 = 'http://www.dellin.ru/tracker/';
    var $tracknumber	 = null;
    var $api_url		 = 'http://public.services.dellin.ru/tracker/XML/';

    function __construct() {

    }

    /**
     * URL на страницу просмотра трекинга посылки
     */
    function getSiteUrlTracking() {
	  return $this->siteTrackingUrl . '?rwID=' . $this->tracknumber;
    }

    function getCurlParam() {
	  $param		 = array();
	  $param['rwID']	 = $this->tracknumber;
	  return $param;
    }

    function pharseRequest($xmlString) {
	  $ret = array();
	  $xml = new SimpleXMLElement($xmlString);
	  $ret['textstatus'] = trim($xml->header);
	  $ret['departure'] = $xml->departure;
	  $ret['status'] = 'inrout';
	  if ($ret['textstatus'] != 'Груз в пути.') {
		$ret['status'] = 'delivered';
	  }

//	  foreach ($xml->bbb->cccc as $element) {
//		foreach ($element as $key => $val) {
//		    echo "{$key}: {$val}";
//		}
//	  }
	  return $ret;
    }

}
