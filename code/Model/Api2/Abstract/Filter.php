<?php

/**
 * Casts fields as well as filtering them
 *
 * @author Daniel Deady <daniel@clockworkgeek.com>
 * @license MIT
 */
class Clockworkgeek_Extrarestful_Model_Api2_Abstract_Filter extends Mage_Api2_Model_Acl_Filter
{

    protected $_spec;

    public function __construct(Mage_Api2_Model_Resource $resource)
    {
        parent::__construct($resource);

        $node = $resource->getConfig()->getNode('resources/'.$resource->getResourceType().'/filters');
        $this->_spec = $node ? $node->asCanonicalArray() : array();

        $model = $resource->getConfig()->getResourceWorkingModel($resource->getResourceType());
        /** @var $entityType Mage_Eav_Model_Entity_Type */
        $entityType = Mage::getModel('eav/entity_type')->load($model, 'entity_model');
        if ($entityType->getId()) {
            /** @var $attribute Mage_Eav_Model_Entity_Attribute */
            foreach ($entityType->getAttributeCollection() as $attribute) {
                if (!$attribute->getSourceModel()) {
                    if ($attribute->getBackendType() == 'int') {
                        $this->_spec[$attribute->getAttributeCode()] = 'int';
                    }
                    elseif ($attribute->getBackendType() == 'decimal') {
                        $this->_spec[$attribute->getAttributeCode()] = 'float';
                    }
                }
                elseif ($attribute->getSource() instanceof Mage_Eav_Model_Entity_Attribute_Source_Boolean) {
                    $this->_spec[$attribute->getAttributeCode()] = 'boolean';
                }
            }
        }
    }

    public function out(array $data)
    {
        $data = parent::out($data);

        if ($this->_spec) {
            $this->_cast($this->_spec, $data);
        }

        return $data;
    }

    /**
     * Recursively cast data values to type as specified
     *
     * @param array $spec
     * @param array $data
     */
    protected function _cast(array $spec, array &$data)
    {
        foreach ($data as $attr => &$val) {
            if ($val === null) continue;

            if (is_array($val) && is_array(@$spec[$attr])) {
                // recurse through nested data
                foreach ($val as &$child) {
                    $this->_cast($spec[$attr], $child);
                }
            }
            else {
                // expect a scalar, cast to native
                // if complex type is received there will probably be a warning
                switch (@$spec[$attr]) {
                    case 'boolean':
                        $val = boolval($val);
                        break;
                    case 'float':
                        $val = floatval($val);
                        break;
                    case 'int':
                        $val = intval($val);
                        break;
                }
            }
        }
    }

    /**
     * Filters the <code>filter</code> query parameter appropriate to this filter object using data filtering
     *
     * This allows collections to be queried with boolean values like "true" and "false" instead of "1" and "0".
     *
     * @param array $query
     * @return array unknown
     * @see https://php.net/manual/book.filter.php
     */
    public function sanitize(array &$query)
    {
        foreach ($query as &$term) {
            $type = filter_id(@$this->_spec[$term['attribute']]);
            if ($type) {
                foreach ($term as $operator => &$value) {
                    if ($operator === 'attribute') continue;
                    $value = filter_var($value, $type);
                }
            }
        }
    }
}
