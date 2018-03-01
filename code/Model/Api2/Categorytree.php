<?php

/**
 * Generic API methods here, specialise elsewhere
 */
class Clockworkgeek_Extrarestful_Model_Api2_Categorytree extends Clockworkgeek_Extrarestful_Model_Api2_Category
{

    /**
     * Retrieve data without pagination
     *
     * @see Mage_Api2_Model_Resource::_retrieveCollection()
     */
    protected function _retrieveCollection()
    {
        $categories = $this->_getCollection();
        // filter no paging
        $this->_applyFilter($categories);
        // add product counts
        $this->_loadCollection($categories);
        $data = $categories->toArray();
        return (array) (@$data['items'] ?: $data);
    }

    /**
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    protected function _getCollection()
    {
        // borrow attribute filters from other category resource
        /** @var $source Clockworkgeek_Extrarestful_Model_Api2_Category */
        $source = $this->_getSubModel('category', $this->getRequest()->getParams());
        $this->setFilter($source->getFilter());

        $categories = parent::_getCollection();

        // final order will be different after placing in a tree, but still affected by 'position'
        $categories->addOrderField('position');
        return $categories;
    }

    /**
     * @return array|Mage_Core_Model_Store[]
     */
    protected function _getStores()
    {
        return Mage::app()->getStores();
    }

    /**
     * Rearrange list into a tree after filter object has applied collectionOut()
     *
     * {@inheritDoc}
     * @see Mage_Api2_Model_Resource::_render()
     */
    protected function _render($categories)
    {
        $data = array();
        foreach ($categories as &$category) {
            if (@$category['level'] <= 1) {
                unset($category['parent_id']);
                foreach ($this->_getStores() as $store) {
                    if ($store->getRootCategoryId() == @$category['entity_id']) {
                        $category['stores'][] = $store->getId();
                    }
                }
                $data[] = &$category;
            }
            elseif ($parent = @$category['parent_id']) {
                $categories[$parent]['children'][] = &$category;
            }
        }
        return parent::_render($data);
    }

    /**
     * Do not show EAV attributes for category tree since it inherits from category list
     *
     * @see Clockworkgeek_Extrarestful_Model_Api2_Category::_getResourceAttributes()
     */
    protected function _getResourceAttributes()
    {
        return array();
    }
}
