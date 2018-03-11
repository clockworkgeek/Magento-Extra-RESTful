<?php

/**
 * Guests can only see 'visible' and 'enabled' products
 *
 * @author Daniel Deady <daniel@clockworkgeek.com>
 * @license MIT
 */
trait Clockworkgeek_Extrarestful_Model_Api2_Product_GuestTrait
{

    protected function _getCollection()
    {
        $products = parent::_getCollection();
        $products
            ->addAttributeToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE))
            ->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED))
            ->addPriceData(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
        return $products;
    }
}
