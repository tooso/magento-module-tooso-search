<?php
/**
 * @package Bitbull_Tooso
 * @author Gennaro Vietri <gennaro.vietri@bitbull.it>
 */

class Bitbull_Tooso_Model_Indexer
{
    const XML_PATH_INDEXER_STORES = 'tooso/indexer/stores_to_index';
    const XML_PATH_INDEXER_DRY_RUN = 'tooso/indexer/dry_run_mode';
    const DRY_RUN_FILENAME = 'tooso_index_%store%.csv';

    /**
     * @var Bitbull_Tooso_Helper_Log
     */
    protected $_logger = null;


    public function __construct()
    {
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
            $stores = $this->_getStores();
            foreach ($stores as $storeCode => $storeId) {
                $storeLangCode = Mage::getStoreConfig('general/locale/code', $storeId);
                $this->_logger->debug("Indexer: indexing store ".$storeCode." [".$storeLangCode."]");
                if($this->_isDebugEnabled()){
                    $this->_logger->debug("Indexer: store output into debug file ");
                    $this->_writeDebugFile($this->_getCsvContent($storeId), $storeCode);
                }else{
                    $client = Mage::helper('tooso')->getClient($storeCode, $storeLangCode);
                    $client->index($this->_getCsvContent($storeId));
                }
                $this->_logger->debug("Indexer: store ".$storeCode." index completed");
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
     * Get catalog exported CSV content
     *
     * return string
    */
    protected function _getCsvContent($storeId)
    {
        /**
         * Exclude unused system attributes
         */
        $excludeAttributes = array(
            'image_label',
            'old_id',
            'small_image_label',
            'thumbnail_label',
            'uf_product_link',
            'url_path',
            'custom_layout_update',
            'recurring_profile',
            'group_price',
            'is_recurring',
            'minimal_price',
            'msrp',
            'msrp_display_actual_price_type',
            'msrp_enabled',
            'options_container',
            'page_layout',
            'price_view',
            'country_of_manufacture',
            'gift_message_available',
            'tax_class_id',
            'tier_price',
            'custom_design'
        );

        /**
         * Attribute frontend types to load
         *
         * Excluding:
         *   'date'
         *   'media_image'
         *   'image'
         *   'gallery'
         */
        $attributeFrontendTypes = array(
            'text',
            'textarea',
            'multiselect',
            'select',
            'boolean',
            'price'
        );


        /**
         * Attribute backend types to load
         *
         * Excluding:
         *   'date',
         *   'datetime',
         *   'static'
         */
        $attributeBackendTypes = array(
            'varchar',
            'int',
            'text',
            'decimal',
        );


        /**
         * System attribute to select (used also for CSV headers)
         */
        $attributes = array(
            'sku' => 'sku',
            'name' => 'name',
            'description' => 'description',
            'short_description' => 'short_description',
            'status' => 'status',
            'is_in_stock' => 'is_in_stock'
        );

        /**
         * Dynamic columns
         */
        $headers = array_merge($attributes, array(
            'variants' => 'variants',
            'categories' => 'categories'
        ));

        /**
         * Not use getAttributeText to get value of data
         */
        $preserveAttributeValue = array(
            'status',
            'visibility'
        );

        $limitAttributeCount = 40; //limit number of attributes

        // load custom attributes
        $attributesCollection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addFieldToFilter('backend_type', array('in' => $attributeBackendTypes))
            ->addFieldToFilter('frontend_input', array('in' => $attributeFrontendTypes))
            ->addFieldToFilter('attribute_code', array('nin' => $excludeAttributes))
            ->setCurPage(1)
            ->setPageSize($limitAttributeCount);
        ;

        // load store products visible individually and select system attributes
        $productCollection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE))
            ->addStoreFilter($storeId)
        ;
        foreach ($attributes as $attributeCode){
            $productCollection->addAttributeToSelect($attributeCode);
        }

        // load and select custom attributes
        foreach ($attributesCollection as $attribute) {
            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendInput();
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

        $productCollection->joinField('is_in_stock',
            'cataloginventory/stock_item',
            'is_in_stock',
            'product_id=entity_id',
            '{{table}}.stock_id=1',
            'left'
        );

        // create new writer object
        $writer = $this->_getWriter();
        $writer->setHeaderCols($headers);

        // load attribute values
        foreach ($productCollection as $product) {
            $product->setStoreId($storeId);

            $row = array();
            foreach ($attributes as $attributeCode => $frontendInput) {
                if($frontendInput === 'select' && !in_array($attributeCode, $preserveAttributeValue)){
                    $row[$attributeCode] = $product->getAttributeText($attributeCode);
                }else{
                    $row[$attributeCode] = $product->getData($attributeCode);
                }
            }

            // load product variants
            $variants = $this->_getProductVariants($product);
            if(sizeof($variants) > 0){
                $row["variants"] = json_encode($variants);
            }

            // load product category
            $row["categories"] = implode("|", $this->_getProductCategories($product));

            $writer->writeRow($row);
        }

        return $writer->getContents();
    }

    /**
     * Return product variants object with associated products
     *
     * @param $product
     * @return array
     */
    protected function _getProductVariants($product){
        $variants = array();
        if($product->getTypeId() == Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
            $productAttributesOptions = $product->getTypeInstance(true)->getConfigurableOptions($product);

            foreach ($productAttributesOptions as $productAttributeOption) {
                $configurableData[$product->getId()] = array();
                foreach ($productAttributeOption as $optionValues) {
                    $optionData = array();
                    $optionData[$optionValues['attribute_code']] = $optionValues['option_title'];
                    $variants[$optionValues['sku']] = $optionData;
                }
            }
        }
        return $variants;
    }

    /**
     * Return product categories as array of path
     *
     * @param $product
     * @return array
     */
    protected function _getProductCategories($product){
        $categories = array();

        $categoriesIds = $product->getCategoryIds();
        foreach ($categoriesIds as $categoryId) {
            $category = Mage::getModel('catalog/category')->load($categoryId);

            // transform category path from collections of ids to names
            $pathIds = explode('/', $category->getPath());
            $pathNames = [];
            foreach ($pathIds as $id) {
                $relatedCategory = Mage::getModel('catalog/category')->load($id);
                if ($relatedCategory) {
                    array_push($pathNames, $relatedCategory->getName());
                }
            }

            array_push($categories, implode('/', $pathNames));
        }

        return $categories;
    }

    /**
     * Get stores grouped by lang code
     * @return array stores
     */
    protected function _getStores()
    {
        $storesConfig = Mage::getStoreConfig(self::XML_PATH_INDEXER_STORES);

        $stores = array();
        if($storesConfig == null || $storesConfig == "0"){
            $collection = Mage::getModel('core/store')->getCollection();
            foreach ($collection as $store) {
                $stores[$store->getCode()] = $store->getId();
            }
        }else{
            $storesArrayConfig = explode(",", $storesConfig);
            foreach ($storesArrayConfig as $storeId) {
                $store = Mage::getModel('core/store')->load($storeId);
                $stores[$store->getCode()] = $store->getId();
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
        return $this->_writer = Mage::getModel('importexport/export_adapter_csv', array());
    }

    /**
     * Print content into debug CSV file
     *
     * @param $content
     * @param $store_id
     * @return bool
     */
    protected function _writeDebugFile($content, $storeId = null){

        $logPath = Mage::getBaseDir('var').DS.'log';
        $fileName = "";
        if($storeId == null){
            $fileName = str_replace("_%store%", "", self::DRY_RUN_FILENAME);
        }else{
            $fileName = str_replace("%store%", $storeId, self::DRY_RUN_FILENAME);
        }
        $file_path = $logPath.DS.$fileName;
        $file = fopen($file_path, "w");
        if(!$file){
            $this->_logger->logException(new Exception("Unable to open file CSV debug file [".$file_path."]"));
            return false;
        }else{
            fwrite($file, $content);
            fclose($file);
            return true;
        }
    }

    /**
     * Debug flag
     *
     * @return bool
     */
    protected function _isDebugEnabled(){
        return Mage::getStoreConfigFlag(self::XML_PATH_INDEXER_DRY_RUN);
    }

    /**
     * Return stores for backend multiselect options
     */
    public function toOptionArray() {
        return Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true);
    }
}
