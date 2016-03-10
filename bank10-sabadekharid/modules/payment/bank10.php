<?php
/*****************************************************************************
* www.10bank.ir
*****************************************************************************/

/**
 * @connect_module_class_name Cbank10
 *
 */

class Cbank10 extends PaymentModule{

	function _initVars(){
		
		$this->title 		= Cbank10_TTL;
		$this->description 	= Cbank10_DSCR;
		$this->sort_order 	= 1;
		$this->Settings = array( 
			"CONF_PAYMENTMODULE_bank10_MERCHANT_ID",
			"CONF_PAYMENTMODULE_bank10_MERCHANT_API",
			"CONF_PAYMENTMODULE_bank10_RIAL_CURRENCY"
			);
	}

	function after_processing_html( $orderID ) 
	{
		$order = ordGetOrder( $orderID );
		if ( $this->_getSettingValue('CONF_PAYMENTMODULE_bank10_RIAL_CURRENCY') > 0 )
		{
			$PAcurr = currGetCurrencyByID ( $this->_getSettingValue('CONF_PAYMENTMODULE_bank10_RIAL_CURRENCY') );
			$PAcurr_rate = $PAcurr["currency_value"];
		}
		if (!isset($PAcurr) || !$PAcurr)
		{
			$PAcurr_rate = 1;
		}
		$order_amount = round($order["order_amount"]);
		$modID =  $this ->get_id();
        $amount = $order_amount; 
		$redirect_url = urlencode(CONF_FULL_SHOP_URL."?bank10&modID=$modID&pay=1&orderID=$orderID");
		$gateway_id = $this->_getSettingValue('CONF_PAYMENTMODULE_bank10_MERCHANT_ID');
		$rand = substr(md5(time() . microtime()), 0, 10);
		@session_start();
		$_SESSION['rand'] = $rand; 
		$params = 'gateway_id=' . $gateway_id . '&amount=' . $amount . '&redirect_url=' . $redirect_url . '&rand=' . $rand;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://10bank.ir/transaction/create');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);
		if ($result > 0 && is_numeric($result)) 
		{
			$go = "http://10bank.ir/transaction/submit?id=" . $result;
			header("Location: $go");
			
			exit ;
			return;
		}else
		{
			return $result;
		}
	}

	function _initSettingFields(){
		
		$this->SettingsFields['CONF_PAYMENTMODULE_bank10_MERCHANT_ID'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> Cbank10_CFG_MERCHANT_ID_TTL,
			'settings_description' 	=> Cbank10_CFG_MERCHANT_ID_DSCR,
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_bank10_MERCHANT_API'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> Cbank10_CFG_MERCHANT_API_TTL,
			'settings_description' 	=> Cbank10_CFG_MERCHANT_API_DSCR,
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);

		$this->SettingsFields['CONF_PAYMENTMODULE_bank10_RIAL_CURRENCY'] = array(
			'settings_value' 		=> '0', 
			'settings_title' 			=> Cbank10_CFG_RIAL_CURRENCY_TTL,
			'settings_description' 	=> Cbank10_CFG_RIAL_CURRENCY_DSCR,
			'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
			'sort_order' 			=> 1,
		);
	}
}
?>