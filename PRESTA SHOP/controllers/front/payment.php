<?php
/*
* iPresta.ir
*
*
*  @author iPresta.ir - Danoosh Miralayi
*  @copyright  2014-2015 iPresta.ir
*/
class bank10paymentPaymentModuleFrontController extends ModuleFrontController
{
	
	public function __construct()
	{
		//$this->auth = true;
		parent::__construct();

		$this->context = Context::getContext();
		$this->ssl = true;
	}
	
	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		//$this->display_column_left = false;
		parent::initContent();
		$this->assignTpl();
	}
	
	public function postProcess()
	{
		if(Configuration::get('IPRESTA_PAYLINE_DEBUG'))
			@ini_set('display_errors', 'on');
	} 
	
	
	
	public function assignTpl()
	{
		$return = $this->module->prePayment();
		if($return === true)
			$this->context->smarty->assign('prepay', 'true');
		return $this->setTemplate('payment.tpl');
	}
	
}