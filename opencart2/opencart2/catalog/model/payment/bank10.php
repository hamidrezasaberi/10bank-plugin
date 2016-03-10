<?php
class ModelPaymentbank10 extends Model {
  	public function getMethod() {
		$this->load->language('payment/bank10');

		if ($this->config->get('bank10_status')) {
      		  	$status = TRUE;
      	} else {
			$status = FALSE;
		}
		
		$method_data = array();
	
		if ($status) {  
      		$method_data = array( 
        		'code'         => 'bank10',
        		'title'      => $this->language->get('text_title'),
				'sort_order' => $this->config->get('bank10_sort_order')
      		);
    	}
   
    	return $method_data;
  	}
}
?>