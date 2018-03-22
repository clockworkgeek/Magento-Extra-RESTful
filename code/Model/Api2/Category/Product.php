<?php

/**
 * Exposes products within a given category
 *
 * Accessible at <code>/api/rest/categories/:category/products</code>.
 * <code>:category</code> must exist.
 * If category is an anchor type then products in it's child categories are included too.
 *
 * @author Daniel Deady <daniel@clockworkgeek.com>
 * @license MIT
 */
class Clockworkgeek_Extrarestful_Model_Api2_Category_Product extends Clockworkgeek_Extrarestful_Model_Api2_Product
{

    protected function _getCollection()
    {
        $categoryId = $this->getRequest()->getParam('category');
        /** @var $category Mage_Catalog_Model_Category */
        $category = Mage::getModel('catalog/category');
        $category->setStoreId($this->_getStore()->getId());
        $category->load($categoryId);
        if ($category->isObjectNew()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }

        /** @var $products Mage_Catalog_Model_Resource_Product_Collection */
        $products = $category->getProductCollection();
        $products->addAttributeToSelect($this->getFilter()->getAttributesToInclude());

        if ($this->isReadable('image_url')) {
            // addAttributeToSelect does not work with flat tables
            // must use joinAttribute which also works fine with EAV tables
            $products->joinAttribute('image', 'catalog_product/image', 'entity_id');
        }
        return $products;
    }
}
