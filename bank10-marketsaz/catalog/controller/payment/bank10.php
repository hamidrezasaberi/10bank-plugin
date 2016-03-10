<?php

class ControllerPaymentbank10 extends Controller {
	protected function index() {


    	$this->data['button_confirm'] = $this->language->get('button_confirm');
		$this->data['button_back'] = $this->language->get('button_back');
		
		$this->load->model('checkout/order');
		
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		
		$this->load->library('encryption');
		
		$encryption = new Encryption($this->config->get('config_encryption'));
		
		if($this->currency->getCode()!='RLS') {
		 $amount = $amount * 10;
		}
		
		$this->data['Amount'] = @$this->currency->format($order_info['total'], $order_info['currency'], $order_info['value'], FALSE);
		$this->data['PIN']=$this->config->get('bank10_PIN');
		
		$this->data['return'] = $this->url->https('checkout/success');
		$this->data['cancel_return'] = $this->url->https('checkout/payment');

		$this->data['back'] = $this->url->https('checkout/payment');

		

		$amount = intval($this->data['Amount']);
		$redirect_url = urlencode($this->url->https('payment/bank10/callback&order_id='.$encryption->encrypt($this->session->data['order_id'])));
		$bank10_id =$this->config->get('bank10_ID');
		$rand = substr(md5(time() . microtime()), 0, 10);
		$this->session->data['rand'] = $rand;
		$params = 'gateway_id=' . $bank10_id . '&amount=' . $amount . '&redirect_url=' . $redirect_url . '&rand=' . $rand;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://10bank.ir/transaction/create');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($ch);
		curl_close($ch);
		
		
		if($res > 0 && is_numeric($res)){

			$this->data['action'] = "http://10bank.ir/transaction/submit?id=$res";
		
		} else {
			
			echo  $res;
			die();
		}

//
		
		$this->id       = 'payment';
		$this->template = $this->config->get('config_template') . 'payment/bank10.tpl';
		
		$this->render();		
}

