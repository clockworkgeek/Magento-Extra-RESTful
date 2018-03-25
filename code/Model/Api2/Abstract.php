<?php

/**
 * Useful base class for working with Varien models and collections in an API
 *
 * This class handles the usual CRUD operations automatically.
 * Descendants typically override <code>_loadModel</code>, <code>_saveModel</code>,
 * <code>_getCollection</code>, and <code>_loadCollection</code>.
 * Various enhancements are supplied too, such as better pagination.
 *
 * @author Daniel Deady <daniel@clockworkgeek.com>
 * @license MIT
 * @see Clockworkgeek_Extrarestful_Model_Api2_Abstract::_loadModel
 * @see Clockworkgeek_Extrarestful_Model_Api2_Abstract::_saveModel
 * @see Clockworkgeek_Extrarestful_Model_Api2_Abstract::_getCollection
 * @see Clockworkgeek_Extrarestful_Model_Api2_Abstract::_loadCollection
 */
class Clockworkgeek_Extrarestful_Model_Api2_Abstract extends Mage_Api2_Model_Resource
{

    protected $_links = array();

    /**
     * Adds a URI to be included in a Link: header
     *
     * Example:
     * <code>addLink('/some/collection?page=2', 'next')</code>
     *
     * @param string $uri
     * @param string $rel
     * @see https://tools.ietf.org/html/rfc5988
     */
    public function addLink($uri, $rel)
    {
        $this->_links[$uri][] = $rel;
    }

    /**
     * Set some common sense caching guidelines
     *
     * If response will be public add Authorization to Vary.
     * This allows next private request to bypass proxy but still be cached at user's end.
     *
     * @param int $maxAge
     * @return Clockworkgeek_Extrarestful_Model_Api2_Abstract
     */
    public function addCacheHeaders($maxAge)
    {
        $maxAge = intval($maxAge) ?: 3600;
        if ($this->getRequest()->getHeader('Authorization')) {
            $scope = 'private,max-age='.$maxAge;
            $vary = 'Accept';
        }
        else {
            $scope = 'public,max-age='.$maxAge;
            $vary = 'Accept,Authorization';
        }
        if (count($this->getConfig()->getVersions($this->getResourceType())) > 1) {
            $vary .= ',Version';
        }
        $this->getResponse()->setHeader('Cache-Control', $scope, true);
        $this->getResponse()->setHeader('Vary', $vary, true);

        return $this;
    }

