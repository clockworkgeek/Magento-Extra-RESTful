<?php

/**
 * Generic API methods here, specialise elsewhere
 */
class Clockworkgeek_Extrarestful_Model_Api2_Category extends Mage_Api2_Model_Resource
{

    /**
     * Retrieve single category by entity ID
     *
     * @return array
     */
    protected function _retrieve()
    {
        $category = $this->_getCategory();
        if (! $category->getIsActive()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        if (in_array('product_count', $this->getFilter()->getAttributesToInclude())) {
            $count = $this->getProductCounts(array($category->getId()));
            $category->setProductCount((int) @$count[$category->getId()]);
        }
        return $category->getData();
    }

    /**
     * @return Mage_Catalog_Model_Category
     */
    protected function _getCategory()
    {
        /* @var $category Mage_Catalog_Model_Category */
        $category = Mage::getModel('catalog/category');
        $category->setStoreId($this->getRequest()->getParam('store'));

        $categoryId = $this->getRequest()->getParam('id');
        $category->load($categoryId);

        if (! $category->getId()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }

        return $category;
    }

    protected function _retrieveCollection()
    {
        $categories = $this->_getCategories();
        if (Mage::helper('extrarestful')->isCollectionOverflowed($categories, $this->getRequest())) {
            return array();
        }
        else {
            $categories->load();
            if (in_array('product_count', $this->getFilter()->getAttributesToInclude())) {
                $counts = $this->getProductCounts($categories->getLoadedIds());
                foreach ($categories as $category) {
                    $category->setProductCount((int) @$counts[$category->getId()]);
                }
            }
            $data = $categories->toArray();
            return isset($data['items']) ? $data['items'] : $data;
        }
    }

    /**
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    protected function _getCategories()
    {
        /* @var $categories Mage_Catalog_Model_Resource_Category_Collection */
        $categories = Mage::getResourceModel('catalog/category_collection');
        $categories->setStoreId($this->getRequest()->getParam('store'));
        if (($parentId = $this->getRequest()->getParam('parent'))) {
            $categories->addAttributeToFilter('parent_id', $parentId);
        }
        $categories->addAttributeToSelect(array_keys(
            $this->getAvailableAttributes($this->getUserType(), Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ)
        ));

        // global root must always be hidden
        $categories->addFieldToFilter('path', array('neq' => '1'));

        // if not admin, filter is_active
        if (Mage_Api2_Model_Auth_User_Admin::USER_TYPE != $this->getUserType()) {
            $categories->addIsActiveFilter();
        }

        $this->_applyCollectionModifiers($categories);
        return $categories;
    }

    /**
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
