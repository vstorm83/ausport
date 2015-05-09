<?php
class Eway_Rapid31_Model_Request_Direct extends Eway_Rapid31_Model_Request_Abstract
{
    /**
     * Call Transaction API (Authorized & Capture at the same time)
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @return Eway_Rapid31_Model_Request_Direct $this
     */
    public function doTransaction(Mage_Sales_Model_Order_Payment $payment, $amount)
    {
        $this->_buildRequest($payment, $amount);
        $this->setMethod(Eway_Rapid31_Model_Config::METHOD_PROCESS_PAYMENT);
        $response = $this->_doRapidAPI('Transaction');

        if($response->isSuccess()) {
            $payment->setTransactionId($response->getTransactionID());
            $payment->setCcLast4($response->getCcLast4());
            return $this;
        } else {
            Mage::throwException(Mage::helper('ewayrapid')->__('An error occurred while making the transaction. Please try again. (Error message: %s)',
                $response->getMessage()));
        }
    }

    /**
     * Call Authorisation API (Authorized only)
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param $amount
     * @return Eway_Rapid31_Model_Request_Direct
     */
    public function doAuthorisation(Mage_Sales_Model_Order_Payment $payment, $amount)
    {
        $this->_buildRequest($payment, $amount);
        $this->setMethod(Eway_Rapid31_Model_Config::METHOD_AUTHORISE);
        $response = $this->_doRapidAPI('Authorisation');

        if($response->isSuccess()) {
            $payment->setTransactionId($response->getTransactionID());
            $payment->setIsTransactionClosed(0);
            $payment->setCcLast4($response->getCcLast4());
            return $this;
        } else {
            if ($payment->getIsRecurring()) {
                Mage::getSingleton('core/session')->setData('errorMessage', $response->getMessage());
            }
            Mage::throwException(Mage::helper('ewayrapid')->__('An error occurred while doing the authorisation. Please try again. (Error message: %s)',
                $response->getMessage()));
        }
    }

    /**
     * Call Capture API (do the Capture only, must Authorized previously)
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param $amount
     * @return Eway_Rapid31_Model_Request_Direct
     */
    public function doCapturePayment(Mage_Sales_Model_Order_Payment $payment, $amount)
    {
        // Empty Varien_Object's data
        $this->unsetData();

        /* @var Mage_Sales_Model_Order_Invoice $invoice */
        $invoice = Mage::registry('current_invoice');
        $order = $payment->getOrder();

        $paymentParam = Mage::getModel('ewayrapid/field_payment');
        $paymentParam->setTotalAmount($amount)
            ->setCurrencyCode($order->getBaseCurrencyCode());
        if($invoice && $invoice->getIncrementId()) {
            $paymentParam->setInvoiceNumber($invoice->getIncrementId())
                ->setInvoiceReference($invoice->getIncrementId())
                ->setInvoiceDescription(Mage::helper('ewayrapid')->__('Invoice created from Magento'));
        }
        $this->setPayment($paymentParam);
        $this->setTransactionId($payment->getLastTransId());

        $response = $this->_doRapidAPI('CapturePayment');

        if($response->isSuccess()) {
            $payment->setTransactionId($response->getTransactionID());
            return $this;
        } else {
            if ($payment->getIsRecurring()) {
                Mage::getSingleton('core/session')->setData('errorMessage', $response->getMessage());
            }
            Mage::throwException(Mage::helper('ewayrapid')->__('An error occurred while doing the capture. Please try again. (Error message: %s)',
                $response->getMessage()));
        }
    }

    /**
     * Call Refund API, must complete the transaction (Authorized & Capture) beforehand
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param $amount
     * @return Eway_Rapid31_Model_Request_Direct
     */
    public function doRefund(Mage_Sales_Model_Order_Payment $payment, $amount)
    {
        // Empty Varien_Object's data
        $this->unsetData();

        $order = $payment->getOrder();
        /* @var Mage_Sales_Model_Order_Creditmemo $creditMemo */
        $creditMemo = Mage::registry('current_creditmemo');

        $invoice = ($creditMemo ? $creditMemo->getInvoice() : null);
        /* @var Mage_Sales_Model_Order_Invoice $invoice */
        if(!$invoice || !$invoice->getTransactionId()) {
            Mage::throwException(Mage::helper('ewayrapid')->__('An error occurred while doing the online refund: Invoice or transaction does not exist.'));
        }

        $paymentParam = Mage::getModel('ewayrapid/field_payment');
        $paymentParam->setTotalAmount($amount)
            ->setCurrencyCode($order->getBaseCurrencyCode())
            ->setTransactionID($invoice->getTransactionId());
        if($creditMemo && $creditMemo->getIncrementId()) {
            $paymentParam->setInvoiceDescription("Creditmemo ID " . $creditMemo->getIncrementId());
        }

        if($invoice && $invoice->getIncrementId()) {
            $paymentParam->setInvoiceNumber($invoice->getIncrementId())
                ->setInvoiceReference($invoice->getIncrementId());
        }
        $this->setRefund($paymentParam);

        $response = $this->_doRapidAPI('Transaction/' . $invoice->getTransactionId() . '/Refund');

        if($response->isSuccess()) {
            $payment->setTransactionId($response->getTransactionID());
            return $this;
        } else {
            Mage::throwException(Mage::helper('ewayrapid')->__('An error occurred while doing the refund. Please try again. (Error message: %s)',
                $response->getMessage()));
        }
    }

