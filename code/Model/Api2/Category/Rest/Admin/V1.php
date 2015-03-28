<?php

class Clockworkgeek_Extrarestful_Model_Api2_Category_Rest_Admin_V1
extends Clockworkgeek_Extrarestful_Model_Api2_Category
{

    protected function _create($data)
    {
        try {
            /* @var $category Mage_Catalog_Model_Category */
            $category = Mage::getModel('catalog/category');
            $category->setStoreId((int) $this->getRequest()->getParam('store', 0));

            $this->_prepareUpdatedCategory($category, $data);
            $category->save();
        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Mage_Api2_Exception $e) {
            throw $e;
        } catch (Exception $e) {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }

        return $this->_getLocation($category);
    }

    /**
     * Allow disabled categories for admin only
     *
     * @see Clockworkgeek_Extrarestful_Model_Api2_Category::_retrieve()
     */
    protected function _retrieve()
    {
        return $this->_getCategory()->getData();
    }

    protected function _update($data)
    {
        try {
            $category = $this->_getCategory();
            $this->_prepareUpdatedCategory($category, $data);
            $category->save();
        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Mage_Api2_Exception $e) {
            throw $e;
        } catch (Exception $e) {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }
    }

    protected function _delete()
    {
        try {
            $category = $this->_getCategory();
            $category->delete();
        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Exception $e) {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }
    }

    protected function _prepareUpdatedCategory(Mage_Catalog_Model_Category $category, $data)
    {
        $category->addData($data);

        // autocorrect path if new parent is submitted
        if (isset($data['parent_id'])) {
            /* @var $parent Mage_Catalog_Model_Category */
            $parent = Mage::getModel('catalog/category')->load($data['parent_id']);
            $category->setPath($parent->getPath());
        }
        $this->_validateCategory($category);

        return $this;
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
