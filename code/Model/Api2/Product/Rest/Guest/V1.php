<?php

class Clockworkgeek_Extrarestful_Model_Api2_Product_Rest_Guest_V1 extends Clockworkgeek_Extrarestful_Model_Api2_Product_Public
{

    protected function _getCollection()
    {
        return parent::_getCollection()->addPriceData(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
    }
}
