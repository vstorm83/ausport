<?php

/**
 * Open Biz Ltd
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file OPEN-BIZ-LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://mageconsult.net/terms-and-conditions
 *
 * @category   Magecon
 * @package    Magecon_Rma
 * @version    1.0.0
 * @copyright  Copyright (c) 2013 Open Biz Ltd (http://www.mageconsult.net)
 * @license    http://mageconsult.net/terms-and-conditions
 */
class Magecon_Rma_Block_Adminhtml_Rma extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_rma';
        $this->_blockGroup = 'rma';
        $this->_headerText = Mage::helper('rma')->__('RMAs');
        $this->_addButtonLabel = Mage::helper('rma')->__('Create New RMA');
        parent::__construct();
    }

    public function getCreateUrl() {
        return $this->getUrl('*/adminhtml_rma_create/index');
    }

}