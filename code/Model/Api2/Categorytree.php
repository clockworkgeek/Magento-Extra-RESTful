<?php

/**
 * Generic API methods here, specialise elsewhere
 */
class Clockworkgeek_Extrarestful_Model_Api2_Categorytree extends Mage_Api2_Model_Resource
{

    /**
     * @var Clockworkgeek_Extrarestful_Model_Api2_Category
     */
    protected $_source;

    protected function _retrieveCollection()
    {
        $categories = $this->_getCategories();
        if (in_array('product_count', $this->getFilter()->getAttributesToInclude())) {
            $counts = $this->_source->getProductCounts($categories->getAllIds());
            foreach ($categories as $category) {
                $category->setProductCount((int) @$counts[$category->getId()]);
            }
        }
        else {
            $categories->load();
        }
        $data = $categories->toArray();
        return isset($data['items']) ? $data['items'] : $data;
    }

    /**
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    protected function _getCategories()
    {
        // borrow attribute filters from other category resource
        $this->_source = $this->_getSubModel('category', $this->getRequest()->getParams());
        $this->setFilter(Mage::getModel('api2/acl_filter', $this->_source));

        // in case of store specific values like localisation
        /* @var $categories Mage_Catalog_Model_Resource_Category_Collection */
        $categories = Mage::getResourceModel('catalog/category_collection');
        $storeId = $this->getRequest()->getParam('store');
        $categories->setStoreId($storeId);

        if ($storeId) {
            // exclude wrong trees
            $rootCategoryId = Mage::app()->getStore($storeId)->getRootCategoryId();
            $categories->addFieldToFilter('path', array('regexp' => '1/'.$rootCategoryId.'(/|$)'));
        }
        else {
            // global root must always be hidden
            $categories->addFieldToFilter('path', array('neq' => '1'));
        }

        // get available attributes from other resource again
        $categories->addAttributeToSelect(array_keys(
            $this->_source->getAvailableAttributes($this->getUserType(), Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ)
        ));

        // do not apply collection modifiers like normal, only filter params and a default sort
        $categories->addOrderField('position');
        $this->_applyFilter($categories);
        return $categories;
    }

    /**
     * @return array|Mage_Core_Model_Store[]
     */
    protected function _getStores()
    {
        return Mage::app()->getStores();
    }

    /**
     * Rearrange list into a tree after filter object has applied collectionOut()
     *
     * {@inheritDoc}
     * @see Mage_Api2_Model_Resource::_render()
     */
    protected function _render($categories)
    {
        $data = array();
        foreach ($categories as &$category) {
            if (@$category['level'] <= 1) {
                unset($category['parent_id']);
                foreach ($this->_getStores() as $store) {
                    if ($store->getRootCategoryId() == @$category['entity_id']) {
                        $category['stores'][] = $store->getId();
                    }
                }
                $data[] = &$category;
            }
            elseif ($parent = @$category['parent_id']) {
                $categories[$parent]['children'][] = &$category;
            }
        }
        return parent::_render($data);
    }
}
