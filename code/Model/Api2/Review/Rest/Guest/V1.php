<?php

class Clockworkgeek_Extrarestful_Model_Api2_Review_Rest_Guest_V1
extends Clockworkgeek_Extrarestful_Model_Api2_Review
{

    /**
     * Guests do not get to see pending or rejected reviews.
     *
     * @see Clockworkgeek_Extrarestful_Model_Api2_Review::_getReviews()
     */
    protected function _getCollection()
    {
        $reviews = parent::_getCollection();
        $reviews->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED);
        return $reviews;
    }

    protected function _create($data)
    {
        if (!Mage::getStoreConfigFlag('catalog/review/allow_guest')) {
            $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED);
        }
        else {
            return parent::_create($data);
        }
    }
}
