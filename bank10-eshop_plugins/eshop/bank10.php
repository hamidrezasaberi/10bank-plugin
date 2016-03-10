<?php

// bank10 IPN Integration Class File

global $wpdb,$wp_query,$wp_rewrite,$blog_id,$eshopoptions;

$detailstable=$wpdb->prefix.'eshop_orders';
$derror=__('در هنگام بارگزاری ماژول بانک ۱۰ خطایی رخ داده است','eshop');

//sanitise
include_once(WP_PLUGIN_DIR.'/eshop/cart-functions.php');
$_POST=sanitise_array($_POST);

//required info for your gateway
include_once (WP_PLUGIN_DIR.'/eshop-bank10-mg/eshop-bank10-mg.php');

// Setup class
require_once(WP_PLUGIN_DIR.'/eshop-bank10-mg/bank10-class.php');  // include the class file

$p = new bank10_class; // initiate an instance of the class
$p->bank10_url = 'http://10bank.ir/pay_invoice/';     

// required info /end

$this_script = site_url();
global $wp_rewrite;

if($eshopoptions['checkout']!=''){
	$p->autoredirect=add_query_arg('eshopaction','redirect',get_permalink($eshopoptions['checkout']));
}else{
	die('<p>'.$derror.'</p>');
}

// if there is no action variable, set the default action of 'process'
if(!isset($wp_query->query_vars['eshopaction']))
	$eshopaction='process';
else
	 $eshopaction=$wp_query->query_vars['eshopaction'];

