<?php

class Clockworkgeek_Extrarestful_Model_Api2_Review_Rest_Admin_V1
extends Clockworkgeek_Extrarestful_Model_Api2_Review
{

    protected function _loadModel()
    {
        $review = parent::_loadModel();
        $status = $review->getStatusCollection()->getItemById($review->getStatusId());
        if ($status) {
            $review->setStatus($status->getStatusCode());
        }
        return $review;
    }

    protected function _getCollection()
    {
        $reviews = parent::_getCollection();
        $reviews->join(
            array('statuses' => $reviews->getTable('review/review_status')),
            'main_table.status_id=statuses.status_id',
            array('status' => 'status_code'));
        $reviews->addFilterToMap('status', 'status_code');
        return $reviews;
    }
}
