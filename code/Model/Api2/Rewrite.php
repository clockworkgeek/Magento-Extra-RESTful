<?php

class Clockworkgeek_Extrarestful_Model_Api2_Rewrite extends Mage_Api2_Model_Resource
{

    protected function _retrieveCollection()
    {
        /* @var $rewrites Mage_Core_Model_Resource_Url_Rewrite_Collection */
        $rewrites = Mage::getResourceModel('core/url_rewrite_collection');
        $rewrites->addFieldToFilter('is_system', 0);
        if (! is_null($storeId = $this->getRequest()->getParam('store'))) {
            $rewrites->addStoreFilter($storeId);
        }

        $this->_applyCollectionModifiers($rewrites);
        $collection = $rewrites->load()->toArray();
        return (array) @$collection['items'];
    }
}
