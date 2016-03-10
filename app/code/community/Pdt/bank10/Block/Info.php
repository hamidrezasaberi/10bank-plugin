<?php
/**
 * Magento
 * @category   Payment
 * @package    Pdt_bank10
 * @copyright  Copyright (c) 2015 10bank.ir
 * @see http://10bank.ir
 */
class Pdt_bank10_Block_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('bank10/info.phtml');
    }

    public function getMethodCode()
    {
        return $this->getInfo()->getMethodInstance()->getCode();
    }

    public function toPdf()
    {
        $this->setTemplate('bank10/pdf/info.phtml');
        return $this->toHtml();
    }
}