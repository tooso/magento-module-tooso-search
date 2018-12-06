<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
class Bitbull_Tooso_Helper_Search extends Mage_Core_Helper_Abstract
{
    const XML_PATH_RESPONSE_TYPE = 'tooso/search/response_type';
    const XML_PATH_FALLBACK_ENABLE = 'tooso/search/fallback_enable';
    const XML_PATH_DEFAULT_LIMIT = 'tooso/search/default_limit';
    const REGISTRY_SEARCH_RESULT_KEY = 'tooso-search-response';

    /**
     * @param $store
     * @return bool
     */
    public function isSearchEnriched($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_RESPONSE_TYPE, $store);
    }

    /**
     * @param $store
     * @return bool
     */
    public function isFallbackEnable($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_FALLBACK_ENABLE, $store);
    }

    /**
     * @param Bitbull_Tooso_Search_Result $response
     */
    public function storeResponse($response)
    {
        Mage::register(self::REGISTRY_SEARCH_RESULT_KEY, $response);
    }

    /**
     * @return Bitbull_Tooso_Search_Result
     */
    public function getResponse()
    {
        return Mage::registry(self::REGISTRY_SEARCH_RESULT_KEY);
    }

    /**
     * @param $sku string
     * @return string|null
     */
    public function getSkuVariantBySku($sku)
    {
        $result = $this->getResponse();
        if($result == null){
            return null;
        }

        if(!$this->isSearchEnriched()){
            return $sku;
        }

        $products = $result->getResults();
        foreach ($products as $product) {
            if(!is_object($product)){
                return null;
            }

            if($product->sku == $sku){
                return $product->sku_variant;
            }
        }

        return $sku;
    }

    /**
     * @param $sku
     * @return null
     */
    public function getProductInfoBySku($sku)
    {
        $result = $this->getResponse();
        if($result == null || !$this->isSearchEnriched()){
            return null;
        }

        $products = $result->getResults();
        foreach ($products as $product) {
            if(!is_object($product)){
                return null;
            }

            if($product->sku == $sku){
                return $product;
            }
        }

        return null;
    }

    /**
     * Get default limit param
     *
     * @return int|null
     */
    public function getDefaultLimit()
    {
        $limit = Mage::getStoreConfig(self::XML_PATH_DEFAULT_LIMIT);
        if ($limit === null || !is_numeric($limit)) {
            return null;
        }

        return (int)$limit;
    }
}