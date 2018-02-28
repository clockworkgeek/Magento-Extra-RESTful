<?php

class Clockworkgeek_Extrarestful_Model_Api2_Review_Rest_Admin_V1
extends Clockworkgeek_Extrarestful_Model_Api2_Review
{

    protected function _loadModel()
    {
        $review = parent::_loadModel();
        $statuses = Mage::helper('review')->getReviewStatuses();
        if (isset($statuses[$review->getStatusId()])) {
            $review->setStatus($statuses[$review->getStatusId()]);
        }
        return $review;
    }
}
