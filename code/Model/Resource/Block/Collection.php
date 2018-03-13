<?php

/**
 * Modified block collection that adds store data automatically
 *
 * @author Daniel Deady <daniel@clockworkgeek.com>
 * @license MIT
 */
class Clockworkgeek_Extrarestful_Model_Resource_Block_Collection extends Mage_Cms_Model_Resource_Block_Collection
{

    protected function _construct()
    {
        $this->_init('extrarestful/block');
        $this->addFilterToMap('store', 'store_table.store_id');
    }

    /**
     * Join store IDs as comma separated value
     *
     * @see Mage_Core_Model_Resource_Db_Collection_Abstract::_initSelectFields()
     */
    protected function _prepareSelect(Varien_Db_Select $select)
    {
        $this->getSelect()
            ->joinLeft(
                array('stores_table' => $this->getTable('cms/block_store')),
                'stores_table.block_id=main_table.block_id',
                'GROUP_CONCAT(stores_table.store_id) AS stores')
            ->group('main_table.block_id');
        return parent::_prepareSelect($select);
    }

    /**
     * Unpack store IDs
     *
     * @see Mage_Core_Model_Resource_Db_Collection_Abstract::_afterLoad()
     */
    protected function _afterLoad()
    {
        foreach ($this as $block) {
            // if no stores then set an empty array
            $block->setStores(array_filter(explode(',', $block->getStores()), 'strlen'));
        }
        return $this;
    }
}
