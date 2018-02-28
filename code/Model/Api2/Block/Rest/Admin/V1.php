<?php

class Clockworkgeek_Extrarestful_Model_Api2_Block_Rest_Admin_V1 extends Clockworkgeek_Extrarestful_Model_Api2_Block
{

    protected function _saveModel($data)
    {
        $block = $this->_loadModel();
        $block->addData($data);

        // validate
        $helper = Mage::helper('extrarestful');
        if (is_null($block->getTitle())) {
            $this->_critical($helper->__('Title is required'), 400);
        }
        if (is_null($block->getIdentifier())) {
            $this->_critical($helper->__('Identifier is required'), 400);
        }
        if (is_null($block->getStores()) || $block->getStores() === array()) {
            $this->_critical($helper->__('At least one store is required'), 400);
        }
        foreach ((array) $block->getStores() as $storeId) {
            try {
                Mage::app()->getStore($storeId);
            }
            catch (Mage_Core_Model_Store_Exception $e) {
                $this->_critical($helper->__("Store '$storeId' doesn't exist"), 400);
            }
        }

        // success
        return $block->save();
    }
}
