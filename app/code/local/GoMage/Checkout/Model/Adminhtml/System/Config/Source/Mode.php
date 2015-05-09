<?php
 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2012 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 3.2
 * @since        Class available since Release 1.0
 */
	
class GoMage_Checkout_Model_Adminhtml_System_Config_Source_Mode{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label'=>Mage::helper('gomage_checkout')->__('Registered and guest customers')),
            array('value' => 1, 'label'=>Mage::helper('gomage_checkout')->__('Only registered customers')),
            array('value' => 2, 'label'=>Mage::helper('gomage_checkout')->__('Only guest customers')),
        );
    }

}