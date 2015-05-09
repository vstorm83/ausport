<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Autorelated
 * @version    2.2.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */


class AW_Autorelated_Test_Model_Source_Type extends EcomDev_PHPUnit_Test_Case {
    /**
     * @test
     * @doNotIndexAll
     * @dataProvider dataProvider
     */
    public function testGetOptionLabel($type) {
        $model = Mage::getModel('awautorelated/source_type');
        $this->assertEquals(
            $this->expected('type'.$type)->getLabel(),
            $model->getOptionLabel($type)
        );
    }
    
    public function testToShortOptionArray() {
        $model = Mage::getModel('awautorelated/source_type');
        $fullArray = $model->toOptionArray();
        $shortArray = array();
        foreach($fullArray as $option)
            $shortArray[$option['value']] = $option['label'];
        $this->assertEquals($shortArray, $model->toShortOptionArray());
    }
}
