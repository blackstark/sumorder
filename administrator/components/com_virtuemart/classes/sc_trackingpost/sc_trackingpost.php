<?php

ini_set('display_errors', 1);
error_reporting(E_ERROR);
set_time_limit(0);
/*
  /administrator/components/com_virtuemart/classes/sc_trackingpost/sc_tkp_cron.php
 */

/**
 * Отслеживание посылок
 *
 */
require_once( CLASSPATH . 'sc_trackingpost/provider.php' );

class sc_trackingpost {

    var $providerlist; //Inf Список доступных компаний
    var $provider		 = null; //Inf class выбраной компании
    var $providerName	 = '';
    var $db			 = null; //Inf class базы данных
    var $_table		 = 'jos_sc_trackingpost'; //Inf таблица в DB

    function __construct() {
	  $this->db = & JFactory::getDBO();
	  $this->checkInstallDb();
    }

    function getProvider($provider = NULL) {
	  if (!empty($provider)) {
		$this->setProvider($provider);
	  }
	  if (empty($this->provider)) {
		$this->setProvider($provider);
	  }
	  return $this->provider;
    }

    function setProvider($provider) {
	  require_once( CLASSPATH . 'sc_trackingpost/providers/' . $provider . '.php' );
	  $provider_class = 'sc_tkp_provider_' . $provider;
	  if (class_exists($provider_class)) {
		$this->providerName	 = $provider;
		return $this->provider		 = new $provider_class();
	  }
	  return $this->provider = NULL;
    }

    /**
     * Получение списка доступных компаний
     *
     * @return type
     */
    function getProviderlist() {
	  if (empty($this->providerlist)) {
		$this->setProviderlist();
	  }
	  return $this->providerlist;
    }

    /**
     * Установка списка доступных коспаний
     *
     * @param type $providerlist
     */
    function setProviderlist($providerlist = null) {
	  $providerlist = array();

	  $providerlist['gdeposylka']	 = array('gdeposylka', 'Почта России');
	  $providerlist['dellin']		 = array('dellin', 'Деловые линии');
	  $providerlist['pecom']		 = array('pecom', 'ПЭК');
	  $providerlist['ae5000']		 = array('ae5000', 'Автотрейдинг');
	  $providerlist['nrgtk']		 = array('nrgtk', 'Энергия ');
	  $providerlist['tkkit']		 = array('tkkit', 'КИТ');
	  $providerlist['edostavka']	 = array('edostavka', 'СДЭК');
	  $providerlist['another']	 = array('another', 'Другая');

	  $this->providerlist = $providerlist;
    }

    /**
     * Генерация списка
     *
     * @param type $list
     * @return type
     */
    function genOption($list) {
	  $options	 = array();
	  $options[]	 = JHTML::_('select.option', 0, 'Выберите');
	  foreach ($list as $opt) {
		$options[] = JHTML::_('select.option', $opt[0], $opt[1]);
	  }
	  return $options;
    }

    /**
     * Форма добавления трекинг номера
     *
     * @param type $order_id
     */
    function getAddForm($order_id) {
	  $tracing = $this->getTracking($order_id);

	  require( CLASSPATH . 'sc_trackingpost/tmpl/addform.php' );
    }

    function addCssJs($param = NULL) {
	  $document = JFactory::getDocument();
	  if (!$param) {
		$document->addScript('/administrator/components/com_virtuemart/classes/sc_trackingpost/assets/js/jquery-2.1.1.min.js');
		$document->addScript('/administrator/components/com_virtuemart/classes/sc_trackingpost/assets/js/jquery.form.min.js');
	  }
	  $document->addScript('/administrator/components/com_virtuemart/classes/sc_trackingpost/assets/js/js.js');

//	  $document->addStyleSheet($css);
    }

    function showTracking($order_id) {
	  $tracing = $this->getTracking($order_id);

	  if ($tracing->provider) {
		$provider = $this->getProvider($tracing->provider);
		$provider->setData($tracing);

		if ($tracing->tracknumber) {
		    require( CLASSPATH . 'sc_trackingpost/tmpl/showtracking.php' );
		}
	  }
    }

    /**
     * Добавление таблицы в DB
     *
     * @return boolean
     */
    function checkInstallDb() {
	  $query = "SHOW TABLES LIKE '" . $this->_table . "'";
	  $this->db->setQuery($query);
	  $this->db->Query($query);
	  $table = $this->db->loadAssoc();
	  if ($this->db->getErrorNum()) {
		echo $this->db->stderr();
		return false;
	  }
	  $c_table = count($table);
	  if (!$c_table) {
		//Inf Добавление таблицы в базу
		$table = 'CREATE TABLE IF NOT EXISTS `' . $this->_table . '` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`order_id` int(11) NOT NULL,
		`provider` varchar(25) CHARACTER SET utf8 NOT NULL,
		`tracknumber` varchar(255) CHARACTER SET utf8 NOT NULL,
		`date` varchar(255) CHARACTER SET utf8 NOT NULL,
		`city` varchar(255) CHARACTER SET utf8 NOT NULL,
		`name` varchar(255) CHARACTER SET utf8 NOT NULL,
		PRIMARY KEY (`id`)
	    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';
		$this->db->setQuery($table);
		$this->db->Query($table);
		if ($this->db->getErrorNum()) {
		    echo $this->db->stderr();
		    return false;
		}
	  }
	  return true;
    }

