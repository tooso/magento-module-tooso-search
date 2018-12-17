<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
class Bitbull_Tooso_Helper_Tracking extends Mage_Core_Helper_Abstract
{
    const CONTAINER_BLOCK_AFTER = 'after_body_start';
    const CONTAINER_BLOCK_BEFORE = 'before_body_end';

    const XML_PATH_ANALYTICS_INCLUDE_LIBRARY = 'tooso/analytics/include_library';
    const XML_PATH_ANALYTICS_LIBRARY_ENDPOINT = 'tooso/analytics/library_endpoint';
    const XML_PATH_ANALYTICS_API_ENDPOINT = 'tooso/analytics/api_endpoint';
    const XML_PATH_ANALYTICS_API_VERSION = 'tooso/analytics/api_version';
    const XML_PATH_ANALYTICS_KEY = 'tooso/analytics/key';
    const XML_PATH_ANALYTICS_DEBUG_MODE = 'tooso/analytics/debug_mode';
    const XML_PATH_ANALYTICS_COOKIE_DOMAIN = 'tooso/analytics/cookie_domain';
    const XML_PATH_ANALYTICS_TRACK_USERID = 'tooso/analytics/track_userid';

    /**
     * Get block to append init tracking script and cookies managers
     *
     */
    public function getInitScriptContainerBlock(){
        $layout = Mage::app()->getLayout();
        return $layout->getBlock(self::CONTAINER_BLOCK_AFTER);
    }

    /**
     * Get block to append tracking script and cookies managers
     *
     */
    public function getScriptContainerBlock(){
        $layout = Mage::app()->getLayout();
        return $layout->getBlock(self::CONTAINER_BLOCK_BEFORE);
    }

    /**
     * Create Product Tracking Block
     *
     * @param $productId
     * @return Bitbull_Tooso_Block_TrackingPixel
     */
    public function getProductTrackingBlock($productId){
        $layout = Mage::app()->getLayout();
        $block = $layout->createBlock('tooso/tracking_productView', 'tooso_tracking_productView');
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
        $block = $layout->createBlock('tooso/tracking_pageView', 'tooso_tracking_pageView');
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
        $block = $layout->createBlock('tooso/tracking_checkout', 'tooso_tracking_checkout');
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
        $block = $layout->createBlock('tooso/clearSearchId', 'tooso_clearSearchId');
        return $block;
    }

    /**
     * Check if user is coming from a search page or not
     *
     * @return boolean
     */
    public function isUserComingFromSearch(){
        $lastPageSearch = Mage::helper('tooso/session')->getLastSearchPage();
        if ($lastPageSearch === false || $lastPageSearch === '') {
            return false;
        }
        $lastPage = $this->getLastPage();
        return $lastPageSearch == $lastPage;
    }

    /**
     * Get client remote address, if server is behind proxy use forwarded http
     *
     * @return string
     */
    public function getRemoteAddr(){
        $remoteAddr = Mage::helper('core/http')->getRemoteAddr();
        if (strpos($remoteAddr, ',') !== false){
            $remoteAddrParts = explode(',', $remoteAddr);
            $remoteAddr = $remoteAddrParts[0];
        }
        return $remoteAddr;
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
     * Get current path
     */
    public function getCurrentPath(){
        $currentUrl = $this->getCurrentPage();
        $url = Mage::getSingleton('core/url')->parseUrl($currentUrl);
        return $url->getPath();
    }

    /**
     * Get cookie domain
     */
    public function getCookieDomain($default = null, $store = null){
        $cookieDomain = Mage::getStoreConfig(self::XML_PATH_ANALYTICS_COOKIE_DOMAIN, $store);
        if ($cookieDomain === null || trim($cookieDomain) === '') {
            if ($default === null) {
                $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
                $domainPart = explode('.', parse_url($url,  PHP_URL_HOST));
                $cookieDomain = '.'.$domainPart[count($domainPart) - 2].'.'.$domainPart[count($domainPart) - 1];
            }else{
                $cookieDomain = $default;
            }
        }
        return $cookieDomain;
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
        $block = $layout->createBlock('tooso/tracking_library', 'tooso_tracking_library');
        return $block;
    }

    /**
     * Create Product TrackingPixel Block
     *
     * @return Bitbull_Tooso_Block_Tracking_LibraryInclusion
     */
    public function getTrackingLibraryInitBlock(){
        $layout = Mage::app()->getLayout();
        $block = $layout->createBlock('tooso/tracking_libraryInit', 'tooso_tracking_libraryInit');
        return $block;
    }

    /**
     * Create Plugin Infos Block
     *
     * @return Bitbull_Tooso_Block_Tracking_PluginInfos
     */
    public function getPluginInfosBlock(){
        $layout = Mage::app()->getLayout();
        $block = $layout->createBlock('tooso/tracking_pluginInfos', 'tooso_tracking_pluginInfos');
        return $block;
    }

    /**
     * Create Customer Tracking Block
     *
     * @return Bitbull_Tooso_Block_Tracking_CustomerTracking
     */
    public function getCustomerTrackingBlock(){
        $layout = Mage::app()->getLayout();
        $block = $layout->createBlock('tooso/tracking_customerTracking', 'tooso_tracking_customerTracking');
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
     * Get tracking endpoint
     */
    public function getTrackingAPIVersion($store = null){
        return Mage::getStoreConfig(self::XML_PATH_ANALYTICS_API_VERSION, $store);
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
     * Get store currency code
     *
     * @param null $store
     * @return string
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getCurrencyCode($store = null){
        $code = null;
        try{
            $code = Mage::app()->getStore($store)->getCurrentCurrencyCode();
        } catch (Mage_Core_Model_Store_Exception $e){
            Mage::helper('tooso/log')->logException($e);
        }
        return $code;
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
        $customerSession = Mage::getSingleton('customer/session');

        $params = array_merge([
            "z" => Mage::helper('tooso')->getUuid(),
            "tid" => $this->getTrackingKey(),
            "v" => $this->getTrackingAPIVersion(),
        ], $profilingParams, $params);

        if ($customerSession->isLoggedIn() === true && Mage::helper('tooso/tracking')->isUserIdTrakingEnable() === true){
            $params['uid'] = $customerSession->getCustomerId();
        }

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

    /**
     * Retrive current search Id with a new uuid as fallback
     *
     * @return string
     */
    public function getSearchIdWithFallback()
    {
        $searchId = Mage::helper('tooso/session')->getSearchId();
        if($searchId == null || $searchId == ""){
            $searchId = substr('magento_'.Mage::helper('tooso')->getUuid(), 0, 36);
        }

        return $searchId;
    }

    /**
     * Is user id tracking active?
     *
     * @return bool
     */
    public function isUserIdTrakingEnable($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ANALYTICS_TRACK_USERID, $store);
    }

    /**
     * Get tracking agent
     *
     * @return string
     */
    public function getTrackingAgent()
    {
        $agent = 'PHP/'.phpversion();
        $agent .= ' Magento/'.Mage::getVersion();
        $agent .= ' Tooso/'.Mage::getConfig()->getNode()->modules->Bitbull_Tooso->version;
        return $agent;
    }

}