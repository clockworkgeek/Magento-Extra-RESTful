<?php

class Clockworkgeek_Extrarestful_Model_Api2_Review_Rest_Guest_V1
extends Clockworkgeek_Extrarestful_Model_Api2_Review
{

    /**
     * A collection of approved review objects
     *
     * Guests do not get to see pending or rejected reviews.
     *
     * @see Clockworkgeek_Extrarestful_Model_Api2_Review::_getReviews()
     */
    protected function _getReviews()
    {
        $reviews = parent::_getReviews();
        $reviews->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED);
        return $reviews;
    }
}
