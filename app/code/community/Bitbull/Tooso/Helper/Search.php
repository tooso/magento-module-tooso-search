<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
class Bitbull_Tooso_Helper_Search extends Mage_Core_Helper_Abstract
{
    const XML_PATH_RESPONSE_TYPE = 'tooso/search/response_type';
    const REGISTRY_SEARCH_RESULT_KEY = 'tooso-search-response';

    /**
     * @param $store
     * @return bool
     */
    public function isSearchEnriched($store = null)
    {
        $type = Mage::getStoreConfig(self::XML_PATH_RESPONSE_TYPE, $store);
        return $type == 'enriched';
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
            if($product->sku == $sku){
                return $product->sku_variant;
            }
        }

        return $sku;
    }
}