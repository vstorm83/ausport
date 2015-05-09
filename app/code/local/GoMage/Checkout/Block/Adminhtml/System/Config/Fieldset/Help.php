<?php

 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 3.2
 * @since        Class available since Release 2.4
 */

class GoMage_Checkout_Block_Adminhtml_System_Config_Fieldset_Help
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
     

    /**
     * Get frontend class
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getFrontendClass($element)
    {
        $frontendClass = (string)$this->getGroup($element)->frontend_class;
        return 'section-config' . (empty($frontendClass) ? '' : (' ' . $frontendClass));
    }

}