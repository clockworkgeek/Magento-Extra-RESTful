<?php

class Clockworkgeek_Extrarestful_Model_Api2_Block_Rest_Admin_V1 extends Mage_Api2_Model_Resource
{

    protected function _retrieve()
    {
        $block = Mage::getModel('cms/block')
            ->setStoreId($this->_getStore()->getId())
            ->load($this->getRequest()->getParam('block'));
        if ($block->isObjectNew()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        return $block->getData();
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
}
