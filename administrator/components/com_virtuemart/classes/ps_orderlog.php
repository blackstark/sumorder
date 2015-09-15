<?php
class ps_orderlog
{
    var $db = null; //Inf class базы данных
    var $_table_orderlog = 'order_log'; //Inf таблица логов изменения заказов в DB

    function __construct()
    {
        $this->db = &JFactory::getDBO();
        $this->checkInstallDb();
    }

        function checkInstallDb()
        {
            $query = "SHOW TABLES LIKE '" . $this->_table_orderlog . "'";
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
                $table = 'CREATE TABLE IF NOT EXISTS `' . $this->_table_orderlog . '` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `order_id` int(11) NOT NULL,
                `category` varchar(255) CHARACTER SET utf8 NOT NULL,
                `name` varchar(255) CHARACTER SET utf8 NOT NULL,
                `prev_value` varchar(255) CHARACTER SET utf8 NOT NULL,
                `cur_value` varchar(255) CHARACTER SET utf8 NOT NULL,
                `user_id` int(11) NOT NULL,
                `cdate` datetime NOT NULL,
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
     * Сохранение лога DB
     *
     *
     */
    function saveLog($order_id, $category, $name, $prev_value, $cur_value)
    {
        $query = "INSERT INTO " . $this->_table_orderlog . " SET order_id = '" . $order_id . "',category = '" . $category . "',name = '" . $name . "',"
            . "prev_value = '" . $prev_value . "',cur_value = '" . $cur_value . "',user_id = '" . $_SESSION['auth']['user_id'] . "', cdate = NOW()";
        $this->db->setQuery($query);
        $this->db->Query($query);
        if ($this->db->getErrorNum()) {
            echo $this->db->stderr();
            return false;
        }
        return true;
    }
}
