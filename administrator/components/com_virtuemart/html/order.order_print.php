<?php
if (!defined('_VALID_MOS') && !defined('_JEXEC'))
    die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');
/**
 *
 * @version $Id: order.order_print.php 2659 2010-11-21 11:25:33Z zanardi $
 * @package VirtueMart
 * @subpackage html
 * @copyright Copyright (C) 2004-2009 soeren - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */
mm_showMyFileName(__FILE__);
global $ps_order_status;

require_once(CLASSPATH . 'ps_product.php');
require_once(CLASSPATH . 'ps_order_status.php');
require_once(CLASSPATH . 'ps_checkout.php');
require_once(CLASSPATH . 'ps_order_change.php');
require_once(CLASSPATH . 'ps_order_change_html.php');

$ps_product			 = new ps_product;
$order_id			 = vmRequest::getInt('order_id');
$ps_order_change_html	 = new ps_order_change_html($order_id);

//Added Option to resend the Confirmation Mail
$resend_action = vmRequest::getVar('func');
if ($resend_action == 'resendconfirm' && $order_id) {
    ps_checkout::email_receipt($order_id);
    $redirurl = $_SERVER['PHP_SELF'];
    foreach ($_POST as $key => $value) {
	  if ($value != 'resendconfirm')
		$redirurl.=!strpos($redirurl, '?') ? '?' : '&' . $key . '=' . vmRequest::getVar($key);
    }
    vmRedirect($redirurl, $VM_LANG->_('PHPSHOP_ORDER_RESEND_CONFIRMATION_MAIL_SUCCESS'));
}

if (!is_numeric($order_id))
    echo "<h2>The Order ID $order_id is not valid.</h2>";
