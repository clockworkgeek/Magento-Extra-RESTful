<?php

/**
 * Exposes custom URL rewrites
 *
 * @author Daniel Deady <daniel@clockworkgeek.com>
 * @license MIT
 * @method Mage_Core_Model_Url_Rewrite _loadModel()
 */
class Clockworkgeek_Extrarestful_Model_Api2_Rewrite extends Clockworkgeek_Extrarestful_Model_Api2_Abstract
{

    protected function _saveModel($data)
    {
        $rewrite = $this->_loadModel();
        $rewrite->addData($data);
        /** @var $catalogUrl Mage_Catalog_Model_Url */
        $catalogUrl = Mage::getModel('catalog/url');

        if (!$rewrite->getStoreId()) {
            $this->_error('Store ID is required', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }

        if (!in_array($rewrite->getOptions(), array(null, '', 'R', 'RP'))) {
            $this->_error('Options must be one of "", "R", "RP"', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }

        if ($rewrite->getProductId()) {
            // only load minimum attributes necessary
            $product = Mage::getModel('catalog/product')->load($rewrite->getProductId(), array('entity_id', 'url_key'));
            // should not be possible to have a bad entity ID because of database's foreign keys but check anyway
            if ($product->isObjectNew()) {
                $this->_error('Product ID does not exist', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
        }
        else {
            $product = null;
        }

        if ($rewrite->getCategoryId()) {
            $category = Mage::getModel('catalog/category')->load($rewrite->getCategoryId(), array('entity_id', 'parent_id', 'level', 'url_key'));
            if ($category->isObjectNew()) {
                $this->_error('Category ID does not exist', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
        }
        else {
            $category = null;
        }

        // empty paths are allowed, nulls and complex types are not
        if (!is_scalar($rewrite->getRequestPath())) {
            // try to fill in missing path
            if ($category) {
                // category path lookup requires a frontend store ID or there will be fatal error
                $category->setStoreId($rewrite->getStoreId() ?: Mage::app()->getDefaultStoreView()->getId());
                $rewrite->setRequestPath($catalogUrl->generatePath('request', $product, $category));
            }
            else {
                $this->_error('Request path is required', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
        }

        if (!is_scalar($rewrite->getTargetPath())) {
            if ($product || $category) {
                $rewrite->setTargetPath($catalogUrl->generatePath('target', $product, $category));
            }
            else {
                $this->_error('Target path is required', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
        }

        // mimic normal catalog ID paths
        if ($product || $category) {
            $rewrite->setIdPath($catalogUrl->generatePath('id', $product, $category));
            // compare new path against existing paths
            if (!$rewrite->getId() && $rewrite->getResource()->getRequestPathByIdPath($rewrite->getIdPath(), $rewrite->getStoreId())) {
                $this->_error('A rewrite already exists for this product/category and store', 409);
            }
        }
        else {
            // this path will not be used so it can be random
            // overwrite any previous value just in case it was related to catalog
            $rewrite->setIdPath($catalogUrl->generateUniqueIdPath());
        }

        if (!$this->getResponse()->isException()) {
            // only internally generated requests may be system type
            $rewrite->setIsSystem(false)->save();
        }
        return $rewrite;
    }
}