    public function setApiUser(Mage_Api2_Model_Auth_User_Abstract $apiUser)
    {
        parent::setApiUser($apiUser);

        // the earliest opportunity we can confirm this
        // never allow inactive stores to be seen by public
        if ($this->getUserType() != Mage_Api2_Model_Auth_User_Admin::USER_TYPE && !$this->_getStore()->getIsActive()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        else {
            // set a store context here because it's tiresome doing everywhere that's needed
            Mage::app()->setCurrentStore($this->_getStore()->getId());
            // is Mage_Core_Model_App::addEventArea() needed here?
        }

        return $this;
    }

    /**
     * Convenient check of an attribute against filter
     *
     * @param string $attribute
     * @return boolean
     */
    public function isReadable($attribute)
    {
        return in_array($attribute, $this->getFilter()->getAttributesToInclude());
    }

    public function dispatch()
    {
        if ($this->getOperation() == self::OPERATION_RETRIEVE) {
            // set temporary headers in case of error on the next line
            $this->addCacheHeaders(600);
        }
        parent::dispatch();
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
        $model = $this->_loadModel();
        $this->addCacheHeaders($model->getCollection()->getCacheLifetime());
        return $model->getData();
    }

    /**
     * Returns a nested array yet to be filtered
     *
     * If there are no records then an empty array is still necessary.
     * Also adds URIs to the Link header if applicable
     *
     * @see Mage_Api2_Model_Resource::_retrieveCollection()
     * @see Clockworkgeek_Extrarestful_Model_Api2_Abstract::addLink($uri, $rel)
     */
    protected function _retrieveCollection()
    {
        $collection = $this->_getCollection();
        $this->_applyCollectionModifiers($collection);
        $this->_loadCollection($collection);
        $this->addCacheHeaders($collection->getCacheLifetime());

        // relative links for pagination
        // if there is "Resource collection paging error" it will happen before this point
        $pageNum = $this->getRequest()->getPageNumber() ?: 1;
        $prevPage = $pageNum - 1;
        $nextPage = $pageNum + 1;
        $lastPage = $collection->getLastPageNumber();
        if ($pageNum > 1) {
            // page is null to overwrite current page value
            $this->addLink($this->_getCollectionLocation(array('page' => null)), 'first');
        }
        if ($prevPage == 1) {
            $this->addLink($this->_getCollectionLocation(array('page' => null)), 'prev');
        }
        elseif ($prevPage > 1 && $prevPage <= $lastPage) {
            $this->addLink($this->_getCollectionLocation(array('page' => $prevPage)), 'prev');
        }
        if ($nextPage >= 1 && $nextPage <= $lastPage) {
            $this->addLink($this->_getCollectionLocation(array('page' => $nextPage)), 'next');
        }
        if ($pageNum < $lastPage) {
            $this->addLink($this->_getCollectionLocation(array('page' => $lastPage)), 'last');
        }

        if ($pageNum != $collection->getCurPage()) {
            // requested page is outside available range
            return array();
        }
        else {
            $data = $collection->walk('toArray');
            // an array with non-consecutive keys is an unordered object when rendered as JSON
            // array_values effectively gives new keys and preserves order to the client
            return array_values((array) $data);
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
     * Also add any accrued links to the Link: header
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

            if ($this->_links) {
                $links = array();
                foreach ($this->_links as $uri => $rels) {
                    $links[] = '<'.$uri.'>;rel="'.implode(' ', $rels).'"';
                }
                // comma separated link-slugs
                $response->setHeader('Link', implode(', ', $links));
            }
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
     * Builds a URI that matches the current resource and parameters
     *
     * Attempts to match parameters to routes with variables.
     * Unused parameters are formed into a query string instead.
     * Result is an absolute path without host, this is how ancestor does it.
     *
     * @param array $params
     * @return string
     * @see Mage_Api2_Model_Resource::_getLocation
     */
    protected function _getCollectionLocation($params = array())
    {
        // + operator ignores duplicate keys in right hand side
        $params += array_diff_key(
            $this->getRequest()->getParams(),
            array('type'=>0, 'action_type'=>0, 'model'=>0));
        if (is_array(@$params['attrs'])) {
            $params['attrs'] = implode(',', $params['attrs']);
        }
        /** @var $apiTypeRoute Mage_Api2_Model_Route_ApiType */
        $apiTypeRoute = Mage::getModel('api2/route_apiType');
        $queries = array_diff_key($params, array_flip($apiTypeRoute->getVariables()));

        // find the most complete route
        $xpath = 'resources/'.$this->getResourceType().'/routes/*[action_type/text()="collection"]';
        $numVars = -1;
        $bestRoute = null;
        foreach ($this->getConfig()->getXpath($xpath) as $node) {
            $route = new Zend_Controller_Router_Route($node->route);
            $vars = $route->getVariables();
            // skip if it requires variables we don't have
            if (count($vars) > $numVars && !array_diff($vars, array_keys($queries))) {
                $bestRoute = $route;
                $numVars = count($vars);
            }
        }
        if (!$bestRoute) {
            $this->_critical('There is no suitable route for '.$this->getResourceType(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
        $queries = array_diff_key($queries, array_flip($bestRoute->getVariables()));

        // form the path part
        $chain = $apiTypeRoute->chain($bestRoute);
        $params['api_type'] = $this->getRequest()->getApiType();
        $uri = '/' . $chain->assemble($params);

        if ($queries) {
            // normalise and append query params
            ksort($queries);
            $uri .= '?' . http_build_query($queries);
        }

        return $uri;
    }
}
