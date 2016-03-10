<?php
/**
 * Magento
 * @category   Payment
 * @package    Pdt_bank10
 * @copyright  Copyright (c) 2015 10bank.ir
 * @see http://10bank.ir
 */
class Pdt_bank10_Block_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('bank10/form.phtml');
    }

    public function getPaymentImageSrc()
    {
    	$locale = strtolower(Mage::app()->getLocale()->getLocaleCode());
    	$imgSrc = $this->getSkinUrl('images/bank10/'.$locale.'_outl.gif');

    	if (!file_exists(Mage::getDesign()->getSkinBaseDir().'/images/bank10/'.$locale.'_outl.gif')) {
    		$imgSrc = $this->getSkinUrl('images/bank10/intl_outl.gif');
    	}
    	return $imgSrc;
    }
}