<?php

class Clockworkgeek_Extrarestful_Model_Api2_Categorytree_Rest_Customer_V1
extends Clockworkgeek_Extrarestful_Model_Api2_Categorytree
{

    /**
     * Customers may not see inactive categories
     *
     * {@inheritDoc}
     * @see Clockworkgeek_Extrarestful_Model_Api2_Categorytree::_getCollection()
     */
    protected function _getCollection()
    {
        $categories = parent::_getCategories();
        $categories->addIsActiveFilter();
        return $categories;
    }

    /**
     * Customers may not see inactive store views
     *
     * {@inheritDoc}
     * @see Clockworkgeek_Extrarestful_Model_Api2_Categorytree::_getStores()
     */
    protected function _getStores()
    {
        /* @var $store Mage_Core_Model_Store */
        return array_filter(parent::_getStores(), function($store) {
            return $store->getIsActive();
        });
    }
}
