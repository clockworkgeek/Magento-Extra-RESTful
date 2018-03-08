<?php

class Clockworkgeek_Extrarestful_Model_Api2_Category_Product_Rest_Guest_V1 extends Clockworkgeek_Extrarestful_Model_Api2_Category_Product
{

    protected function _getCollection()
    {
        $products = parent::_getCollection();
        $products
            ->setVisibility(array(
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
            ))
            ->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED))
            ->addPriceData(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
        return $products;
    }
}
