<?php

class Clockworkgeek_Extrarestful_Model_Api2_Block_Rest_Customer_V1
extends Clockworkgeek_Extrarestful_Model_Api2_Block
{

    protected function _retrieveCollection()
    {
        $this->_critical(self::RESOURCE_NOT_FOUND);
    }
    }
