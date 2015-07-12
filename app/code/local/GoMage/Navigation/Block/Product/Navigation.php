<?php
 /**
 * GoMage Advanced Navigation Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2013 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 4.0
 * @since        Class available since Release 4.0
 */
	
	class GoMage_Navigation_Block_Product_Navigation extends Mage_Core_Block_Template{
		
		protected $_current_category = false;
		protected $_current_product = false;
		protected $_product_collection = false;
		
		public function __construct()
	    {
	    	if ($this->_canWork())
	    	{
	    		$this->setTemplate('gomage/navigation/catalog/product/navigation.phtml');
	    	}
	    	
	    }
	    
	    protected function _getCurrentProduct()
	    {
	    	if ( !$this->_current_product )
	    	{
	    		$this->_current_product = Mage::registry('current_product');	
	    	}
	    	  
	    	return $this->_current_product;
	    }
	    
		protected function _getCurrentCategory()
	    {
	    	if ( !$this->_current_category )
	    	{
	    		$this->_current_category = Mage::registry('current_category');	
	    	}
	    	  
	    	return $this->_current_category;
	    }
	    
	    protected function _canWork()
	    {
	    	if (Mage::getStoreConfigFlag('gomage_navigation/products/enable') && Mage::getStoreConfigFlag('gomage_navigation/general/mode'))
	    	{
	    		if ( $this->_getCurrentCategory() )
	    		{
	    			return true;
	    		}
	    	}
	    	
	    	return false;
	    }
	    
	    protected function _getProductCollection()
	    {
	    	if ( !$this->_product_collection )
	    	{
	    		$this->_product_collection = $this->_getCurrentCategory()->getProductCollection();
	    	}
	    	
	    	return $this->_product_collection;
	    }

		public function categoryLinkHTML()
	    {
	    	$category = $this->_getCurrentCategory();
	    	$template = Mage::getStoreConfig('gomage_navigation/products/category_link');
	    	$image = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'gomage/config/' . Mage::getStoreConfig('gomage_navigation/products/category_img'); 
    		$image_tag = '<img src="' . $image . '" alt="' . $category->getName() . '" title="' . $category->getName() . '" />';
	    	
	    	$template = str_replace("%category%", $category->getName(), $template);
	    	$template = str_replace("%category_image%", $image_tag, $template);
	    	$template = '<a href="' . $category->getUrl() . '">' . $template . '</a>';
	    	
	    	return $template;
	    }
	    
	    public function prevLinkHTML()
	    {
	    	if ( $this->_showPrev() )
	    	{
	    		$product = $this->_getPrevProduct();
	    		$template = Mage::getStoreConfig('gomage_navigation/products/prev_link');
	    		
	    		return $this->_getTemplate($product, $template); 
	    	}
	    }
	    
	    protected function _getPrevProduct()
	    {
	    	if ( $this->_getProductCollection() )
	    	{
	    		$product_array = array();
	    		$i = 0;
			    foreach ($this->_getProductCollection() as $product) 
			    {
			    	$product_array[$i] = $product;
			    	
					if ( $product->getId() == $this->_getCurrentProduct()->getId() )
					{
						
						return Mage::getModel('catalog/product')->load($product_array[$i-1]->getId());
					}			    	
			        $i++;
			    }
	    	}
	    	
	    	return false;
	    }
	    
		public function nextLinkHTML()
	    {
	    	if ( $this->_showNext() )
	    	{
	    		$product = $this->_getNextProduct();
	    		$template = Mage::getStoreConfig('gomage_navigation/products/next_link');
	    		
	    		return $this->_getTemplate($product, $template);
	    	}
	    }
	    
		protected function _getNextProduct()
	    {
	    	if ( $this->_getProductCollection() )
	    	{
	    		$show = false;
	    		
			    foreach ($this->_getProductCollection() as $product) 
			    {
					if ( $show )
					{
						return Mage::getModel('catalog/product')->load($product->getId());
					}			    	
			        if ( $product->getId() == $this->_getCurrentProduct()->getId())
			        {
			        	$show = true;
			        }
			    }
	    	}
	    	
	    	return false;
	    }
	    
	    protected function _getTemplate($product, $template)
	    {
	    	$name = $product->getName();
    		if ( Mage::getStoreConfig('gomage_navigation/products/max_symbol') != 0 )
    		{
    			$name = substr($name, 0, Mage::getStoreConfig('gomage_navigation/products/max_symbol'));	
    		}
    		
    		$url = $product->getProductUrl();
    		$price = Mage::helper('core')->currency($product->getPrice());

            if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE)
            {
                $priceModel  = $product->getPriceModel();
                list($minimalPrice, $maximalPrice) = $priceModel->getTotalPrices($product, null, null, false);
                $price = Mage::helper('core')->currency($minimalPrice,2) . ' - ' .
                    Mage::helper('core')->currency($maximalPrice);
            }
    		
    		$image_prev = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'gomage/config/' . Mage::getStoreConfig('gomage_navigation/products/prev_img'); 
    		$image_tag_prev = '<img src="' . $image_prev . '" alt="' . $name . '" title="' . $name . '" />';
    		
    		$image_next = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'gomage/config/' . Mage::getStoreConfig('gomage_navigation/products/next_img'); 
    		$image_tag_next = '<img src="' . $image_next . '" alt="' . $name . '" title="' . $name . '" />';
    		
    		$template = str_replace("%product%", $name, $template);
    		$template = str_replace("%price%", $price, $template);
    		$template = str_replace("%previous_image%", $image_tag_prev, $template);
    		$template = str_replace("%next_image%", $image_tag_next, $template);
    		
    		$template = '<a href="' . $url . '">' . $template . '</a>';
    		
    		return $template; 
	    }
		
	    
	    protected function _showPrev()
	    {
	    	if ( $this->_getProductCollection() )
	    	{
	    		$i = 0;
	    		
			    foreach ($this->_getProductCollection() as $product) 
			    {
			        if ( $product->getId() == $this->_getCurrentProduct()->getId()
			        		&&
			        	 $i == 0 )
			        {   	
			        	return false;
			        }
			        
			        $i++;
			    }
			    
			    return true;
	    	}	    	
	    	
	    	return false;
	    }
	    
		protected function _showNext()
	    {
	    	if ( $this->_getProductCollection() )
	    	{
	    		$i = 0;
	    		$j = 0;
			    foreach ($this->_getProductCollection() as $product)
			    {
			    	$i++;
			        if ( $product->getId() == $this->_getCurrentProduct()->getId())
			        {
			        	$j = $i;
			        }
			    }
			    
			    if ( $j == $i )
			    {
			    	return false;
			    }
			    
			    return true;
	    	}	    	
	    	
	    	return false;
	    }
	    
    }