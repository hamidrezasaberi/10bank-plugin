<?php
/**
 * Magento
 * @category   Payment
 * @package    Pdt_bank10
 * @copyright  Copyright (c) 2015 10bank.ir
 * @see http://10bank.ir
 */
class Pdt_bank10_Block_Success extends Mage_Core_Block_Template
{
    protected function _toHtml()
    {
        $successUrl = Mage::getUrl('*/*/success', array('_secure'=>true));

        $html	= '<html>'
        		. '<meta http-equiv="refresh" content="0; URL='.$successUrl.'">'
        		. '<body>'
        		. '<p>' . $this->__('پرداخت شما با موفقیت انجام پذیرفت.') . '</p>'
        		. '<p>' . $this->__('اگر به صفحه بعد منتقل نشدید <a href="%s">اینجا</a> کلیک کنید', $successUrl) . '</p>'
        		. '</body></html>';

        return $html;
    }
}