<?php

class Clockworkgeek_Extrarestful_Model_Api2_Block extends Mage_Api2_Model_Resource
{

    protected function _retrieve()
    {
        $block = Mage::getModel('cms/block')
            ->setStoreId($this->_getStore()->getId())
            ->load($this->getRequest()->getParam('block'));
        if ($block->isObjectNew()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        $this->setFilter(Mage::getModel('extrarestful/api2_block_filter', $this));
        if ($this->_needsRenderer()) {
            $this->setRenderer(Mage::getModel('extrarestful/api2_block_renderer'));
        }
        return $block->getData();
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
