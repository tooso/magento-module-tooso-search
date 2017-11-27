<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Model_System_Config_Source_AttributesSimple
{
    /**
     * @var Bitbull_Tooso_Helper_Indexer
     */
    protected $_indexerHelper = null;

    /**
     * Bitbull_Tooso_Model_System_Config_Source_Attributes constructor.
     */
    public function __construct()
    {
        $this->_indexerHelper = Mage::helper('tooso/indexer');
    }

    /**
     * Return attributes for backend multiselect options
     */
    public function toOptionArray() {

        $attributes = array();

        $excludeAttributes = $this->_indexerHelper->getExcludeAttributes();
        $attributeFrontendTypes = $this->_indexerHelper->getAttributeFrontendTypes();
        $attributeBackendTypes = $this->_indexerHelper->getAttributeBackendTypes();

        $attributesCollection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addFieldToFilter('backend_type', array('in' => $attributeBackendTypes))
            ->addFieldToFilter('frontend_input', array('in' => $attributeFrontendTypes))
            ->addFieldToFilter('attribute_code', array('nin' => $excludeAttributes));

        foreach ($attributesCollection as $attribute) {
            $attributes[] = [
                "label" => $attribute->getFrontendLabel(),
                "value" => $attribute->getAttributeCode(),
            ];
        }

        return $attributes;
    }
}
