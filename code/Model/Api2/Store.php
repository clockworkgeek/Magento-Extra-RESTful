<?php

/**
 * Exposes store views
 *
 * @author Daniel Deady <daniel@clockworkgeek.com>
 * @license MIT
 */
class Clockworkgeek_Extrarestful_Model_Api2_Store extends Clockworkgeek_Extrarestful_Model_Api2_Abstract
{

    protected function _loadModel()
    {
        /** @var $store Mage_Core_Model_Store */
        $store = parent::_loadModel();
        $store->addData(array(
            'general_locale_code' => $store->getConfig('general/locale/code'),
            'general_locale_timezone' => $store->getConfig('general/locale/timezone'),
            'unsecure_base_url' => $store->getBaseUrl(),
            'secure_base_url' => $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, true)
        ));
        return $store;
    }

    protected function _loadCollection(Varien_Data_Collection_Db $stores)
    {
        /** @var $store Mage_Core_Model_Store */
        foreach ($stores as $store) {
            $store->addData(array(
                'unsecure_base_url' => $store->getBaseUrl(),
                'secure_base_url' => $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, true)
            ));
        }
    }
}
