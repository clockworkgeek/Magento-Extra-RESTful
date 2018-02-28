<?php

class Clockworkgeek_Extrarestful_Model_Api2_Category_Rest_Guest_V1
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
