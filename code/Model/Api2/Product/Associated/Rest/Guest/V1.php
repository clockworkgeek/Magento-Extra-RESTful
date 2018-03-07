<?php

class Clockworkgeek_Extrarestful_Model_Api2_Product_Associated_Rest_Guest_V1 extends Clockworkgeek_Extrarestful_Model_Api2_Product_Associated
{

    protected function _getCollection()
    {
        $products = parent::_getCollection();
        $products
            ->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED))
            ->addPriceData(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
        return $products;
    }
}
