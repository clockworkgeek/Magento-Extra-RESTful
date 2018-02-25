<?php

class Clockworkgeek_Extrarestful_Model_Api2_Review extends Mage_Api2_Model_Resource
{

    protected function _retrieve()
    {
        $review = $this->_getReview();
        if (in_array('ratings', $this->getFilter()->getAttributesToInclude())) {
            $reviews = new Varien_Data_Collection();
            $reviews->addItem($review);
            $this->_addRatingsToReviews($reviews);
        }
        return $review->getData();
    }

    /**
     * @return Mage_Review_Model_Review
     */
    protected function _getReview()
    {
        $reviewId = $this->getRequest()->getParam('id');
        $review = Mage::getModel('review/review')->load($reviewId);
        if ($reviewId != $review->getId()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        return $review;
    }

    protected function _retrieveCollection()
    {
        $reviews = $this->_getReviews();
        if (Mage::helper('extrarestful')->isCollectionOverflowed($reviews, $this->getRequest())) {
            return array();
        }
        else {
            if (in_array('ratings', $this->getFilter()->getAttributesToInclude())) {
                $this->_addRatingsToReviews($reviews);
            }
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
        if (in_array('stores', $this->getFilter()->getAttributesToInclude())) {
            $reviews->addStoreData();
        }

        $reviews->addFilterToMap('status', 'status_code');
        $this->_applyCollectionModifiers($reviews);

        return $reviews;
    }

    /**
     * Adds a keyed object to every review where keys are localised titles and values are percentages
     *
     * This causes collection to load.
     *
     * @param Mage_Review_Model_Resource_Review_Collection $reviews
     */
    protected function _addRatingsToReviews(Varien_Data_Collection $reviews)
    {
        /* @var $emulation Mage_Core_Model_App_Emulation */
        $emulation = Mage::getModel('core/app_emulation');
        $environment = $emulation->startEnvironmentEmulation((int) $this->getRequest()->getParam('store'));

        /* @var $allRatings Mage_Rating_Model_Resource_Rating_Option_Vote_Collection */
        $allRatings = Mage::getModel('rating/rating_option_vote')->getCollection();

        // join title fields
        $allRatings->addRatingInfo($this->getRequest()->getParam('store'));

        // limit to our subset for efficiency
        $allRatings->addFieldToFilter('review_id', array('in' => $reviews->getColumnValues('review_id')));

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

    /**
     * Validates and instantiates a review object
     *
     * Whether a newly constructed object or a recently loaded one,
     * the result is ready to be saved.
     *
     * @param array $data
     * @return Mage_Review_Model_Review
     * @throws Mage_Api2_Exception
     */
    protected function _prepareReview($data)
    {
        if ($productId = (@$data['product_id'] ?: $this->getRequest()->getParam('product'))) {
            $product = Mage::getModel('catalog/product')->load($productId);
            if ($product->isObjectNew()) {
                $this->_error(
                    Mage::helper('extrarestful')->__('Product doesn\'t exist'),
                    Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
        }

        if (@$data['status'] && !is_numeric($data['status'])) {
            /* @var $statuses Mage_Review_Model_Resource_Review_Status_Collection */
            $statuses = Mage::getResourceSingleton('review/review_status_collection');
            $status = $statuses->getItemByColumnValue('status_code', $data['status']);
            if ($status) {
                $data['status_id'] = $status->getId();
            }
        }

        $review = $this->_getReview();
        if ($review->isObjectNew()) {
            if (!$productId) {
                $this->_error(
                    Mage::helper('extrarestful')->__('Product ID can\'t be empty'),
                    Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }

            $storeId = $this->_getStore()->getId();
            // default values
            $review->setData(array(
                'entity_id' => $review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE),
                'entity_pk_value' => $product->getId(),
                'status_id' => Mage_Review_Model_Review::STATUS_PENDING,
                'store_id' => $storeId,
                'stores' => $storeId
            ));
        }
        // overwrite with user values
        $review->addData($data);

        if (isset($data['ratings'])) {
            $review->setRatings($this->_getRatingOptions($data['ratings']));
        }

        $validate = $review->validate();
        if (is_array($validate)) {
            foreach ($validate as $message) {
                $this->_error($message, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
        }
        if ($this->getResponse()->isException()) {
            $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
        }

        return $review;
    }

    /**
     * The complement to _addRatingsToReviews
     *
     * Attempts to match ratings' names to store specific values.
     * Percentages are rounded up to next multiple of 20 and matched to option_id.
     * Result is keyed by rating_id and may be used to save rating votes directly.
     *
     * @param array $ratings
     * @return array
     */
    protected function _getRatingOptions($ratings)
    {
        $helper = Mage::helper('extrarestful');
        $storeId = $this->_getStore()->getId();
        /* @var $ratings Mage_Rating_Model_Resource_Rating_Collection */
        static $allRatings;
        if (!$allRatings) {
            $allRatings = Mage::getResourceModel('rating/rating_collection');
            $allRatings->addRatingPerStoreName($storeId);
            $allRatings->addOptionToItems();
        }

        $optionIds = array();
        foreach ($ratings as $code => $percentage) {
            /* @var $rating Mage_Rating_Model_Rating */
            if ($rating = $allRatings->getItemByColumnValue('rating_code', $code)) {
                if (!is_numeric($percentage) || $percentage < 0 || $percentage > 100) {
                    $this->_error($helper->__($code.' isn\'t a percentage'), Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                }
                $options = $rating->getOptions();
                $index = max((int) ceil($percentage * count($options) / 100) - 1, 0);
                if (isset($options[$index])) {
                    $optionIds[$rating->getId()] = $options[$index]->getId();
                }
            }
            else {
                $this->_error($helper->__($code.' isn\'t a rating for this store'), Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
        }

        return $optionIds;
    }

    protected function _create($data)
    {
        $review = $this->_prepareReview($data);
        $review->save();

        if (is_array($review->getRatings())) {
            foreach ($review->getRatings() as $ratingId => $optionId) {
                Mage::getModel('rating/rating')
                    ->setRatingId($ratingId)
                    ->setReviewId($review->getId())
                    ->addOptionVote($optionId, $review->getProductId());
            }
        }
        $review->aggregate();

        return $this->_getLocation($review);
    }
}
