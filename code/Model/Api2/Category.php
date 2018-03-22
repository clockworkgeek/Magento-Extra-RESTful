<?php

/**
 * Exposes catalog categories
 *
 * <code>product_count</code> is the number of enabled products in each category,
 * and it's child categories if it is an anchor type.
 * Only categories in the configured category tree for a store are shown.
 * The store is either as specified or the default store if not specified by a public user.
 *
 * @author Daniel Deady <daniel@clockworkgeek.com>
 * @license MIT
 */
class Clockworkgeek_Extrarestful_Model_Api2_Category extends Clockworkgeek_Extrarestful_Model_Api2_Abstract
{

    /**
     * Load product count after loading category
     *
     * @return Mage_Catalog_Model_Category
     * @see Clockworkgeek_Extrarestful_Model_Api2_Abstract::_loadModel()
     */
    protected function _loadModel()
    {
        /** @var $category Mage_Catalog_Model_Category */
        $category = parent::_loadModel();
        if ($this->isReadable('product_count')) {
            $products = $this->_getProductCollection();
            $products->addCategoryFilter($category);
            $category->setProductCount($products->getSize());
        }
        if ($this->isReadable('url')) {
            $category->setRequestPath(
                $category->getUrlRewrite()->loadByIdPath('category/'.$category->getId())->getRequestPath());
        }
        $category->setImageUrl($category->getImageUrl());
        $this->_prepareCategory($category);

        if ($lastMod = strtotime($category->getUpdatedAt())) {
            $this->getResponse()->setHeader('Last-Modified', date('r', $lastMod));
        }

        return $category;
    }

    /**
     * Load product count after loading categories
     *
     * @see Clockworkgeek_Extrarestful_Model_Api2_Abstract::_loadCollection()
     */
    protected function _loadCollection(Varien_Data_Collection_Db $categories)
    {
        parent::_loadCollection($categories);

        if ($this->isReadable('product_count')) {
            $this->_getProductCollection()->addCountToCategories($categories);
        }

        foreach ($categories as $category) {
            $this->_prepareCategory($category);
        }
    }

    protected function _prepareCategory(Mage_Catalog_Model_Category $category)
    {
        $category->setChildrenCount((int) $category->getChildrenCount());
        $category->setCustomApplyToProducts((bool) $category->getCustomApplyToProducts());
        $category->setCustomUseParentSettings((bool) $category->getCustomUseParentSettings());
        $category->setIncludeInMenu((bool) $category->getIncludeInMenu());
        $category->setIsActive((bool) $category->getIsActive());
        $category->setIsAnchor((bool) $category->getIsAnchor());
        $category->setLevel((int) $category->getLevel());
        $category->setPosition((int) $category->getPosition());
        $category->setProductCount((int) $category->getProductCount());

        if ($this->isReadable('url') && $category->hasRequestPath()) {
            $url = Mage::getUrl('', array(
                '_direct' => $category->getRequestPath(),
                // prevent accidentally starting session for SID check
                '_nosid' => true
            ));
            $category->setUrl($url);
        }
    }

    /**
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    protected function _getCollection()
    {
        /** @var $categories Mage_Catalog_Model_Resource_Category_Collection */
        $categories = parent::_getCollection();
        if ($categories instanceof Mage_Catalog_Model_Resource_Category_Flat_Collection) {
            // filter's getAttributesToInclude can include too much, such as product_count
            // here '*' limits to just flat table's columns
            $categories->addAttributeToSelect('*');
        }

        if (($parentId = $this->getRequest()->getParam('parent'))) {
            $categories->addAttributeToFilter('parent_id', $parentId);
        }

        $storeId = $this->_getStore()->getId();
        if ($storeId) {
            // exclude wrong trees
            $rootCategoryId = Mage::app()->getStore($storeId)->getRootCategoryId();
            $categories->addFieldToFilter('path', array('regexp' => '1/'.$rootCategoryId.'(/|$)'));
        }
        else {
            // global root must always be hidden
            $categories->addFieldToFilter('path', array('neq' => '1'));
        }

        if ($this->isReadable('image_url')) {
            $dir = Mage::getBaseUrl('media').'catalog/category/';
            $method = method_exists($categories, 'addExpressionAttributeToSelect') ? 'addExpressionAttributeToSelect' : 'addExpressionFieldToSelect';
            $categories->$method('image_url', "CONCAT('{$dir}', {{image}})", 'image');
            // map is only used by flat tables, doesn't hurt EAV tables
            $categories->addFilterToMap('image_url', "CONCAT('{$dir}', image)");
        }

        if ($this->isReadable('url')) {
            $categories->addUrlRewriteToResult();
        }

        return $categories;
    }

    /**
     * Products which are likely to be seen in catalog
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _getProductCollection()
    {
        return Mage::getResourceModel('catalog/product_collection')->setVisibility(array(
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
        ))
        // because price index table is inner joined and indexes can be out of date,
        // this is necessary for a accurate count.
        // actual customer group probably won't affect join so use this ID for all
        ->addPriceData(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
    }

    /**
     * @see Mage_Customer_Model_Api2_Customer::_getResourceAttributes()
     */
    protected function _getResourceAttributes()
    {
        return $this->getEavAttributes(true, true);
    }
}
