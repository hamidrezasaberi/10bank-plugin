<?php
/*****************************************************************************
* www.10bank.ir
*****************************************************************************/

	// show aux page
	if ( isset($_GET['bank10'] ))
	{
		if (isset($_GET['modID'])) {
		$modID = $_GET['modID'];
		$orderID = $_GET['orderID'];
		$rs = $_GET['pay'];

		
		$reference_id = (int) ($_GET['trans_id']);
		$valid = ($_GET['valid']);
		
		$q = db_query("SELECT * FROM ".SETTINGS_TABLE." WHERE settings_constant_name='CONF_PAYMENTMODULE_bank10_MERCHANT_ID_$modID'");
		$res = db_fetch_row($q);
		$q = db_query("SELECT * FROM ".SETTINGS_TABLE." WHERE settings_constant_name='CONF_PAYMENTMODULE_bank10_MERCHANT_API_$modID'");
		$res2 = db_fetch_row($q);
		$comStatID = _getSettingOptionValue('CONF_COMPLETED_ORDER_STATUS');
			
			
		if(!empty($res['settings_value'])){
			$gateway_id = $res['settings_value'];
			}else{
				Redirect( "index.php" );
			}
			if(!empty($res2['settings_value'])){
			$gateway_api = $res2['settings_value'];
			}else{
				Redirect( "index.php" );
			}
		

			$order =_getOrderById($orderID);
			
			if(($rs == '1') && ($order['StatusID'] != $comStatID) && isset($valid))
			{
				if ($orderID) 
				{
					$amount = ($order["order_amount"]);
					@session_start();
					$rand = $_SESSION['rand'] ; 
					$verify_valid = md5($gateway_id .$amount. $gateway_api . $rand) == $valid;
					if($verify_valid)
					{
						$comStatID = _getSettingOptionValue('CONF_COMPLETED_ORDER_STATUS');
						$pininfo = ostSetOrderStatusToOrder($orderID, $comStatID, 'Your Online Payment with bank10 gateway accepted', 1);
						$body =  STR_SHETAB_THANKS.'<br>';
						$body .= STR_SHETAB_REFNUM.': '.$reference_id.'<br>';
						$body .= $pininfo;
						
					} else 
					{

						ostSetOrderStatusToOrder($orderID, 1);
						$body =	'تراکنش ناموفق بوده است';
					}
				}
			    else 
				{
					$body =	ERROR_SHETAB_14;
			    }
			
			}else {
				if ($orderID) {
				ostSetOrderStatusToOrder($orderID, 1);
				}
				$body =	'تراکنش ناموفق بوده است';
			}
			$smarty->assign("page_body", $body );
			$smarty->assign("main_content_template", "bank10.tpl.html" );
		}
		else
		{
			$smarty->assign("main_content_template", "page_not_found.tpl.html" );
		}
}

?>