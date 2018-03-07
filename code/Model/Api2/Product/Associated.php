<?php

/**
 * Explores 'super' product relations
 *
 * Accessible at <code>/api/rest/products/:product/associated</code>.
 * <code>:product</code> must exist.
 * Collection is sorted by admin controlled position.
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
        if ($product->isObjectNew() || !$product->isGrouped()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }

        /** @var $products Mage_Catalog_Model_Resource_Product_Link_Product_Collection */
        $products = $product->getTypeInstance(true)->getAssociatedProductCollection($product);
        $products->addStoreFilter($this->_getStore()->getId())
            ->addAttributeToSelect($this->getFilter()->getAttributesToInclude())
            ->addFilterByRequiredOptions()
            ->setPositionOrder();

        if (in_array('image_url', $this->getFilter()->getAttributesToInclude())) {
            // addAttributeToSelect does not work with flat tables
            // must use joinAttribute which also works fine with EAV tables
            $products->joinAttribute('image', 'catalog_product/image', 'entity_id');
        }
        return $products;
    }
}
