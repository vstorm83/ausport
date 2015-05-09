<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@mageworx.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 * or send an email to sales@mageworx.com
 *
 * @category   MageWorx
 * @package    MageWorx_XSitemap
 * @copyright  Copyright (c) 2013 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Extended Sitemap extension
 *
 * @category   MageWorx
 * @package    MageWorx_XSitemap
 * @author     MageWorx Dev Team <dev@mageworx.com>
 */
class MageWorx_XSitemap_Block_Catalog_Categories extends Mage_Core_Block_Template
{
    const XML_PATH_SHOW_PRODUCTS = 'mageworx_seo/xsitemap/show_products';

    protected $_storeRootCategoryPath  = '';
    protected $_storeRootCategoryLevel = 0;
    protected $_categories             = array();

    protected function _prepareLayout()
    {
        $parent                        = Mage::getModel('catalog/category')->setStoreId(Mage::app()->getStore()->getId())
            ->load(Mage::app()->getStore()->getRootCategoryId());
        $this->_storeRootCategoryPath  = $parent->getPath();
        $this->_storeRootCategoryLevel = $parent->getLevel();
        //$collection = $this->getTreeCollection();
        $this->getTreeCollection();
        //$this->setCollection($collection);
        return $this;
    }

    public function getCategories()
    {
        return $this->_categories;
    }

    public function getTreeCollection()
    {
        /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection */
        $collection = Mage::getResourceModel('catalog/category_collection')
            ->addAttributeToSelect('*')
            ->joinUrlRewrite()
            ->addAttributeToFilter('is_active', 1)
            ->setOrder('level', 'ASC')
            ->setOrder(Mage::helper('xsitemap')->getHtmlSitemapSort());
        ;

        // Magento v1.2.0.2 Compatibility
        $collection->getSelect()->where('e.path LIKE ?', $this->_storeRootCategoryPath . '/%');

        foreach ($collection->getItems() as $item) {

            if ($item->getData('exclude_from_html_sitemap')) {
                continue;
            }
            if (!isset($level)) {
                $level = $item->getLevel();
            }
            if ($item->getLevel() == $level) {
                $this->_categories[] = $item;
                ///if ($item->getChildrenCount()) {
                    $this->_addChildren($item->getId(), $collection);
                ///}
            }
        }
        return $collection;
    }

    protected function _addChildren($parentId, $collection)
    {
        foreach ($collection->getItems() as $item) {
            if ($item->getParentId() == $parentId) {
                if ($item->getData('exclude_from_html_sitemap')) {
                    continue;
                }
                $this->_categories[] = $item;
                ///if ($item->getChildrenCount()) {
                    $this->_addChildren($item->getId(), $collection);
                ///}
            }
        }
    }

    public function getLevel($item, $delta = 1)
    {
        return (int) ($item->getLevel() - $this->_storeRootCategoryLevel - 1) * $delta;
    }

    public function getItemUrl($category)
    {
        $helper  = Mage::helper('catalog/category');
        $xhelper = Mage::helper('xsitemap');
        /* @var $helper Mage_Catalog_Helper_Category */
        return $xhelper->trailingSlash($helper->getCategoryUrl($category));
    }

    /*
     * @param int It isn't remote because of compatibility with template
     */
    public function showProducts($category = false)
    {
        if (!isset($this->_showProducts)) {
            $this->_showProducts = Mage::getStoreConfigFlag(self::XML_PATH_SHOW_PRODUCTS);
        }
        return $this->_showProducts;
    }

}
