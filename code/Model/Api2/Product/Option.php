<?php

/**
 * Exposes products' custom options
 *
 * Each option has a type like "text" or "drop_down" which describes an input field.
 * The client should ensure the user fills these in when ordering a product.
 *
 * Certain product types, like Bundle, cannot have custom options but that is not enforced here.
 *
 * @author Daniel Deady <daniel@clockworkgeek.com>
 * @license MIT
 */
class Clockworkgeek_Extrarestful_Model_Api2_Product_Option extends Clockworkgeek_Extrarestful_Model_Api2_Abstract
{

    /**
     * @return Mage_Catalog_Model_Product_Option
     */
    protected function _loadModel()
    {
        $id = $this->getRequest()->getParam('id');
        // a collection of one is easier to load than a model in this case
        $options = $this->getWorkingModel()->getCollection();
        $options
            ->addIdsToFilter($id)
            // these methods only exist on collection
            ->addTitleToResult($this->_getStore()->getId())
            ->addPriceToResult($this->_getStore()->getId())
            ->setOrder('sort_order', 'asc')
            ->setOrder('title', 'asc');
        $this->_loadCollection($options);
        $option = $options->getFirstItem();
        if ($id !== $option->getId()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        return $option;
    }

    /**
     * @param mixed $productId
     * @return Mage_Catalog_Model_Product
     */
    protected function _loadProduct($productId = null)
    {
        $productId = $productId ?: $this->getRequest()->getParam('product');
        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product');
        $product->setStoreId($this->_getStore()->getId());
        $product->load($productId, array('entity_id'));
        if ($productId != $product->getId()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        return $product;
    }

    protected function _getCollection()
    {
        $product = $this->_loadProduct();
        return $this->getWorkingModel()->getProductOptionCollection($product);
    }

    protected function _loadCollection(Varien_Data_Collection_Db $options)
    {
        parent::_loadCollection($options);

        if ($this->isReadable('values')) {
            $options->addValuesToResult($this->_getStore()->getId());
            /** @var $option Mage_Catalog_Model_Product_Option */
            foreach ($options as $option) {
                $values = array();
                /** @var $value Mage_Catalog_Model_Product_Option_Value */
                foreach ($option->getValues() as $value) {
                    $values[] = $value->toArray(array(
                        'price',
                        'price_type',
                        'sku',
                        'sort_order',
                        'title'
                    )) + array(
                        'value_id' => $value->getId()
                    );
                }
                // setData does not affect private member variables but will be exported by Varien_Object::toArray
                $option->setData('values', $values);
            }
        }

        foreach ($options as $option) {
            if ($option->getGroupByType() != Mage_Catalog_Model_Product_Option::OPTION_GROUP_FILE) {
                $option->unsFileExtension()->unsImageSizeX()->unsImageSizeY();
            }
            if ($option->getGroupByType() != Mage_Catalog_Model_Product_Option::OPTION_GROUP_TEXT) {
                $option->unsMaxCharacters();
            }
            if ($option->getGroupByType() == Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT) {
                $option->unsPrice()->unsPriceType();
            }
            else {
                $option->unsValues();
            }
        }
    }
}
