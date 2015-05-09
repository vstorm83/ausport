<?php

class Eway_Rapid31_Block_Redirect_Transparent extends Mage_Core_Block_Template
{
    protected $methodPayment;
    protected $transMethod;
    protected $paypalSavedToken;
    protected $savedToken;

    public function _construct()
    {
        $this->methodPayment = Mage::getSingleton('core/session')->getMethod();
        $this->transMethod = Mage::getSingleton('core/session')->getTransparentNotsaved();
        if (!$this->transMethod) {
            $this->transMethod = Mage::getSingleton('core/session')->getTransparentSaved();
        }

        if ($this->methodPayment == 'ewayrapid_saved') {
            if ($this->transMethod == Eway_Rapid31_Model_Config::PAYPAL_STANDARD_METHOD) {
                $this->paypalSavedToken = Mage::getSingleton('core/session')->getPaypalSavedToken();
            } else {
                $this->savedToken = Mage::getSingleton('core/session')->getSavedToken();
            }
        }

        $this->setTemplate('ewayrapid/redirect/transparent.phtml')->toHtml();
    }

    /**
     * Retrieve credit card expire months
     *
     * @return array
     */
    public function getCcMonths()
    {
        $months = $this->getData('cc_months');
        if (is_null($months)) {
            $months[0] = $this->__('Month');
            $months = array_merge($months, Mage::getSingleton('payment/config')->getMonths());
            $this->setData('cc_months', $months);
        }
        return $months;
    }

    /**
     * Retrieve credit card expire years
     *
     * @return array
     */
    public function getCcYears()
    {
        $years = $this->getData('cc_years');
        if (is_null($years)) {
            $years = Mage::getSingleton('payment/config')->getYears();
            $years = array(0 => $this->__('Year')) + $years;
            $this->setData('cc_years', $years);
        }
        return $years;
    }

    /**
     * Get New Shipping from items
     * @return int
     */
    public function getNewShippingTotal()
    {
        $totalItem = 0;
        foreach ($this->_getQuote()->getAllVisibleItems() as $item) {
            $totalItem += $item->getQty();
        }
        return $totalItem;
    }

    /**
     * Check if CVN is required or not
     *
     * @return bool
     */
    public function hasVerification()
    {
        // No need for CVN in creating/updating token
        return Mage::getModel('ewayrapid/method_notsaved')->hasVerification();
    }
}