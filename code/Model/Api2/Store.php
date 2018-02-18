<?php

class Clockworkgeek_Extrarestful_Model_Api2_Store extends Mage_Api2_Model_Resource
{

    protected function _retrieveCollection()
    {
        $stores = array();
        /* @var $store Mage_Core_Model_Store */
        foreach (Mage::app()->getStores() as $id => $store) {
            if ($store->getIsActive()) {
                $stores[$id] = $store->getData();
            }
        }
        return $stores;
    }
}
