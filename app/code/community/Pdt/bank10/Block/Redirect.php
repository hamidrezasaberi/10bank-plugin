<?php
/**
 * Magento
 * @category   Payment
 * @package    Pdt_bank10
 * @copyright  Copyright (c) 2015 10bank.ir
 * @see http://10bank.ir
 */
class Pdt_bank10_Block_Redirect extends Mage_Core_Block_Template
{
    /**
     * Return checkout session instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Return order instance
     *
     * @return Mage_Sales_Model_Order|null
     */
    protected function _getOrder()
    {
        if ($this->getOrder()) {
            return $this->getOrder();
        } elseif ($orderIncrementId = $this->_getCheckout()->getLastRealOrderId()) {
            return Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        } else {
            return null;
        }
    }

    /**
     * Get form data
     *
     * @return array
     */
    public function getFormData()
    {
			return null;
    }

    /**
     * Getting gateway url
     *
     * @return string
     */
    public function getFormAction()
    {
    		   
		
		$order = $this->_getOrder()->_data;
		$array = $this->_getOrder()->getPayment()->getMethodInstance()->getFormFields();
		$price = $array["price"];
			
		$seller_id = $this->_getOrder()->getPayment()->getMethodInstance()->getConfigData('seller_id');	
	
		$price = round($order["grand_total"],0);
		
		$callBackUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
		$callBackUrl .= "bank10/processing/response/?";
		
		$rand = substr(md5(time() . microtime()), 0, 10);
		Mage::getSingleton('core/session')->setMyRand($rand);
		
		$params = 'gateway_id=' . $seller_id . '&amount=' . $price . '&redirect_url=' . $callBackUrl . '&rand=' . $rand;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://10bank.ir/transaction/create');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($ch);
		curl_close($ch);
		 if ($res > 0 && is_numeric($res)) 
		 {
			$go = 'http://10bank.ir/transaction/submit?id='.$res;
			Mage::app()->getFrontController()->getResponse()->setRedirect($go);
			return null;
		 }
		 else
		 {
			 return $res;
		 }
    }
}
