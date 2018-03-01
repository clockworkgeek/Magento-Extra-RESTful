<?php

class Clockworkgeek_Extrarestful_Model_Api2_Category extends Clockworkgeek_Extrarestful_Model_Api2_Abstract
{

    public function getWorkingModel()
    {
        $category = parent::getWorkingModel();
        $category->setStoreId($this->_getStore()->getId());
        return $category;
    }

    /**
     * Load product count after loading category
     *
     * @return Mage_Catalog_Model_Category
     * @see Clockworkgeek_Extrarestful_Model_Api2_Abstract::_loadModel()
     */
    protected function _loadModel()
    {
        $category = parent::_loadModel();
        if (in_array('product_count', $this->getFilter()->getAttributesToInclude())) {
            $count = $this->getProductCounts(array($category->getId()));
            $category->setProductCount((int) @$count[$category->getId()]);
        }
        return $category;
    }

    /**
     * Load product count after loading categories
     *
     * @see Clockworkgeek_Extrarestful_Model_Api2_Abstract::_loadCollection()
     */
    protected function _loadCollection(Varien_Data_Collection_Db $categories)
    {
        parent::_loadCollection($categories);

        if (in_array('product_count', $this->getFilter()->getAttributesToInclude())) {
            $counts = $this->getProductCounts($categories->getLoadedIds());
            foreach ($categories as $category) {
                $category->setProductCount((int) @$counts[$category->getId()]);
            }
        }
    }

    /**
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    protected function _getCollection()
    {
        /** @var $categories Mage_Catalog_Model_Resource_Category_Collection */
        $categories = parent::_getCollection();
        $categories->setStoreId($this->_getStore()->getId());
        if (($parentId = $this->getRequest()->getParam('parent'))) {
            $categories->addAttributeToFilter('parent_id', $parentId);
        }


        $storeId = $this->_getStore()->getId();
        if ($storeId) {
            // exclude wrong trees
            $rootCategoryId = Mage::app()->getStore($storeId)->getRootCategoryId();
            $categories->addFieldToFilter('path', array('regexp' => '1/'.$rootCategoryId.'(/|$)'));
        }
        else {
            // global root must always be hidden
            $categories->addFieldToFilter('path', array('neq' => '1'));
        }

        return $categories;
    }

    /**
     * Returns an associative array of categories and their immediate child counts
     *
     * @param array $categoryIds
     * @return array
     */
    public function getProductCounts($categoryIds)
    {
        // cannot use Mage_Catalog_Model_Resource_Product_Collection::addCountToCategories
        // it "correctly" applies is_anchor logic which doesn't fit the API
        /* @var $products Mage_Catalog_Model_Resource_Product_Collection */
        $products = Mage::getResourceModel('catalog/product_collection');
        $products->addStoreFilter($this->_getStore()->getId())
            ->setStoreId($this->_getStore()->getId())
            ->addAttributeToFilter('visibility', array(
                'neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE))
            ->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED));
        $select = $products->getProductCountSelect();
        $select->where('count_table.category_id IN (?)', $categoryIds);
        $select->where('count_table.is_parent = 1');
        return $products->getConnection()->fetchPairs($select);
    }

    /**
     * @see Mage_Customer_Model_Api2_Customer::_getResourceAttributes()
     */
    protected function _getResourceAttributes()
    {
        return $this->getEavAttributes(true, true);
    }
}
