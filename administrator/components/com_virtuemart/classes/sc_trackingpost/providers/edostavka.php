<?php

class sc_tkp_provider_edostavka extends sc_tkp_provider {

    var $name		 = 'edostavka';
    var $title		 = 'СДЭК';
    var $siteTrackingUrl	 = 'http://www.edostavka.ru/track.html';
    var $tracknumber	 = null;
    var $api_url		 = 'http://gw.edostavka.ru:11443/status_report_h.php';
    var $secure 		 = '';
    var $account 		 = '';

    function __construct() {

    }

    /**
     * URL на страницу просмотра трекинга посылки
     */
    function getSiteUrlTracking() {
	  return $this->siteTrackingUrl;
    }

    function getCurlParam() {
	  $param		 = array();
	  $param['Number']	 = $this->tracknumber;
	  $param['account']	 = $this->account;
	  $param['secure']	 = $this->secure;
	  return $param;
    }

    function pharseRequest($xmlString) {
	  $ret			 = array();
	  $xml			 = new SimpleXMLElement($xmlString);
	  $ret['textstatus'] = trim($xml->header);
	  $ret['departure']	 = $xml->departure;
	  $ret['status']	 = 'inrout';

	  if ((int)$result[0]->status_code >= 3) {
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
