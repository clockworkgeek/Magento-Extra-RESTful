<?php

class Clockworkgeek_Extrarestful_Model_Resource_Cms_Page_Collection extends Mage_Cms_Model_Resource_Page_Collection
{

    protected function _construct()
    {
        $this->_init('extrarestful/cms_page');
        $this->addFilterToMap('page_id', 'main_table.page_id');
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
                array('stores_table' => $this->getTable('cms/page_store')),
                'stores_table.page_id=main_table.page_id',
                'GROUP_CONCAT(stores_table.store_id) AS stores')
            ->group('main_table.page_id');
        return parent::_prepareSelect($select);
    }

    /**
     * Unpack store IDs
     *
     * @see Mage_Core_Model_Resource_Db_Collection_Abstract::_afterLoad()
     */
    protected function _afterLoad()
    {
        foreach ($this as $page) {
            // if no stores then set an empty array
            $page->setStores(array_filter(explode(',', $page->getStores()), 'strlen'));
        }
        return $this;
    }
}
