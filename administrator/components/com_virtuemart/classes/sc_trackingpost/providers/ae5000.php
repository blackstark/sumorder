<?php

class sc_tkp_provider_ae5000 extends sc_tkp_provider {

    var $name = 'ae5000';
    var $title = 'Автотрейдинг';
    var $siteTrackingUrl = 'http://www.ae5000.ru/senders/state_delivery/';
    var $tracknumber = null;

    function __construct() {

    }

    /**
     * URL на страницу просмотра трекинга посылки
     */
    function getSiteUrlTracking() {
	  return $this->siteTrackingUrl;
    }
}
