<?php
/**
 * @package Bitbull_Tooso
 * @author Gennaro Vietri <gennaro.vietri@bitbull.it>
 */

class Bitbull_Tooso_Model_Indexer
{
    const XML_PATH_INDEXER_STORES = 'tooso/indexer/stores_to_index';
    const XML_PATH_INDEXER_DRY_RUN = 'tooso/indexer/dry_run_mode';
    const XML_PATH_INDEXER_ATTRIBUTES = 'tooso/indexer/attributes_to_index';
    const XML_PATH_INDEXER_ATTRIBUTES_SIMPLE = 'tooso/indexer/attributes_simple_to_index';
    const XML_PATH_INDEXER_INVENTORY_SUPPORT = 'tooso/indexer/inventory_support';
    const DRY_RUN_FILENAME = 'tooso_index_%store%.csv';

    /**
     * @var Bitbull_Tooso_Helper_Log
     */
    protected $_logger = null;

    /**
     * @var Bitbull_Tooso_Helper_Indexer
     */
    protected $_indexerHelper = null;

    /**
     * @var array
     */
    protected $_categories = null;

    /**
     * @var Mage_Catalog_Model_Resource_Eav_Attribute
     */
    protected $_mediaAttribute = null;

    /**
     * @var Mage_Catalog_Model_Resource_Product_Attribute_Backend_Media
     */
    protected $_mediaResource = null;

    /**
     * @var Mage_Catalog_Model_Product_Media_Config
     */
    protected $_mediaConfig = null;

    /**
     * @var array
     */
    protected $_cachedStockIds = [];


    public function __construct()
    {
        $this->_logger = Mage::helper('tooso/log');
        $this->_indexerHelper = Mage::helper('tooso/indexer');

        $this->_initCategories();
    }