switch ($eshopaction) {
    case 'redirect':

    	//auto-redirect bits
		header('Cache-Control: no-cache, no-store, must-revalidate'); //HTTP/1.1
		header('Expires: Sun, 01 Jul 2005 00:00:00 GMT');
		header('Pragma: no-cache'); //HTTP/1.0

		// saves all the data into the database		
		$_cost=$_POST['amount'];
		
		$_theid=$eshopoptions['bank10']['id'];
		$_cost=number_format($_cost,0,'','');
		@session_start();
		$_SESSION['amount'] = $_cost;
		$rand = substr(md5(time() . microtime()), 0, 10);
		$_SESSION['rand'] = $rand;
		
		if(isset($_COOKIE['ap_id'])) $_POST['affiliate'] = $_COOKIE['ap_id'];
		if(isset($_COOKIE['ap_id'])) unset($_POST['affiliate']);

		$p = new bank10_class; 
		
		$params = 'gateway_id=' . $_theid . '&amount=' . $_cost . '&redirect_url=' . $callBackUrl . '&rand=' . $rand;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://10bank.ir/transaction/create');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($ch);
		curl_close($ch);
	
		if ($res > 0 && is_numeric($res))
		{
				orderhandle($_POST,$res);
				$p->bank10_url = 'http://10bank.ir/transaction/submit?id='.$res;     
				$echoit.=$p->eshop_submit_bank10_post($_POST);
		}
		
		break;
        
   case 'process':      
		if($eshopoptions['cart_success']!=''){
			$ilink=add_query_arg(array('eshopaction'=>'bank10ipn'),get_permalink($eshopoptions['cart_success']));
		}else{
			die('<p>'.$derror.'</p>');
		}
		$p->add_field('bank10URL', $ilink);
		$p->add_field('shipping_1',eshopShipTaxAmt());
		$sttable=$wpdb->prefix.'eshop_states';
		$getstate=$eshopoptions['shipping_state'];
		if($eshopoptions['show_allstates'] != '1'){
			$stateList=$wpdb->get_results("SELECT id,code,stateName FROM $sttable WHERE list='$getstate' ORDER BY stateName",ARRAY_A);
		}else{
			$stateList=$wpdb->get_results("SELECT id,code,stateName,list FROM $sttable ORDER BY list,stateName",ARRAY_A);
		}
		foreach($stateList as $code => $value){
			$eshopstatelist[$value['id']]=$value['code'];
		}		
		foreach($_POST as $name=>$value){
			// discount code check here
			if(strstr($name,'amount_')){
				if(isset($_SESSION['eshop_discount'.$blog_id]) && eshop_discount_codes_check()){
					$chkcode=valid_eshop_discount_code($_SESSION['eshop_discount'.$blog_id]);

					if($chkcode && apply_eshop_discount_code('discount')>0){
						$discount=apply_eshop_discount_code('discount')/100;
						$value = number_format(round($value-($value * $discount), 2),2);
						$vset='yes';
					}
				}

				if(is_discountable(calculate_total())!=0 && !isset($vset)){
					$discount=is_discountable(calculate_total())/100;
					$value = number_format(round($value-($value * $discount), 2),2);
				}
			}

			if(sizeof($stateList)>0 && ($name=='state' || $name=='ship_state')){
				if($value!='')
					$value=$eshopstatelist[$value];
			}
			$p->add_field($name, $value);
		}

		
			$echoit .= $p->submit_bank10_post(); // submit the fields to 
    	
      	break;

   case 'bank10ipn':

   		$p->validate_ipn();
   		$data = $p->ipn_data;
   		if(!empty($data)) 
		{
			$valid = $p->ipn_data['valid'];
   			if ($valid)
			{
				$_theid=$eshopoptions['bank10']['id'];
			
				$_checkid=$p->ipn_data['trans_id'];
				$order_info = $wpdb->get_results($wpdb->prepare("SELECT * FROM `" . $wpdb->prefix.'eshop_orders' . "` WHERE `checkid`= %s  LIMIT 1", $_checkid), ARRAY_A);
				$save_data['status'] = 'Failed'; 
				if(strtolower($order_info[0]['status']) != 'completed')
				{

					@session_start();
					$amount =  $_SESSION['amount'];
					$rand =  $_SESSION['rand'];
					// Assign proper Status depending on received status
					$verify_valid = md5($eshopoptions['bank10']['id'].$amount.$eshopoptions['bank10']['api'].$rand) == $valid;
					if( $verify_valid)
					{
						$save_data['status'] = 'Completed';
					}
					else
					{
						$save_data['status'] = 'Failed'; 
					}
				}
				if(!empty($order_info) && isset($save_data['status'])) 
				{

					$wpdb->update($wpdb->prefix.'eshop_orders', $save_data, array('checkid' => $order_info[0]['checkid']), array('%s', '%s'));
					if($save_data['status'] == 'Completed')
					{
						$subject = __('بانک ۱۰ -','eshop');
						$subject .=__("پرداخت کامل شد ",'eshop');	
						eshop_mg_process_product($au,$_checkid);
						$subject .=" کد پی گیری :".$au;
						
						$array=eshop_rtn_order_details($_checkid);

						// email to business a complete copy of the notification from CCNow to keep!
						$body =  __("پرداخت شما توسط بانک ۱۰ تکمیل شده است",'eshop')."\n";
						$body .= "\n".__("from ",'eshop').$array['eemail'].__(" در روز  ",'eshop').date('m/d/Y');
						$body .= __(" ساعت ",'eshop').date('g:i A')."\n\n".__(' جزییات ','eshop').":\n";
						$body .= json_encode($_REQUEST);

						$headers=eshop_from_address();
						$eshopemailbus=$eshopoptions['bank10']['email'];
						$to = apply_filters('eshop_gatbank10_details_email', array($eshopemailbus));
						wp_mail($to, $subject, $body, $headers);
						wp_mail($order_info[0]['email'], $subject, $body, $headers);

						include_once(WP_PLUGIN_DIR.'/eshop/cart-functions.php');
						eshop_send_customer_email($_checkid, '151');
						echo 'ok';
					}else
					{
						$op = get_site_option( 'eshop_plugin_settings' );
						wp_redirect( home_url().'/?page_id='.$op['cart_cancel'].'err='.base64_encode('تراکنش ناموفق') );
						$_SESSION = '';
						session_destroy();
					}
				}
   			}
   		}
   		$_SESSION ='';
		session_destroy();
   		break;
}

?>