else{
    $dbc	 = new ps_DB;
    $q	 = "SELECT * FROM #__{vm}_orders WHERE order_id='$order_id'";
    $db->query($q);
    if ($db->next_record()) {

	  // Print View Icon
	  $print_url = $_SERVER['PHP_SELF'] . "?page=order.order_printdetails&amp;order_id=$order_id&amp;no_menu=1&pop=1";
	  if (vmIsJoomla('1.5', '>=')) {
		$print_url .= "&amp;tmpl=component";
	  }

	  $print_url	 = $sess->url($print_url);
	  $print_url	 = defined('_VM_IS_BACKEND') ? str_replace("index2.php", "index3.php", $print_url) : str_replace("index.php", "index2.php", $print_url);


	  $sendmail_url	 = $_SERVER['PHP_SELF'] . "?page=order.order_sendmail&amp;order_id=$order_id&amp;no_menu=1&pop=1&amp;tmpl=component";
	  $sendmail_url	 = $sess->url($sendmail_url);
	  $sendmail_url	 = defined('_VM_IS_BACKEND') ? str_replace("index2.php", "index3.php", $sendmail_url) : str_replace("index.php", "index2.php", $sendmail_url);
	  ?>
	  <?php
	  //Inf Информация о магазине
	  require_once( CLASSPATH . 'ps_multishop.php' );
	  $ps_multishop	 = new ps_multishop($order_id);
	  ?>
	  <style>
	      .info_shop_name {
	  	  background: #eeecc6 none repeat scroll 0 0;
	  	  border: 1px solid #ccc;
	  	  font-size: 25px;
	  	  font-weight: bold;
	  	  padding: 5px;
	  	  position: fixed;
	  	  right: 0;
	  	  text-align: center;
	  	  top: 0;
	  	  width: 250px;
	  	  z-index: 9999;
	      }
	  </style>
	  <div class="info_shop_name">
		<?= $ps_multishop->getShop_name() ?>
	  </div>
	  <?php
	  ?>
	  <script>
	      jQuery(window).ready(function (e) {

	  	  jQuery('select[name=order_status]').change(function (e) {
	  		if (jQuery(this).val() == 'R') {
	  		    jQuery('#notify_customer').click();
	  		    jQuery('#include_comment').click();
	  		}
	  	  });
	      });
	  </script>
	  <div style="float: right;">
	      <span class="pagenav" style="font-weight: bold;">
	  	  <a href="javascript:void window.open('<?php echo $print_url ?>', 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');">
	  		<img src="<?php echo $mosConfig_live_site ?>/images/M_images/printButton.png" align="ABSMIDDLE" height="16" width="16" border="0" />
	  <?php echo $VM_LANG->_('PHPSHOP_CHECK_OUT_THANK_YOU_PRINT_VIEW') ?>
	  	  </a>
	      </span>
	  </div>

	  <?php
	  //Inf Подключение классов учёта остатков
	  require_once( CLASSPATH . 'shop_stock/shop_stock.php' );
	  $ps_stock			 = new ps_stock();
	  $shop_stock_history	 = new Shop_Stock_History();

	  $OrderStatus = $ps_stock->checkOrderStatus($order_id);

	  $class = 'toggletoorderstatus';
	  if (!$OrderStatus) {
		$class .= ' dnone';
	  }
	  
	  
	  ?>

	  <div style="float: right;">
	  <?php if (strpos($db->f("ship_method_id"), "postcalc") === 0 ) : ?>
	      <span class="pagenav" style="font-weight: bold;">
	  	  <a href="javascript:void window.open('/administrator/index.php?page=order.order_printdetails_blank&order_id=<?= $order_id; ?>&no_menu=1&pop=1&tmpl=component&option=com_virtuemart', 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=1000,height=800,directories=no,location=no');">
	  		<img src="<?php echo $mosConfig_live_site ?>/images/M_images/emailButton.png" align="ABSMIDDLE" height="16" width="16" border="0" />
	  		Напечатать бланк
	  	  </a>&nbsp;
	      </span>
	  <?php endif; ?>
	  </div>
	  <div style="float: right;">
	      <span class="pagenav" style="font-weight: bold;">
	  <?php
//	  <!-- Изменение способа доставки start -->
	  require_once( CLASSPATH . 'sc_ordertopay/sc_ordertopay.php' );
	  $sc_ordertopay = new sc_ordertopay();
	  $sc_ordertopay->getLink($order_id, 1);
//	  <!-- Изменение способа доставки end -->
	  ?>
	      </span>
	  </div>

	  <div style="float: right;">
	      <span class="pagenav <?= $class ?>" style="font-weight: bold;">
	  	  <a href="javascript:void window.open('/administrator/index.php?page=order.order_printdetails2&order_id=<?= $order_id; ?>&no_menu=1&pop=1&tmpl=component&option=com_virtuemart', 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=1000,height=800,directories=no,location=no');">
	  		<img src="<?php echo $mosConfig_live_site ?>/images/M_images/emailButton.png" align="ABSMIDDLE" height="16" width="16" border="0" />
	  		Напечатать чек
	  	  </a>&nbsp;
	      </span>
	  </div>

	  <?php
	  // Navigation
	  echo ps_order::order_print_navigation($order_id);

	  $q			 = "SELECT h.*, u.username FROM #__{vm}_order_history AS h LEFT JOIN jos_users AS u ON user_id = u.id WHERE order_id='$order_id' ORDER BY order_status_history_id ASC";
	  $dbc->query($q);
	  $order_events	 = $dbc->record;


	  $order_status	 = $db->f("order_status");

//	  global $sess;
//	  $shopper_order_link = $sess->url( "/index.php?page=account.order_details&order_id=$order_id&order_key=". md5('AIR'. $order_id .'SOFT'. $order_id .'STORE'), true, true );
	  $shopper_order_link = "/index.php?page=account.order_details&option=com_virtuemart&Itemid=2&order_id=$order_id&order_key=". md5('AIR'. $order_id .'SOFT'. $order_id .'STORE');

	  ?>
	  <br />
	  <table class="adminlist" style="table-layout: fixed;">
	      <tr>
	  	  <td valign="top">
	  		<table border="0" cellspacing="0" cellpadding="1">
	  		    <tr class="sectiontableheader">
					<th colspan="2"><?php echo $VM_LANG->_('PHPSHOP_ORDER_PRINT_PO_LBL') ?> (<a target="_blank" href="<?=$shopper_order_link?>">Посмотреть на сайте</a>)</th>
	  		    </tr>
	  <?php
	  $dbop			 = new ps_DB;
	  $dbop->query('SELECT payment_method_id FROM jos_vm_order_payment WHERE order_id = "' . $order_id . '"');
	  $dbop->next_record();
	  if ($dbop->f('payment_method_id') == 22) {
		?>
				    <tr>
					  <td colspan="2"><strong><span style="color: red; font-size: 20px;">Наложенный платеж</span></strong></td>
				    </tr>

		<?php
		if ($auth['user_id'] == 62) {
		    ?>
		    		    <tr>
		    			  <td colspan="2">
		    				<form name="formNalPay" id="formNalPay">
		    				    <input type="hidden" name="page" value="order.sc_change_shipping">
		    				    <input type="hidden" name="fn" value="update_nal_pay">
		    				    <input type="hidden" name="option" value="com_virtuemart">
		    				    <input type="hidden" value="<?= $order_id; ?>" name="order_id">
		    				    <input type="checkbox" name="nalpay" value="1" <? if ($db->f('nalpay')) echo 'checked="checked"'; ?> > - оплата получена
		    					     <input type="button" name="btn_nalpay" value="Сохранить" onclick="saveNalPay()">
		    				</form>
		    			  </td>
		    		    </tr>
		    		    <?
		    		    }
		    		    }

		    		    if($db->f("confirm_warn") == 1 && (strpos($db->f("ship_method_id"), "emsrussianpost") === 0 || strpos($db->f("ship_method_id"), "postcalc") === 0)) : ?>
		    		    <tr>
		    			  <td colspan="2"><strong><span style="color: green; font-size: 20px;">С возможными рисками ознакомлен. Прошу отправить заказ</span></strong></td>
		    		    </tr>
		    <?php
		    endif;

		    if ($db->f("confirm_warn") == 2 && (strpos($db->f("ship_method_id"), "emsrussianpost") === 0 || strpos($db->f("ship_method_id"), "postcalc") === 0)) :
			  ?>
			  		    <tr>
			  			  <td colspan="2"><strong><span style="color: red; font-size: 20px;">На отправку не согласен</span></strong></td>
			  		    </tr>
			  <?php
		    endif;
		    ?>
		    		    <tr>
		    			  <td><strong><?php echo $VM_LANG->_('PHPSHOP_ORDER_PRINT_PO_NUMBER') ?>:</strong></td>
		    			  <td><?php printf("%08d", $db->f("order_id")); ?></td>
		    		    </tr>
		    		    <tr>
		    			  <td><strong><?php echo $VM_LANG->_('PHPSHOP_ORDER_PRINT_PO_DATE') ?>:</strong></td>
		    			  <td><?php echo vmFormatDate($db->f("cdate") + $mosConfig_offset); ?></td>
		    		    </tr>
		    		    <tr>
		    			  <td><strong><?php echo $VM_LANG->_('PHPSHOP_ORDER_PRINT_PO_STATUS') ?>:</strong></td>
		    			  <td><?php echo ps_order_status::getOrderStatusName($db->f("order_status")) ?></td>
		    		    </tr>
		    		    <tr>
		    			  <td><strong><?php echo $VM_LANG->_('VM_ORDER_PRINT_PO_IPADDRESS') ?>:</strong></td>
		    			  <td><?php $db->p("ip_address"); ?></td>
		    <?php if (PSHOP_COUPONS_ENABLE == '1') { ?>
			  		    <tr>
			  			  <td><strong><?php echo $VM_LANG->_('PHPSHOP_COUPON_COUPON_HEADER') ?>:</strong></td>
			  			  <td><?php
			  if ($db->f("coupon_code"))
				$db->p("coupon_code");
			  else
				echo '-';
			  ?></td>
			  		    </tr>
						    <?php }
						    ?>
		        </tr>
		        <td colspan="2">
		    	  <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
		    		<input type="submit" class="button show-button" name="Submit" value="Выслать извещение повторно" />
		    		<input type="hidden" name="page" value="order.order_print" />
		    		<input type="hidden" name="func" value="resendconfirm" />
		    		<input type="hidden" name="option" value="com_virtuemart" />
		    		<input type="hidden" name="order_id" value="<?php echo $order_id ?>" />
		    	  </form>
		        </td>
		    </tr>
		    </table>
		    <br><br>
		    <form name="formDeliveryDate" id="formDeliveryDate">
		    <?php
		    $dated = $db->f('date_delivery');

		    if ($dated != "0000-00-00") {
			  $dated	 = date("d.m.Y", strtotime($dated));
			  $datedN	 = date("N", strtotime($dated));
			  $days		 = array('', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье');
			  $dated	 = $dated . ' ' . $days[$datedN];
		    }else
			  $dated = "";
		    ?>
		        <div>Дата доставки заказа: <?php echo JHTML::_('calendar', $dated, 'date_delivery_full', 'date_delivery_full', '%d.%m.%Y %A', 'class="date1 totoggle1" placeholder="Дата"'); ?> <input type="button" name="btn_datedel" value="Сохранить дату доставки" onclick="saveDateDelivery()"></div>
		        <input type="hidden" name="page" value="order.sc_change_shipping">
		        <input type="hidden" name="fn" value="update_date_delivery">
		        <input type="hidden" name="option" value="com_virtuemart">
		        <input type="hidden" value="<?= $order_id; ?>" name="order_id">
		    </form>
		    <script>
		        function saveDateDelivery() {
		    	  var data = jQuery('#formDeliveryDate').formSerialize();
		    	  jQuery.ajax({
		    		type: "POST",
		    		dataType: 'json',
		    		url: "/administrator/index.php?no_menu=1&format=json",
		    		data: data,
		    		success: function (retdata) {
		    		    var mess = retdata['mess'];
		    		    if (mess == 'OK')
		    			  alert('Изменения сохранены');
		    		    else
		    			  alert(mess);
		    		}
		    	  });
		        }

		        function saveNalPay() {
		    	  var data = jQuery('#formNalPay').formSerialize();
		    	  jQuery.ajax({
		    		type: "POST",
		    		dataType: 'json',
		    		url: "/administrator/index.php?no_menu=1&format=json",
		    		data: data,
		    		success: function (retdata) {
		    		    var mess = retdata['mess'];
		    		    if (mess == 'OK')
		    			  alert('Изменения сохранены');
		    		    else
		    			  alert(mess);
		    		}
		    	  });
		        }
		    </script>
		    </td>
		    <td valign="top">
		    <?php
		    $tab	 = new vmTabPanel(1, 1, "orderstatuspanel");
		    $tab->startPane("order_change_pane");
		    $tab->startTab($VM_LANG->_('PHPSHOP_ORDER_STATUS_CHANGE'), "order_change_page");
		    ?>
		        <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
		    	  <table class="adminform">
		    		<tr>
		    		    <th colspan="2"><?php echo $VM_LANG->_('PHPSHOP_ORDER_STATUS_CHANGE') ?></th>
		    		</tr>
		    		<tr>
		    		    <td class="labelcell"><?php echo $VM_LANG->_('PHPSHOP_ORDER_PRINT_PO_STATUS') . ":"; ?>
		    		    </td>
		    		    <td><?php $ps_order_status->list_order_status($db->f("order_status")); ?>
		    			  <input type="submit" class="button" name="Submit" value="<?php echo $VM_LANG->_('PHPSHOP_UPDATE') ?>" />
		    			  <input type="hidden" name="page" value="order.order_print" />
		    			  <input type="hidden" name="func" value="orderStatusSet" />
		    			  <input type="hidden" name="vmtoken" value="<?php echo vmSpoofValue($sess->getSessionId()) ?>" />
		    			  <input type="hidden" name="option" value="com_virtuemart" />
		    			  <input type="hidden" name="current_order_status" value="<?php $db->p("order_status") ?>" />
		    			  <input type="hidden" name="order_id" value="<?php echo $order_id ?>" />
		    		    </td>
		    		</tr>
		    		<tr>
		    		    <td class="labelcell" valign="top"><?php echo $VM_LANG->_('PHPSHOP_COMMENT') . ":"; ?>
		    		    </td>
		    		    <td>
		    			  <textarea name="order_comment" rows="5" cols="25"></textarea>
		    		    </td>
		    		<tr>
		    		<tr>
		    		    <td class="labelcell"><label for="notify_customer"><?php echo $VM_LANG->_('PHPSHOP_ORDER_LIST_NOTIFY') ?></label></td>
		    		    <td><input type="checkbox" name="notify_customer" id="notify_customer" checked="checked" value="Y" /></td>
		    		</tr>
		    		<tr>
		    		    <td class="labelcell"><label for="include_comment"><?php echo $VM_LANG->_('PHPSHOP_ORDER_HISTORY_INCLUDE_COMMENT') ?></label>
		    		    </td>
		    		    <td>
		    			  <input type="checkbox" name="include_comment" id="include_comment" checked="checked" value="Y" />
		    		    </td>
		    		</tr>
		    	  </table>
		        </form>
		    <?php
		    $tab->endTab();
		    $tab->startTab($VM_LANG->_('PHPSHOP_ORDER_HISTORY'), "order_history_page");
		    ?>
		        <table class="adminlist">
		    	  <tr >
		    		<th><?php echo $VM_LANG->_('PHPSHOP_ORDER_HISTORY_DATE_ADDED') ?></th>
		    		<th><?php echo $VM_LANG->_('PHPSHOP_ORDER_HISTORY_CUSTOMER_NOTIFIED') ?></th>
		    		<th><?php echo $VM_LANG->_('PHPSHOP_ORDER_LIST_STATUS') ?></th>
		    		<th>Пользователь</th>
		    		<th><?php echo $VM_LANG->_('PHPSHOP_COMMENT') ?></th>
		    	  </tr>
		    <?php
		    foreach ($order_events as $order_event) {
			  echo "<tr>";
			  echo "<td>" . date('d.m.Y H:i', strtotime($order_event->date_added) + 60 * 60 * 3) . "</td>\n";
			  echo "<td align=\"center\"><img alt=\"" . $VM_LANG->_('VM_ORDER_STATUS_ICON_ALT') . "\" src=\"$mosConfig_live_site/administrator/images/";
			  echo $order_event->customer_notified == 1 ? 'tick.png' : 'publish_x.png';

			  echo "\" border=\"0\" align=\"absmiddle\" /></td>\n";
			  echo "<td>" . ps_order_status::getOrderStatusName($order_event->order_status_code) . "</td>\n";
			  echo "<td>" . $order_event->username . "</td>\n";
			  echo "<td>" . $order_event->comments . "</td>\n";
			  echo "</tr>\n";
		    }
		    ?>
		        </table>
				<?php
				$tab->endTab();
				$tab->endPane();
				?>
		    </td>
		    </tr>
		    </table>
		    &nbsp;

		    <?php
//	  <!-- Трекинг посылок start -->
		    require_once( CLASSPATH . 'sc_trackingpost/sc_trackingpost.php' );
		    $sc_trackingpost	 = new sc_trackingpost();
		    $sc_trackingpost->addCssJs();
		    $sc_trackingpost->getAddForm($order_id);
//	  <!-- Трекинг посылок end -->
		    ?>

		    <table class="adminlist" width="100%" >
		    <?php
		    $user_id		 = $db->f("user_id");
		    $dbt			 = new ps_DB;
		    $qt			 = "SELECT * from #__{vm}_order_user_info WHERE user_id='$user_id' AND order_id='$order_id' ORDER BY address_type ASC";
		    $dbt->query($qt);
		    $dbt->next_record();
		    require_once( CLASSPATH . 'ps_userfield.php' );
		    $userfields		 = ps_userfield::getUserFields('registration', false, '', true, true);
		    $shippingfields	 = ps_userfield::getUserFields('shipping', false, '', true, true);
		    $user_opt_fields	 = ps_userfield::getUserFields('opt');
		    $shippingfields	 = array_merge($shippingfields, $user_opt_fields);
		    ?>
		        <tr>
		    	  <th width="50%"  valign="top">Действия</th>
		    	  <th width="50%" valign="top"><?php echo $VM_LANG->_('PHPSHOP_ORDER_PRINT_SHIP_TO_LBL') ?></th>
		        </tr>
		        <tr>
		    	  <td valign="top">
		    		<table class="mailbuttons">
		    		    <tr>
		    <?php
		    $emsstyle		 = 'display: none';
		    if (strpos($db->f("ship_method_id"), "emsrussianpost") === 0 || $db->f("allbutton"))
			  $emsstyle		 = '';
		    ?>
		    			  <td class="emsbutton" style="<?= $emsstyle; ?>" >
		    				<a class="order_action_link" href="javascript:void window.open('/administrator/index.php?no_menu=1&pop=1&tmpl=component&option=com_virtuemart&page=order.order_sendmail_1&order_id=<?= $order_id; ?>&template=send_pay_info_ems', 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=1000,height=800,directories=no,location=no');">Оплата и доставка EMS</a><br>
		    				<a class="order_action_link" id="mail_form_delivery_ems" href="javascript:void window.open('/administrator/index.php?no_menu=1&pop=1&tmpl=component&option=com_virtuemart&page=order.order_sendmail_1&order_id=<?= $order_id; ?>&template=send_ems', 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=1000,height=800,directories=no,location=no');">Заказ отправлен EMS</a><br>
		    			  </td>
		    <?php
		    $rpstyle		 = 'display: none';
		    if (strpos($db->f("ship_method_id"), "postcalc") === 0 || $db->f("allbutton"))
			  $rpstyle		 = '';
		    ?>
		    			  <td class="rpbutton" style="<?= $rpstyle; ?>">
		    				<a class="order_action_link" href="javascript:void window.open('/administrator/index.php?no_menu=1&pop=1&tmpl=component&option=com_virtuemart&page=order.order_sendmail_1&order_id=<?= $order_id; ?>&template=send_pay_info_rp', 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=1000,height=800,directories=no,location=no');">Оплата и доставка Почтой России</a><br>
		    				<a class="order_action_link" href="javascript:void window.open('/administrator/index.php?no_menu=1&pop=1&tmpl=component&option=com_virtuemart&page=order.order_sendmail_1&order_id=<?= $order_id; ?>&template=send_rp', 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=1000,height=800,directories=no,location=no');">Заказ отправлен Почтой России</a><br>
		    			  </td>
		    <?php
		    $tkstyle		 = 'display: none';
		    if (strpos($db->f("ship_method_id"), "standard_shipping|Доставка транспортной компанией") === 0 || $db->f("allbutton"))
			  $tkstyle		 = '';
		    ?>
		    			  <td class="tkbutton" style="<?= $tkstyle; ?>">
		    				<a class="order_action_link" href="javascript:void window.open('/administrator/index.php?no_menu=1&pop=1&tmpl=component&option=com_virtuemart&page=order.order_sendmail_1&order_id=<?= $order_id; ?>&template=send_pay_info_tk', 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=1000,height=800,directories=no,location=no');">Оплата и доставка ТК</a><br>
		    				<a class="order_action_link" href="javascript:void window.open('/administrator/index.php?no_menu=1&pop=1&tmpl=component&option=com_virtuemart&page=order.order_sendmail_1&order_id=<?= $order_id; ?>&template=send_tk', 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=1000,height=800,directories=no,location=no');">Заказ отправлен ТК</a><br>
		    			  </td>
		    		    </tr>
		    		</table>
                      <div class="form_add_order" style="float:right;font-size: 12px">
                          <div class="button_add_order">
                              <input style="width: 250px" type="button" class="button show-button" onclick="toggleOrderUnion()" name="button" value="Объединить заказы">
                          </div>
                          <div style="display: none" class="order_union">
                              <label>Введите № заказа: <input type="text" id="label_add_child"></label><br>
                              <label>Что делать с заказом?<input id="radio1"  name="action_child_order" type="radio" checked>Ничего <input id="radio2"  name="action_child_order" type="radio">Отменить </label><br>
                              <button style="width: 48%" id="button_accept"  onclick="addOrderUnion()">Добавить</button>
                              <button style="width: 48%" id="button_cancel"  onclick="toggleOrderUnion()">Отменить</button><br>
                          </div>
                          <input style="display: none;" id="order_id" value="<?php echo $order_id ?>">
                       </div>

		    		<a class="order_action_link" href="javascript:void window.open('/administrator/index.php?no_menu=1&pop=1&tmpl=component&option=com_virtuemart&page=order.order_sendmail_1&order_id=<?= $order_id; ?>&template=receipt_of_payment', 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=1000,height=800,directories=no,location=no');">Оплата поступила</a><br>

		    		<div class="maillog">
		    		    <br><br>
		    		    <h2>Отправленные письма</h2>
		    <?php
		    $dbblank		 = new ps_DB;
		    $q			 = "SELECT m.id, m.date, m.subject, u.username FROM `mail_log` AS m LEFT JOIN jos_users AS u ON m.user = u.id WHERE `order_id` = " . $order_id . " ORDER BY date";
		    $dbblank->query($q);
		    while ($dbblank->next_record()) {
			  echo date('d.m.Y H:i:s', strtotime($dbblank->f('date'))) . " <a href=\"javascript:void window.open('/administrator/index.php?page=order.order_printdetails3&mail_id=" . $dbblank->f('id') . "&no_menu=1&pop=1&tmpl=component&option=com_virtuemart', 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=1000,height=800,directories=no,location=no');\">" . $dbblank->f('subject') . '</a>, <span class="maillog_username">' . $dbblank->f('username') . '</span><br>';
		    }
		    ?>
		    		</div>
		    		<table class="table adminlist" style="vertical-align: top;">
		    		    <tr>
		    			  <th scope="col"><h2>Сформированные бланки</h2></th>
		    		    <th scope="col"><h2>Сформированные счета</h2></th>
		        </tr>
		        <tr>
		    	  <td style="vertical-align: top;">
		    		<div class="maillog">
		    <?php
		    $dbblank	 = new ps_DB;
		    $q		 = "SELECT b.id, b.date, u.username, blank_data FROM `blank_log` AS b LEFT JOIN jos_users AS u ON b.user = u.id WHERE `order_id` = " . $order_id . " ORDER BY date";
		    $dbblank->query($q);
		    while ($dbblank->next_record()) {
			  $blank_data			 = json_decode($dbblank->f('blank_data'), true);
			  $blank_title		 = '';
			  if (!$blank_data['formtype'])
				$blank_data['formtype']	 = 1;

			  if ($blank_data['formtype'] == 1)
				$blank_title = 'на посылку';
			  else if ($blank_data['formtype'] == 2)
				$blank_title = 'ф.116';

			  if ($blank_data['formtype'] == 3)
				$blank_title = 'ф.117 - ф.113';


			  echo "<a href=\"javascript:void window.open('/administrator/index.php?page=order.order_printdetails_blank&order_id=" . $order_id . "&no_menu=1&pop=1&tmpl=component&option=com_virtuemart&onlyblank=" . $dbblank->f('id') . "', 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=1000,height=800,directories=no,location=no');\">" . date('d.m.Y H:i:s', strtotime($dbblank->f('date'))) . '</a>, <span class="form_type form_type' . $blank_data['formtype'] . '">' . $blank_title . '</span>, <span class="blanklog_username">' . $dbblank->f('username') . '</span><br>';
		    }
		    ?>
		    		</div>
		    	  </td>
		    	  <td style="vertical-align: top;">
		    <?php $sc_ordertopay->getDataFromLogByOrder($order_id); ?>
		    	  </td>
		        </tr>
		    </table>
		    <hr>
		    <?php
		    $dbpm	 = new ps_DB;
		    $q	 = "SELECT * FROM #__{vm}_payment_method, #__{vm}_order_payment WHERE #__{vm}_order_payment.order_id='$order_id' ";
		    $q .= "AND #__{vm}_payment_method.payment_method_id=#__{vm}_order_payment.payment_method_id";
		    $dbpm->query($q);
		    $dbpm->next_record();

		    $mrh_login	 = "AirsoftStoreru";
		    $mrh_pass1	 = "fd73Haas";
		    $inv_id	 = $db->f('order_id');
		    $inv_desc	 = "Оплата заказа №" . $db->f('order_id'); ;
		    $out_summ	 = str_replace(".00000", "", $db->f('order_total'));
		    $shp_item	 = "2";
		    $in_curr	 = "";
		    $culture	 = "ru";
		    $crc		 = md5("$mrh_login:$out_summ:$inv_id:$mrh_pass1:Shp_item=$shp_item");
		    $link		 = 'https://merchant.roboxchange.com/Index.aspx?MrchLogin=' . $mrh_login . '&OutSum=' . $out_summ . '&';
		    $link .= 'InvId=' . $inv_id . '&Desc=' . $inv_desc . '&SignatureValue=' . $crc . '&IncCurrLabel=' . $in_curr . '&';
		    $link .= 'Culture=' . $culture . '&Shp_item=' . $shp_item;

		    if ($dbpm->f('payment_method_id') == 21) :
			  ?>
			  <div class="paybutton">
			      <a class="order_action_link" target="_blank" href="<?= $link; ?>">Оплатить через Робокассу</a>
			  </div>
			  <?php
		    endif;
		    ?>

		    <table width="100%" border="0" cellspacing="0" cellpadding="1" style="display: none">
		    <?php
		    require_once(CLASSPATH . 'ps_country.php');

		    foreach ($userfields as $field) {
			  if ($field->name == 'email')
				$field->name = 'user_email';
			  if ($field->type == 'captcha')
				continue;
			  ?>
			      <tr>
			  	  <td width="35%" align="right">&nbsp;<?php echo $VM_LANG->_($field->title) ? $VM_LANG->_($field->title) : $field->title ?>:</td>
			  	  <td width="65%" align="left"><?php
			  switch ($field->name) {
				case 'country':
				    require_once(CLASSPATH . 'ps_country.php');
				    $country	 = new ps_country();
				    $dbc		 = $country->get_country_by_code($dbt->f($field->name));
				    if ($dbc !== false)
					  echo $dbc->f('country_name');
				    break;
				default:
				    $fieldvalue	 = $dbt->f($field->name);
				    if (is_null($fieldvalue) OR $fieldvalue == "") {
					  echo "&nbsp;";
				    }else{
					  echo $fieldvalue;
				    }
				    break;
			  }
			  ?>
			  	  </td>
			      </tr>
			  <?php
		    }
		    ?>
			  <?php $ps_order_change_html->html_change_bill_to($user_id) ?>
		    </table>
		    </td>
		    <td valign="top" style="width: 500px">
		    <?php
		    // Get Ship To Address
		    $dbt->next_record();
		    ?>
			  <?php
			  $tab2		 = new vmTabPanel(1, 1, "orderaddresspanel");
			  $tab2->startPane("order_change_address");
			  $tab2->startTab('Оригинальные данные', "order_origin_address_page");
			  ?>
		        <div>
			  <?php
			  //echo '<pre>';
			  $orig_data	 = json_decode($dbt->record[0]->origdata, true);
			  //print_r($orig_data);
			  //echo '</pre>';
			  ?>
		    	  <table class="orig_user_info">
		    		<tr>
		    		    <td class="orig_user_info_l">ФИО:</td>
		    		    <td class="orig_user_info_r"><?= $orig_data['first_name']; ?></td>
		    		</tr>
		    		<tr>
		    		    <td class="orig_user_info_l">Телефон:</td>
		    		    <td class="orig_user_info_r"><?= $orig_data['phone_1']; ?></td>
		    		</tr>
		    		<tr>
		    		    <td class="orig_user_info_l"> Адрес:</td>
		    		    <td class="orig_user_info_r"><?= $orig_data['address_1']; ?></td>
		    		</tr>
		    		<tr>
		    		    <td class="orig_user_info_l">Страна:</td>
		    		    <td class="orig_user_info_r">
		    <?php
		    $country	 = new ps_country();
		    $dbc		 = $country->get_country_by_code($orig_data['country']);
		    $country_id	 = $dbt->f($field->name);
		    if ($dbc !== false)
			  echo $dbc->f('country_name');
		    ?>
		    		    </td>
		    		</tr>
		    		<tr>
		    		    <td class="orig_user_info_l">Регион:</td>
		    		    <td class="orig_user_info_r">
		    <?php
		    $country	 = new ps_country();
		    $state	 = $dbt->f($field->name);
		    $dbc		 = $country->get_state_by_code($orig_data['state'], $orig_data['country']);
		    if ($dbc !== false)
			  echo $dbc->f('state_name');
		    ?>
		    		    </td>
		    		</tr>
		    		<tr>
		    		    <td class="orig_user_info_l">Индекс:</td>
		    		    <td class="orig_user_info_r"><?= $orig_data['zip']; ?></td>
		    		</tr>
		    		<tr>
		    		    <td class="orig_user_info_l">E-mail:</td>
		    		    <td class="orig_user_info_r"><?= $orig_data['user_email']; ?></td>
		    		</tr>
		    	  </table>


		        </div>
		    <?php
		    $tab2->endTab();
		    $tab2->startTab("Адрес доставки", "order_change_address_page");
		    ?>
		        <form action="" id="adminForm" name="adminForm">
		    	  <table width="100%" border="0" cellspacing="0" cellpadding="1" style="width: 300px">
		    <?php
		    $country_id	 = 0;
		    $zip		 = $dbt->f('zip');
		    foreach ($shippingfields as $field) {
			  if ($field->name == 'email')
				$field->name = 'user_email';

			  if ($field->name == 'vm_firmname')
				continue;
			  ?>
			  		<tr>
			  		    <td width="35%" align="right">&nbsp;<?php echo $VM_LANG->_($field->title) ? $VM_LANG->_($field->title) : $field->title ?>:</td>
			  		    <td width="65%" align="left"><?php
			  switch ($field->name) {
				case 'country':
				    $country	 = new ps_country();
				    $dbc		 = $country->get_country_by_code($dbt->f($field->name));
				    $country_id	 = $dbt->f($field->name);
				    //if ($dbc !== false)
				    //    echo $dbc->f('country_name');

				    $ps_html = new ps_html();

				    $onchange = "onchange=\"changeStateList();\"";
				    $ps_html->list_country("country", $country_id, "id=\"country_field\" $onchange");

				    break;
				case 'state':
				    $country	 = new ps_country();
				    $state	 = $dbt->f($field->name);
				    $dbc		 = $country->get_state_by_code($state, $country_id);
				    //if ($dbc !== false)
				    //   echo $dbc->f('state_name');

				    echo $ps_html->dynamic_state_lists("country", "state", $country_id, $state);
				    break;
				default:
				    $fieldvalue = $dbt->f($field->name);
				    if (0) {
					  echo "&nbsp;";
				    }else{
					  echo '<input type="text" name="' . $field->name . '" value="' . htmlspecialchars($fieldvalue) . '" class="order_user_filed">';
				    }
				    break;
			  }
			  ?>
			  		    </td>
			  		</tr>
			  <?php
		    }
		    ?>
				    <?php /* $ps_order_change_html->html_change_ship_to($user_id) */ ?>
		    	  </table>
		    	  <div style="padding: 10px"><input type="button" name="saveaddr" id="saveaddr" value="Сохранить изменения" onclick="updateData()"></div>
		    	  <input type="hidden" name="page" value="order.sc_change_shipping">
		    	  <input type="hidden" name="fn" value="update_order_user_data">
		    	  <input type="hidden" name="option" value="com_virtuemart">
		    	  <input type="hidden" value="<?= $order_id; ?>" name="order_id">

		    	  <span class="order_data_log" style="font-weight: bold;">
		    		<a href="javascript:void window.open('/administrator/index.php?page=order.order_datalog&order_id=<?= $order_id; ?>&no_menu=1&pop=1&tmpl=component&option=com_virtuemart', 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=1000,height=800,directories=no,location=no');">
		    		    <img src="<?php echo $mosConfig_live_site ?>/images/M_images/emailButton.png" align="ABSMIDDLE" height="16" width="16" border="0" />
		    		    Посмотреть историю
		    		</a>&nbsp;
		    	  </span>

		        </form>

		        <script>
		    	  function updateData() {
		    		var data = jQuery('#adminForm').formSerialize();
		    		jQuery.ajax({
		    		    type: "POST",
		    		    dataType: 'json',
		    		    url: "/administrator/index.php?no_menu=1&format=json",
		    		    data: data,
		    		    success: function (retdata) {
		    			  var mess = retdata['mess'];
		    			  if (mess == 'OK')
		    				alert('Изменения сохранены');

		    			  // обновим для доставки
		    			  jQuery('#getshippingmetodsforrm input[name=zip]').val(jQuery('#adminForm input[name=zip]').val());
		    			  jQuery('#getshippingmetodsforrm input[name=country]').val(jQuery('#adminForm select[name=country]').val());
		    			  jQuery('#getshippingmetodsforrm input[name=state]').val(jQuery('#adminForm select[name=state]').val());
		    		    }
		    		});

		    	  }
		    	  ;

		    	  function showallbutton() {
		    		var data = jQuery('#getallbuttonsform').formSerialize();
		    		jQuery.ajax({
		    		    type: "POST",
		    		    dataType: 'json',
		    		    url: "/administrator/index.php?no_menu=1&format=json",
		    		    data: data,
		    		    success: function (retdata) {
		    			  if (retdata['success']) {
		    				jQuery('#getallbuttonsform').html(retdata['mess']);
		    				// показываем все кнопки
		    				jQuery('.emsbutton').show();
		    				jQuery('.rpbutton').show();
		    				jQuery('.tkbutton').show();
		    			  }
		    		    }
		    		});

		    	  }
		    	  ;
		        </script>

		    <?php
		    $tab2->endTab();
		    $tab2->endPane();
		    ?>
		    </td>
		    </tr>
		    </table>
		    &nbsp;


		    <table  class="adminlist">
		        <tr>
		    	  <td colspan="2">
		    <?php
		    $ps_stock->addCssJs();


		    $helperFields			 = $ps_stock->getHelper('fields');
		    $scanitemactions_param		 = array();
		    $scanitemactions_param['soa']	 = jRequest::getVar('soa', 'sbor');
		    $actions				 = $helperFields->getField('scanitemactions', $order_id, $scanitemactions_param);
		    if( $order_status != 'X' )
			{
				echo $actions;
			}
		    ?>
		    	  </td>
		        </tr>
		    </table>

		    <div id="orderitemstable_cont" order_id='<?= $order_id ?>'>
		        <table class="adminlist" id='orderitemstable'>
		    	  <tr>
		    		<td colspan="2">
		    		    <table  class="adminlist">
		    			  <tr >
		    				<th class="title" width="5%" align="left"><?php echo $VM_LANG->_('PHPSHOP_ORDER_EDIT_ACTIONS') ?></th>
		    				<th class="title" width="40" align="left">Принято</th>
		    				<th class="title" width="40" align="left"><?php echo $VM_LANG->_('PHPSHOP_ORDER_PRINT_QUANTITY') ?></th>
		    				<th class="title" width="10" align="left">На складе</th>
		    				<th class="title" width="*" align="left"><?php echo $VM_LANG->_('PHPSHOP_ORDER_PRINT_NAME') ?></th>
		    				<th class="title" width="10%" align="left"><?php echo $VM_LANG->_('PHPSHOP_ORDER_PRINT_SKU') ?></th>
		    				<th class="title" style="width: 1px; padding: 0px" align="left"></th>
		    				<th class="title" width="10%"><?php echo $VM_LANG->_('PHPSHOP_ORDER_PRINT_PO_STATUS') ?></th>
		    				<th class="title" width="50"><?php echo $VM_LANG->_('PHPSHOP_PRODUCT_FORM_PRICE_NET') ?></th>
		    				<th class="title" width="50"><?php echo $VM_LANG->_('PHPSHOP_PRODUCT_FORM_PRICE_GROSS') ?></th>
		    				<th class="title" width="5%"><?php echo $VM_LANG->_('PHPSHOP_ORDER_PRINT_TOTAL') ?></th>
		    			  </tr>
		    <?php
		    $dbt					 = new ps_DB;
		    $qt					 = "SELECT order_item_id,product_quantity,order_item_name,order_item_sku,oi.product_id,product_item_price,product_final_price, product_attribute, order_status, product_preorder, pp.product_in_stock, product_weight, pp.product_thumb_image, product_parent_id
						FROM `#__{vm}_order_item` AS oi LEFT JOIN `#__{vm}_product` AS pp ON oi.product_id = pp.product_id
						WHERE oi.order_id='$order_id' ";
		    $dbt->query($qt);
		    $product_weight			 = 0;
		    $i					 = 0;
		    $dbd					 = new ps_DB();

		    while ($dbt->next_record()) {
			  if ($i++ % 2) {
				$bgcolor = 'row0';
			  }else{
				$bgcolor = 'row1';
			  }
			  $t			 = $dbt->f("product_quantity") * $dbt->f("product_final_price");
			  $product_weight += ($dbt->f("product_quantity") * $dbt->f("product_weight"));
			  // Check if it's a downloadable product
			  $downloadable	 = false;
			  $files		 = array();
			  $dbd->query('SELECT product_id, attribute_name
  							FROM `#__{vm}_product_attribute`
  							WHERE product_id=' . $dbt->f('product_id') . ' AND attribute_name=\'download\'');
			  if ($dbd->next_record()) {
				$downloadable = true;
				$dbd->query('SELECT product_id, end_date, download_max, download_id, file_name
  							FROM `#__{vm}_product_download`
  							WHERE product_id=' . $dbt->f('product_id') . ' AND order_id=\'' . $order_id . '\'');
				while ($dbd->next_record()) {
				    $files[] = $dbd->get_row();
				}
			  }

			  //Inf Просканированные позиции
			  $scanitem		 = $ps_stock->getScanItem($order_id, $dbt->f("product_id"));
			  $fullorderitem	 = '';
			  if ($scanitem['scan'] == $dbt->f("product_quantity")) {
				$fullorderitem = 'fullorderitem';
			  }
			  ?>
			  			  <tr fullorderitem="<?= $dbt->f("product_quantity") ?>" class="<?php echo $bgcolor; ?> itemrow_<?= $dbt->f('product_id') ?> <?= $fullorderitem ?>" valign="top">
						    <?php
						    if ($order_status == 'X') {
							  ?>
								<td>
								</td>
								<td>
								</td>
								<td>
				<?= $dbt->f("product_quantity") ?>
								</td>
								    <?php
								}else{
								    if (!$scanitem['scan']) {
									  $ps_order_change_html->html_change_delete_item($dbt->f("order_item_id"));
								    }else{
									  ?>
				    				<td width="5%">
				    				</td>
				    <?php
				}
				?>

								<td class="cell_prinyato">
				<?php
				echo $scanitem['scan'];
				?>
								</td>
								    <?php
								    $ps_order_change_html->html_change_item_quantity($dbt->f("order_item_id"), $dbt->f("product_quantity"));
								}
								?>
			  				<td><?= $dbt->f('product_in_stock'); ?></td>
			  				<td width="30%" align="left">
			  <?php
			  $dbt->p("order_item_name");
			  if ($dbt->f("product_preorder")) {
				echo "<br/><b>Предзаказ: " . $dbt->f("product_preorder") . '</b>';
			  }
			  echo "<br /><span style=\"font-size: smaller;\">" . ps_product::getDescriptionWithTax($dbt->f("product_attribute")) . "</span>";
			  if ($downloadable) {
				echo '<br /><br />
  			  			<div style="font-weight:bold;">' . $VM_LANG->_('VM_DOWNLOAD_STATS') . '</div>';
				if (empty($files)) {
				    echo '<em>- ' . $VM_LANG->_('VM_DOWNLOAD_NOTHING_LEFT') . ' -</em>';
				    $enable_download_function = $ps_function->get_function('insertDownloadsForProduct');
				    if ($perm->check($enable_download_function['perms'])) {
					  echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">
  			  			<input type="hidden" name="page" value="' . $page . '" />
  			  			<input type="hidden" name="order_id" value="' . $order_id . '" />
  			  			<input type="hidden" name="product_id" value="' . $dbt->f('product_id') . '" />
  			  			<input type="hidden" name="user_id" value="' . $db->f('user_id') . '" />
  			  			<input type="hidden" name="func" value="insertDownloadsForProduct" />
  						  <input type="hidden" name="vmtoken" value="' . vmSpoofValue($sess->getSessionId()) . '" />
  			  			<input type="hidden" name="option" value="' . $option . '" />
  			  			<input class="button" type="submit" name="submit" value="' . $VM_LANG->_('VM_DOWNLOAD_REENABLE') . '" />
  			  			</form>';
				    }
				}else{
				    foreach ($files as $file) {
					  echo '<em>'
					  . '<a href="' . $sess->url($_SERVER['PHP_SELF'] . '?page=product.file_form&amp;product_id=' . $dbt->f('product_id') . '&amp;file_id=' . $db->f("file_id")) . '&amp;no_menu=' . @$_REQUEST['no_menu'] . '" title="' . $VM_LANG->_('PHPSHOP_MANUFACTURER_LIST_ADMIN') . '">'
					  . $file->file_name . '</a></em><br />';
					  echo '<ul>';
					  echo '<li>' . $VM_LANG->_('VM_DOWNLOAD_REMAINING_DOWNLOADS') . ': ' . $file->download_max . '</li>';
					  if ($file->end_date > 0) {
						echo '<li>' . $VM_LANG->_('VM_EXPIRY') . ': ' . vmFormatDate($file->end_date + $mosConfig_offset) . '</li>';
					  }
					  echo '</ul>';
					  echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">
  			  			<input type="hidden" name="order_id" value="' . $order_id . '" />
  			  			<input type="hidden" name="page" value="' . $page . '" />
  			  			<input type="hidden" name="func" value="mailDownloadId" />
  						  <input type="hidden" name="vmtoken" value="' . vmSpoofValue($sess->getSessionId()) . '" />
  			  			<input type="hidden" name="option" value="' . $option . '" />
  			  			<input class="button" type="submit" name="submit" value="' . $VM_LANG->_('VM_DOWNLOAD_RESEND_ID') . '" />
  			  			</form>';
				    }
				}
			  }
			  ?>
			  				    <div class="scanitem_list_cont">

			  <?php
			  $scanitemlist	 = $helperFields->getField('scanitemlist', $scanitem);
			  echo $scanitemlist;
			  ?>
			  				    </div>
			  				</td>
			  				<td width="10%" align="left" class="display_image"><a target="_blank" href="/administrator/index.php?page=product.product_form&limitstart=0&keyword=&product_id=<?= $dbt->f("product_id"); ?>&product_parent_id=<?= $dbt->f("product_parent_id"); ?>&option=com_virtuemart"><?php $dbt->p("order_item_sku") ?></a></td>
			  <?php
			  // если нет фото но есть родитель - берем фото от родителя
			  $itemphoto		 = $dbt->f("product_thumb_image");

			  if (!$itemphoto && $dbt->f("product_parent_id")) {
				$parentdb = new ps_DB;
				$parentdb->query('SELECT product_thumb_image FROM jos_vm_product WHERE product_id = "' . $dbt->f("product_parent_id") . '"');
				$parentdb->next_record();

				$itemphoto = $parentdb->f("product_thumb_image");
			  }
			  ?>
			  				<td style="width: 1px; padding: 0px"><img class="skuimg" src="http://www.airsoftstore.ru/components/com_virtuemart/shop_image/product/<?= $itemphoto ?>" style="position: absolute; display: none;"></td>
			  				<td width="10%">

			  <?php
			  if ($order_status == 'X') {
				echo $dbt->f("order_status");
			  }else{
				?>

								    <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<?php
				echo "<strong>" . $VM_LANG->_('PHPSHOP_ORDER_PRINT_PO_STATUS') . ": </strong>";
				$ps_order_status->list_order_status($dbt->f("order_status"));
				?>
									  <input type="submit" class="button" name="Submit" value="<?php echo $VM_LANG->_('PHPSHOP_UPDATE') ?>" />
									  <input type="hidden" name="page" value="order.order_print" />
									  <input type="hidden" name="func" value="orderStatusSet" />
									  <input type="hidden" name="vmtoken" value="<?php echo vmSpoofValue($sess->getSessionId()) ?>" />
									  <input type="hidden" name="option" value="com_virtuemart" />
									  <input type="hidden" name="current_order_status" value="<?php $dbt->p("order_status") ?>" />
									  <input type="hidden" name="order_id" value="<?php echo $order_id ?>" />
									  <input type="hidden" name="order_item_id" value="<?php $dbt->p("order_item_id") ?>" />
								    </form>
				<?php
			  }
			  ?>
			  				</td>
			  				<td align="right">
			  <?php $ps_order_change_html->html_change_product_item_price($dbt->f("order_item_id"), $dbt->f("product_item_price")) ?>
			  				</td>
			  				<td align="right">
			  <?php $ps_order_change_html->html_change_product_final_price($dbt->f("order_item_id"), $dbt->f("product_final_price")) ?>
			  				</td>
			  				<td width="5%" align="right" style="padding-right: 5px;"><?php echo $GLOBALS['CURRENCY_DISPLAY']->getFullValue($t, '', $db->f('order_currency')); ?></td>
			  			  </tr>
			  <?php
		    }
		    ?>

		    		    </table>
		    		    <table  class="adminlist">
		    			  <tr>
		    				<td align="right" colspan="7"><div align="right"><strong>Вес заказа: </strong></div></td>
		    				<td width="5%" align="right" style="padding-right: 5px;"><?= $product_weight; ?> кг.</td>
		    			  </tr>
		    			  <tr>
		    				<td align="right" colspan="7"><div align="right"><strong> <?php echo $VM_LANG->_('PHPSHOP_ORDER_PRINT_SUBTOTAL') ?>: </strong></div></td>
		    				<td width="5%" align="right" style="padding-right: 5px;"><?php echo $GLOBALS['CURRENCY_DISPLAY']->getFullValue($db->f("order_subtotal"), '', $db->f('order_currency')); ?></td>
		    			  </tr>
		    <?php
		    /* COUPON DISCOUNT */
		    $coupon_discount = $db->f("coupon_discount");


		    if (PAYMENT_DISCOUNT_BEFORE == '1') {
			  if ($db->f("order_discount") != 0) {
				?>
							  <tr>
								<td align="right" colspan="7"><strong><?php
				if ($db->f("order_discount") > 0)
				    echo $VM_LANG->_('PHPSHOP_PAYMENT_METHOD_LIST_DISCOUNT');
				else
				    echo $VM_LANG->_('PHPSHOP_FEE');
				?>:</strong></td>
								<td width="5%" align="right" style="padding-right: 5px;"><?php
									  if ($db->f("order_discount") > 0)
										echo "-" . $GLOBALS['CURRENCY_DISPLAY']->getFullValue(abs($db->f("order_discount")), '', $db->f('order_currency'));
									  elseif ($db->f("order_discount") < 0)
										echo "+" . $GLOBALS['CURRENCY_DISPLAY']->getFullValue(abs($db->f("order_discount")), '', $db->f('order_currency'));
									  ?>
								</td>
							  </tr>

				<?php
			  }
			  if ($coupon_discount > 0 || $coupon_discount < 0) {
				?>
							  <tr>
								<td align="right" colspan="7"><strong><?php echo $VM_LANG->_('PHPSHOP_COUPON_DISCOUNT') ?>:</strong></td>
								<td  width="5%" align="right" style="padding-right: 5px;"><?php echo "- " . $GLOBALS['CURRENCY_DISPLAY']->getFullValue($coupon_discount, '', $db->f('order_currency')); ?>
								</td>
							  </tr>
				<?php
			  }
		    }
		    ?>

		    			  <tr>
		    				<td align="right" colspan="7"><strong><?php echo $VM_LANG->_('PHPSHOP_ORDER_PRINT_TOTAL_TAX') ?>:</strong></td>
		    				<td width="5%" align="right" style="padding-right: 5px;"><?php echo $GLOBALS['CURRENCY_DISPLAY']->getFullValue($db->f("order_tax"), '', $db->f('order_currency')) ?></td>
		    			  </tr>
		    			  <tr>
		    				<td align="right" colspan="7"><strong><?php echo $VM_LANG->_('PHPSHOP_ORDER_PRINT_SHIPPING') ?>:</strong></td>
		    				<td width="5%" align="right" style="padding-right: 5px;"><?php echo $GLOBALS['CURRENCY_DISPLAY']->getFullValue($db->f("order_shipping"), '', $db->f('order_currency')) ?></td>
		    			  </tr>
		    			  <tr>
		    				<td align="right" colspan="7"><strong><?php echo $VM_LANG->_('PHPSHOP_ORDER_PRINT_SHIPPING_TAX') ?>:</strong></td>
		    				<td width="5%" align="right" style="padding-right: 5px;"><?php echo $GLOBALS['CURRENCY_DISPLAY']->getFullValue($db->f("order_shipping_tax"), '', $db->f('order_currency')) ?></td>
		    			  </tr>
		    <?php
		    if (PAYMENT_DISCOUNT_BEFORE != '1') {
			  if ($db->f("order_discount") != 0) {
				?>
							  <tr>
								<td align="right" colspan="7"><strong><?php
				if ($db->f("order_discount") > 0)
				    echo $VM_LANG->_('PHPSHOP_PAYMENT_METHOD_LIST_DISCOUNT');
				else
				    echo $VM_LANG->_('PHPSHOP_FEE');
				?>:</strong></td>
								<td width="5%" align="right" style="padding-right: 5px;"><?php
									  if ($db->f("order_discount") > 0)
										echo "-" . $GLOBALS['CURRENCY_DISPLAY']->getFullValue(abs($db->f("order_discount")), '', $db->f('order_currency'));
									  elseif ($db->f("order_discount") < 0)
										echo "+" . $GLOBALS['CURRENCY_DISPLAY']->getFullValue(abs($db->f("order_discount")), '', $db->f('order_currency'));
									  ?>
								</td>
							  </tr>

				<?php
			  }
			  if ($coupon_discount > 0 || $coupon_discount < 0) {
				?>
							  <tr>
								<td align="right" colspan="7"><strong><?php echo $VM_LANG->_('PHPSHOP_COUPON_DISCOUNT') ?>:</strong></td>
								<td width="5%" align="right" style="padding-right: 5px;"><?php echo "- " . $GLOBALS['CURRENCY_DISPLAY']->getFullValue($coupon_discount, '', $db->f('order_currency')); ?></td>
							  </tr>
				<?php
			  }
		    }
		    ?>
		    			  <tr>
		    				<td align="right" colspan="7"><strong><?php echo $VM_LANG->_('PHPSHOP_CART_TOTAL') ?>:</strong></td>
		    				<td width="5%" align="right" style="padding-right: 5px;"><strong><?php echo $GLOBALS['CURRENCY_DISPLAY']->getFullValue($db->f("order_total"), '', $db->f('order_currency')); ?></strong>
		    				</td>
		    			  </tr>
		    <?php
		    // Get the tax details, if any
		    $tax_details = ps_checkout::show_tax_details($db->f('order_tax_details'), $db->f('order_currency'));
		    ?>
						<?php if (!empty($tax_details)) : ?>
			  			  <tr>
			  				<td colspan="8" align="right"><?php echo $tax_details; ?></td>
			  			  </tr>
		    <?php endif; ?>
		    		    </table>
						<?php 
						if( $order_status != 'X' )
						{
							$ps_order_change_html->html_change_add_item();
						}
						?>
		    		</td>
		    	  </tr>
		        </table>
		    </div>
		    &nbsp;
		    <table class="adminlist">
		        <tr>
		    	  <td valign="top" width="300">
		    		<table class="adminlist">
		    <?php
		    $details = array();
		    if ($db->f("ship_method_id")) {
			  $details = explode("|", $db->f("ship_method_id"));
		    }
		    ?>
		    		    <tr>
		    			  <th><?php echo $VM_LANG->_('PHPSHOP_ORDER_PRINT_SHIPPING_LBL') ?></th>
		    		    </tr>
		    		    <tr>
		    			  <td align="left">
		    				<strong><?php echo $VM_LANG->_('PHPSHOP_ORDER_PRINT_SHIPPING_CARRIER_LBL') ?>: </strong>
		    <?php if ($details && $details[1]) echo $details[1]; ?>&nbsp;
		    			  </td>
		    		    </tr>
		    		    <tr>
		    			  <td align="left">
		    				<strong><?php echo $VM_LANG->_('PHPSHOP_ORDER_PRINT_SHIPPING_MODE_LBL') ?>: </strong>
		    <?php if ($details && $details[2]) echo $details[2]; ?>
		    			  </td>
		    		    </tr>
		    		    <tr>
		    			  <td align="left">
		    				<strong><?php echo $VM_LANG->_('PHPSHOP_ORDER_PRINT_SHIPPING_PRICE_LBL') ?>: </strong>
		    <?php
		    if ($details && $details[3]) {
			  echo $GLOBALS['CURRENCY_DISPLAY']->getFullValue($details[3], '', $db->f('order_currency'));
		    }
		    ?>
		    			  </td>
		    		    </tr>
		    <?php 
			if( $order_status != 'X' )
			{
				$ps_order_change_html->html_change_shipping();
			}
			?>
		    		    <tr>
		    			  <td align="left">
		    <?php
//	  <!-- Изменение способа доставки start -->
			if( $order_status != 'X' )
			{
				require_once( CLASSPATH . 'sc_change_shipping.php' );
				$sc_change_shipping	 = new sc_change_shipping();
				$sc_change_shipping->getShangeBtn($order_id, $state, $product_weight, $country_id, $zip);
			}
//	  <!-- Изменение способа доставки end -->
		    ?> </td>
		    		    </tr>
		    		</table>
		    	  </td>

		    <?php
		    $dbpm				 = new ps_DB;
		    $q				 = "SELECT * FROM #__{vm}_payment_method, #__{vm}_order_payment WHERE #__{vm}_order_payment.order_id='$order_id' ";
		    $q .= "AND #__{vm}_payment_method.payment_method_id=#__{vm}_order_payment.payment_method_id";
		    $dbpm->query($q);
		    $dbpm->next_record();

		    // DECODE Account Number
		    $dbaccount	 = new ps_DB;
		    $q		 = "SELECT " . VM_DECRYPT_FUNCTION . "(order_payment_number,'" . ENCODE_KEY . "')
  					AS account_number, order_payment_code FROM #__{vm}_order_payment
  					WHERE order_id='" . $order_id . "'";
		    $dbaccount->query($q);
		    $dbaccount->next_record();
		    ?>
		    	  <!-- Payment Information -->
		    	  <td valign="top" width="*">
		    		<table class="adminlist">
		    		    <tr class="sectiontableheader">
		    			  <th width="13%"><?php echo $VM_LANG->_('PHPSHOP_ORDER_PRINT_PAYMENT_LBL') ?></th>
		    			  <th width="40%"><?php echo $VM_LANG->_('PHPSHOP_ORDER_PRINT_ACCOUNT_NAME') ?></th>
		    			  <th width="30%"><?php echo $VM_LANG->_('PHPSHOP_ORDER_PRINT_ACCOUNT_NUMBER'); ?></th>
		    			  <th width="17%"><?php echo $VM_LANG->_('PHPSHOP_ORDER_PRINT_EXPIRE_DATE') ?></th>
		    		    </tr>
		    		    <tr>
		    			  <td width="13%">
		    <?php 
			if( $order_status != 'X' )
			{
				$ps_order_change_html->html_change_payment($dbpm->f("payment_method_id"));
			}
			?>
		    			  </td>
		    			  <td width="40%"><?php $dbpm->p("order_payment_name"); ?></td>
		    			  <td width="30%"><?php
				echo ps_checkout::asterisk_pad($dbaccount->f("account_number"), 4, true);
				if ($dbaccount->f('order_payment_code')) {
				    echo '<br/>(' . $VM_LANG->_('VM_ORDER_PAYMENT_CCV_CODE') . ': ' . $dbaccount->f('order_payment_code') . ') ';
				}
		    ?>
		    			  </td>
		    			  <td width="17%"><?php echo $dbpm->f("order_payment_expire") ? vmFormatDate($dbpm->f("order_payment_expire"), '%b-%Y') : ''; ?></td>
		    		    </tr>
		    		    <tr class="sectiontableheader">
		    			  <th colspan="4"><?php echo $VM_LANG->_('PHPSHOP_ORDER_PRINT_PAYMENT_LOG_LBL') ?></th>
		    		    </tr>
		    		    <tr>
		    			  <td colspan="4"><?php
				if ($dbpm->f("order_payment_log"))
				    echo $dbpm->f("order_payment_log");
				else
				    echo "./.";
		    ?></td>
		    		    </tr>
		    		    <tr>
		    			  <td colspan="2" align="center">
		    <?php 
			if( $order_status != 'X' )
			{
				$ps_order_change_html->html_change_discount();
			}
			?>
		    			  </td>
		    			  <td colspan="2" align="center">
		    <?php 
			if( $order_status != 'X' )
			{
				$ps_order_change_html->html_change_coupon_discount();
			}
			?>
		    			  </td>
		    		    </tr>
		    		</table>
		    	  </td>
		        </tr>
		        <tr>
		    	  <!-- Customer Note -->
		    	  <td valign="top" width="30%" colspan="2">
		    		<table class="adminlist">
		    		    <tr>
		    			  <th><?php echo $VM_LANG->_('PHPSHOP_ORDER_PRINT_CUSTOMER_NOTE') ?></th>
		    		    </tr>
		    		    <tr>
		    			  <td valign="top" align="center" width="50%">
		    <?php 
				echo $ps_order_change_html->html_change_customer_note();

			?>
		    			  </td>
		    		    </tr>
		    		</table>
		    <?php
		    if ($db->f("allbutton")) {
			  echo $db->f("allbutton");
		    }else{
			  ?>
			  		<br>
			  		<form method="post" action="" id="getallbuttonsform">
			  		    <button class="btn getallbuttons" onclick="showallbutton();
			  							return false;">Показать все кнопки писем</button>
			  		    <input type="hidden" name="page" value="order.sc_change_shipping">
			  		    <input type="hidden" name="fn" value="showallbutton">
			  		    <input type="hidden" name="option" value="com_virtuemart">
			  		    <input type="hidden" value="<?= $db->f("order_id"); ?>" name="order_id">
			  		</form>
			  <?php
		    }
		    ?>        <?php
                      $dblog	 = new ps_DB;
                      $q	 = "SELECT * FROM order_log WHERE order_id='$order_id' ORDER BY id DESC";
                      $dblog->query($q);
                      $log_list = $dblog->loadObjectList();

                      ?>
                      <table border="1">
                          <tr>
                              <th>Категория события</th>
                              <th>Наименование</th>
                              <th>Старое значение</th>
                              <th>Новое значение</th>
                              <th>Идентификатор пользователя</th>
                              <th>Время изменения</th>
                          </tr>
                          <?php
                          foreach ($log_list as $arr) {
                              ?>
                              <tr>
                                  <td width="15%"><?= $arr->category ?></td>
                                  <td width="15%"><?= $arr->name ?></td>
                                  <td width="15%"><?= $arr->prev_value ?></td>
                                  <td width="15%"><?= $arr->cur_value ?></td>
                                  <td width="3%"><?= $arr->user_id ?></td>
                                  <td width="7%"><?= $arr->cdate ?></td>
                              </tr>
                          <?php
                          }
                          ?>
                      </table>
		    	  </td>
		        </tr>
		    </table>
		    <?php
		}else{
		    echo $VM_LANG->_('VM_ORDER_NOTFOUND');
		}
	  }
	  ?>
<script src="/js/chosen/chosen.jquery.js" type="text/javascript"></script>
<link rel="stylesheet" href="/js/chosen/chosen.css" type="text/css" />
<script>
						  jQuery('select[name=product_id]').chosen({search_contains: true});
						  jQuery('select[name=product_id_bysku]').hide();

						  //jQuery(document).ready(function(e) {
						  jQuery(document).on('mouseover', '.display_image', function (e) {
							jQuery(this).parent().find('img.skuimg').show();
						  });
						  jQuery(document).on('mouseout', '.display_image', function (e) {
							jQuery(this).parent().find('img.skuimg').hide();
						  });
						  //});

</script>