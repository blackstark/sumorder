<?php

class sc_tkp_provider_nrgtk extends sc_tkp_provider {

    var $name		 = 'nrgtk';
    var $title		 = 'Энергия';
    var $siteTrackingUrl	 = 'http://nrg-tk.ru/tracking.html';
    var $tracknumber	 = null;
    var $city		 = null;
    var $city_id		 = null;
    var $api_url		 = 'http://api.nrg-tk.ru/api/rest/';

    function __construct() {

    }

    /**
     * URL на страницу просмотра трекинга посылки
     */
    function getSiteUrlTracking() {
	  return $this->siteTrackingUrl;
    }

    function preCurlSend() {
	  $param		 = array();
	  $param['numdoc']	 = $this->tracknumber;
	  $param['idcity']	 = $this->city_id;
	  $param['method']	 = 'nrg.get.locations';
	  $result		 = $this->sendCurl($param);
	  $result		 = json_decode($result);
	  
	  ini_set('mbstring.internal_encoding', 'UTF-8');
	  
	  if ($result->rsp->stat == 'ok') {
		foreach ($result->rsp->locations as $location) {
		    //echo mb_strtolower($location->name) ."==". mb_strtolower($this->city) .'<br>';
			if (mb_strtolower($location->name) == mb_strtolower($this->city)) {
			  $this->city_id = $location->id;
		    }
		}
	  }

	  //var_dump($this->city_id);
	  return $this->city_id;
    }

//    function setData($data) {
//	  $this->tracknumber = $data->tracknumber;
//	  $this->city		 = $data->city;
//	  $this->date		 = $data->date;
//    }

    function getCurlParam() {
	  $param		 = array();
	  $param['numdoc']	 = $this->tracknumber;
	  $param['idcity']	 = $this->city_id;
	  $param['method']	 = 'nrg.get_sending_state';
	  return $param;
    }

    function pharseRequest($result) {
	
	  $ret		 = array();
	  $result	 = json_decode($result);
	  
	  if ($result->rsp->stat == 'ok') {
		$ret['textstatus'] = $result->rsp->info->cur_state;
		$ret['status']	 = 'inrout';
		if ($ret['textstatus'] == 'Выдана') {
		    $ret['status'] = 'delivered';
		}
	  }
	  return $ret;
    }

    function getFields() {
	  return array('city');
    }

}
