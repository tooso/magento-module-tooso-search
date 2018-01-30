<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
class Bitbull_Tooso_Helper_Suggestion extends Mage_Core_Helper_Abstract
{
    const CONTAINER_BLOCK_AFTER = 'after_body_start';
    const CONTAINER_BLOCK_BEFORE = 'before_body_end';

    const XML_PATH_SUGGEST_LIBRARY = 'tooso/suggestion/include_library';
    const XML_PATH_SUGGEST_LIBRARY_ENDPOINT = 'tooso/suggestion/library_endpoint';
    const XML_PATH_SUGGEST_INPUT_SELECTOR = 'tooso/suggestion/input_selector';
    const XML_PATH_SUGGEST_BUCKETS = 'tooso/suggestion/buckets';
    const XML_PATH_SUGGEST_ZINDEX = 'tooso/suggestion/zindex';
    const XML_PATH_SUGGEST_API_KEY = 'tooso/suggestion/api_key';

    /**
     * Get block to append init suggestion library
     *
     */
    public function getInitScriptContainerBlock(){
        $layout = Mage::app()->getLayout();
        return $layout->getBlock(self::CONTAINER_BLOCK_BEFORE);
    }

    /**
     * Get block to append suggestion library
     *
     */
    public function getScriptContainerBlock(){
        $layout = Mage::app()->getLayout();
        return $layout->getBlock(self::CONTAINER_BLOCK_AFTER);
    }

    /**
     * Create Suggestion Library Block
     *
     * @return Bitbull_Tooso_Block_Suggestion_Library
     */
    public function getSuggestionLibraryBlock(){
        $layout = Mage::app()->getLayout();
        $block = $layout->createBlock('tooso/suggestion_library');
        return $block;
    }

    /**
     * Create Suggestion Library Init Block
     *
     * @return Bitbull_Tooso_Block_Suggestion_LibraryInit
     */
    public function getSuggestionLibraryInitBlock(){
        $layout = Mage::app()->getLayout();
        $block = $layout->createBlock('tooso/suggestion_libraryInit');
        return $block;
    }

    /**
     * Include Suggestion library
     *
     * @param null $store
     * @return bool
     */
    public function includeSuggestionJSLibrary($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_SUGGEST_LIBRARY, $store);
    }

    /**
     * Get suggestion library endpoint
     *
     * @param null $store
     * @return mixed
     */
    public function getSuggestionJSLibraryEndpoint($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_SUGGEST_LIBRARY_ENDPOINT, $store);
    }

    /**
     * Get Input selector
     *
     * @param null $store
     * @return mixed
     */
    public function getSuggestionInputSelector($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_SUGGEST_INPUT_SELECTOR, $store);
    }

    /**
     * Get Buckets
     *
     * @param null $store
     * @return mixed
     */
    public function getSuggestionBuckets($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_SUGGEST_BUCKETS, $store);
    }

    /**
     * Get Z-Index
     *
     * @param null $store
     * @return mixed
     */
    public function getSuggestionZIndex($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_SUGGEST_ZINDEX, $store);
    }

    /**
     * get API Key
     *
     * @param null $store
     * @return mixed
     */
    public function getApiKey($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_SUGGEST_API_KEY, $store);
    }

}