<?php

class Clockworkgeek_Extrarestful_Model_Api2_Review extends Mage_Api2_Model_Resource
{

    protected function _retrieveCollection()
    {
        $reviews = $this->_getReviews();
        if (Mage::helper('extrarestful')->isCollectionOverflowed($reviews, $this->getRequest())) {
            return array();
        }
        else {
            $this->_addRatingsToReviews($reviews);
            $collection = $reviews->toArray();
            return (array) @$collection['items'];
        }
    }

    /**
     * A collection of Mage_Review_Model_Review objects
     *
     * Requested filters are applied here.
     *
     * @return Mage_Review_Model_Resource_Review_Collection
     */
    protected function _getReviews()
    {
        /* @var $reviews Mage_Review_Model_Resource_Review_Collection */
        $reviews = Mage::getResourceModel('review/review_collection');

        // product reviews only
        // category and customer reviews are possible although unlikely
        if (($productId = $this->getRequest()->getParam('product'))) {
            $reviews->addEntityFilter(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE, $productId);
        }
        $reviews->addFilterToMap('product_id', 'entity_pk_value');
        $reviews->addExpressionFieldToSelect('product_id', 'entity_pk_value', array());

        if (! is_null($storeId = $this->getRequest()->getParam('store'))) {
            $reviews->addStoreFilter($storeId);
        }

        $reviews->addFilterToMap('status', 'status_code');
        $this->_applyCollectionModifiers($reviews);

        return $reviews;
    }

    /**
     * Adds a keyed object to every review where keys are localised titles and values are percentages
     *
     * @param Mage_Review_Model_Resource_Review_Collection $reviews
     */
    protected function _addRatingsToReviews(Mage_Review_Model_Resource_Review_Collection $reviews)
    {
        /* @var $emulation Mage_Core_Model_App_Emulation */
        $emulation = Mage::getModel('core/app_emulation');
        $environment = $emulation->startEnvironmentEmulation((int) $this->getRequest()->getParam('store'));

        /* @var $allRatings Mage_Rating_Model_Resource_Rating_Option_Vote_Collection */
        $allRatings = Mage::getModel('rating/rating_option_vote')->getCollection();

        // join title fields
        $allRatings->addRatingInfo($this->getRequest()->getParam('store'));

        // limit to our subset for efficiency
        $allRatings->addFieldToFilter('review_id', array('in' => $reviews->getAllIds()));

        // sort similar to order as added by admin for no particular reason
        $allRatings->addOrder('position', 'asc')->addOrder('rating.rating_id', 'asc');

        /* @var $review Mage_Review_Model_Review */
        foreach ($reviews as $review) {
            $ratings = array();
            /* @var $rating Mage_Rating_Model_Rating */
            foreach ($allRatings->getItemsByColumnValue('review_id', $review->getId()) as $rating) {
                $ratings[$rating->getRatingCode()] = $rating->getPercent();
            }
            if ($ratings) {
                $review->setRatings($ratings);
            }
        }

        $emulation->stopEnvironmentEmulation($environment);
    }
}
