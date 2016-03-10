<?php
/**
 * Magento
 * @category   Payment
 * @package    Pdt_bank10
 * @copyright  Copyright (c) 2015 10bank.ir
 * @see http://10bank.ir
 */
class Pdt_bank10_Model_Config_Backend_Sellerapi extends Mage_Core_Model_Config_Data
{
    /**
     * Verify seller id in ClickandBuy registration system to reduce configuration failures (experimental)
     *
     * @return Pdt_bank10_Model_bank10_Config_Backend_Sellerid
     */
    protected function _beforeSave()
    {
    	try {
    	    if ($this->getValue()) {
    			$client = new Varien_Http_Client();
    			$client->setUri((string)Mage::getConfig()->getNode('pdt/bank10/verify_url'))
    				->setConfig(array('timeout'=>10,))
    				->setHeaders('accept-encoding', '')
    				->setParameterPost('seller_api', $this->getValue())
    				->setMethod(Zend_Http_Client::POST);
    			$response = $client->request();
//    			$responseBody = $response->getBody();
//    			if (empty($responseBody) || $responseBody != 'VERIFIED') {
    				// verification failed. throw error message (not implemented yet).
//    			}

    			// okay, seller id verified. continue saving.
    	    }
		} catch (Exception $e) {
			// verification system unavailable. no further action.
		}

        return $this;
    }
}
