<?php

/**
 * Outputs CMS blocks as HTML fragments
 *
 * @author Daniel Deady <daniel@clockworkgeek.com>
 * @license MIT
 */
class Clockworkgeek_Extrarestful_Model_Api2_Block extends Clockworkgeek_Extrarestful_Model_Api2_Abstract
{

    /**
     * @return Mage_Cms_Model_Block
     */
    protected function _loadModel()
    {
        $this->setFilter(Mage::getModel('extrarestful/api2_block_filter', $this));
        if ($this->_needsRenderer()) {
            $this->setRenderer(Mage::getModel('extrarestful/api2_block_renderer'));
        }

        $blockId = $this->getRequest()->getParam('id');
        /** @var $block Mage_Cms_Model_Block */
        $block = $this->getWorkingModel()
            ->setStoreId($this->_getStore()->getId())
            ->load($blockId);
        if ($blockId != $block->getId() && $blockId != $block->getIdentifier()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }

        if ($lastMod = strtotime($block->getUpdateTime())) {
            $this->getResponse()->setHeader('Last-Modified', date('r', $lastMod));
        }

        return $block;
    }

    protected function _getCollection()
    {
        $blocks = parent::_getCollection();
        if (!is_null($storeId = $this->getRequest()->getParam('store'))) {
            $blocks->addStoreFilter($storeId);
        }
        return $blocks;
    }

    /**
     * True if "text/html" is present and higher priority than other recognised mime types
     *
     * @return boolean
     */
    protected function _needsRenderer()
    {
        $types = $this->getRequest()->getAcceptTypes();
        $htmlPos = array_search(Clockworkgeek_Extrarestful_Model_Api2_Block_Renderer::MIME_TYPE, $types);
        if ($htmlPos !== false) {
            /** @var $helper Mage_Api2_Helper_Data */
            $helper = Mage::helper('api2');
            $adapters = $helper->getResponseRenderAdapters();
            foreach ($adapters as $item) {
                $typePos = array_search($item->type, $types);
                if ($typePos < $htmlPos) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }
}
