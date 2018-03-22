<?php

/**
 * Lists categories but without paging and rearranged into a tree
 *
 * @author Daniel Deady <daniel@clockworkgeek.com>
 * @license MIT
 * @see Clockworkgeek_Extrarestful_Model_Api2_Category
 */
class Clockworkgeek_Extrarestful_Model_Api2_Categorytree extends Clockworkgeek_Extrarestful_Model_Api2_Category
{

    /**
     * Association data from category IDs to parent IDs
     *
     * @var array
     */
    protected $_parents;

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
        // preload relations data
        $this->_loadParents($categories);
        $data = $categories->walk('toArray');
        return (array) $data;
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
     * Keep ancestor information separate from collection data
     *
     * This makes it possible for <code>parent_id</code> to be filtered out
     * and yet still correctly render the tree.
     *
     * @param Varien_Data_Collection_Db $categories
     */
    protected function _loadParents(Varien_Data_Collection_Db $categories)
    {
        // getParentId is smart and checks the path too, increasing chances that necessary data is already loaded
        $this->_parents = $categories->walk('getParentId');
        if (count($this->_parents) < count($categories)) {
            $query = $categories->getSelect();
            $query->reset(Zend_Db_Select::COLUMNS)->columns(array('entity_id','parent_id'));
            $this->_parents = $categories->getConnection()->fetchPairs($query);
        }
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
        foreach ($categories as $categoryId => &$category) {
            $parent = @$this->_parents[$categoryId];
            if (isset($categories[$parent])) {
                $categories[$parent]['children'][] = &$category;
            }
            else {
                $data[] = &$category;
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
