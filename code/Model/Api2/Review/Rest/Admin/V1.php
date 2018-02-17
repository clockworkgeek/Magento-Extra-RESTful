<?php

class Clockworkgeek_Extrarestful_Model_Api2_Review_Rest_Admin_V1
extends Clockworkgeek_Extrarestful_Model_Api2_Review
{

    protected function _getReviews()
    {
        $reviews = parent::_getReviews();
        $reviews->join(
            array('statuses' => $reviews->getTable('review/review_status')),
            'main_table.status_id=statuses.status_id',
            array('status' => 'status_code'));
        return $reviews;
    }
}
