<?php
/*
Plugin Name: درگاه پرداخت بانک ۱۰ 
Version: 2.0.0
Description:  درگاه پرداخت  <a href="http://10bank.ir/" target="_blank"> بانک ۱۰ </a> برای نسخه جدید افزونه Easy Digital Downloads 
Plugin URI: http://10bank.ir/
Author: 10bank.ir
Author URI: http://10bank.ir/
*/
if (!defined('ABSPATH')) exit;
@session_start();
if (!class_exists('EDD_bank10')) {
final class EDD_bank10 {
	public function __construct() {
		$this->hooks();
	}
	private function hooks() {
		add_filter('edd_payment_gateways', array($this, 'add_gateway'));
		add_action('edd_bank10_cc_form', array($this, 'cc_form'));
		add_action('edd_gateway_bank10', array($this, 'bank10_request'));
		add_action('init', array($this, 'bank10_verify'));
		add_filter('edd_settings_gateways', array($this, 'options'));
	}
	public function add_gateway($gateways) {
        global $edd_options;
		$gateways['bank10'] = array(
				'admin_label'		=>	'درگاه پرداخت بانک ۱۰',
				'checkout_label'	=>	$edd_options['bank10_name']
			);
		return $gateways;
	}
	public function cc_form() {
		return;
	}
	public function bank10_request($purchase_data) {
		global $edd_options;
		$payment_data = array(
			'price' => $purchase_data['price'], 
			'date' => $purchase_data['date'],
			'user_email' => $purchase_data['post_data']['edd_email'],
			'purchase_key' => $purchase_data['purchase_key'],
			'currency' => $edd_options['currency'],
			'downloads' => $purchase_data['downloads'],
			'cart_details' => $purchase_data['cart_details'],
			'user_info' => $purchase_data['user_info'],
			'status' => 'pending'
		);
		$payment = edd_insert_payment($payment_data);
		if ($payment) {
		    $_SESSION['bank10_payment'] = $payment;
			$rand = substr(md5(time() . microtime()), 0, 10);
		    $_SESSION['bank10_rand'] = $rand;
            EDD()->session->set( 'edd_bank10_payment_sess', $payment );
            set_transient( 'edd_bank10_payment_transient', $payment, 60*60*12 );
			$amount = (int) $purchase_data['price'];
			$_SESSION['bank10_price'] = $amount;
            EDD()->session->set( 'edd_bank10_price_sess', $amount );
            set_transient( 'edd_bank10_price_transient', $amount, 60*60*12 );
            $gateway_id = $edd_options['bank10_id'];
            $redirect_url = add_query_arg('returntobank10', 'bank10', get_permalink($edd_options['success_page']));
			
			$params = 'gateway_id=' . $gateway_id . '&amount=' . $amount . '&redirect_url=' . urlencode($redirect_url) . '&rand=' . $rand;
		
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'http://10bank.ir/transaction/create');
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$res = curl_exec($ch);
			curl_close($ch);
			if ($res > 0 && is_numeric($res)) 
			{
				EDD()->session->set( 'edd_bank10_aut_sess', $res );
				set_transient( 'edd_bank10_aut_transient', $res, 60*60*12 );
				edd_insert_payment_note( $payment, ' شناسه = ' . $payment . '| شناسه پیگیری = ' . $res . ' | قیمت = ' . $amount . ' | وضعیت = ارسال شده به بانک ' );
				$go = "http://10bank.ir/transaction/submit?id=" . $res;
				header("Location: $go");
				exit;
			} 
			else 
			{
				edd_update_payment_status( $payment, 'abandoned' );
				edd_insert_payment_note( $payment, 'خطا  ' . $message . ' | شناسه = ' . $payment . ' | قیمت = ' . $amount . ' | وضعیت = خطا در ارسال به سمت بانک ' );
				wp_die( ' رخ داده است یعنی ' . $message );
				exit;
			}
		} else {
			edd_send_back_to_checkout('?payment-mode=' . $purchase_data['post_data']['edd-gateway']);
		}
	}
	public function bank10_verify() 
	{
		global $edd_options;
		if ( $_GET['returntobank10'] == 'bank10' ) 
		{
			$session = edd_get_purchase_session();
			$payment_id = edd_get_purchase_id_by_key( $session['purchase_key'] );
			$price = edd_get_payment_amount($payment_id);
			if ( EDD()->session->get( 'edd_bank10_payment_sess' ))
			{
				$payment = EDD()->session->get( 'edd_bank10_payment_sess' );
			}
			elseif ($payment_id){
				$payment = $payment_id;
			}
			elseif ($_SESSION['bank10_payment']){
				$payment = $_SESSION['bank10_payment'];
			}
			else 
			{
				$payment= get_transient( 'edd_bank10_payment_transient' );
			}		
			if ( EDD()->session->get( 'edd_bank10_price_sess' ))
			{
				$amount = EDD()->session->get( 'edd_bank10_price_sess' );
			}
			elseif ($price){
				$amount = $price;
			}
			elseif ($_SESSION['bank10_price']){
				$amount = $_SESSION['bank10_price'];
			}
			else 
			{
				$amount= get_transient( 'edd_bank10_price_transient' );
			}

			$gateway_api = $edd_options['bank10_api'];		
			$gateway_id = $edd_options['bank10_id'];		

			$rand = $_SESSION['bank10_rand'];
			$trans_id = isset($_GET['trans_id']) ? (int) ($_GET['trans_id']) : '';
			$valid = isset($_GET['valid']) ? $_GET['valid'] : '';
			$verify_valid = md5($gateway_id . $amount . $gateway_api . $rand) == $valid;
			
			$message = '';
			if($verify_valid) 
			{
				edd_insert_payment_note( $payment, 'تراکنش موفقیت آمیز بوده است  = ' . $payment . '| شناسه پیگیری = ' . $trans_id );
				edd_update_payment_status( $payment, 'publish' );
				edd_empty_cart();
				edd_send_to_success_page();
			}
			else 
			{
				edd_insert_payment_note( $payment, 'شناسه |تراکنش ناموفق بوده است =' . $payment );
				edd_update_payment_status($payment, 'failed');
				wp_redirect(get_permalink($edd_options['failure_page']));
				exit;
			}
		}
	}
	public function options($settings) {
         $bank10_settings = array (
			array (
				'id'	=>		'bank10_settings',
				'name'	=>		'<strong>تنظیمات درگاه پرداخت بانک ۱۰</strong>',
				'desc'	=>		'در اینجا شما می توانید تنظیمات درگاه را وارد کنید',
				'type'	=>		'header'
			),
			array (
				'id'	=>		'bank10_api',
				'name'	=>		'api درگاه را وارد کنید',
				'type'	=>		'text',
				'size'	=>		'regular'
			),
			array (
				'id'	=>		'bank10_id',
				'name'	=>		'id درگاه را وارد کنید',
				'type'	=>		'text',
				'size'	=>		'regular'
			),
			array (
				'id'	=>		'bank10_name',
				'name'	=>		'نام نمایشی درگاه',
				'type'	=>		'text',
				'size'	=>		'regular'
			)
                      
		);
		return array_merge($settings, $bank10_settings);
	}
}
}
if ( !function_exists( 'edd_rial' ) ) {
	function edd_rial( $formatted, $currency, $price ) {
		return $price . ' ریال'; }
	add_filter( 'edd_rial_currency_filter_after', 'edd_rial', 10, 3 );
}
new EDD_bank10();