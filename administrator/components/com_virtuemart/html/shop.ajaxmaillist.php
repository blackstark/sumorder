<?php

if (!defined('_VALID_MOS') && !defined('_JEXEC'))
    die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');

if (is_email($_REQUEST['user_email']) !== 1) {
    echo 'Вы ввели неверный e-mail адрес';
    exit;
}

global $db;

ob_clean();

$query = "SELECT EXISTS (SELECT 1 FROM email_list WHERE send = 0 AND product_id = '" . $_REQUEST['product_sku'] . "' AND email = '" . $_REQUEST['user_email'] . "')";
$db->setQuery($query);
$db->Query($query);
if ($db->loadResult() == 1) {
    echo 'Ваш e-mail уже подписан';
    exit;
}

$query = "INSERT INTO email_list (product_id,email,send)"
    . "VALUES ('" . $_REQUEST['product_sku'] . "','" . $_REQUEST['user_email'] . "',0)";
$db->setQuery($query);
$db->Query($query);

echo 'Вы успешно подписаны на уведомление';

function is_email($email)
{
    return preg_match('/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i', $email);
}

exit();