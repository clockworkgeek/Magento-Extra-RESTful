<?php

/**
 * Modified review collection for working with status codes
 *
 * @author Daniel Deady <daniel@clockworkgeek.com>
 * @license MIT
 */
class Clockworkgeek_Extrarestful_Model_Resource_Review_Collection extends Mage_Review_Model_Resource_Review_Collection
{

    protected function _construct()
    {
        parent::_construct();
        $this->_init('extrarestful/review');
    }

    /**
     * Status is approved OR customer is current user
     *
     * @param int $customerId
     * @return Clockworkgeek_Extrarestful_Model_Resource_Review_Collection
     */
    public function addActiveCustomer($customerId)
    {
        $this->addFieldToFilter(array(
            'main_table.status_id',
            'detail.customer_id'
        ), array(
            Mage_Review_Model_Review::STATUS_APPROVED,
            $customerId
        ));
        return $this;
    }

    public function addStatusCodes()
    {
        $this->getSelect()->joinLeft(
            array('statuses' => $this->getTable('review/review_status')),
            'main_table.status_id=statuses.status_id',
            array('status' => 'status_code'));
        $this->addFilterToMap('status', 'status_code');
        return $this;
    }
}
