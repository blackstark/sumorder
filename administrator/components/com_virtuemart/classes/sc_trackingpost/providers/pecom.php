<?php

class sc_tkp_provider_pecom extends sc_tkp_provider {

    var $name		 = 'pecom';
    var $title		 = 'ПЭК';
    var $siteTrackingUrl	 = 'https://kabinet.pecom.ru/status/';
    var $tracknumber	 = null;
    var $api_url		 = '';
    var $api_user		 = 'airsoft';
    var $api_key		 = '66BDC61ECF9010723C9AE2F29562CBDE8CD9E8C4';

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
	  $param['user'] = $this->api_user;
	  $param['key'] = $this->api_key;
	  return $param;
    }

    function pharseRequest($result) {
	  
	  $ret			 = array();
	  $ret['textstatus'] = $result->cargos[0]->info->cargoStatus;
	  $ret['status']	 = 'inrout';
	  if ($ret['textstatus'] == 'Выдан') {
		$ret['status'] = 'delivered';
	  }
	  return $ret;
    }

    function sendCurl($params) {
	
	 ini_set('mbstring.internal_encoding', 'UTF-8');
	 
	 $this->tracknumber = mb_strtoupper($this->tracknumber);
	
	  require_once( CLASSPATH . 'sc_trackingpost/lib/pecom_kabinet.php' );
	  $sdk = new PecomKabinet($params['user'], $params['key']);
	  $result = $sdk->call('cargos', 'status', array('cargoCodes' => array(
		    $this->tracknumber
		)
	  ));
	  $sdk->close();
	  return $result;
    }

}
