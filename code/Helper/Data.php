<?php

class Clockworkgeek_Extrarestful_Helper_Data extends Mage_Core_Helper_Data
{

    public function isCollectionOverflowed(Varien_Data_Collection $collection, Mage_Api2_Model_Request $request)
    {
        $pageSize = (int) $request->getPageSize() ?: Mage_Api2_Model_Resource::PAGE_SIZE_DEFAULT;
        $pageNum = ((int) $request->getPageNumber() ?: 1) - 1;
        return $pageSize * $pageNum >= $collection->getSize();
    }
}
