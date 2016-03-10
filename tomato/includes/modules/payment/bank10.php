<?php
/*************************************************************
* www.bank10.ir
**************************************************************/

class osC_Payment_bank10 extends osC_Payment {
	var $_title, $_code = 'bank10', $_status = false, $_sort_order, $_order_id;

	function osC_Payment_bank10() {
		global $osC_Database, $osC_Language, $osC_ShoppingCart;
		$this->_title = $osC_Language->get('درگاه پرداخت بانک10');
		$this->_method_title = $osC_Language->get('درگاه پرداخت بانک10');
		$this->_status = (MODULE_PAYMENT_bank10_STATUS == '1') ? true : false;
		$this->_sort_order = MODULE_PAYMENT_bank10_SORT_ORDER;
		$this->form_action_url = '';
		
		if ($this->_status === true) {
			if ((int) MODULE_PAYMENT_bank10_ORDER_STATUS_ID > 0) {
				$this->order_status = MODULE_PAYMENT_bank10_ORDER_STATUS_ID;
			}
			if ((int) MODULE_PAYMENT_bank10_ZONE > 0) {
				$check_flag = false;
				$Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
				$Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
				$Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_bank10_ZONE);
				$Qcheck->bindInt(':zone_country_id', $osC_ShoppingCart->getBillingAddress('country_id'));
				$Qcheck->execute();
				while ($Qcheck->next()) {
					if ($Qcheck->valueInt('zone_id') < 1) {
						$check_flag = true;
						break;
					}
					elseif ($Qcheck->valueInt('zone_id') == $osC_ShoppingCart->getBillingAddress('zone_id')) {
						$check_flag = true;
						break;
					}
				}
				if ($check_flag === false) {
					$this->_status = false;
				}
			}
		}
	}
	function selection() {
		return array('id' => $this->_code, 'module' => $this->_method_title);
	}
	function pre_confirmation_check() {
		return false;
	}
	function confirmation() {
		global $osC_Language, $osC_CreditCard;
		$this->_order_id = osC_Order :: insert(ORDERS_STATUS_PREPARING);
		$confirmation = array('title' => $this->_method_title, 'fields' => array(array('title' => $osC_Language->get('درگاه پرداخت بانک10'))));
		return $confirmation;
	}
	function process_button() {
		global $osC_Currencies, $osC_ShoppingCart, $osC_Language, $osC_Database;
		if (MODULE_PAYMENT_bank10_CURRENCY == 'Selected Currency') {
			$currency = $osC_Currencies->getCode();
		}
		else {
			$currency = MODULE_PAYMENT_bank10_CURRENCY;
		}
		$amount = round($osC_Currencies->formatRaw($osC_ShoppingCart->getTotal(), $currency), 2);
		$order = $this->_order_id;
		$status = 1;
		$callbackUrl = (osc_href_link(FILENAME_CHECKOUT, 'process', 'HTTP', null, null, true));
		
		$gateway_id = MODULE_PAYMENT_bank10_ID;
		$rand = substr(md5(time() . microtime()), 0, 10);
		@session_start();
		$_SESSION['rand'] = $rand;
		$params = 'gateway_id=' . $gateway_id . '&amount=' . $amount . '&redirect_url=' . $callbackUrl . '&rand=' . $rand;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://10bank.ir/transaction/create');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);
		if ($result > 0 && is_numeric($result)) 
		{
			$link = "http://10bank.ir/transaction/submit?id=" . $result;
			$osC_Database->simpleQuery("insert into `" . DB_TABLE_PREFIX . "online_transactions`(orders_id,receipt_id,transaction_method,transaction_date,transaction_amount,transaction_id) values('$order','$result','bank10','','$amount','')");
			echo '<div style="text-align:left;">' . osc_link_object(osc_href_link($link, '', '', '', false), osc_draw_image_button('button_confirm_order.gif', $osC_Language->get('button_confirm_order'), 'id="btnConfirmOrder"')) . '</div><div style="display:none">';

		}else
		{
			echo '<div style="text-align:left;"><font color="red">' .$result.'</font></div><div style="display:none">';
		}
		
		
	}
	function get_error() {
		global $osC_Language;
		return $error;
	}
	function process() {
		global $osC_Language, $osC_Customer,$osC_Currencies, $osC_ShoppingCart, $_POST, $_GET, $messageStack, $osC_Database;
		
		$invoiceid = $this->_order_id;
		$amount = round($osC_Currencies->formatRaw($osC_ShoppingCart->getTotal(), $currency), 2);
		$valid =  isset($_GET['valid']) ? $_GET['valid'] : 0;
		$referenceId = isset($_GET['trans_id']) ? $_GET['trans_id'] : 0;

		if ($valid)
		{
			@session_start();
			$rand = $_SESSION['rand'];
			$verify_valid = md5(MODULE_PAYMENT_bank10_ID.$amount.MODULE_PAYMENT_bank10_API.$rand) == $valid;
			 
			if($verify_valid)
			{
				$osC_Database->simpleQuery("update `" . DB_TABLE_PREFIX . "online_transactions` set transaction_id = '$referenceId',transaction_date = '" . date("YmdHis") . "' where  orders_id = '$invoiceid' ");
				$Qtransaction = $osC_Database->query('insert into :table_orders_transactions_history (orders_id, transaction_code, transaction_return_value, transaction_return_status, date_added) values (:orders_id, :transaction_code, :transaction_return_value, :transaction_return_status, now())');
				$Qtransaction->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
				$Qtransaction->bindInt(':orders_id', $invoiceid);
				$Qtransaction->bindInt(':transaction_code', 1);
				$Qtransaction->bindValue(':transaction_return_value', $referenceId);
				$Qtransaction->bindInt(':transaction_return_status', 1);
				$Qtransaction->execute();
				//
				$this->_order_id = osC_Order :: insert();
				$comments = $osC_Language->get('payment_bank10_method_authority') . '[' . $invoiceid . ']';
				osC_Order :: process($this->_order_id, $this->order_status, $comments);
			}
			else 
			{
				$order = $this->_order_id;
				$orderid = $order;
				$reversaltoreversal = $order;
				$reversalstatus = 1;
				$osC_Database->simpleQuery("delete from `" . DB_TABLE_PREFIX . "online_transactions` where 1 and ( receipt_id = '$referenceId' ) and ( orders_id = '$order' )");
				osC_Order :: remove($this->_order_id);
				$messageStack->add_session('checkout', 'خطا ! تراکنش ناموفق بود.', 'error');
				osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'checkout&view=paymentInformationForm', 'SSL', null, null, true));
		    }
		}
		else {
	        $order = $this->_order_id;
			$orderid = $order;
			$reversaltoreversal = $order;
			$reversalstatus = 1;
			$osC_Database->simpleQuery("delete from `" . DB_TABLE_PREFIX . "online_transactions` where 1 and ( receipt_id = '$referenceId' ) and ( orders_id = '$order' )");
			osC_Order :: remove($this->_order_id);
			$messageStack->add_session('checkout', 'در عملیات پرداخت خطا رخ داده است ' , 'error');
			osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'checkout&view=paymentInformationForm', 'SSL', null, null, true));
		    }
		}

	function callback() {
		global $osC_Database;
		//
	}
}
?>