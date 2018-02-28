<?php

class Clockworkgeek_Extrarestful_Model_Api2_Review_Rest_Customer_V1
extends Clockworkgeek_Extrarestful_Model_Api2_Review
{

    /**
     * Hides ID and Status for customers other than the calling one
     *
     * @see Clockworkgeek_Extrarestful_Model_Api2_Review::_retrieveCollection()
     */
    protected function _loadCollection(Varien_Data_Collection_Db $reviews)
    {
        /* @var $review Mage_Review_Model_Review */
        foreach ($reviews as $review) {
            if ($review->getCustomerId() != $this->getApiUser()->getUserId()) {
                $review->unsCustomerId()->unsStatus();
            }
        }
    }

    /**
     * Customers do not get to see pending or rejected reviews.
     *
     * @see Clockworkgeek_Extrarestful_Model_Api2_Review::_getReviews()
     */
    protected function _getCollection()
    {
        $reviews = parent::_getCollection();
        $reviews->addActiveCustomer($this->getApiUser()->getUserId());
        return $reviews;
    }

    protected function _saveModel($data)
    {
        $data['customer_id'] = $this->getApiUser()->getUserId();
        return parent::_saveModel($data);
    }
}
