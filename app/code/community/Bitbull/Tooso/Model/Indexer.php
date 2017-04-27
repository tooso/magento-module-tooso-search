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
    const DRY_RUN_FILENAME = 'tooso_index_%store%.csv';

    /**
     * @var integer
     */
    protected $_productPagination = 15;

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
                    $client->index($this->_getCsvContent($storeId));
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
        $systemAttributes = $this->_indexerHelper->getSystemAttributes();

        $attributesTypes = array();

        $attributes = explode(",", Mage::getStoreConfig(self::XML_PATH_INDEXER_ATTRIBUTES));

        $headers = array_merge(array(
            'sku' => 'sku'
        ), $attributes);

        /**
         * Not use getAttributeText to get value of data
         */
        $preserveAttributeValue = array(
            'status',
            'visibility'
        );

        $this->_logger->debug("Indexer: using attributes ".json_encode($attributes));

        // load custom attributes
        $attributesCollection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addFieldToFilter('attribute_code', array('in' => $attributes));

        // load store products visible individually and select system attributes
        $productCollection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE))
            ->addStoreFilter($storeId)
        ;

        // load and select custom attributes
        foreach ($attributesCollection as $attribute) {

            $attributeCode = $attribute->getAttributeCode();
            if(!in_array($attributeCode, $systemAttributes)){

                $attributesTypes[$attributeCode] = $attribute->getFrontendInput();
                $headers[$attributeCode] = $attributeCode;

                $productCollection->addAttributeToSelect($attribute->getAttributeCode());

                $productCollection->joinAttribute(
                    $attribute->getAttributeCode(),
                    'catalog_product/' . $attribute->getAttributeCode(),
                    'entity_id',
                    null,
                    'left',
                    $storeId
                );
            }else{
                $productCollection->addAttributeToSelect($attributeCode);
            }
        }

        if(in_array('is_in_stock', $attributes)) {
            $productCollection->joinField('is_in_stock',
                'cataloginventory/stock_item',
                'is_in_stock',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left'
            );
        }

        // create new writer object
        $writer = $this->_getWriter();
        $writer->setHeaderCols($headers);

        $this->_logger->debug("Indexer: found ".$productCollection->getSize()." products");
        
        $productCollection->setPageSize($this->_productPagination);
        $pages = $productCollection->getLastPageNumber();
        for ($p = 1; $p <= $pages; $p++ ){
            $productCollection->setCurPage($p);
            $productCollection->load();

            foreach ($productCollection as $product) {
                $product->setStoreId($storeId);

                $row = array();
                $row["sku"] = $product->getSku();

                foreach ($attributes as $attributeCode) {
                    if($attributeCode == 'variants'){
                        $variants = $this->_getProductVariants($product);
                        if(sizeof($variants) > 0){
                            $row["variants"] = json_encode($variants);
                        }
                    }elseif ($attributeCode == 'categories'){
                        $row["categories"] = implode("|", $this->_getProductCategories($product));
                    }else{
                        if($attributesTypes[$attributeCode] === 'select' && !in_array($attributeCode, $preserveAttributeValue)){
                            $row[$attributeCode] = $product->getAttributeText($attributeCode);
                        }else{
                            $row[$attributeCode] = $product->getData($attributeCode);
                        }
                    }
                }
                $writer->writeRow($row);
            }
            $productCollection->clear();
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
}
