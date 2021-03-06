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
 * @since        Class available since Release 2.0
 */
 	
class GoMage_Navigation_Model_Resource_Eav_Mysql4_Collection extends Mage_CatalogSearch_Model_Mysql4_Fulltext_Collection{
	
	public function getSelectCountSql(){
    	
        $select = parent::getSelectCountSql();
        $select->reset(Zend_Db_Select::GROUP);
        
        return $select;
    }
    
	public function getSearchedEntityIds(){
		return false;		
	}
	
}