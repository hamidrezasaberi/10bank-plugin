<?php
class ControllerPaymentbank10 extends Controller {
	public function index() {
		$this->language->load('payment/bank10');
    	$data['button_confirm'] = $this->language->get('button_confirm');

		$data['text_loading'] = $this->language->get('text_loading');
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$bank10_id=$this->config->get('bank10_ID');
		$redirect_url = ($this->url->link('payment/bank10/callback','', 'SSL'));
		$amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);

		if($this->currency->getCode() == 'TOM') {
			 $amount =  $amount * 10;
		}
		
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
			$data['action'] =  "http://bank10.ir/transaction/submit?id=" . $res;
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/bank10.tpl')) {

				return $this->load->view($this->config->get('config_template') . '/template/payment/bank10.tpl', $data);

			} else {

				return $this->load->view('default/template/payment/bank10.tpl', $data);

			}
		}else
		{
			echo '<font color="red">خطا :<b>'.$res.'<b></font>';
		}
	}
	public function callback() 
	{
		$this->language->load('payment/bank10');
		$order_id = $this->session->data['order_id'];
		$referenceId = isset($this->request->get['trans_id']) ? $this->request->get['trans_id'] : null;
		$valid = isset($this->request->get['valid']) ? $this->request->get['valid']: null;
		
		$this->document->setTitle($this->language->get('text_heading'));
		$data['text_wait'] = $this->language->get('text_wait');
		$data['text_heading'] = $this->language->get('text_heading');
		$data['text_results'] = $this->language->get('text_results');
		$data['button_confirm'] = $this->language->get('button_confirm');
		$data['button_continue'] = $this->language->get('button_continue');
		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/home', '', 'SSL'), 'separator' => false);
		$data['breadcrumbs'][] = array('text' => $this->language->get('text_heading'), 'href' => $this->url->link('payment/bank10/callback', '', 'SSL'), 'separator' => $this->language->get('text_separator'));
		$data['error_warning'] = '';
		if($valid)
		{
			$bank10_ID = $this->config->get('bank10_ID');
			$bank10_PIN = $this->config->get('bank10_PIN');
			
			$this->load->model('checkout/order');
			$order_info = $this->model_checkout_order->getOrder($order_id);
			$Amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);		
			$amount = $Amount/$order_info['currency_value'];
			if(strtolower($this->currency->getCode()) == 'tom') 
			{
				$amount = $amount * 10;
			}

			if ($order_info) 
			{
				$rand = $this->session->data['rand'];
				$verify_valid = md5($bank10_ID . $amount . $bank10_PIN . $rand) == $valid;
				
				if ($verify_valid)
				{
					$data['authority'] = $referenceId;
					$data['continue'] = $this->url->link('checkout/success');
					$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('bank10_order_status_id'), $referenceId, true);
					$data['error_warning'] = NULL;

						$data['text_success'] = $this->language->get('text_success');

						$data['text_refer_number'] = $this->language->get('text_results');

						$data['refer_number'] = $referenceId;

						$data['button_continue'] = $this->language->get('button_complete');

						$data['continue'] = $this->url->link('checkout/success');
				}
				else
				{
					$data['error_warning'] = $this->language->get('error_veryfi');
					$data['continue'] = $this->url->link('checkout/checkout');
				}
			}
			else 
			{
				$data['error_warning'] =$this->language->get('error_order_id');
				$data['continue'] = $this->url->link('checkout/checkout');
			}
		} 
		else 
		{
			$data['error_warning'] = 'پرداخت کامل نشده است';
			$data['continue'] = $this->url->link('checkout/checkout');
		}
		
			$data['column_left'] = $this->load->controller('common/column_left');

			$data['column_right'] = $this->load->controller('common/column_right');

			$data['content_top'] = $this->load->controller('common/content_top');

			$data['content_bottom'] = $this->load->controller('common/content_bottom');

			$data['footer'] = $this->load->controller('common/footer');

			$data['header'] = $this->load->controller('common/header');
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/bank10_confirm.tpl')) {

				$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/payment/bank10_confirm.tpl', $data));

			} else {

				$this->response->setOutput($this->load->view('default/template/payment/bank10_confirm.tpl', $data));

			}
		
	}

}
?>