	public function callback() 
	{
		$this->language->load('payment/bank10');
		
		$order_id = $this->session->data['order_id'];
		$referenceId = isset($this->request->get['trans_id']) ? $this->request->get['trans_id'] : null;
		$valid = isset($this->request->get['valid']) ? $this->request->get['valid']: null;

	
		
		$this->data['text_wait'] = $this->language->get('text_wait');
		$this->data['text_heading'] = $this->language->get('text_heading');
		$this->data['text_results'] = $this->language->get('text_results');
		$this->data['button_confirm'] = $this->language->get('button_confirm');
		$this->data['button_continue'] = $this->language->get('button_continue');
		$this->data['breadcrumbs'] = array();
		$this->data['breadcrumbs'][] = array('text' => $this->language->get('text_home'), 'href' => $this->url->https('common/home', '', 'SSL'), 'separator' => false);
		$this->data['breadcrumbs'][] = array('text' => $this->language->get('text_heading'), 'href' => $this->url->https('payment/bank10/callback', '', 'SSL'), 'separator' => $this->language->get('text_separator'));
		$this->data['error_warning'] = '';
		
		if($valid)
		{

			$bank10_ID = $this->config->get('bank10_ID');
			$bank10_API=$this->config->get('bank10_API');
			$rand = $this->session->data['rand'];
			
			$this->load->model('checkout/order');
			$order_info = $this->model_checkout_order->getOrder($order_id);
			
			$amount = @$this->currency->format($order_info['total'], $order_info['currency'], $order_info['value'], FALSE);	
			
			if($this->currency->getCode()!='RLS') 
			{
				$amount = $amount * 10;
			}
			
			
			if ($order_info) 
			{
				$verify_valid = md5($bank10_ID . $amount . $bank10_API . $rand) == $valid;
				
				if ($verify_valid)
				{
					$this->data['authority'] = $referenceId;
					$this->data['continue'] = $this->url->https('checkout/success');
					$this->model_checkout_order->confirm($order_id, $this->config->get('bank10_order_status_id'),' referenceId:'.$referenceId);
					$this->response->setOutput('<html><head><meta http-equiv="refresh" CONTENT="2; url=index.php?route=checkout/success"></head><body><table border="0" width="100%"><tr><td>&nbsp;</td><td style="border: 1px solid gray; font-family: tahoma; font-size: 14px; direction: rtl; text-align: right;">&#1576;&#1575; &#1578;&#1588;&#1705;&#1585; &#1662;&#1585;&#1583;&#1575;&#1582;&#1578; &#1578;&#1705;&#1605;&#1740;&#1604; &#1588;&#1583;. &#1604;&#1591;&#1601;&#1575; &#1670;&#1606;&#1583; &#1604;&#1581;&#1592;&#1607; &#1589;&#1576;&#1585; &#1705;&#1606;&#1740;&#1583; &#1608; &#1740;&#1575; <a href="index.php?route=checkout/success"><b>&#1575;&#1740;&#1606;&#1580;&#1575; &#1705;&#1604;&#1740;&#1705; &#1606;&#1605;&#1575;&#1740;&#1740;&#1583;</b></a></td><td>&nbsp;</td></tr></table></body></html>');
					$this->session->data['rand'] = 1;
				}
				else
				{
					$this->data['error_warning'] = $this->language->get('error_veryfi');
					$this->data['continue'] = $this->url->https('checkout/checkout');
					$this->response->setOutput('<html><body><table border="0" width="100%"><tr><td>&nbsp;</td><td style="border: 1px solid gray; font-family: tahoma; font-size: 14px; direction: rtl; text-align: right;">&#1582;&#1591;&#1575; &#1583;&#1585; &#1662;&#1585;&#1583;&#1575;&#1582;&#1578; : '.$this->data['error_warning'].'<br /><br /><a href="index.php?route=checkout/cart"><b>&#1576;&#1575;&#1586;&#1711;&#1588;&#1578; &#1576;&#1607; &#1601;&#1585;&#1608;&#1588;&#1711;&#1575;&#1607;</b></a></td><td>&nbsp;</td></tr></table></body></html>');
				}
			}
			else 
			{
				$this->data['error_warning'] ='سفارش یافت نشد';
				$this->data['continue'] = $this->url->https('checkout/checkout');
				$this->response->setOutput('<html><body><table border="0" width="100%"><tr><td>&nbsp;</td><td style="border: 1px solid gray; font-family: tahoma; font-size: 14px; direction: rtl; text-align: right;">&#1582;&#1591;&#1575; &#1583;&#1585; &#1662;&#1585;&#1583;&#1575;&#1582;&#1578; : '.$this->data['error_warning'].'<br /><br /><a href="index.php?route=checkout/cart"><b>&#1576;&#1575;&#1586;&#1711;&#1588;&#1578; &#1576;&#1607; &#1601;&#1585;&#1608;&#1588;&#1711;&#1575;&#1607;</b></a></td><td>&nbsp;</td></tr></table></body></html>');
			}
		} 
		else 
		{
			$this->data['error_warning'] = 'تراکنش ناموفق بوده است';
			$this->data['continue'] = $this->url->https('checkout/checkout');
			$this->response->setOutput('<html><body><table border="0" width="100%"><tr><td>&nbsp;</td><td style="border: 1px solid gray; font-family: tahoma; font-size: 14px; direction: rtl; text-align: right;">&#1582;&#1591;&#1575; &#1583;&#1585; &#1662;&#1585;&#1583;&#1575;&#1582;&#1578; : '.$this->data['error_warning'].'<br /><br /><a href="index.php?route=checkout/cart"><b>&#1576;&#1575;&#1586;&#1711;&#1588;&#1578; &#1576;&#1607; &#1601;&#1585;&#1608;&#1588;&#1711;&#1575;&#1607;</b></a></td><td>&nbsp;</td></tr></table></body></html>');
		}
		
		

	}
}
?>
