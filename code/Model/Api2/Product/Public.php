<?php

/**
 * Non-admin users can only see 'visible' and 'enabled' products
 */
class Clockworkgeek_Extrarestful_Model_Api2_Product_Public extends Clockworkgeek_Extrarestful_Model_Api2_Product
{

    protected function _getCollection()
    {
        /** @var $products Mage_Catalog_Model_Resource_Product_Collection */
        $products = parent::_getCollection();
        $products
            ->addAttributeToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE))
            ->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED));
        return $products;
    }
}
