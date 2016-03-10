<?php
/**
 * Magento
 * @category   Payment
 * @package    Pdt_bank10
 * @copyright  Copyright (c) 2015 10bank.ir
 * @see http://10bank.ir
 */
class Pdt_bank10_ProcessingController extends Mage_Core_Controller_Front_Action
{
    protected $_successBlockType  = 'bank10/success';
    protected $_failureBlockType  = 'bank10/failure';

    protected $_order = NULL;
    protected $_paymentInst = NULL;


    /**
     * Get singleton of Checkout Session Model
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }
	public function mkzero($z){
		$str = "1";
		while($z > 0){
			$str .= "0";
			$z -= 1;
		}
		return $str;	
	} 
    public function redirectAction()
    {
        try {
            $session = $this->_getCheckout();

            $order = Mage::getModel('sales/order');
            $order->loadByIncrementId($session->getLastRealOrderId());
            if (!$order->getId()) {
                Mage::throwException('No order for processing found');
            }
            if ($order->getState() != Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
                $order->setState(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    $this->_getPendingPaymentStatus(),
                    Mage::helper('bank10')->__('Customer was redirected to bank10 gateway.')
                )->save();
            }

            if ($session->getQuoteId() && $session->getLastSuccessQuoteId()) {
                $session->setClickandbuyQuoteId($session->getQuoteId());
                $session->setClickandbuySuccessQuoteId($session->getLastSuccessQuoteId());
                $session->setClickandbuyRealOrderId($session->getLastRealOrderId());
                $session->getQuote()->setIsActive(false)->save();
                $session->clear();
            }

            $this->loadLayout();
            $this->renderLayout();
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getCheckout()->addError($e->getMessage());
        } catch(Exception $e) {
            Mage::logException($e);
        }
		
		
        //$this->_redirect('checkout/cart');
    }
    public function responseAction()
    {
	
		$referenceId =$this->getRequest()->getParam('trans_id');
		$valid = $this->getRequest()->getParam('valid');
		if ($valid)
		{
			
			$session = $this->_getCheckout();
			
			$orderid = $session->getClickandbuyRealOrderId();
			
			$this->_order = Mage::getModel('sales/order')->loadByIncrementId($orderid);			
			$this->_paymentInst = $this->_order->getPayment()->getMethodInstance();
			
			$quote = $orderid = $this->_order->getData();
			$price = number_format($quote["grand_total"],0,'.',''); ;
			
			$seller_id = $this->_paymentInst->getConfigData('seller_id');
			$seller_api = $this->_paymentInst->getConfigData('seller_api');
			$rand = Mage::getSingleton('core/session')->getMyRand();
			$verify_valid = md5($seller_id.$price.$seller_api.$rand) == $valid;
			if ($verify_valid )
			{
				$this->_order->getPayment()->setTransactionId($referenceId);
          		$this->_order->getPayment()->setLastTransId($referenceId);
				
				// create invoice
				if ($this->_order->canInvoice()) {
					$invoice = $this->_order->prepareInvoice();
					$invoice->register()->capture();
					Mage::getModel('core/resource_transaction')
						->addObject($invoice)
						->addObject($invoice->getOrder())
						->save();
				}
	
				// add order history comment
				$this->_order->addStatusToHistory($this->_paymentInst->getConfigData('order_status'), Mage::helper('bank10')->__('Payment complete'));
	
				// send email
				$this->_order->sendNewOrderEmail();
				$this->_order->setEmailSent(true);
	
				$this->_order->save();
	
				// redirect to success page
				$this->getResponse()->setBody(
					$this->getLayout()
						->createBlock($this->_successBlockType)
						->setOrder($this->_order)
						->toHtml());
				
			}else{
				$this->_redirect('bank10/processing/caberror');
			}			
		}else{
			$this->_redirect('bank10/processing/caberror');
		}
    }
    public function successAction()
    {
        try {
            $session = $this->_getCheckout();
            $session->unsClickandbuyRealOrderId();
            $session->setQuoteId($session->getClickandbuyQuoteId(true));
            $session->setLastSuccessQuoteId($session->getClickandbuySuccessQuoteId(true));
            $this->_redirect('checkout/onepage/success');
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getCheckout()->addError($e->getMessage());
        } catch(Exception $e) {
            Mage::logException($e);
        }
        $this->_redirect('checkout/cart');
    }

    /**
     * ClickandBuy sub error action
     */
    public function caberrorAction()
    {
        // set quote to active
        $session = $this->_getCheckout();
        if ($quoteId = $session->getClickandbuyQuoteId()) {
            $quote = Mage::getModel('sales/quote')->load($quoteId);
            if ($quote->getId()) {
                $quote->setIsActive(true)->save();
                $session->setQuoteId($quoteId);
            }
        }

        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock($this->_failureBlockType)
                ->setOrder($this->_order)
                ->toHtml()
        );
    }

    /**
     * ClickandBuy sub success action
     */
    public function cabsuccessAction()
    {
       
    }

    /**
     * Checking GET and SERVER variables.
     * Creating invoice if payment was successfull or cancel order if payment was declined
     */
    protected function _checkReturnedParams()
    {
        // get request variables
        $externalBDRID = $this->getRequest()->getParam('externalBDRID');
        $request = $this->getRequest()->getServer();

        if (!isset($request['HTTP_X_USERID']) || !isset($request['HTTP_X_PRICE']) || !isset($request['HTTP_X_CURRENCY']) || !isset($request['HTTP_X_TRANSACTION']) || !isset($request['HTTP_X_CONTENTID']) || !isset($request['HTTP_X_USERIP']))
            throw new Exception('Request doesn\'t contain all required C&B elements.', 10);

        // validate request ip coming from ClickandBuy proxy
        $helper = Mage::helper('core/http');
        if (method_exists($helper, 'getRemoteAddr')) {
            $remoteAddr = $helper->getRemoteAddr();
        } else {
            $request = $this->getRequest()->getServer();
            $remoteAddr = $request['REMOTE_ADDR'];
        }
        if (substr($remoteAddr,0,11) != '217.22.128.') {
            throw new Exception('IP can\'t be validated as ClickandBuy-IP.', 20);
        }

        // validate ClickandBuy user id
        if (empty($request['HTTP_X_USERID']) || is_nan($request['HTTP_X_USERID']))
            throw new Exception('Invalid ClickandBuy-UID.', 30);

        // check order id
		list($orderId) = explode('-', $externalBDRID, 2);
        if (empty($orderId) || strlen($orderId) > 50)
            throw new Exception('Missing or invalid order ID', 30);

        // load order for further validation
        $this->_order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
		if (!$this->_order->getId())
			throw new Exception('Order ID not found.', 35);

        // check transaction amount and currency
		if ($this->_order->getPayment()->getMethodInstance()->getConfigData('use_store_currency')) {
        	$price      = number_format($this->_order->getGrandTotal()*100,0,'.','');
        	$currency   = $this->_order->getOrderCurrencyCode();
    	} else {
        	$price      = number_format($this->_order->getBaseGrandTotal()*100,0,'.','');
        	$currency   = $this->_order->getBaseCurrencyCode();
    	}

		if (intval($price) != intval($request['HTTP_X_PRICE']/1000))
			throw new Exception('Transaction amount doesn\'t match.', 40);
		if ($currency != $request['HTTP_X_CURRENCY'])
			throw new Exception('Transaction currency doesn\'t match.', 50);

        return $externalBDRID;
    }

    protected function _getPendingPaymentStatus()
    {
        return Mage::helper('bank10')->getPendingPaymentStatus();
    }
}
