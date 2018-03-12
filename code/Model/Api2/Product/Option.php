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

    protected function _getCollection()
    {
        $productId = $this->getRequest()->getParam('product');
        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product');
        $product->setStoreId($this->_getStore()->getId());
        $product->load($productId, array('entity_id'));
        if ($productId != $product->getId()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        return $this->getWorkingModel()->getProductOptionCollection($product);
    }

    protected function _loadCollection(Varien_Data_Collection_Db $options)
    {
        parent::_loadCollection($options);

        if (in_array('values', $this->getFilter()->getAttributesToInclude())) {
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
                        'value' => $value->getId()
                    );
                }
                // setData does not affect private member variables but will be exported by Varien_Object::toArray
                $option->setData('values', $values);
            }
        }

        foreach ($options as $option) {
            if ($option->getType() != 'file') {
                $option->unsFileExtension()->unsImageSizeX()->unsImageSizeY();
            }
            if (!in_array($option->getType(), array('field', 'area'))) {
                $option->unsMaxCharacters();
            }
            if (in_array($option->getType(), array('drop_down', 'radio', 'checkbox', 'multiple'))) {
                $option->unsPrice()->unsPriceType();
            }
            else {
                $option->unsValues();
            }
        }
    }
}
