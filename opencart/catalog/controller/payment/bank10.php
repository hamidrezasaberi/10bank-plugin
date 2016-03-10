<?php
class ControllerPaymentbank10 extends Controller {
	protected function index() {
		$this->language->load('payment/bank10');
    	$this->data['button_confirm'] = $this->language->get('button_confirm');
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$this->load->library('encryption');
		$encryption = new Encryption($this->config->get('config_encryption'));
		$amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
		$bank10_id =$this->config->get('bank10_id');
		
		//$this->data['back'] = $this->url->link('checkout/payment', '', 'SSL');

		if($this->currency->getCode()!='RLS') {
		    $amount = $amount * 10;
	    }
		
		//$this->data['order_id'] = $this->session->data['order_id'];
		$redirect_url  =  ($this->url->link('payment/bank10/callback','', 'SSL'));
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
		if ($res > 0 && is_numeric($res)) 
		{
			$this->data['action'] =  "http://10bank.ir/transaction/submit?id=" . $res;
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/bank10.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/payment/bank10.tpl';
			} else {
				$this->template = 'default/template/payment/bank10.tpl';
			}
		}
		else
		{
			$this->data['err'] = $res;
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/bank10error.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/payment/bank10error.tpl';
			} else {
				$this->template = 'default/template/payment/bank10error.tpl';
			}
		}
		$this->render();
	}

	public function callback() 
	{
		$this->language->load('payment/bank10x');
		$order_id = $this->session->data['order_id'];
		$referenceId = isset($this->request->get['trans_id']) ? $this->request->get['trans_id'] : null;
		$valid = isset($this->request->get['valid']) ? $this->request->get['valid']: null;
		
		$this->document->setTitle($this->language->get('text_heading'));
		$this->data['text_wait'] = $this->language->get('text_wait');
		$this->data['text_heading'] = $this->language->get('text_heading');
		$this->data['text_results'] = $this->language->get('text_results');
		$this->data['button_confirm'] = $this->language->get('button_confirm');
		$this->data['button_continue'] = $this->language->get('button_continue');
		$this->data['breadcrumbs'] = array();
		$this->data['breadcrumbs'][] = array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/home', '', 'SSL'), 'separator' => false);
		$this->data['breadcrumbs'][] = array('text' => $this->language->get('text_heading'), 'href' => $this->url->link('payment/bank10/callback', '', 'SSL'), 'separator' => $this->language->get('text_separator'));
		$this->data['error_warning'] = '';
		
		if($valid)
		{
			$bank10_id = $this->config->get('bank10_id');
			$bank10_api = $this->config->get('bank10_api');
			
			//
			$this->load->model('checkout/order');
			$order_info = $this->model_checkout_order->getOrder($order_id);
			$Amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);		
			$amount = $Amount/$order_info['currency_value'];
			if($this->currency->getCode()!='RLS') 
			{
				$amount = $amount * 10;
			}
			if ($order_info) 
			{
				$rand = $this->session->data['rand'];
				$verify_valid = md5($bank10_id . $amount . $bank10_api . $rand) == $valid;
				
				if ($verify_valid)
				{
					$this->data['authority'] = $referenceId;
					$this->data['continue'] = $this->url->link('checkout/success');
					$this->model_checkout_order->confirm($order_id, $this->config->get('bank10_order_status_id'), $this->data['text_results'] . ' ' . $this->data['authority'], true);
				}
				else
				{
					$this->data['error_warning'] = $this->language->get('error_veryfi');
					$this->data['continue'] = $this->url->link('checkout/checkout');
				}
			}
			else 
			{
				$this->data['error_warning'] =$this->language->get('error_order_id');
				$this->data['continue'] = $this->url->link('checkout/checkout');
			}
		} 
		else 
		{
			$this->data['error_warning'] ='پرداخت موفق آمیز نبود';
			$this->data['continue'] = $this->url->link('checkout/checkout');
		}
		
		/* Template */
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/bank10_confirm.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/bank10_confirm.tpl';
		} else {
			$this->template = 'default/template/payment/bank10_confirm.tpl';
		}
	
		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'
		);
		
		$this->response->setOutput($this->render());
	}

}
?>