    /**
     * Call Cancel API, the transaction must be Authorized beforehand
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Eway_Rapid31_Model_Request_Direct
     */
    public function doCancel(Mage_Sales_Model_Order_Payment $payment)
    {
        // Empty Varien_Object's data
        $this->unsetData();

        $transactionId = $payment->getLastTransId();
        $this->setTransactionId($transactionId);
        $response = $this->_doRapidAPI('CancelAuthorisation');

        if($response->isSuccess()) {
            $payment->setTransactionId($response->getTransactionID());
            $payment->setIsTransactionClosed(1);
            return $this;
        } else {
            Mage::throwException(Mage::helper('ewayrapid')->__('An error occurred while doing the cancel. Please try again. (Error message: %s)',
                $response->getMessage()));
        }

    }

    /**
     * Build the request with necessary parameters for doAuthorisation() and doTransaction()
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param $amount
     * @return Eway_Rapid31_Model_Request_Direct
     */
    protected function _buildRequest(Mage_Sales_Model_Order_Payment $payment, $amount)
    {
        // Empty Varien_Object's data
        $this->unsetData();
        $methodInstance = $payment->getMethodInstance();
        $infoInstance = $methodInstance->getInfoInstance();
        $order = $payment->getOrder();
        $billing = $order->getBillingAddress();
        $shipping = $order->getShippingAddress();

        // if item is virtual product
        if (!$shipping) {
            $quote = Mage::getModel('checkout/cart')->getQuote();
            if ($quote->isVirtual()) {
                $shipping = $quote->getBillingAddress();
            }
        }

        $this->setCustomerIP(Mage::helper('core/http')->getRemoteAddr());
        if(Mage::helper('ewayrapid')->isBackendOrder()) {
            $this->setTransactionType(Eway_Rapid31_Model_Config::TRANSACTION_MOTO);
        } else {
            $this->setTransactionType(Eway_Rapid31_Model_Config::TRANSACTION_PURCHASE);
        }
        $version = Mage::helper('ewayrapid')->getExtensionVersion();
        $this->setDeviceID('Magento ' . Mage::getEdition() . ' ' . Mage::getVersion().' - eWAY Official '.$version);
        $this->setShippingMethod('Other');

        $paymentParam = Mage::getModel('ewayrapid/field_payment');
        $paymentParam->setTotalAmount($amount)
            ->setCurrencyCode($order->getBaseCurrencyCode());
        $this->setPayment($paymentParam);

        $customerParam = Mage::getModel('ewayrapid/field_customer');
        $customerParam->setTitle($billing->getPrefix())
            ->setFirstName($billing->getFirstname())
            ->setLastName($billing->getLastname())
            ->setCompanyName($billing->getCompany())
            ->setJobDescription('')
            ->setStreet1($billing->getStreet1())
            ->setStreet2($billing->getStreet2())
            ->setCity($billing->getCity())
            ->setState($billing->getRegion())
            ->setPostalCode($billing->getPostcode())
            ->setCountry(strtolower($billing->getCountryModel()->getIso2Code()))
            ->setEmail($billing->getEmail())
            ->setPhone($billing->getTelephone())
            ->setMobile('')
            ->setComments('')
            ->setFax($billing->getFax())
            ->setUrl('');
        $cardDetails = Mage::getModel('ewayrapid/field_cardDetails');
        $cardDetails->setName($infoInstance->getCcOwner())
            ->setNumber($infoInstance->getCcNumber())
            ->setExpiryMonth($infoInstance->getCcExpMonth())
            ->setExpiryYear($infoInstance->getCcExpYear())
            ->setCVN($infoInstance->getCcCid());

        $customerParam->setCardDetails($cardDetails);
        $this->setCustomer($customerParam);

        $shippingParam = Mage::getModel('ewayrapid/field_shippingAddress');
        $shippingParam->setFirstName($shipping->getFirstname())
            ->setLastName($shipping->getLastname())
            ->setStreet1($shipping->getStreet1())
            ->setStreet2($shipping->getStreet2())
            ->setCity($shipping->getCity())
            ->setState($shipping->getRegion())
            ->setPostalCode($shipping->getPostcode())
            ->setCountry(strtolower($shipping->getCountryModel()->getIso2Code()))
            ->setEmail($shipping->getEmail())
            ->setPhone($shipping->getTelephone())
            ->setFax($shipping->getFax());
        $this->setShippingAddress($shippingParam);

        if($methodInstance->getConfigData('transfer_cart_items')) {
            $orderItems = $order->getAllVisibleItems();
            $lineItems = array();
            foreach($orderItems as $orderItem) {
                /* @var Mage_Sales_Model_Order_Item $orderItem */
                $lineItem = Mage::getModel('ewayrapid/field_lineItem');
                $lineItem->setSKU($orderItem->getSku());
                $lineItem->setDescription(substr($orderItem->getName(), 0, 26));
                $lineItem->setQuantity($orderItem->getQtyOrdered());
                $lineItem->setUnitCost(round($orderItem->getBasePrice() * 100));
                $lineItem->setTax(round($orderItem->getBaseTaxAmount() * 100));
                $lineItem->setTotal(round($orderItem->getBaseRowTotalInclTax() * 100));
                $lineItems[] = $lineItem;
            }
            $this->setItems($lineItems);
        }

        return $this;
    }
}