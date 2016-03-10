<?php
/*
* iPresta.ir
*
*
*  @author iPresta.ir - Danoosh Miralayi
*  @copyright  2014-2015 iPresta.ir
*/
class bank10paymentValidationModuleFrontController extends ModuleFrontController
{
    private $res_num = '';
    private $ref_num = '';
    //private $state = '';

	public function __construct()
	{
		//$this->auth = true;
		parent::__construct();

		$this->context = Context::getContext();
		$this->ssl = true;

	}
	
	public function postProcess()
	{
		if(Configuration::get('IPRESTA_BAZPARDAKHT_DEBUG'))
			@ini_set('display_errors', 'on');
		$this->ref_num = Tools::getValue('trans_id');
		$this->res_num = Tools::getValue('id_get');
		//$this->state = Tools::getValue('status');
	}

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		parent::initContent();
		
		if(empty($this->res_num) || empty($this->ref_num))
			$this->errors[] = $this->module->l('Payment Information is incorrect.');
		elseif(empty($this->context->cart->id))
			$this->errors[] = $this->module->l('Your cart is empty.');
		if(!count($this->errors))
		{
			$validate = $this->module->verify($this->res_num,$this->ref_num);

			if($validate === true)
				$paid = $this->module->validateOrder((int)$this->context->cart->id, _PS_OS_PAYMENT_, (float)$this->context->cart->getOrderTotal(true, 3), $this->module->displayName, $this->module->l('reference').': '.$this->ref_num , array(),(int)$this->context->currency->id, false, $this->context->customer->secure_key);

			elseif($validate === false)
				$paid = $this->module->validateOrder((int)$this->context->cart->id, _PS_OS_ERROR_, (float)$this->context->cart->getOrderTotal(true, 3), $this->module->displayName, $this->module->l('reference').': '.$this->ref_num , array(),(int)$this->context->currency->id, false, $this->context->customer->secure_key);

			$this->context->cookie->__unset("RefId");
			$this->context->cookie->__unset("amount");

            if(isset($paid) && $paid)
                Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?key='.$this->context->customer->secure_key.'&id_cart='.(int)$this->context->cart->id.'&id_module='.(int)$this->module->id.'&id_order='.(int)$this->module->currentOrder.'&res_num='.$this->res_num.'&ref_num='.$this->ref_num);
		}
		$this->assignTpl();
	}
	

	
	
	
	
	public function assignTpl()
	{
		$this->context->smarty->assign(array(
            'access' => 'denied',
            'ver' => $this->module->version,
            'ref_num' => $this->ref_num,
            'res_num' => $this->res_num,
			'path' => $this->module->displayName
	));
		return $this->setTemplate('validation.tpl');
	}
	
}