    /**
     * Получение данных по номеру заказа
     *
     * @param type $order_id
     * @return boolean
     */
    function getTracking($order_id) {
	  $query	 = "SHOW TABLES LIKE '" . $this->_table . "'";
	  $query	 = "SELECT * FROM " . $this->_table . ""
	  . " WHERE order_id=" . $order_id;
	  $this->db->setQuery($query);
	  $this->db->Query($query);
	  $tracking	 = $this->db->loadObject();
	  if ($this->db->getErrorNum()) {
		echo $this->db->stderr();
		return false;
	  }

	  return $tracking;
    }

    /**
     * Добавление трекинг номера к заказу
     *
     * @param type $order_id
     * @param type $privider
     * @return type
     */
    function add($order_id, $provider, $tracknumber, $date = '', $city = '', $name = '') {
      require_once(CLASSPATH . 'ps_orderlog.php');
      $orderlog = new ps_orderlog();
	  $tracking = $this->getTracking($order_id);

      $dblog = new ps_DB;
      $query = "SELECT provider FROM " . $this->_table;
      $query.= ' WHERE order_id = ' . $order_id;
      $dblog->query($query);

      $prev_provider = $dblog->loadResult($query);
	  if (!$tracking) {
		$query = "INSERT INTO " . $this->_table;
		$WHERE = '';
	  }else{
		$query = "UPDATE " . $this->_table;
		$WHERE = ' WHERE order_id = ' . $order_id;
	  }

	  $query .= " SET order_id='" . $order_id . "', provider='" . $provider . "', tracknumber='" . $tracknumber . "', date='" . $date . "', city='" . $city . "', name='" . $name . "'";
	  $query .= $WHERE;
	  $ret	 = $this->db->setQuery($query);
	  $ret	 = $this->db->Query($query);
	  if ($this->db->getErrorNum()) {
		echo $this->db->stderr();
		return false;
	  }
      if($provider!=$prev_provider){
          $orderlog->saveLog($order_id, 'Изменение трекинга', '', $prev_provider, $provider);
      }
	  return $ret;
    }

    /**
     * Отправка письма грузополучателю
     *
     * @param type $order_id
     * @return boolean
     */
    function sendEmail($order_id, $status = null) {

		return;
	  global $sess, $VM_LANG, $vmLogger;

	  $url = SECUREURL . "index.php?option=com_virtuemart&page=account.order_details&order_id=" . $order_id . '&order_key=' . md5('AIR' . $order_id . 'SOFT' . $order_id . 'RETAIL') . '&Itemid=' . $sess->getShopItemid();

	  $db	 = new ps_DB;
	  $dbv	 = new ps_DB;
	  $q	 = "SELECT vendor_name,contact_email FROM #__{vm}_vendor ";
	  $q .= "WHERE vendor_id='" . $_SESSION['ps_vendor_id'] . "'";
	  $dbv->query($q);
	  $dbv->next_record();

	  $q = "SELECT first_name,last_name,user_email,order_status_name FROM #__{vm}_order_user_info,#__{vm}_orders,#__{vm}_order_status ";
	  $q .= "WHERE #__{vm}_orders.order_id = '" . $db->getEscaped($order_id) . "' ";
	  $q .= "AND #__{vm}_orders.user_id = #__{vm}_order_user_info.user_id ";
	  $q .= "AND #__{vm}_orders.order_id = #__{vm}_order_user_info.order_id ";
	  $q .= "AND order_status = order_status_code ";
	  $db->query($q);
	  $db->next_record();

	  $providerlist	 = $this->getProviderlist();
	  $tracking		 = $this->getTracking($order_id);
	  if ($tracking->provider) {
		$provider = $this->getProvider($tracking->provider);
		$provider->setData($tracking);
	  }
	  $siteTrackingUrl = $provider->getSiteUrlTracking();

	  $provider	 = $tracking->provider;
	  $tracknumber = $tracking->tracknumber;
	  $date		 = $tracking->date;

	  if (!$tracknumber) {
		return false;
	  }

	  $statusText = '';
	  $statusText = 'Следующие заказы были доставлены:';


	  ob_start();
	  require( CLASSPATH . 'sc_trackingpost/tmpl/email/user_email_tracking.php' );
	  $message = ob_get_contents();
	  ob_end_clean();


	  $mail_Body = $message;
//	  $mail_Body = html_entity_decode($message);


	  $result = vmMail($dbv->f("contact_email"), $dbv->f("vendor_name"), $db->f("user_email"), $status, $mail_Body, '', true);

	  return $result;
    }

