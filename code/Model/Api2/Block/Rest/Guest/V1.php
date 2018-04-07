<?php

/**
 * Deny collection retrieval to public users
 *
 * @author Daniel Deady <daniel@clockworkgeek.com>
 * @license MIT
 */
class Clockworkgeek_Extrarestful_Model_Api2_Block_Rest_Guest_V1
extends Clockworkgeek_Extrarestful_Model_Api2_Block
{

    /**
     * Replace "abstract filter" with a translating one
     *
     * Abstract filter casts <code>is_active</code> to boolean,
     * but that field is not visible here so the filter is not needed.
     *
     * @see Clockworkgeek_Extrarestful_Model_Api2_Block::_loadModel()
     */
    protected function _loadModel()
    {
        $this->setFilter(Mage::getModel('extrarestful/api2_block_filter', $this));
        return parent::_loadModel();
    }

    protected function _retrieveCollection()
    {
        $this->_critical(self::RESOURCE_NOT_FOUND);
    }
}
