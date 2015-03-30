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
        $data = $this->_getCategories()->load()->toArray();
        return isset($data['items']) ? $data['items'] : $data;
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
     * @see Mage_Customer_Model_Api2_Customer::_getResourceAttributes()
     */
    protected function _getResourceAttributes()
    {
        return $this->getEavAttributes(true, true);
    }
}
