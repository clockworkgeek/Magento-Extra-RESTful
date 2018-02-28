<?php

class Clockworkgeek_Extrarestful_Model_Api2_Abstract extends Mage_Api2_Model_Resource
{

    public function setApiUser(Mage_Api2_Model_Auth_User_Abstract $apiUser)
    {
        parent::setApiUser($apiUser);

        // the earliest opportunity we can confirm this
        // never allow inactive stores to be seen by public
        if ($this->getUserType() != Mage_Api2_Model_Auth_User_Admin::USER_TYPE && !$this->_getStore()->getIsActive()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }

        return $this;
    }

    /**
     * Takes a filtered array of fields and returns URL to new entity
     *
     * Calls <code>_saveModel</code> and descendants should override that.
     * Should not return bad URL, even if it means raising an error instead.
     *
     * @see Mage_Api2_Model_Resource::_create($filteredData)
     * @see Clockworkgeek_Extrarestful_Model_Api2_Abstract::_saveModel($data)
     * @throws Mage_Api2_Exception
     */
    protected function _create($data)
    {
        $model = $this->_saveModel($data);
        if ($this->getResponse()->isException()) {
            $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
        }
        return $this->_getLocation($model);
    }

    /**
     * Returns an unfiltered array if possible
     *
     * Calls <code>_loadModel</code> and descendants should override that.
     *
     * @see Mage_Api2_Model_Resource::_retrieve()
     * @see Clockworkgeek_Extrarestful_Model_Api2_Abstract::_loadModel()
     */
    protected function _retrieve()
    {
        return $this->_loadModel()->getData();
    }

    /**
     * Returns a nested array yet to be filtered
     *
     * If there are no records then an empty array is still necessary.
     *
     * @see Mage_Api2_Model_Resource::_retrieveCollection()
     */
    protected function _retrieveCollection()
    {
        $collection = $this->_getCollection();
        $this->_applyCollectionModifiers($collection);

        if ($this->getRequestedOffset() >= $collection->getSize()) {
            return array();
        }
        else {
            $this->_loadCollection($collection);
            $data = $collection->toArray();
            return (array) (@$data['items'] ?: $data);
        }
    }

    /**
     * Same as <code>_create</code> but an ID should be specified in the URL
     *
     * @see Mage_Api2_Model_Resource::_update($filteredData)
     * @see Clockworkgeek_Extrarestful_Model_Api2_Abstract::_create($data)
     * @throws Mage_Api2_Exception
     */
    protected function _update($data)
    {
        $this->_saveModel($data);
        if ($this->getResponse()->isException()) {
            $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
        }
    }

    /**
     * Deletes an entity with specified ID
     *
     * @see Mage_Api2_Model_Resource::_delete()
     */
    protected function _delete()
    {
        $this->_loadModel()->delete();
    }

    /**
     * Set a <code>Content-Length</code> header when possible
     *
     * This is more efficient than chunked transfers which have a small overhead per chunk.
     *
     * @see Mage_Api2_Model_Resource::_render()
     */
    protected function _render($data)
    {
        parent::_render($data);

        $response = $this->getResponse();
        if ($response->canSendHeaders()) {
            $length = array_sum(array_map('strlen', $response->getBody(true)));
            $response->setHeader('Content-Length', $length, true);
        }
    }

    /**
     * Loads an instance of working model with an ID of <code>:id</code>
     *
     * <code>:id</code> is parsed from the URL which matches a route from <code>api2.xml</code>.
     * If an appropriate model is not available then throw a 404 Not Found error.
     *
     * @return Mage_Core_Model_Abstract
     * @throws Mage_Api2_Exception
     */
    protected function _loadModel()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->getWorkingModel();
        if ($model->getResource() instanceof Mage_Eav_Model_Entity_Abstract) {
            // avoid joining more attributes than necessary
            $model->load($id, $this->getFilter()->getAttributesToInclude());
        }
        else {
            $model->load($id);
        }
        if ($id != $model->getId()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        return $model;
    }

    /**
     * Update an existing record or create a new one
     *
     * To check for existing records it is recommended to use <code>_loadModel</code>.
     *
     * @param array $data
     * @return Mage_Core_Model_Abstract
     * @see Clockworkgeek_Extrarestful_Model_Api2_Abstract::_loadModel()
     */
    protected function _saveModel($data)
    {
        $model = $this->_loadModel();
        $model->addData($data);
        return $model->save();
    }

    /**
     * Requested filters are applied here.
     *
     * @return Varien_Data_Collection_Db
     */
    protected function _getCollection()
    {
        $collection = $this->getWorkingModel()->getCollection();
        if ($collection instanceof Mage_Eav_Model_Entity_Collection_Abstract) {
            $collection->addAttributeToSelect($this->getFilter()->getAttributesToInclude());
        }
        return $collection;
    }

    /**
     * Load all entities within a collection and post-process them
     *
     * This resource should attempt to not trigger a load before this point.
     *
     * @param Varien_Data_Collection_Db $collection
     */
    protected function _loadCollection(Varien_Data_Collection_Db $collection)
    {
        $collection->load();
    }

    /**
     * Determine if requested range is past collection's available range
     *
     * e.g. If limit=10 & page=2 then offset will be 10.
     * The previous 10 entities are #0 to #9.
     *
     * @return integer
     */
    protected function getRequestedOffset()
    {
        $pageSize = (int) $this->getRequest()->getPageSize() ?: Mage_Api2_Model_Resource::PAGE_SIZE_DEFAULT;
        $pageNum = ((int) $this->getRequest()->getPageNumber() ?: 1) - 1;
        return $pageSize * $pageNum;
    }
}
