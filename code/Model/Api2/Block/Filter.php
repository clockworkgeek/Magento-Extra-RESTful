<?php

class Clockworkgeek_Extrarestful_Model_Api2_Block_Filter extends Mage_Api2_Model_Acl_Filter
{

    public function out(array $retrievedData)
    {
        if (isset($retrievedData['content'])) {
            $processor = Mage::helper('cms')->getBlockTemplateProcessor();
            $retrievedData['content'] = $processor->filter($retrievedData['content']);

        }
        return parent::out($retrievedData);
    }
}
