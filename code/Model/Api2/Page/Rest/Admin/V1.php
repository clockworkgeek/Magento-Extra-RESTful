<?php

class Clockworkgeek_Extrarestful_Model_Api2_Page_Rest_Admin_V1 extends Clockworkgeek_Extrarestful_Model_Api2_Page
{

    /**
     * @return Mage_Cms_Model_Page
     */
    protected function _loadModel()
    {
        $pageId = $this->getRequest()->getParam('id');
        /* @var $page Mage_Cms_Model_Page */
        $page = $this->getWorkingModel()
            ->setStoreId($this->_getStore()->getId())
            ->load($pageId);
        if ($pageId != $page->getId() && $pageId != $page->getIdentifier()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        if (!$page->hasStores()) {
            $page->setStores((array) $page->getStoreId());
        }
        return $page;
    }

    protected function _saveModel($data)
    {
        $page = $this->_loadModel();
        $page->addData($data);

        // validate
        $helper = Mage::helper('extrarestful');
        if (is_null($page->getTitle())) {
            $this->_critical($helper->__('Title is required'), 400);
        }
        if (is_null($page->getIdentifier())) {
            $this->_critical($helper->__('Identifier is required'), 400);
        }
        if (!$page->getRootTemplate()) {
            $this->_critical($helper->__('Root Template is required'), 400);
        }
        $layouts = Mage::getSingleton('page/source_layout')->getOptions();
        if (!isset($layouts[$page->getRootTemplate()])) {
            $this->_critical($helper->__('Root Template is not recognised'), 400);
        }
        if (is_null($page->getStores()) || $page->getStores() === array()) {
            $this->_critical($helper->__('At least one store is required'), 400);
        }
        foreach ((array) $page->getStores() as $storeId) {
            try {
                Mage::app()->getStore($storeId);
            }
            catch (Mage_Core_Model_Store_Exception $e) {
                $this->_critical($helper->__("Store '$storeId' doesn't exist"), 400);
            }
        }

        // success
        return $page->save();
    }

    protected function _getCollection()
    {
        $pages = parent::_getCollection();
        if (!is_null($storeId = $this->getRequest()->getParam('store'))) {
            $pages->addStoreFilter($storeId);
        }
        return $pages;
    }
}
