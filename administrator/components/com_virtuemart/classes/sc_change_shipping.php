<?php

class sc_change_shipping {

    function __construct() {

    }

    function getShippingList($order_id, $d) {
	  global $PSHOP_SHIPPING_MODULES, $vmLogger, $auth, $weight_total, $VM_LANG;

	  ob_start();
	  ?>
	  <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
	      <table class="table">
		    <?php
		    $_REQUEST['ship_to_info_id']	 = 0;
		    $vars['ship_to_info_id']	 = 26;
		    $vars['shipping_rate_id']	 = 0;
		    $vars['zone_qty']			 = 1;
		    $vars['state']			 = $d['state'];
		    $vars['weight']			 = $d['weight'];
		    $_GET['c1'] = $vars['country']			 = $d['country'];
		    $vars['zip']			 = $d['zip'];

		    foreach ($PSHOP_SHIPPING_MODULES as $shipping_module) {
			  if (file_exists(CLASSPATH . "shipping/" . $shipping_module . ".php")) {
				include_once( CLASSPATH . "shipping/" . $shipping_module . ".php" );
			  }
			  if (class_exists($shipping_module)) {
				$SHIPPING = new $shipping_module();
				$SHIPPING->list_rates($vars);
			  }
		    }
		    ?>

	      </table>
	      <input type="image" title="<?php echo $VM_LANG->_('PHPSHOP_UPDATE') ?>"
	  	     src="<?php echo VM_THEMEURL ?>images/edit_f2.gif" border="0"  alt="<?php echo $VM_LANG->_('PHPSHOP_UPDATE') ?>" />
	      <input type="hidden" name="page" value="order.sc_change_shipping" />
	      <input type="hidden" name="option" value="com_virtuemart" />
	      <input type="hidden" name="fn" value="shangeshipping" />
	      <input type="hidden" name="order_id" value="<?php echo $order_id ?>" />
	  </form>
	  <?php
	  $script = ob_get_contents();
	  ob_end_clean();
	  return $script;
	  //Inf Список всех плагинов доставки
//	  require_once( CLASSPATH . 'ps_shipping_method.php');
//	  $ps_shipping_method	 = new ps_shipping_method;
//	  $carrier_list		 = $ps_shipping_method->method_list();
//			print_r('<pre>');
//			print_r('$carrier_list - ');
//			print_r($carrier_list);
//			print_r('</pre>');
//			exit;
	  //Inf Стандартные способы доставки
//			require_once( CLASSPATH . 'ps_shipping.php');
	  // Instantiate Class
//			$ps_shipping = new ps_shipping;
//			$carrier_list = $ps_shipping->carrier_list('shipping', null);
//			return $carrier_list;
    }

    function getShangeBtn($order_id, $state, $weight, $country, $zip) {
	  ?>
	  <form method="post" action="" id="getshippingmetodsforrm">
	      <button class="btn getshippingmetods">Изменить способ доставки</button>
	      <input type="hidden" name="page" value="order.sc_change_shipping" />
	      <input type="hidden" name="fn" value="getvariants" />
	      <input type="hidden" name="option" value="com_virtuemart" />
	      <input type="hidden" value="<?= $order_id ?>" name="order_id">
	      <input type="hidden" value="<?= $state ?>" name="state">
	      <input type="hidden" value="<?= $weight ?>" name="weight">
	      <input type="hidden" value="<?= $country ?>" name="country">
	      <input type="hidden" value="<?= $zip ?>" name="zip">
	  </form>
	  <div id="shippingmetods"></div>
	  <script type="text/javascript">
	      //	      jQuery(function ($) {

	      jQuery(".getshippingmetods").click(function ($) {
	  	  var data = jQuery('#getshippingmetodsforrm').formSerialize();
	  	  jQuery.ajax({
	  		type: "POST",
	  		dataType: 'json',
	  		url: "/administrator/index.php?no_menu=1&format=json",
	  		data: data,
	  		success: function (retdata) {
	  		    if (retdata['success']) {

	  		    }
	  		    var mess = retdata['mess'];
	  		    var html = retdata['html'];
                console.log(html);
	  		    jQuery('#shippingmetods').html(html);
	  		}
	  	  });


	  	  return false;
	      });
	      //	      });
	  </script>
	  <?php
    }

    function getRecalcBtn($order_id) {
	  ?>
	  <form method="post" action="" id="recalcorder">
	      <button class="btn recalcorder">Пересчитать заказ</button>
	      <input type="hidden" name="page" value="order.sc_change_shipping" />
	      <input type="hidden" name="fn" value="recalcorder" />
	      <input type="hidden" name="option" value="com_virtuemart" />
	      <input type="hidden" value="<?= $order_id ?>" name="order_id">
	  </form>
	  <?php
    }

    function changeShipping($order_id, $r) {
	  global $VM_LANG, $vmLogger;
      require_once(CLASSPATH . 'ps_orderlog.php');
      $orderlog = new ps_orderlog();

	  $d			 = array();
	  $ship_method_id	 = $r['shipping_rate_id'];

	  $ship_method_id = @urldecode($ship_method_id);

	  $d["ship_method_id"]	 = $ship_method_id;
	  $shipping_rate_id_ar	 = explode('|', $ship_method_id);

	  //Inf Получение названия ТК
	  $transportnaya_company = htmlspecialchars($r['transportnaya_company'], ENT_QUOTES);
	  if (!empty($transportnaya_company)) {
		if ($shipping_rate_id_ar[1] == 'Доставка транспортной компанией') {
		    $shipping_rate_id_ar[2] .= $transportnaya_company;
		}
		$d["ship_method_id"] = implode('|', $shipping_rate_id_ar);
	  }

	  $d['order_shipping'] = $shipping_rate_id_ar[3];

	  $db		 = new ps_DB;
      $q = "SELECT ship_method_id FROM #__{vm}_orders WHERE order_id = '" . $order_id . "'";
      $db->query($q);
      $prev_ship_method_id	 = $db->loadResult();

	  $db->buildQuery('UPDATE', '#__{vm}_orders', $d, ' WHERE order_id=' . $order_id);
	  $result	 = $db->query();


	  $ps_order_change = new ps_order_change($order_id);
	  $ps_order_change->recalc_order($order_id);
      if($prev_ship_method_id!=$ship_method_id){
          $orderlog->saveLog($order_id, 'Изменение варианта доставки', '', $prev_ship_method_id, $ship_method_id);
      }
    }

    function recalcorder($order_id, $r) {
	  $db		 = new ps_DB;
	  	$query = "SELECT * FROM #__{vm}_orders";
	$db->Query($query);
	$list = $db->loadObjectList();
	if ($db -> getErrorNum()) {
		echo $db -> stderr();
		return false;
	}
	  $ps_order = new ps_order($order_id);
	  $ps_order->


	  $ps_order_change = new ps_order_change($order_id);
	  $ps_order_change->recalc_order($order_id);
    }

}
