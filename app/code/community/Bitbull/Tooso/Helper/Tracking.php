<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
class Bitbull_Tooso_Helper_Tracking extends Mage_Core_Helper_Abstract
{
    const CONTAINER_BLOCK = 'before_body_end';

    const XML_PATH_ANALYTICS_INCLUDE_LIBRARY = 'tooso/analytics/include_library';
    const XML_PATH_ANALYTICS_LIBRARY_ENDPOINT = 'tooso/analytics/library_endpoint';
    const XML_PATH_ANALYTICS_API_ENDPOINT = 'tooso/analytics/api_endpoint';
    const XML_PATH_ANALYTICS_KEY = 'tooso/analytics/key';
    const XML_PATH_ANALYTICS_DEBUG_MODE = 'tooso/analytics/debug_mode';

    /**
     * Get block to append tracking script and cookies managers
     *
     */
    public function getScriptContainerBlock(){
        $layout = Mage::app()->getLayout();
        return $layout->getBlock(self::CONTAINER_BLOCK);
    }

    /**
     * Create Product Tracking Block
     *
     * @param $productId
     * @return Bitbull_Tooso_Block_TrackingPixel
     */
    public function getProductTrackingBlock($productId){
        $layout = Mage::app()->getLayout();
        $block = $layout->createBlock('tooso/tracking_productView');
        $block->setProductID($productId);
        return $block;
    }

    /**
     * Create Page Tracking Block
     *
     * @return Bitbull_Tooso_Block_TrackingPixel
     */
    public function getPageTrackingBlock(){
        $layout = Mage::app()->getLayout();
        $block = $layout->createBlock('tooso/tracking_pageView');
        return $block;
    }

    /**
     * Create Checkout Tracking Block
     *
     * @param $orderId
     * @return Bitbull_Tooso_Block_TrackingPixel
     */
    public function getCheckoutTrackingBlock($orderId){
        $layout = Mage::app()->getLayout();
        $block = $layout->createBlock('tooso/tracking_checkout');
        $block->setOrderId($orderId);
        return $block;
    }

    /**
     * Create Clear Search ID block
     *
     * @return Bitbull_Tooso_Block_ClearSearchID
     */
    public function getClearSearchIDBlock(){
        $layout = Mage::app()->getLayout();
        $block = $layout->createBlock('tooso/clearSearchId');
        return $block;
    }

    /**
     * Check if user is coming from a search page or not
     *
     * @return boolean
     */
    public function isUserComingFromSearch(){
        $searchId = Mage::helper('tooso/session')->getSearchId();
        return $searchId != null && $searchId != "";
    }

    /**
     * Detect if request comes from mobile device
     *
     * @return integer
     */
    public function isMobile(){
        $detect = new Bitbull_Mobile_Detect();
        return (int) $detect->isMobile();
    }

    /**
     * Get client remote address, if server is behind proxy use forwarded http
     *
     * @return string
     */
    public function getRemoteAddr(){
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[count($ips) - 1]);
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    /**
     * Get client user agent
     *
     * @return string
     */
    public function getUserAgent(){
        return Mage::helper('core/http')->getHttpUserAgent();
    }

    /**
     * Get last page visited
     */
    public function getLastPage(){
        return Mage::app()->getRequest()->getServer('HTTP_REFERER');
    }

    /**
     * Get current page
     */
    public function getCurrentPage(){
        return Mage::helper('core/url')->getCurrentUrl();
    }

    /**
     * Check if is necessary to include JS library
     */
    public function includeTrackingJSLibrary($store = null){
        return Mage::getStoreConfigFlag(self::XML_PATH_ANALYTICS_INCLUDE_LIBRARY, $store);
    }

    /**
     * Create Product TrackingPixel Block
     *
     * @return Bitbull_Tooso_Block_Tracking_LibraryInclusion
     */
    public function getTrackingLibraryBlock(){
        $layout = Mage::app()->getLayout();
        $block = $layout->createBlock('tooso/tracking_library');
        return $block;
    }

    /**
     * Create Product TrackingPixel Block
     *
     * @return Bitbull_Tooso_Block_Tracking_LibraryInclusion
     */
    public function getTrackingLibraryInitBlock(){
        $layout = Mage::app()->getLayout();
        $block = $layout->createBlock('tooso/tracking_libraryInit');
        return $block;
    }

    /**
     * Get tracking endpoint
     */
    public function getTrackingLibraryEndpoint($store = null){
        return Mage::getStoreConfig(self::XML_PATH_ANALYTICS_LIBRARY_ENDPOINT, $store);
    }

    /**
     * Get tracking endpoint
     */
    public function getTrackingAPIEndpoint($store = null){
        return Mage::getStoreConfig(self::XML_PATH_ANALYTICS_API_ENDPOINT, $store);
    }

    /**
     * Get tracking key
     */
    public function getTrackingKey($store = null){
        return Mage::getStoreConfig(self::XML_PATH_ANALYTICS_KEY, $store);
    }

    /**
     * Is debug mode
     */
    public function isDebugMode($store = null){
        return Mage::getStoreConfigFlag(self::XML_PATH_ANALYTICS_DEBUG_MODE, $store);
    }

    /**
     * Get product tracking params
     *
     * @param $productId
     * @return null|array
     */
    public function getProductTrackingParams($productId){
        $product = Mage::getModel('catalog/product')->load($productId);
        if($product == null){
            return null;
        }
        $trackingProductParams = [
            'id' => $product->getSku(),
            'name' => $product->getName(),
            'brand' => $product->getBrand(),
            'price' => $product->getFinalPrice(),
            'quantity' => 1
        ];

        $categoryIds = $product->getCategoryIds();
        $currentProductCategory = null;
        if(count($categoryIds) > 0){
            $currentProductCategory = Mage::getModel('catalog/category')->load($categoryIds[0]);
            $trackingProductParams['category'] = $currentProductCategory->getName();
        }else{
            $trackingProductParams['category'] = null;
        }

        return $trackingProductParams;
    }

    /**
     * Make tracking request server2server
     *
     * @param $params
     * @return bool
     */
    public function makeTrackingRequest($params)
    {
        $profilingParams = Mage::helper('tooso')->getProfilingParams();

        $params = array_merge([
            "z" => Mage::helper('tooso')->getUuid(),
            "uip" => $this->getRemoteAddr(),
            "tid" => $this->getTrackingKey(),
            "v" => "1",
            "dl" => $this->getCurrentPage(),
            "dr" => $this->getLastPage(),
            "cid" => $profilingParams['clientId'],
            "uid" => $profilingParams['userId'],
            "tm" => round(microtime(true) * 1000),
        ], $params);

        $curl = new Varien_Http_Adapter_Curl();
        $curl->setConfig(array(
            'timeout'   => 5
        ));

        $queryString = [];
        foreach ($params as $key => $value) {
            $queryString[] = $key . '=' . urlencode($value);
        }

        $url = $this->getTrackingAPIEndpoint().'collect?' . implode('&', $queryString);

        Mage::helper('tooso/log')->debug("Performing API request to url: " . $url . " with method: GET");
        Mage::helper('tooso/log')->debug("Params: " . print_r($params, true));

        $curl->write(Zend_Http_Client::GET, $url, '1.0');
        $output = $curl->read();
        if ($output === false) {
            return false;
        }
        $curl->close();

        Mage::helper('tooso/log')->debug("Raw response: " . print_r($output, true));

        return true;
    }

}