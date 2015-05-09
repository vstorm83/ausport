<?php
/**
 * Fontis eWAY Australia payment gateway
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so you can be sent a copy immediately.
 *
 * Original code copyright (c) 2008 Irubin Consulting Inc. DBA Varien
 *
 * @category   Fontis
 * @package    Fontis_EwayAu
 * @copyright  Copyright (c) 2010 Fontis (http://www.fontis.com.au)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Fontis_EwayAu_Model_Shared extends Mage_Payment_Model_Method_Abstract
{
    protected $_code  = 'ewayau_shared';

    protected $_isGateway               = false;
    protected $_canAuthorize            = false;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;

    protected $_formBlockType = 'ewayau/shared_form';
    protected $_paymentMethod = 'shared';

    protected $_order;

    /**
     * Get order model
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (!$this->_order) {
            $paymentInfo = $this->getInfoInstance();
            $this->_order = Mage::getModel('sales/order')
                            ->loadByIncrementId($paymentInfo->getOrder()->getRealOrderId());
        }
        return $this->_order;
    }

    /**
     * Get Customer Id
     *
     * @return string
     */
    public function getCustomerId()
    {
        return Mage::getStoreConfig('payment/' . $this->getCode() . '/customer_id');
    }

    /**
     * Get currency that accepted by eWAY account
     *
     * @return string
     */
    public function getAcceptedCurrency()
    {
        return Mage::getStoreConfig('payment/' . $this->getCode() . '/currency');
    }

    public function validate()
    {
        parent::validate();
        $paymentInfo = $this->getInfoInstance();
        if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
            $currency_code = $paymentInfo->getOrder()->getBaseCurrencyCode();
        } else {
            $currency_code = $paymentInfo->getQuote()->getBaseCurrencyCode();
        }
        if ($currency_code != $this->getAcceptedCurrency()) {
            Mage::throwException(Mage::helper('ewayau')->__('Selected currency code ('.$currency_code.') is not compatabile with eWAY'));
        }
        return $this;
    }

    public function getOrderPlaceRedirectUrl()
    {
        $url = Mage::getUrl('ewayau/' . $this->_paymentMethod . '/redirect');
        if(!$url) {
            $url = 'https://www.eway.com.au/gateway/payment.asp';
        }
        return $url;
    }

    /**
     * prepare params array to send it to gateway page via POST
     *
     * @return array
     */
    public function getFormFields()
    {
        $billing = $this->getOrder()->getBillingAddress();
        $fieldsArr = array();
        $invoiceDesc = '';
        $lengs = 0;
        foreach ($this->getOrder()->getAllItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            if (Mage::helper('core/string')->strlen($invoiceDesc.$item->getName()) > 10000) {
                break;
            }
            $invoiceDesc .= $item->getName() . ', ';
        }
        $invoiceDesc = Mage::helper('core/string')->substr($invoiceDesc, 0, -2);

        $address = clone $billing;
        $address->unsFirstname();
        $address->unsLastname();
        $address->unsPostcode();
        $formatedAddress = '';
        $tmpAddress = explode(' ', str_replace("\n", ' ', trim($address->format('text'))));
        foreach ($tmpAddress as $part) {
            if (strlen($part) > 0) $formatedAddress .= $part . ' ';
        }
        $paymentInfo = $this->getInfoInstance();
        $fieldsArr['ewayCustomerID'] = $this->getCustomerId();
        $fieldsArr['ewayTotalAmount'] = ($this->getOrder()->getBaseGrandTotal()*100);
        $fieldsArr['ewayCustomerFirstName'] = $billing->getFirstname();
        $fieldsArr['ewayCustomerLastName'] = $billing->getLastname();
        $fieldsArr['ewayCustomerEmail'] = $this->getOrder()->getCustomerEmail();
        $fieldsArr['ewayCustomerAddress'] = trim($formatedAddress);
        $fieldsArr['ewayCustomerPostcode'] = $billing->getPostcode();
        $fieldsArr['ewayCustomerInvoiceDescription'] = $invoiceDesc;
        $fieldsArr['ewaySiteTitle'] = Mage::app()->getStore()->getName();
        $fieldsArr['ewayAutoRedirect'] = 1;
        $fieldsArr['ewayURL'] = Mage::getUrl('ewayau/' . $this->_paymentMethod . '/success', array('_secure' => true));
        $fieldsArr['ewayCustomerInvoiceRef'] = $paymentInfo->getOrder()->getRealOrderId();
        $fieldsArr['ewayTrxnNumber'] = $paymentInfo->getOrder()->getRealOrderId();
        $fieldsArr['ewayOption1'] = '';
        $fieldsArr['ewayOption2'] = Mage::helper('core')->encrypt($paymentInfo->getOrder()->getRealOrderId());
        $fieldsArr['ewayOption3'] = '';

        return $fieldsArr;
    }

    /**
     * Get url of eWAY Shared Payment
     *
     * @return string
     */
    public function getEwaySharedUrl()
    {
         if (!$url = Mage::getStoreConfig('payment/ewayau_shared/api_url')) {
             $url = 'https://www.eway.com.au/gateway/payment.asp';
         }
         return $url;
    }

    /**
     * Get debug flag
     *
     * @return string
     */
    public function getDebug()
    {
        return Mage::getStoreConfig('payment/' . $this->getCode() . '/debug_flag');
    }

    public function capture(Varien_Object $payment, $amount)
    {
        $payment->setStatus(self::STATUS_APPROVED)
            ->setLastTransId($this->getTransactionId());

        return $this;
    }

    public function cancel(Varien_Object $payment)
    {
        $payment->setStatus(self::STATUS_DECLINED)
            ->setLastTransId($this->getTransactionId());

        return $this;
    }

    /**
     * parse response POST array from gateway page and return payment status
     *
     * @return bool
     */
    public function parseResponse()
    {
        $response = $this->getResponse();

        if ($response['ewayTrxnStatus'] == 'True') {
            return true;
        }
        return false;
    }

    /**
     * Return redirect block type
     *
     * @return string
     */
    public function getRedirectBlockType()
    {
        return $this->_redirectBlockType;
    }

    /**
     * Return payment method type string
     *
     * @return string
     */
    public function getPaymentMethodType()
    {
        return $this->_paymentMethod;
    }
}
