<?php
/**
 * @version 		1.0.0.0
 * @copyright 		Copyright (c) 2012 Agence Dn'D
 * @author 			Agence Dn'D - Conseil en crŽation de site e-Commerce Magento : http://www.dnd.fr/
 * @license 		http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
class Dnd_Patchindexurl_Model_Indexer_Url extends Mage_Catalog_Model_Indexer_Url
{

    protected function _registerProductEvent(Mage_Index_Model_Event $event)
    {
        $product = $event->getDataObject();
        $dataChange2 = false;
        
        if(($product->dataHasChangedFor('status')&&$product->getData('status')=="1")||($product->dataHasChangedFor('visibility')&&$product->getData('visibility')!="1"))
        {
        	$dataChange2 = true;
        }
       
        $dataChange = $product->dataHasChangedFor('url_key')
            || $product->getIsChangedCategories()
            || $product->getIsChangedWebsites()
            || $dataChange2;
            
        if (!$product->getExcludeUrlRewrite() && $dataChange) {
            $event->addNewData('rewrite_product_ids', array($product->getId()));
        }
    }
}