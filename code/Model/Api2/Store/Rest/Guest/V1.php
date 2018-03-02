<?php

class Clockworkgeek_Extrarestful_Model_Api2_Store_Rest_Guest_V1
extends Clockworkgeek_Extrarestful_Model_Api2_Store
{

    protected function _getCollection()
    {
        /** @var $stores Mage_Core_Model_Resource_Store_Collection */
        $stores = parent::_getCollection();
        $stores->addFieldToFilter('is_active', true);
        return $stores;
    }
}
