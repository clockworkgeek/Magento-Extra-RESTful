<?php

/**
 * @author Daniel Deady <daniel@clockworkgeek.com>
 * @license MIT
 */
class Clockworkgeek_Extrarestful_Model_Api2_Product_Option_Rest_Admin_V1
extends Clockworkgeek_Extrarestful_Model_Api2_Product_Option
{

    protected function _saveModel($data)
    {
        // option values are loaded too
        $option = $this->_loadModel();
        $product = $this->_loadProduct($option->getProductId());
        $product->setStoreId($this->_getStore()->getId());

        // amalgamate saved and incoming values into one list
        if (isset($data['values']) && is_array($data['values'])) {
            $removes = $option->getValues();
            $values = array();
            foreach ($data['values'] as $value) {
                $valueId = @$value['value_id'];
                if ($option->getValueById($valueId)) {
                    // overwrite existing value record
                    $value['option_type_id'] = $valueId;
                    unset($removes[$valueId]);
                }
                else {
                    // is a new value record
                    if (!@$value['title']) {
                        $this->_error('Value title is required', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                    }
                }
                $values[] = $value;
            }
            foreach ($removes as $retired) {
                $retired->setIsDelete(true);
                $values[] = $retired->toArray();
            }
            $data['values'] = $values;
        }
        else {
            // nullify filtered values data that was set during _loadCollection()
            // this prevents duplicating records on every PUT
            $option->setData('values', array());
        }
        $option->addData($data);
        if (!$option->getTitle()) {
            $this->_error('Option title is required', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
        if (!$option->getType()) {
            $this->_error('Option type is required', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
        elseif (!$option->getGroupByType()) {
            $this->_error('Option type is not recognised', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }

        if (!$this->getResponse()->isException()) {
            // Mage_Catalog_Model_Product_Option::saveOptions() handles changing types and values nicely
            $option
                ->setPreviousType($option->getType())
                ->setProduct($product)
                ->setOptions(array($option->toArray()))
                ->saveOptions();
        }
        return $option;
    }
}
