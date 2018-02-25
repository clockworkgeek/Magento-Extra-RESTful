<?php

class Clockworkgeek_Extrarestful_Model_Api2_Block_Rest_Admin_V1 extends Mage_Api2_Model_Resource
{

    protected function _create($data)
    {
        $block = $this->_saveBlock($data);
        return $this->_getLocation($block);
    }

    protected function _retrieve()
    {
        return $this->_getBlock()->getData();
    }

    /**
     * @return Mage_Cms_Model_Block
     */
    protected function _getBlock()
    {
        $blockId = $this->getRequest()->getParam('id');
        /* @var $block Mage_Cms_Model_Block */
        $block = Mage::getModel('cms/block')
            ->setStoreId($this->_getStore()->getId())
            ->load($blockId);
        if ($blockId != $block->getId() && $blockId != $block->getIdentifier()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        return $block;
    }

    protected function _saveBlock($data)
    {
        $block = $this->_getBlock();
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

    protected function _retrieveCollection()
    {
        /* @var $blocks Mage_Cms_Model_Resource_Block_Collection */
        $blocks = Mage::getResourceModel('cms/block_collection');
        if ($this->getRequest()->getParam('store')) {
            $blocks->addStoreFilter($this->_getStore());
        }
        $this->_applyCollectionModifiers($blocks);
        if (Mage::helper('extrarestful')->isCollectionOverflowed($blocks, $this->getRequest())) {
            return array();
        }
        else {
            if (in_array('stores', $this->getFilter()->getAttributesToInclude())) {
                $blocks->join(
                    array('store_table' => 'cms/block_store'),
                    'store_table.block_id=main_table.block_id',
                    'GROUP_CONCAT(store_id) AS stores');
                $blocks->getSelect()->group('main_table.block_id');
                foreach ($blocks as $block) {
                    // if no stores then set an empty array
                    $block->setStores(array_filter(explode(',', $block->getStores()), 'strlen'));
                }
            }

            $collection = $blocks->toArray();
            return @$collection['items'];
        }
    }

    protected function _update($data)
    {
        $this->_saveBlock($data);
    }

    protected function _delete()
    {
        $this->_getBlock()->delete();
    }
}
