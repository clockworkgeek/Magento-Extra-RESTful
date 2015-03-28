<?php

class Clockworkgeek_Extrarestful_Model_Api2_Category_Rest_Admin_V1
extends Clockworkgeek_Extrarestful_Model_Api2_Category
{

    /**
     * Allow disabled categories for admin only
     *
     * @see Clockworkgeek_Extrarestful_Model_Api2_Category::_retrieve()
     */
    public function _retrieve()
    {
        $category = $this->_getCategory();
        if (! $category->getId()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        return $category->getData();
    }
}