    function sendEmailAdmin($order_ids, $status = null) {
	  global $sess, $VM_LANG, $vmLogger;

	  $urls = array();
	  foreach ($order_ids as $order_id) {
		$urls[$order_id]['site']	 = SECUREURL . "index.php?option=com_virtuemart&page=account.order_details&order_id=" . $order_id . '&order_key=' . md5('AIR' . $order_id . 'SOFT' . $order_id . 'STORE') . '&Itemid=' . $sess->getShopItemid();
		$urls[$order_id]['admin']	 = SECUREURL . '/administrator/index.php?page=order.order_print&limitstart=0&order_id=' . $order_id . '&option=com_virtuemart';
	  }

	  $db	 = new ps_DB;
	  $dbv	 = new ps_DB;
	  $q	 = "SELECT vendor_name,contact_email FROM #__{vm}_vendor ";
	  $q .= "WHERE vendor_id='" . $_SESSION['ps_vendor_id'] . "'";
	  $dbv->query($q);
	  $dbv->next_record();
//	  $q = "SELECT first_name,last_name,user_email,order_status_name FROM #__{vm}_order_user_info,#__{vm}_orders,#__{vm}_order_status ";
//	  $q .= "WHERE #__{vm}_orders.order_id = '" . $db->getEscaped($order_id) . "' ";
//	  $q .= "AND #__{vm}_orders.user_id = #__{vm}_order_user_info.user_id ";
//	  $q .= "AND #__{vm}_orders.order_id = #__{vm}_order_user_info.order_id ";
//	  $q .= "AND order_status = order_status_code ";
//	  $db->query($q);
//	  $db->next_record();
	  /*
	    $providerlist	 = $this->getProviderlist();
	    $tracking		 = $this->getTracking($order_id);
	    if ($tracking->provider) {
	    $provider = $this->getProvider($tracking->provider);
	    $provider->setData($tracking);
	    }
	    $siteTrackingUrl = $provider->getSiteUrlTracking();

	    $provider	 = $tracking->provider;
	    $tracknumber = $tracking->tracknumber;
	    $date		 = $tracking->date;

	    if (!$tracknumber) {
	    return false;
	    }
	   *
	   */

	  $statusText = '';

	 $statusText = 'Следующие заказы были доставлены: ';


	  ob_start();
	  require( CLASSPATH . 'sc_trackingpost/tmpl/email/admin_email_tracking.php' );
	  $message = ob_get_contents();
	  ob_end_clean();


	  $mail_Body = $message;
//	  $mail_Body = html_entity_decode($message);

	  //$mail_Subject = 'Данные для отслеживания посылки по к заказу№' . $order_id;

	  //Inf Временная заглушка
//	  $admin_email = $dbv->f("contact_email");
	  $admin_email = 'info@airsoftstore.ru';
	  $result	 = vmMail($admin_email, 'admin', $admin_email, $status, $mail_Body, '', true);

	  return $result;
    }

    /**
     * Отправка письма админу
     *
     * @param type $order_id
     * @return boolean
     */
    function checkStatus($order_id) {
	  $providerlist	 = $this->getProviderlist();
	  $tracking		 = $this->getTracking($order_id);

	  if ($tracking->provider) {
		$provider = $this->getProvider($tracking->provider);
		$provider->setData($tracking);
	  }

	  $siteTrackingUrl = $provider->getSiteUrlTracking();

	  $provider_name	 = $tracking->provider;
	  $tracknumber	 = $tracking->tracknumber;
	  $date			 = $tracking->date;
//	  return $order_id; //Inf Временная заглушка
//	  $StatusTracking['textstatus']
//	  $StatusTracking['status'] = 'delivered' - доставлен
	  $StatusTracking	 = $provider->getStatusTracking(); //Inf Временная заглушка

//	  print_r('<pre>');
//	  print_r('$provider - ');
//	  print_r($provider);
//	  print_r('</pre>');
//	  print_r('<pre>');
//	  print_r('$provider - ');
//	  print_r($StatusTracking);
//	  print_r('</pre>');
//	  exit;

	  if ($StatusTracking['status'] == 'delivered') {
		//Inf отправлять письма админу и пользователю
		//Inf Временная заглушка
//		$this->sendEmail($order_id, $StatusTracking['textstatus']);//Inf Уведомление пользователя
		//Inf Временная заглушка
//		$this->sendEmailAdmin($order_id, $StatusTracking['textstatus']);//Inf Уведомление админа
		echo " Доставлен";
		$this->updateOrderStatus($order_id); //Inf Обновление статуса заказа
		return $order_id;
	  }



	  //ToDo: Получить статус посылки
	  //ToDo: если доставлен отправить письмо админу

	  return false;
    }

    function updateOrderStatus($order_id, $status = 'D') {
	  require_once( CLASSPATH . 'ps_order.php');
	  global $ps_order;
	  $data				 = array();
	  $data['order_id']		 = $order_id;
	  $data['order_status']	 = $status;
	  $ret				 = $ps_order->order_status_update($data);
    }

}
