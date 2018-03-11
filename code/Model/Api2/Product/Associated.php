<?php

/**
 * Explores 'super' product relations
 *
 * Accessible at <code>/api/rest/products/:product/associated</code>.
 * <code>:product</code> must exist and be a 'super' type.
 * Collection is sorted by admin controlled position for Grouped only.
 *
 * TODO: Change product prices to reflect admin-entered super product pricing.
 * Or perhaps not.
 * It is a very silly system.
 *
 * @author Daniel Deady <daniel@clockworkgeek.com>
 * @license MIT
 */
class Clockworkgeek_Extrarestful_Model_Api2_Product_Associated extends Clockworkgeek_Extrarestful_Model_Api2_Product
{

    protected function _getCollection()
    {
        $productId = $this->getRequest()->getParam('product');
        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product');
        $product->setStoreId($this->_getStore()->getId());
        $product->load($productId, array('entity_id'));
        if ($product->isObjectNew() || !$product->isSuper()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }

        if ($product->isGrouped()) {
            /** @var $products Mage_Catalog_Model_Resource_Product_Link_Product_Collection */
            $products = $product->getTypeInstance(true)->getAssociatedProductCollection($product);
            $products->setPositionOrder();
        }
        elseif ($product->isConfigurable()) {
            /** @var $products Mage_Catalog_Model_Resource_Product_Type_Configurable_Product_Collection */
            $products = $product->getTypeInstance(true)->getUsedProductCollection($product);
        }
        $products->addStoreFilter($this->_getStore()->getId())
            ->addAttributeToSelect($this->getFilter()->getAttributesToInclude())
            ->addFilterByRequiredOptions();

        if (in_array('image_url', $this->getFilter()->getAttributesToInclude())) {
            // addAttributeToSelect does not work with flat tables
            // must use joinAttribute which also works fine with EAV tables
            $products->joinAttribute('image', 'catalog_product/image', 'entity_id');
        }
        return $products;
    }
}
