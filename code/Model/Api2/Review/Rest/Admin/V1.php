<?php

class Clockworkgeek_Extrarestful_Model_Api2_Review_Rest_Admin_V1
extends Clockworkgeek_Extrarestful_Model_Api2_Review
{

    protected function _getReview()
    {
        $review = parent::_getReview();
        $status = $review->getStatusCollection()->getItemById($review->getStatusId());
        if ($status) {
            $review->setStatus($status->getStatusCode());
        }
        return $review;
    }

    protected function _getReviews()
    {
        $reviews = parent::_getReviews();
        $reviews->join(
            array('statuses' => $reviews->getTable('review/review_status')),
            'main_table.status_id=statuses.status_id',
            array('status' => 'status_code'));
        return $reviews;
    }

    protected function _update($data)
    {
        $review = $this->_prepareReview($data);
        $review->save();

        if (is_array($review->getRatings())) {
            // this bit copied from Mage_Adminhtml_Catalog_Product_ReviewController::saveAction
            $votes = Mage::getModel('rating/rating_option_vote')
                ->getResourceCollection()
                ->setReviewFilter($review->getId())
                ->addOptionInfo()
                ->load()
                ->addRatingOptions();
            foreach ($review->getRatings() as $ratingId=>$optionId) {
                if($vote = $votes->getItemByColumnValue('rating_id', $ratingId)) {
                    Mage::getModel('rating/rating')
                        ->setVoteId($vote->getId())
                        ->setReviewId($review->getId())
                        ->updateOptionVote($optionId);
                } else {
                    Mage::getModel('rating/rating')
                        ->setRatingId($ratingId)
                        ->setReviewId($review->getId())
                        ->addOptionVote($optionId, $review->getEntityPkValue());
                }
            }
            $review->aggregate();
        }
    }

    protected function _delete()
    {
        $this->_getReview()->delete();
    }
}
