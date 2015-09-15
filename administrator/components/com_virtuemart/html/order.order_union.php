<?php
require_once(CLASSPATH . 'ps_order_change.php');
require_once(CLASSPATH . 'ps_order_change_html.php');
require_once (CLASSPATH . 'ps_product_attribute.php');
require_once (CLASSPATH . 'ps_product.php');
require_once( CLASSPATH . 'ps_multishop.php' );
require_once(CLASSPATH . 'ps_orderlog.php');
//ini_set('display_errors',1);
//error_reporting(E_ALL);
if (!defined('_VALID_MOS') && !defined('_JEXEC'))
    die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');
global $db;
ob_clean();

//echo $_REQUEST['order_id'].'~~~~~~~~~~~~~'.$_REQUEST['cur_order_id'];
/*
$query = "SELECT * FROM jos_vm_order_item WHERE order_id = '" . $_REQUEST['cur_order_id'] . "'";
$db->setQuery($query);
$db->Query($query);
$cur_order_id = $db->loadObjectList();
*/
$query = "SELECT * FROM jos_vm_order_item WHERE order_id = '" . $_REQUEST['order_id'] . "'";
$db->setQuery($query);
$db->Query($query);
$order_list = $db->loadObjectList();
$ps_order_change = new ps_order_change($_REQUEST['cur_order_id']);
foreach($order_list as $arr){
    $query = "SELECT  order_item_id, product_quantity FROM jos_vm_order_item WHERE product_id = '" . $arr->product_id . "' AND order_id = '" . $_REQUEST['cur_order_id'] . "'";
    $db->setQuery($query);
    $db->Query($query);
    $current = $db->loadObject();
    if ($current->product_quantity) {/*
        $query = "UPDATE jos_vm_order_item SET product_quantity = product_quantity + ". $arr->product_quantity
            . " WHERE product_id = '" . $arr->product_id . "' AND order_id = '" . $_REQUEST['cur_order_id'] . "'";
        $db->setQuery($query);
        $db->Query($query);*/
        //echo $_REQUEST['cur_order_id'].'<br>';
        //echo $current->order_item_id.'<br>';
        //echo $current->product_quantity + $arr->product_quantity.'<br>';
        $ps_order_change->change_item_quantity($_REQUEST['cur_order_id'], $current->order_item_id, $current->product_quantity + $arr->product_quantity);
        //$ps_order_change2->recalc_order($_REQUEST['cur_order_id']);
    }else{
        /*
        $query = "INSERT INTO jos_vm_order_item (order_id,user_info_id,vendor_id,product_id,order_item_sku,order_item_name,
product_quantity,product_item_price,product_final_price,order_item_currency,order_status,cdate,mdate,product_attribute,product_preorder)"
            . "VALUES ('" . $_REQUEST['cur_order_id'] . "','" . $arr->user_info_id . "','" . $arr->vendor_id . "',
            '" . $arr->product_id . "','" . $arr->order_item_sku . "','" . $arr->order_item_name . "',
            '" . $arr->product_quantity . "','" . $arr->product_item_price . "','" . $arr->product_final_price . "',
            '" . $arr->order_item_currency . "','" . $arr->order_status . "','" . $arr->cdate . "',
            '" . $arr->mdate . "','" . $arr->product_attribute . "','" . $arr->product_preorder . "')";
        $db->setQuery($query);
        $db->Query($query);*/
        vmGet($_REQUEST, 'product_id');
        $_REQUEST['product_id'] = $arr->product_id;
        $_REQUEST['add_product_validate'] = '1';
        $_REQUEST['product_quantity'] = $arr->product_quantity;
        $ps_order_change->add_product();
        //$ps_order_change2->recalc_order($_REQUEST['cur_order_id']);
    }
}
if($_REQUEST['radio_choise']=='radio2'){
    $query = "UPDATE jos_vm_order_item SET order_status='X' WHERE order_id = '" . $_REQUEST['order_id'] . "'";
    $db->setQuery($query);
    $db->Query($query);
}