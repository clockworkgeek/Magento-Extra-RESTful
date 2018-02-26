<?php

class Clockworkgeek_Extrarestful_Model_Api2_Page_Rest_Admin_V1 extends Mage_Api2_Model_Resource
{

    protected function _create($data)
    {
        $page = $this->_savePage($data);
        return $this->_getLocation($page);
    }

    protected function _retrieve()
    {
        $page = $this->_getPage();
        if (!$page->hasStores()) {
            $page->setStores((array) $page->getStoreId());
        }
        return $page->getData();
    }

    /**
     * @return Mage_Cms_Model_Page
     */
    protected function _getPage()
    {
        $pageId = $this->getRequest()->getParam('id');
        /* @var $page Mage_Cms_Model_Page */
        $page = Mage::getModel('cms/page')
            ->setStoreId($this->_getStore()->getId())
            ->load($pageId);
        if ($pageId != $page->getId() && $pageId != $page->getIdentifier()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        return $page;
    }

    protected function _savePage($data)
    {
        $page = $this->_getPage();
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

    protected function _retrieveCollection()
    {
        /* @var $pages Mage_Cms_Model_Resource_Page_Collection */
        $pages = Mage::getResourceModel('cms/page_collection');
        if ($this->getRequest()->getParam('store')) {
            $pages->addStoreFilter($this->_getStore());
        }
        $this->_applyCollectionModifiers($pages);
        if (Mage::helper('extrarestful')->isCollectionOverflowed($pages, $this->getRequest())) {
            return array();
        }
        else {
            if (in_array('stores', $this->getFilter()->getAttributesToInclude())) {
                $pages->join(
                    array('store_table' => 'cms/page_store'),
                    'store_table.page_id=main_table.page_id',
                    'GROUP_CONCAT(store_id) AS stores');
                $pages->getSelect()->group('main_table.page_id');
                foreach ($pages as $page) {
                    // if no stores then set an empty array
                    $page->setStores(array_filter(explode(',', $page->getStores()), 'strlen'));
                }
            }

            $collection = $pages->toArray();
            return @$collection['items'];
        }
    }

    protected function _update($data)
    {
        $this->_savePage($data);
    }

    protected function _delete()
    {
        $this->_getPage()->delete();
    }
}
