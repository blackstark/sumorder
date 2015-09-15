<?php

class sc_tkp_provider_gdeposylka extends sc_tkp_provider {

    var $name = 'gdeposylka';
    var $title = 'ГдеПосылка';
    var $siteTrackingUrl = 'http://gdeposylka.ru/';
    var $tracknumber = null;
    var $api_url_add		 = 'http://gdeposylka.ru/ws/x1/tracks.add/xml';
    var $api_url_list		 = 'http://postabot.ru/tr/tracker2.php';
    var $api_url		 = 'http://track24.ru/api/tracking.json.php';
    var $api_fn		 = 'add';
    var $api_key		 = '752590.c1ac19e87f';

    function __construct() {

    }

    /**
     * URL на страницу просмотра трекинга посылки
     */
    function getSiteUrlTracking() {
	  return $this->siteTrackingUrl . $this->tracknumber;
    }

    function getCurlParam() {
	  /*$param		 = array();
	  $param['id']	 = $this->tracknumber;
	  $param['apikey']	 = $this->api_key;

	  if ($this->api_fn == 'add') {
		$this->api_url = $this->api_url_add;
	  }
	  if ($this->api_fn == 'list') {
		$this->api_url = $this->api_url_list;
	  }*/
	  
	  $param['apiKey'] = '2aaa334e47055b5f8b490fcbe3bc195e';
	  $param['domain'] = 'airsoftstore.ru';
	  $param['code'] = $this->tracknumber;

	  return $param;
    }

    function pharseRequest($xmlString) {
	
		$ret = array();
		$delivered = false;
		
		$data = json_decode($xmlString, true);
		
		if( empty($data) )
			return false;
		
		if( $data['data']['lastPoint']['operationType'] == 'Вручение' )
			$ret['status'] = 'delivered';

		// трекинг работает с частотой 10 запросов в секунду, по-этому делаем слип
		usleep(200000);
		
		/*if( empty($ret) )
		{	
			// проверяем ещё разок у других
			$request = @array("apikey"=> '1904d77e350324bb0974ccd89a17f89b', "method"=>"parcel", "rpo"=> $this->tracknumber);
			$password = 'asd32342sDs';
                       
			//если пароль указан, аутентификация по методу API ключ + API пароль.
			$all_to_md5 = $request;
			$all_to_md5[] = $password;
			$hash = md5(implode("|", $all_to_md5));
			$request["hash"] = $hash;
			
			// запрашиваем
			
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, "http://russianpostcalc.ru/api_v1.php");
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$data = curl_exec($curl);
			
			curl_close($curl);
			if($data === false)
			{
				die( "10000 server error" );
			}
			
			$js = json_decode($data, $assoc=true);
			
			echo '<hr>';
			echo '<pre>';
			print_r($js);
			exit();
		}*/
			
			
		return $ret;
	  /*
	 var_dump($delivered);

	  exit();
	  
	  $ret['status'] = 'inrout';

	  if (preg_match('/Вручение адресату/', $ret['textstatus'])) {
		$ret['status'] = 'delivered';
	  }
	  return $ret;*/
    }
}
