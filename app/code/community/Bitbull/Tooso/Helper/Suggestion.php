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
    const XML_PATH_SUGGEST_API_KEY = 'tooso/server/api_key';

    const XML_PATH_SUGGEST_LANGUAGE = 'tooso/suggestion/language';
    const XML_PATH_SUGGEST_UID = 'tooso/suggestion/uid';
    const XML_PATH_SUGGEST_BUCKETS = 'tooso/suggestion/buckets';
    const XML_PATH_SUGGEST_LIMIT = 'tooso/suggestion/limit';
    const XML_PATH_SUGGEST_GROUPBY = 'tooso/suggestion/groupby';
    const XML_PATH_SUGGEST_NOCACHE = 'tooso/suggestion/nocache';
    const XML_PATH_SUGGEST_ONSELECT = 'tooso/suggestion/onselect';
    const XML_PATH_SUGGEST_MINCHAR = 'tooso/suggestion/minchars';
    const XML_PATH_SUGGEST_WIDTH = 'tooso/suggestion/with';
    const XML_PATH_SUGGEST_ZINDEX = 'tooso/suggestion/zindex';

    /**
     * Get block to append init suggestion library
     *
     */
    public function getInitScriptContainerBlock(){
        $layout = Mage::app()->getLayout();
        return $layout->getBlock(self::CONTAINER_BLOCK_AFTER);
    }

    /**
     * Get block to append suggestion library
     *
     */
    public function getScriptContainerBlock(){
        $layout = Mage::app()->getLayout();
        return $layout->getBlock(self::CONTAINER_BLOCK_BEFORE);
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
     * Get javascript library initialization params
     *
     * @param null $store
     * @return array
     */
    public function getSuggestioninitParams($store = null)
    {
        $data = [
            'autocomplete' => []
        ];

        $apiKey = Mage::getStoreConfig(self::XML_PATH_SUGGEST_API_KEY, $store);
        if($apiKey != null){
            $data['apiKey'] = $apiKey;
        }

        $language = Mage::getStoreConfig(self::XML_PATH_SUGGEST_LANGUAGE, $store);
        if($language != null){
            $data['language'] = $language;
        }

        $uid = Mage::getStoreConfig(self::XML_PATH_SUGGEST_UID, $store);
        if($uid != null){
            $data['uid'] = $uid;
        }

        $buckets = Mage::getStoreConfig(self::XML_PATH_SUGGEST_BUCKETS, $store);
        if($buckets != null){
            $data['buckets'] = $buckets;
        }

        $limit = Mage::getStoreConfig(self::XML_PATH_SUGGEST_LIMIT, $store);
        if($limit != null){
            $data['limit'] = $limit;
        }

        $groupBy = Mage::getStoreConfig(self::XML_PATH_SUGGEST_GROUPBY, $store);
        if($groupBy != null){
            $data['groupBy'] = $groupBy;
        }

        $noCache = Mage::getStoreConfigFlag(self::XML_PATH_SUGGEST_NOCACHE, $store);
        if($noCache){
            $data['noCache'] = 'true';
        }else{
            $data['noCache'] = 'false';
        }

        $onSelect = Mage::getStoreConfig(self::XML_PATH_SUGGEST_ONSELECT, $store);
        if($buckets != null){
            $data['autocomplete']['onSelect'] = $onSelect;
        }

        $minChars = Mage::getStoreConfig(self::XML_PATH_SUGGEST_MINCHAR, $store);
        if($minChars != null){
            $data['autocomplete']['minChars'] = $minChars;
        }

        $width = Mage::getStoreConfig(self::XML_PATH_SUGGEST_WIDTH, $store);
        if($width != null){
            $data['autocomplete']['width'] = $width;
        }

        $zIndex = Mage::getStoreConfig(self::XML_PATH_SUGGEST_ZINDEX, $store);
        if($zIndex != null){
            $data['autocomplete']['zIndex'] = $zIndex;
        }


        return $data;
    }

}