<?php
/*************************************************************
* www.10bank.ir
**************************************************************/



  class osC_Payment_bank10 extends osC_Payment_Admin {

/**
 * The administrative title of the payment module
 *
 * @var string
 * @access private
 */

    var $_title;

/**
 * The code of the payment module
 *
 * @var string
 * @access private
 */

    var $_code = 'bank10';

/**
 * The developers name
 *
 * @var string
 * @access private
 */

    var $_author_name = '10bank.ir';

/**
 * The developers address
 *
 * @var string
 * @access private
 */

  var $_author_www = 'http://www.10bank.ir';

/**
 * The status of the module
 *
 * @var boolean
 * @access private
 */

    var $_status = false;

/**
 * Constructor
 */

    function osC_Payment_bank10() {
      global $osC_Language;

      $this->_title = $osC_Language->get('درگاه پرداخت بانک10');
      $this->_description = $osC_Language->get('payment_bank10_description');
      $this->_method_title = $osC_Language->get('payment_bank10_method_title');
      $this->_status = (defined('MODULE_PAYMENT_bank10_STATUS') && (MODULE_PAYMENT_bank10_STATUS == '1') ? true : false);
      $this->_sort_order = (defined('MODULE_PAYMENT_bank10_SORT_ORDER') ? MODULE_PAYMENT_bank10_SORT_ORDER : null);
    }

/**
 * Checks to see if the module has been installed
 *
 * @access public
 * @return boolean
 */

    function isInstalled() {
      return (bool)defined('MODULE_PAYMENT_bank10_STATUS');
    }

/**
 * Installs the module
 *
 * @access public
 * @see osC_Payment_Admin::install()
 */

    function install() {
      global $osC_Database;

      parent::install();
	  //
	  $osC_Database->simpleQuery("CREATE TABLE IF NOT EXISTS `" . DB_TABLE_PREFIX . "online_transactions` (
	  `id` int(10) unsigned NOT NULL auto_increment,
	  `orders_id` int(11) default NULL,
	  `receipt_id` varchar(100) default NULL,
	  `transaction_method` varchar(255) default NULL,
	  `transaction_date` datetime default NULL,
	  `transaction_amount` decimal(15,2) unsigned default NULL,
	  `transaction_id` varchar(255) default NULL,
	  PRIMARY KEY (`id`)
	  )ENGINE=MyISAM DEFAULT CHARSET=utf8;");
	  //

      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('فعالسازی درگاه بانک10', 'MODULE_PAYMENT_bank10_STATUS', '-1', 'www.10bank.ir', '6', '0', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('id درگاه', 'MODULE_PAYMENT_bank10_ID', '', 'id درگاه بانک10', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('api درگاه', 'MODULE_PAYMENT_bank10_API', '', 'api درگاه بانک10', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('واحد پول', 'MODULE_PAYMENT_bank10_CURRENCY', 'IRR', 'واحد پول دروازه پرداخت(بر روی ریال تنظیم گردد)', '6', '0', 'osc_cfg_set_boolean_value(array(\'Selected Currency\',\'IRR\'))', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('ترتیب نمایش', 'MODULE_PAYMENT_bank10_SORT_ORDER', '0', 'ترتیب نمایش صفحه پرداخت ، مقادیر کمتر بالاتر قرار می گیرند.', '6', '0', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('منطقه پرداخت', 'MODULE_PAYMENT_bank10_ZONE', '0', 'اگر منطقه انتخاب گردد ، این روش پرداخت فقط برای آن منطقه فعال می باشد.', '6', '0', 'osc_cfg_use_get_zone_class_title', 'osc_cfg_set_zone_classes_pull_down_menu', now())");
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('تنظیم وضعیت سفارش', 'MODULE_PAYMENT_bank10_ORDER_STATUS_ID', '0', 'وضعیت سفارشاتی که از این طریق پرداخت می گردند.', '6', '0', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");
    }

/**
 * Return the configuration parameter keys in an array
 *
 * @access public
 * @return array
 */

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('MODULE_PAYMENT_bank10_STATUS',
                             'MODULE_PAYMENT_bank10_ID',
                             'MODULE_PAYMENT_bank10_API',
                             'MODULE_PAYMENT_bank10_CURRENCY',
                             'MODULE_PAYMENT_bank10_ZONE',
                             'MODULE_PAYMENT_bank10_ORDER_STATUS_ID',
                             'MODULE_PAYMENT_bank10_SORT_ORDER');
      }

      return $this->_keys;
    }
  }
?>