<?php

/**
 * Apply customer specific adjustments to price calculations
 *
 * The customer is the same as the API user because this is a customer type.
 * Customer group is probably biggest effect on tax class
 * but addresses must be handled too because they determine region.
 *
 * Can start a session because Mage_Catalog_Model_Product_Type_Price triggers an event.
 *
 * @author Daniel Deady <daniel@clockworkgeek.com>
 * @license MIT
 */
trait Clockworkgeek_Extrarestful_Model_Api2_Product_CustomerTrait
{

    /**
     * @var Mage_Customer_Model_Customer
     */
    protected $_customer;

    public function setApiUser(Mage_Api2_Model_Auth_User_Abstract $apiUser)
    {
        parent::setApiUser($apiUser);

        $this->_customer = Mage::getModel('customer/customer')->load($apiUser->getUserId(),
            // only load what is absolutely required
            array('group_id', 'default_billing', 'default_shipping'));
        // another way to prevent session starting
        Mage::getSingleton('tax/calculation')->setCustomer($this->_customer);

        return $this;
    }

    protected function _getCollection()
    {
        $products = parent::_getCollection();
        $products
            ->addAttributeToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE))
            ->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED))
            ->addPriceData($this->_customer->getGroupId());
        return $products;
    }

    /**
     * Only calculate final price for customers because other users aren't final yet
     *
     * Regular prices are always available.
     */
    protected function _prepareProduct(Mage_Catalog_Model_Product $product)
    {
        parent::_prepareProduct($product);
        $product->setCustomerGroupId(1);
        $include = $this->getFilter()->getAttributesToInclude();
        if (in_array('final_price_with_tax', $include)) {
            $product->setFinalPriceWithTax($this->_getPrice($product, $product->getFinalPrice(), true));
        }
        if (in_array('final_price_without_tax', $include)) {
            $product->setFinalPriceWithoutTax($this->_getPrice($product, $product->getFinalPrice(), false));
        }
    }

    protected function _getPrice(Mage_Catalog_Model_Product $product, $price, $withTax)
    {
        return Mage::helper('tax')->getPrice($product, $price, $withTax, $this->_customer->getPrimaryShippingAddress(),
            $this->_customer->getPrimaryBillingAddress(), $this->_customer->getTaxClassId());
    }
}
