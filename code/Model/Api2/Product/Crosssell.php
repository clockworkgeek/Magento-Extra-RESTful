<?php

/**
 * Products to be promoted while ordering another product
 *
 * Accessible at <code>/api/rest/products/:product/crosssells</code>.
 * <code>:product</code> must exist.
 * Unlike SOAP the client is not bothered with details of the links, it just works.
 * Collection is sorted by admin controlled position.
 *
 * @author Daniel Deady <daniel@clockworkgeek.com>
 * @license MIT
 */
class Clockworkgeek_Extrarestful_Model_Api2_Product_Crosssell extends Clockworkgeek_Extrarestful_Model_Api2_Product
{

    protected function _getCollection()
    {
        $productId = $this->getRequest()->getParam('product');
        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product');
        $product->setStoreId($this->_getStore()->getId());
        $product->load($productId, array('entity_id'));
        if ($product->isObjectNew()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }

        /** @var $products Mage_Catalog_Model_Resource_Product_Link_Product_Collection */
        $products = $product->getCrossSellProductCollection();
        $products->addStoreFilter($this->_getStore()->getId())
            ->addAttributeToSelect($this->getFilter()->getAttributesToInclude())
            ->setPositionOrder();

        if (in_array('image_url', $this->getFilter()->getAttributesToInclude())) {
            // addAttributeToSelect does not work with flat tables
            // must use joinAttribute which also works fine with EAV tables
            $products->joinAttribute('image', 'catalog_product/image', 'entity_id');
        }
        return $products;
    }
}
