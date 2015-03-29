<?php

class Clockworkgeek_Extrarestful_Model_Api2_Store extends Mage_Api2_Model_Resource
{

    protected function _retrieveCollection()
    {
        $stores = array();
        foreach (Mage::app()->getStores() as $id => $store) {
            $stores[$id] = $store->getData();
        }
        return $stores;
    }
}
