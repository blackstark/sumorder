<?php

class sc_tkp_provider_another extends sc_tkp_provider {

    var $name		 = 'another';
    var $title		 = 'another';
    var $siteTrackingUrl	 = '';
    var $tracknumber	 = null;
    var $api_url		 = '';

    function __construct() {

    }

    function getFields() {
	  return array('name', 'date');
    }


}
