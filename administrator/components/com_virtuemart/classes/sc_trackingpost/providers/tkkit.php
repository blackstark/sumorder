<?php

class sc_tkp_provider_tkkit extends sc_tkp_provider {

    var $name		 = 'tkkit';
    var $title		 = 'КИТ';
    var $siteTrackingUrl	 = 'http://tk-kit.ru/';
    var $tracknumber	 = null;
    var $api_url		 = 'http://tk-kit.ru/API.1/';

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
	  $param['N']	 = $this->tracknumber;
	  $param['f']	 = 'checkstat';
	  return $param;
    }

    function pharseRequest($result) {
	  $ret			 = array();
	  $result = json_decode($result);
	  $ret['textstatus'] = $result[0]->status_text;
	  $ret['status']	 = 'inrout';

	  if ((int)$result[0]->status_code >= 3) {
		$ret['status'] = 'delivered';
	  }
	  return $ret;
    }

}
