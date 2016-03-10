<?php 
/* www.bank10.ir */
class ControllerPaymentbank10 extends Controller {
	private $error = array(); 

	public function index() {
		$this->load->language('payment/bank10');


		$this->document->title = $this->language->get('heading_title');
		
		$this->load->model('setting/setting');
			
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
			$this->load->model('setting/setting');
			
			$this->model_setting_setting->editSetting('bank10', $this->request->post);				
			
			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->https('extension/payment'));
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');
		
		$this->data['entry_ID'] = $this->language->get('entry_ID');
		$this->data['entry_API'] = $this->language->get('entry_API');
		$this->data['entry_order_status'] = $this->language->get('entry_order_status');		
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		
		$this->data['help_encryption'] = $this->language->get('help_encryption');
		
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		$this->data['tab_general'] = $this->language->get('tab_general');

		$this->data['error_warning'] = @$this->error['warning'];
		$this->data['error_ID'] = @$this->error['ID'];
		$this->data['error_API'] = @$this->error['API'];

		$this->document->breadcrumbs = array();

   		$this->document->breadcrumbs[] = array(
       		'href'      => $this->url->https('common/home'),
       		'text'      => $this->language->get('text_home'),
      		'separator' => FALSE
   		);

   		$this->document->breadcrumbs[] = array(
       		'href'      => $this->url->https('extension/payment'),
       		'text'      => $this->language->get('text_payment'),
      		'separator' => ' :: '
   		);

   		$this->document->breadcrumbs[] = array(
       		'href'      => $this->url->https('payment/bank10'),
       		'text'      => $this->language->get('heading_title'),
      		'separator' => ' :: '
   		);
				
		$this->data['action'] = $this->url->https('payment/bank10');
		
		$this->data['cancel'] = $this->url->https('extension/payment');

		if (isset($this->request->post['bank10_ID'])) {
			$this->data['bank10_ID'] = $this->request->post['bank10_ID'];
		} else {
			$this->data['bank10_ID'] = $this->config->get('bank10_ID');
		}
		
		if (isset($this->request->post['bank10_API'])) {
			$this->data['bank10_API'] = $this->request->post['bank10_API'];
		} else {
			$this->data['bank10_API'] = $this->config->get('bank10_API');
		}
		
		
		if (isset($this->request->post['bank10_order_status_id'])) {
			$this->data['bank10_order_status_id'] = $this->request->post['bank10_order_status_id'];
		} else {
			$this->data['bank10_order_status_id'] = $this->config->get('bank10_order_status_id'); 
		} 

		$this->load->model('localisation/order_status');
		
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->post['bank10_status'])) {
			$this->data['bank10_status'] = $this->request->post['bank10_status'];
		} else {
			$this->data['bank10_status'] = $this->config->get('bank10_status');
		}
		
		if (isset($this->request->post['bank10_sort_order'])) {
			$this->data['bank10_sort_order'] = $this->request->post['bank10_sort_order'];
		} else {
			$this->data['bank10_sort_order'] = $this->config->get('bank10_sort_order');
		}
		
		$this->id       = 'content';
		$this->template = 'payment/bank10.tpl';
		$this->layout   = 'common/layout';
		
 		$this->render();
	}

	private function validate() {

		if (!$this->user->hasPermission('modify', 'payment/bank10')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!@$this->request->post['bank10_ID']) {
			$this->error['ID'] = $this->language->get('error_ID');
		}
		if (!@$this->request->post['bank10_API']) {
			$this->error['API'] = $this->language->get('error_API');
		}

		
		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}	
	}
}
?>