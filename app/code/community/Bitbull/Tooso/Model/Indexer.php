<?php
/**
 * @package Bitbull_Tooso
 * @author Gennaro Vietri <gennaro.vietri@bitbull.it>
 */

class Bitbull_Tooso_Model_Indexer
{
    const XML_PATH_INDEXER_STORES = 'tooso/indexer/stores_to_index';

    /**
     * Client for API comunication
     *
     * @var Bitbull_Tooso_Client
     */
    protected $_client;

    /**
     * @var Bitbull_Tooso_Helper_Log
     */
    protected $_logger = null;

    /**
     * @var Mage_ImportExport_Model_Export_Adapter_Csv
    */
    protected $_writer;

    public function __construct()
    {
        $this->_client = Mage::helper('tooso')->getClient();

        $this->_logger = Mage::helper('tooso/log');
    }

    /**
     * Rebuild Tooso Index
     *
     * @return boolean
    */
    public function rebuildIndex()
    {
        try {
            $stores = $this->_getStoreViews();
            foreach ($stores as $storeId) {
                $this->_logger->debug("Indexer: indexing store ".$storeId);
                //$this->_logger->debug($this->_getCsvContent($storeId));
                $this->_client->index($this->_getCsvContent($storesView));
                $this->_logger->debug("Indexer: store ".$storeId." index completed");
            }
        } catch (Exception $e) {
            $this->_logger->logException($e);
            return false;
        }

        return true;
    }

    /**
     * Clean Tooso Index
     *
     * @todo Should be implemented, but so far Tooso don't support index cleaning
     *
     * @return boolean
    */
    public function cleanIndex()
    {
        return true;
    }

    /**
     * return string
    */
    protected function _getCsvContent($storeId)
    {
        $excludeAttributes = array(
            'image_label',
            'old_id',
            'small_image_label',
            'thumbnail_label',
            'uf_product_link',
            'url_path',
        );
        $attributeTypes = array(
            'text',
            'textarea',
            'multiselect',
            'select',
            'boolean',
            'price'
        );
        /*
         * Excluding:
         *   'date'
         *   'media_image'
         *   'image'
         *   'gallery'
         */

        $attributes = array(
            'sku' => 'sku',
            'name' => 'name',
            'description' => 'description',
            'short_description' => 'short_description',
            'status' => 'status',
            'availability' => 'availability'
        );
        $headers = $attributes;

        $attributesCollection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addFieldToFilter('frontend_input', array('in' => $excludeAttributeTypes))
            ->addFieldToFilter('backend_type', array('in' => $excludeAttributeTypes))
            ->addFieldToFilter('attribute_code', array('nin' => $excludeAttributes))
        ;

        $productCollection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE))
            ->addStoreFilter($storeId)
        ;
        foreach ($attributes as $attributeCode => $attributeLabel){
            $productCollection->addAttributeToSelect($attributeCode);
        }

        foreach ($attributesCollection as $attribute) {
            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
            $headers[$attribute->getAttributeCode()] = $attribute->getAttributeCode();

            $productCollection->addAttributeToSelect($attribute->getAttributeCode());

            $productCollection->joinAttribute(
                $attribute->getAttributeCode(),
                'catalog_product/' . $attribute->getAttributeCode(),
                'entity_id',
                null,
                'left',
                $storeId
            );
        }

        $this->_getWriter()->setHeaderCols($headers);

        foreach ($productCollection as $product) {
            $row = array();
            foreach ($attributes as $attributeCode => $attributeLabel) {
                $row[$attributeCode] = $product->getData($attributeCode);
            }

            $this->_getWriter()->writeRow($row);
        }

        return $this->_getWriter()->getContents();
    }

    /**
     * Get stores grouped by lang code
     * @return array stores
     */
    protected function _getStoreViews()
    {
        $storesConfig = Mage::getStoreConfig(self::XML_PATH_INDEXER_STORES);

        $stores = array();
        if($storesConfig == null){
            $collection = Mage::getModel('core/store')->getCollection();
            foreach ($collection as $store) {
                $lang= Mage::getStoreConfig('general/locale/code', $store->getId());
                if (!isset($stores[$lang])) {
                    $stores[$lang] = array();
                }
                array_push($stores, $store->getId());
            }
        }else{
            $storesArrayConfig = explode(",", $storesConfig);
            foreach ($storesArrayConfig as $store) {
                array_push($stores, (int) $store);
            }
        }

        $this->_logger->debug("Indexer: using stores ".json_encode($stores));

        return $stores;
    }

    /**
     * @return Mage_ImportExport_Model_Export_Adapter_Csv
    */
    protected function _getWriter()
    {
        if (!$this->_writer) {
            $this->_writer = Mage::getModel('importexport/export_adapter_csv', array());
        }

        return $this->_writer;
    }

    /**
     * Return stores for backend multiselect options
     */
    public function toOptionArray() {
        return Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true);
    }
}
