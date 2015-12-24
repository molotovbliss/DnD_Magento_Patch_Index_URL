<?php
/**
 * @version 		1.0.0.0
 * @copyright 		Copyright (c) 2012 Agence Dn'D
 * @author 			Agence Dn'D - Conseil en création de site e-Commerce Magento : http://www.dnd.fr/
 * @license 		http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
class Dnd_Patchindexurl_Model_Url extends Mage_Catalog_Model_Url
{
 
    public function refreshProductRewrites($storeId)
    {
        $this->_categories = array();
        $storeRootCategoryId = $this->getStores($storeId)->getRootCategoryId();
        $this->_categories[$storeRootCategoryId] = $this->getResource()->getCategory($storeRootCategoryId, $storeId);

        $lastEntityId = 0;
        $process = true;

        $enableOptimisation = Mage::getStoreConfigFlag('dev/index/enable');
        $excludeProductsDisabled = Mage::getStoreConfigFlag('dev/index/disable');
        $excludeProductsNotVisible = Mage::getStoreConfigFlag('dev/index/notvisible');
        $useCategoriesInUrl = Mage::getStoreConfig('catalog/seo/product_use_categories');
        
        while ($process == true) {
            $products = $this->getResource()->getProductsByStore($storeId, $lastEntityId);
            if (!$products) {
                $process = false;
                break;
            }

            $this->_rewrites = array();
            $this->_rewrites = $this->getResource()->prepareRewrites($storeId, false, array_keys($products));

            $loadCategories = array();
            foreach ($products as $product) {
                foreach ($product->getCategoryIds() as $categoryId) {
                    if (!isset($this->_categories[$categoryId])) {
                        $loadCategories[$categoryId] = $categoryId;
                    }
                }
            }

            if ($loadCategories) {
                foreach ($this->getResource()->getCategories($loadCategories, $storeId) as $category) {
                    $this->_categories[$category->getId()] = $category;
                }
            }
            
            
            foreach ($products as $product) {
	            
           	 	if($enableOptimisation&&$excludeProductsDisabled&&$product->getData("status")==2)
           	 	{
	           	 	continue;
           	 	}
            	
           	 	if($enableOptimisation&&$excludeProductsNotVisible&&$product->getData("visibility")==1)
           	 	{
	           	 	continue;
           	 	}            	
            	
           	 	// Always Reindex short url
                $this->_refreshProductRewrite($product, $this->_categories[$storeRootCategoryId]);
            	
            	
            	if($useCategoriesInUrl!="0"||!$enableOptimisation)
            	{
	            	foreach ($product->getCategoryIds() as $categoryId) {
                    	if ($categoryId != $storeRootCategoryId && isset($this->_categories[$categoryId])) {
                        	$this->_refreshProductRewrite($product, $this->_categories[$categoryId]);
                        }
                    }
            	}

            }

            unset($products);
            $this->_rewrites = array();
        }

        $this->_categories = array();
        return $this;
    }
}