    /**
     * Build categories array with named path
     *
     */
    protected function _initCategories()
    {
        $collection = Mage::getResourceModel('catalog/category_collection')->addNameToResult();

        foreach ($collection as $category) {
            $structure = preg_split('#/+#', $category->getPath());
            $pathSize  = count($structure);
            if ($pathSize > 1) {
                $path = array();
                for ($i = 1; $i < $pathSize; $i++) {
                    $path[] = $collection->getItemById($structure[$i])->getName();
                }
                $this->_categories[$category->getId()] = implode('/', $path);
            }

        }
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

                $time_start = microtime(true);

                if($this->_isDebugEnabled()){
                    $this->_logger->debug("Indexer: store output into debug file ");
                    $this->_writeDebugFile($this->_getCsvContent($storeId), $storeCode);
                }else{
                    $client = Mage::helper('tooso')->getClient($storeCode, $storeLangCode);
                    $client->index($this->_getCsvContent($storeId), $this->_indexerHelper->getIndexerParams());
                }

                $time_end = microtime(true);
                $execution_time_s = ($time_end - $time_start);
                $execution_time_m = $execution_time_s/60;
                $execution_time_h = $execution_time_m/60;

                $execution_time = "";
                if($execution_time_h > 1){
                    $execution_time = round($execution_time_h, 3)."h";
                }else if($execution_time_m > 1){
                    $execution_time = round($execution_time_m, 3)."m";
                }else{
                    $execution_time = round($execution_time_s, 3)."s";
                }

                $this->_logger->debug("Indexer: store ".$storeCode." index completed in ".$execution_time);
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
        $attributesTypes = array();

        $attributes = explode(",", Mage::getStoreConfig(self::XML_PATH_INDEXER_ATTRIBUTES));

        $headers = array_merge(array(
            'sku' => 'sku'
        ), $attributes);

        $this->_logger->debug("Indexer: using attributes ".json_encode($attributes));

        // load custom attributes
        $attributesCollection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addFieldToFilter('attribute_code', array('in' => $attributes));

        // load store products visible individually and select system attributes
        $productCollection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE))
            ->setStoreId($storeId)
            ->addStoreFilter($storeId)
        ;

        // load and select custom attributes
        foreach ($attributesCollection as $attribute) {

            $attributeCode = $attribute->getAttributeCode();

            $attributesTypes[$attributeCode] = $attribute->getFrontendInput();
            $headers[$attributeCode] = $attributeCode;

            $productCollection->addAttributeToSelect($attributeCode, 'left');

        }

        if(in_array('is_in_stock', $attributes) || in_array('qty', $attributes)) {
            $this->_addStockToCollection($productCollection, $storeId);
        }

        if(in_array('gallery', $attributes) &&
            $this->_mediaAttribute == null &&
            $this->_mediaResource == null &&
            $this->_mediaConfig == null)
        {
            $this->_mediaAttribute = Mage::getSingleton('catalog/product')->getResource()->getAttribute('media_gallery');
            $this->_mediaResource = Mage::getResourceSingleton('catalog/product_attribute_backend_media');
            $this->_mediaConfig = Mage::getModel('catalog/product_media_config');
        }

        // create new writer object
        $writer = $this->_getWriter();
        $writer->setHeaderCols($headers);

        $this->_logger->debug("Indexer: found ".$productCollection->getSize()." products");

        Mage::getSingleton('core/resource_iterator')->walk(
            $productCollection->getSelect(),
            array(
                array($this, 'productCollectionWalker')
            ),
            array(
                'storeId' => $storeId,
                'attributes' => $attributes,
                'attributesTypes' => $attributesTypes,
                'preserveAttributeValue' => $this->_indexerHelper->getPreservedAttributeType(),
                'writer' => $writer
            )
        );

        return $writer->getContents();
    }

    /**
     * elaborate product collection row into CSV
     *
     * @param
     */
    public function productCollectionWalker($args){
        $product = Mage::getModel('catalog/product');
        $product->setData($args['row']);

        $storeId = $args['storeId'];
        $attributes = $args['attributes'];
        $attributesTypes = $args['attributesTypes'];
        $preserveAttributeValue = $args['preserveAttributeValue'];
        $writer = $args['writer'];

        $product->setStoreId($storeId);

        $row = array();
        $row["sku"] = $product->getSku();

        foreach ($attributes as $attributeCode) {

            switch ($attributeCode) {
                case 'variants':
                    $variants = $this->_getProductVariants($product, $storeId);
                    if(sizeof($variants) > 0){
                        $row["variants"] = json_encode($variants);
                    }
                    break;
                case 'categories':
                    $row["categories"] = implode("|", $this->_getProductCategories($product));
                    break;
                case 'gallery':
                    $row["gallery"] = implode("|", $this->_getProductImageGallery($product));
                    break;
                default:
                    if(isset($attributesTypes[$attributeCode]) && $attributesTypes[$attributeCode] === 'select' && !in_array($attributeCode, $preserveAttributeValue)){
                        $row[$attributeCode] = $this->_escapeTextValue($product->getAttributeText($attributeCode));
                    }else{
                        $row[$attributeCode] = $this->_escapeTextValue($product->getData($attributeCode));
                    }
                    break;
            }
        }
        $writer->writeRow($row);
    }

    /**
     * Return product variants object with associated products
     *
     * @param $product
     * @return array
     */
    protected function _getProductVariants($product, $storeId){
        $variants = array();
        if($product->getTypeId() == Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
            $productAttributesOptions = $product->getTypeInstance(true)->getConfigurableOptions($product);

            foreach ($productAttributesOptions as $productAttributeOption) {
                foreach ($productAttributeOption as $optionValues) {
                    if(!isset($variants[$optionValues['sku']])){
                        $variants[$optionValues['sku']] = array();
                    }
                    $variants[$optionValues['sku']][$optionValues['attribute_code']] = $optionValues['option_title'];
                }
            }

            $attributes = explode(",", Mage::getStoreConfig(self::XML_PATH_INDEXER_ATTRIBUTES_SIMPLE));
            if(sizeof($attributes) > 0){
                $variantsCollection = Mage::getResourceModel('catalog/product_type_configurable_product_collection')
                    ->setProductFilter($product);

                $attributesTypes= [];
                $attributesCollection = Mage::getResourceModel('catalog/product_attribute_collection')->addFieldToFilter('attribute_code', array('in' => $attributes));
                foreach ($attributesCollection as $attribute) {
                    $attributeCode = $attribute->getAttributeCode();
                    $attributesTypes[$attributeCode] = $attribute->getFrontendInput();
                    $variantsCollection->addAttributeToSelect($attributeCode, 'left');
                }

                if(in_array('is_in_stock', $attributes) || in_array('qty', $attributes)) {
                    $this->_addStockToCollection($variantsCollection, $storeId);
                }

                $preserveAttributeValue = $this->_indexerHelper->getPreservedAttributeType();

                foreach ($variantsCollection as $variant){
                    $variant->setStoreId($storeId);
                    $sku = $variant->getSku();

                    foreach ($attributes as $attributeCode) {
                        if(isset($attributesTypes[$attributeCode]) && $attributesTypes[$attributeCode] === 'select' && !in_array($attributeCode, $preserveAttributeValue)){
                            $variants[$sku][$attributeCode] = $this->_escapeTextValue($variant->getAttributeText($attributeCode));
                        }else{
                            $variants[$sku][$attributeCode] = $this->_escapeTextValue($variant->getData($attributeCode));
                        }
                    }
                }
            }

        }

        if(sizeof($variants) > 0){
            $this->_logger->debug("Indexer: parsing ".$product->getSku()." ".sizeof($variants)." variants");
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
            if($this->_categories[$categoryId])
                array_push($categories, $this->_categories[$categoryId]);
        }

        if(sizeof($categories) > 0){
            $this->_logger->debug("Indexer: parsing ".$product->getSku()." ".sizeof($categories)." categories");
        }

        return $categories;
    }

    /**
     * Return product images
     *
     * @param $product
     * @return array
     */
    protected function _getProductImageGallery($product){
        $gallery = $this->_mediaResource->loadGallery($product, new Varien_Object(array('attribute' => $this->_mediaAttribute)));

        $images = array();
        foreach ($gallery as $image) {
            array_push($images, $this->_mediaConfig->getMediaUrl($image['file']));
        }

        if(sizeof($images) > 0){
            $this->_logger->debug("Indexer: parsing ".$product->getSku()." ".sizeof($images)." images");
        }

        return $images;
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
     * Escape text value
     *
     * @param $value string
     * @return bool
     */
    protected function _escapeTextValue($value){
        return str_replace('"', '', $value);
    }

    /**
     * Multi Warehouse Support
     *
     * @return bool
     */
    protected function _isMultiWarehouseSupportEnabled(){
        $support = Mage::getStoreConfig(self::XML_PATH_INDEXER_INVENTORY_SUPPORT);
        return $support !== null && $support === 'warehouse_store';
    }

    /**
     * @param $collection
     * @param $storeId
     * @throws Varien_Exception
     */
    protected function _addStockToCollection(&$collection, $storeId)
    {
        if ($this->_isMultiWarehouseSupportEnabled()) {
            try{
                $this->_logger->debug('Indexer: searching in stock_id for warehouses connected to the store '.$storeId);
                $stockIds = $this->_getWarehouseStockIdsByStore($storeId);
                if (sizeof($stockIds) > 0) {
                    $websiteId = Mage::getModel('core/store')->load($storeId)->getWebsiteId();
                    $this->_logger->debug("Indexer: getting stock by stock_id ".json_encode($stockIds)." and website ".$websiteId);
                    $table = Mage::getSingleton('core/resource')->getTableName('cataloginventory/stock_status');
                    $collection->getSelect()->joinLeft(
                        ['ss' => $table],
                        'e.entity_id=ss.product_id AND ss.stock_id IN ('.implode(',', $stockIds).') and ss.website_id = '.$websiteId,
                        ['qty' => 'sum(ss.qty)', 'is_in_stock' => 'max(ss.stock_status)']
                    )->group('entity_id');
                }else{
                    $this->_logger->debug('Indexer: no avaiable warehouses for store '.$storeId);
                }
            } catch (Exception $e) {
                $this->_logger->logException($e);
            }
        }else{
            $collection->joinTable('cataloginventory/stock_item',
                'product_id=entity_id',
                ['is_in_stock', 'qty'],
                '{{table}}.stock_id=1',
                'left'
            );
        }
    }

    protected function _getWarehouseStockIdsByStore($storeId)
    {
        if (isset($this->_cachedStockIds[$storeId])) {
            return $this->_cachedStockIds[$storeId];
        }
        $resource = Mage::getSingleton('core/resource');
        $conn = $resource->getConnection('core_read');
        $warehouseTable = $resource->getTableName('warehouse');
        $warehouseStoreTable = $resource->getTableName('warehouse_store');
        $stockIds = $conn->fetchCol('SELECT '.$warehouseTable.'.stock_id FROM '.$warehouseTable.' INNER JOIN '.$warehouseStoreTable.' ON '.$warehouseTable.'.warehouse_id = '.$warehouseStoreTable.'.warehouse_id WHERE '.$warehouseStoreTable.'.store_id = '.$storeId);
        $this->_cachedStockIds[$storeId] = $stockIds;
        return $stockIds;
    }
}
