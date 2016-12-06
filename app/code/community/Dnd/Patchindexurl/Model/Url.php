<?php
/**
 * @version 		1.0.0.0
 * @copyright 		Copyright (c) 2012 Agence Dn'D
 * @author 			Agence Dn'D - Conseil en création de site e-Commerce Magento : http://www.dnd.fr/
 * @license 		http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Dnd_Patchindexurl_Model_Url extends Mage_Catalog_Model_Url
{

    // https://github.com/molotovbliss/DnD_Magento_Patch_Index_URL/issues/2
    // Patch purposed by Magentix98 for Redirects are created, even if product_use_categories=false
    public function refreshProductRewrite($productId, $storeId = null)
    {

    Mage::log('refreshProductRewrite called for product:' . $productId);

    $enableOptimisation = Mage::getStoreConfigFlag('dev/index/enable');
    $useCategoriesInUrl = Mage::getStoreConfig('product_use_categories');

    if (is_null($storeId)) {
        foreach ($this->getStores() as $store) {
            $this->refreshProductRewrite($productId, $store->getId());
        }
        return $this;
    }

    $product = $this->getResource()->getProduct($productId, $storeId);
    if ($product) {
        $store = $this->getStores($storeId);
        $storeRootCategoryId = $store->getRootCategoryId();

        // List of categories the product is assigned to, filtered by being within the store's categories root
        $categories = $this->getResource()->getCategories($product->getCategoryIds(), $storeId);
        $this->_rewrites = $this->getResource()->prepareRewrites($storeId, '', $productId);

        // Add rewrites for all needed categories
        // If product is assigned to any of store's categories -
        // we also should use store root category to create root product url rewrite
        if (!isset($categories[$storeRootCategoryId])) {
            $categories[$storeRootCategoryId] = $this->getResource()->getCategory($storeRootCategoryId, $storeId);
        }

        // Create product url rewrites
        if($useCategoriesInUrl!="0"||!$enableOptimisation) {
            foreach ($categories as $category) {
                $this->_refreshProductRewrite($product, $category);
            }
        }

        // Remove all other product rewrites created earlier for this store - they're invalid now
        $excludeCategoryIds = array_keys($categories);
        $this->getResource()->clearProductRewrites($productId, $storeId, $excludeCategoryIds);

        unset($categories);
        unset($product);
    } else {
        // Product doesn't belong to this store - clear all its url rewrites including root one
        $this->getResource()->clearProductRewrites($productId, $storeId, array());
    }

    return $this;
    }
}