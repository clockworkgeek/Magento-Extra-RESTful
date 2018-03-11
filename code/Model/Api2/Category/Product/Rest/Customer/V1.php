<?php

/**
 * Show only enabled and 'visible in catalog' products to public users
 *
 * @author Daniel Deady <daniel@clockworkgeek.com>
 * @license MIT
 */
class Clockworkgeek_Extrarestful_Model_Api2_Category_Product_Rest_Customer_V1 extends Clockworkgeek_Extrarestful_Model_Api2_Category_Product
{
    use Clockworkgeek_Extrarestful_Model_Api2_Product_CustomerTrait;

    protected function _getCollection()
    {
        // skip trait's override and make our own
        $products = Clockworkgeek_Extrarestful_Model_Api2_Category_Product::_getCollection();
        $products
            ->setVisibility(array(
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
            ))
            ->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED))
            ->addPriceData($this->_customer->getGroupId());
        return $products;
    }
}
