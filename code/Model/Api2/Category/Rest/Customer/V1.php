<?php

/**
 * Inactive categories cannot be seen by public users
 *
 * @author Daniel Deady <daniel@clockworkgeek.com>
 * @license MIT
 */
class Clockworkgeek_Extrarestful_Model_Api2_Category_Rest_Customer_V1
extends Clockworkgeek_Extrarestful_Model_Api2_Category
{

    protected function _loadModel()
    {
        $category = parent::_loadModel();
        if (! $category->getIsActive()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        return $category;
    }

    protected function _getCollection()
    {
        $categories = parent::_getCollection();
        $categories->addIsActiveFilter();
        return $categories;
    }
}
