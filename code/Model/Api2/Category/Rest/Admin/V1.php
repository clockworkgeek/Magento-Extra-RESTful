<?php

/**
 * @author Daniel Deady <daniel@clockworkgeek.com>
 * @license MIT
 */
class Clockworkgeek_Extrarestful_Model_Api2_Category_Rest_Admin_V1
extends Clockworkgeek_Extrarestful_Model_Api2_Category
{

    /**
     * Unfiltered products because admin can see everything
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _getProductCollection()
    {
        return Mage::getResourceModel('catalog/product_collection');
    }

    protected function _saveModel($data)
    {
        $category = parent::_loadModel();
        $category->addData($data);

        // autocorrect path if new parent is submitted
        if (isset($data['parent_id'])) {
            /** @var $parent Mage_Catalog_Model_Category */
            $parent = Mage::getModel('catalog/category')->load($data['parent_id']);
            $category->setPath($parent->getPath());
        }
        $this->_validateCategory($category);

        return $category->save();
    }

    protected function _validateCategory(Mage_Catalog_Model_Category $category)
    {
        $errors = $category->validate();

        // 'required' attrs are, in fact, not
        if (@$errors['available_sort_by'] === true) {
            unset($errors['available_sort_by']);
        }
        if (@$errors['default_sort_by'] === true) {
            unset($errors['default_sort_by']);
        }

        // parent_id should be required but is not EAV
        if (! $category->hasParentId()) {
            $errors['parent_id'] = true;
        }

        if ($errors) {
            foreach ($errors as $attribute => $error) {
                if ($error === true) {
                    $error = $attribute . ' is required';
                }
                $this->_error($error, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
            $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
        }
    }
}
