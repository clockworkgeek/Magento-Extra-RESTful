<?php

/**
 * Exposes catalog product reviews
 *
 * Adds a <code>ratings</code> object to every review where keys are localised titles and values are percentages.
 *
 * @author Daniel Deady <daniel@clockworkgeek.com>
 * @license MIT
 */
class Clockworkgeek_Extrarestful_Model_Api2_Review extends Clockworkgeek_Extrarestful_Model_Api2_Abstract
{

    /**
     * @return Clockworkgeek_Extrarestful_Model_Review
     * @see Clockworkgeek_Extrarestful_Model_Api2_Abstract::_loadModel()
     */
    protected function _loadModel()
    {
        $review = parent::_loadModel();
        if (in_array('ratings', $this->getFilter()->getAttributesToInclude())) {
            $reviews = new Varien_Data_Collection();
            $reviews->addItem($review);
            $this->_addRatingsToReviews($reviews);
        }

        if ($lastMod = strtotime($review->getCreatedAt())) {
            // not strictly LAST modified but a review is expected to change very little
            $this->getResponse()->setHeader('Last-Modified', date('r', $lastMod));
        }

        return $review;
    }

    /**
     * A collection of Clockworkgeek_Extrarestful_Model_Review objects
     *
     * Requested filters are applied here.
     *
     * @return Clockworkgeek_Extrarestful_Model_Resource_Review_Collection
     */
    protected function _getCollection()
    {
        /** @var $reviews Clockworkgeek_Extrarestful_Model_Resource_Review_Collection */
        $reviews = parent::_getCollection();

        // product reviews only
        // category and customer reviews are possible although unlikely
        if (($productId = $this->getRequest()->getParam('product'))) {
            $reviews->addEntityFilter(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE, $productId);
        }
        $reviews->addFilterToMap('product_id', 'entity_pk_value');
        $reviews->addExpressionFieldToSelect('product_id', 'entity_pk_value', array());

        $reviews->addStoreFilter($this->_getStore()->getId());
        if (in_array('stores', $this->getFilter()->getAttributesToInclude())) {
            $reviews->addStoreData();
        }
        if (in_array('status', $this->getFilter()->getAttributesToInclude())) {
            $reviews->addStatusCodes();
        }

        return $reviews;
    }

    protected function _loadCollection(Varien_Data_Collection_Db $reviews)
    {
        if (in_array('ratings', $this->getFilter()->getAttributesToInclude())) {
            $this->_addRatingsToReviews($reviews);
        }
        parent::_loadCollection($reviews);
    }

    /**
     * Adds a keyed object to every review where keys are localised titles and values are percentages
     *
     * @param Varien_Data_Collection $reviews
     */
    protected function _addRatingsToReviews(Varien_Data_Collection $reviews)
    {
        $storeId = $this->_getStore()->getId();

        /** @var $allRatings Mage_Rating_Model_Resource_Rating_Option_Vote_Collection */
        $allRatings = Mage::getModel('rating/rating_option_vote')->getCollection();

        // join title fields
        $allRatings->addRatingInfo($storeId);

        // limit to our subset for efficiency
        $allRatings->addFieldToFilter('review_id', array('in' => $reviews->getColumnValues('review_id')));

        // sort similar to order as added by admin for no particular reason
        $allRatings->addOrder('position', 'asc')->addOrder('rating.rating_id', 'asc');

        /** @var $review Mage_Review_Model_Review */
        foreach ($reviews as $review) {
            $ratings = array();
            /** @var $rating Mage_Rating_Model_Rating */
            foreach ($allRatings->getItemsByColumnValue('review_id', $review->getId()) as $rating) {
                $ratings[$rating->getRatingCode()] = $rating->getPercent();
            }
            if ($ratings) {
                $review->setRatings($ratings);
            }
        }
    }

    /**
     * Validates and instantiates a review object
     *
     * @param array $data
     * @return Mage_Review_Model_Review
     * @throws Mage_Api2_Exception
     */
    protected function _saveModel($data)
    {
        if ($productId = (@$data['product_id'] ?: $this->getRequest()->getParam('product'))) {
            // checking getSize() is quicker than loading a whole entity
            $products = Mage::getResourceModel('catalog/product_collection');
            $products->addIdFilter($productId);
            if ($products->getSize() == 0) {
                $this->_error(
                    Mage::helper('extrarestful')->__('Product doesn\'t exist'),
                    Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
        }

        if (@$data['status'] && !is_numeric($data['status'])) {
            /** @var $statuses Mage_Review_Model_Resource_Review_Status_Collection */
            $statuses = Mage::getResourceSingleton('review/review_status_collection');
            $status = $statuses->getItemByColumnValue('status_code', $data['status']);
            if ($status) {
                $data['status_id'] = $status->getId();
            }
        }

        $review = $this->_loadModel();
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
                'entity_pk_value' => $productId,
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
        if (!$this->getResponse()->isException()) {
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
            }
            $review->aggregate();

            // 202 Accepted because review isn't approved/published yet
            $this->getResponse()->setHttpResponseCode(202);
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
        /** @var $ratings Mage_Rating_Model_Resource_Rating_Collection */
        static $allRatings;
        if (!$allRatings) {
            $allRatings = Mage::getResourceModel('rating/rating_collection');
            $allRatings->addRatingPerStoreName($storeId);
            $allRatings->addOptionToItems();
        }

        $optionIds = array();
        foreach ($ratings as $code => $percentage) {
            /** @var $rating Mage_Rating_Model_Rating */
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
}
