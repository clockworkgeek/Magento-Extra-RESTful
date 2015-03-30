<?php

class Clockworkgeek_Extrarestful_Model_Api2_Review extends Mage_Api2_Model_Resource
{

    protected function _retrieveCollection()
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
        $this->_applyCollectionModifiers($reviews);

        $collection = $reviews->toArray();
        return (array) @$collection['items'];
    }
}
