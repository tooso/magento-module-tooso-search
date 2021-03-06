<?php
/**
 * @package Bitbull_Tooso
 * @author Gennaro Vietri <gennaro.vietri@bitbull.it>
 */

class Bitbull_Tooso_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ENABLE_SEARCH = 'tooso/active/frontend';

    const XML_PATH_ENABLE_INDEX = 'tooso/active/index';

    const XML_PATH_ENABLE_TRACKING = 'tooso/active/tracking';

    const XML_PATH_ENABLE_SUGGESTION_ACTIVE = 'tooso/active/suggestion';

    const XML_PATH_ENABLE_SPEECHTOTEXT_ACTIVE = 'tooso/active/speech_to_text';

    const XML_PATH_SERVER_APIKEY = 'tooso/server/api_key';

    const XML_PATH_SERVER_APIVESION = 'tooso/server/api_version';

    const XML_PATH_SERVER_API_BASEURL = 'tooso/server/api_base_url';

    protected $_fixedSearchString = null;
    
    protected $_searchId = null;

    protected $_products = null;

    protected $_searchAlreadyDone = false;

    public function isSearchEnabled($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLE_SEARCH, $store);
    }

    public function isIndexEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLE_INDEX);
    }

    public function isTrackingEnabled($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLE_TRACKING, $store);
    }

    public function isSuggestionEnabled($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLE_SUGGESTION_ACTIVE, $store);
    }

    public function isSdkEnabled($store = null)
    {
        return $this->isSpeechToTextEnabled(); // add more sdk feature flags here
    }

    public function isSpeechToTextEnabled($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLE_SPEECHTOTEXT_ACTIVE, $store);
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        return $this->_products;
    }

    /**
     * @param array $products
     */
    public function setProducts($products)
    {
        $this->_products = $products;
    }

    /**
     * @param string $fixedSearchString
     */
    public function setFixedSearchString($fixedSearchString)
    {
        $this->_fixedSearchString = $fixedSearchString;
    }

    /**
     * @return string
     */
    public function getFixedSearchString()
    {
        return $this->_fixedSearchString;
    }

    /**
     * @param string $searchId
     */
    public function setSearchId($searchId)
    {
        $this->_searchId = $searchId;
    }

    /**
     * @return string
     */
    public function getSearchId()
    {
        return $this->_searchId;
    }

    public function isTypoCorrectedSearch()
    {
        return Mage::app()->getRequest()->getParam('typoCorrection', 'true') == 'true';
    }

    /**
     * @return string
     */
    public function getParentSearchId()
    {
        return Mage::app()->getRequest()->getParam('parentSearchId');
    }
    
    /**
     * Create and configure a Tooso API Client instance
     * 
     * @return Bitbull_Tooso_Client
    */
    public function getClient($storeCode = null, $language = null)
    {
        $apiKey = Mage::getStoreConfig(self::XML_PATH_SERVER_APIKEY, $storeCode);
        $apiVersion = Mage::getStoreConfig(self::XML_PATH_SERVER_APIVESION, $storeCode);
        $apiBaseUrl = Mage::getStoreConfig(self::XML_PATH_SERVER_API_BASEURL, $storeCode);
        if($language == null){
            $language = Mage::app()->getLocale()->getLocaleCode();
        }
        if($storeCode == null){
            $storeCode = Mage::app()->getStore()->getCode();
        }
        $client = new Bitbull_Tooso_Client($apiKey, $apiVersion, $apiBaseUrl, $language, $storeCode);

        $client->setLogger(Mage::helper('tooso/log'));
        $client->setReportSender(Mage::helper('tooso/log_send'));
        $client->setSessionStorage(Mage::helper('tooso/session'));
        $client->setAgent(Mage::helper('tooso/tracking')->getTrackingAgent());

        return $client;
    }

    /**
     * Get API key
     *
     * @param null $store
     * @return string
     */
    public function getApiKey($store = null){
        return Mage::getStoreConfig(self::XML_PATH_SERVER_APIKEY, $store);
    }

    /**
     * Get profiling parameters
     *
     * @param null|array $override
     * @return array
     */
    public function getProfilingParams($override = null)
    {
        $customerSession = Mage::getSingleton('customer/session');
        $sessionId = Mage::getSingleton('core/session')->getSessionId();
        $clientId = Mage::helper('tooso/session')->getClientId();

        $params = array(
            'uip' => Mage::helper('tooso/tracking')->getRemoteAddr(),
            'ua' => Mage::helper('tooso/tracking')->getUserAgent(),
            'sessionId' => $sessionId,
            'cid' => $clientId,
            'dr' => Mage::helper('tooso/tracking')->getLastPage(),
            'dl' => Mage::helper('tooso/tracking')->getCurrentPage(),
            'tm' => round(microtime(true) * 1000)
        );

        if (Mage::helper('tooso/tracking')->isUserIdTrakingEnable() && $customerSession->isLoggedIn()) {
            $params['uid'] = $customerSession->getCustomerId();
        }

        if($override != null && is_array($override)){
            foreach ($override as $key => $value){
                $params[$key] = $value;
            }
        }

        return $params;
    }

    /**
     * Set SearchAlreadyDone to true
     */

    public function setSearchAlreadyDone(){
        $this->_searchAlreadyDone = true;
    }

    /**
     * @return boolean
     */

    public function getSearchAlreadyDone(){
        return $this->_searchAlreadyDone;
    }

    /**
     * Get product attributes
     */

    public function getAttributesToIndex(){

    }

    /**
     * Generate uuid
     *
     * @return string
     */
    public function getUuid(){
        return Mage::helper('tooso')->getClient()->getUuid();
    }

    /**
     * Build layout xml
     * this is a fix to let Turpentine find block during ESI request
     *
     * @param $block Mage_Core_Block_Template
     * @return string
     */
    public function addLayoutUpdate($block){
        $layout = Mage::app()->getLayout();
        $parent = $block->getParentBlock();
        $referenceName = '';
        if ($parent !== null) {
            $referenceName = $block->getParentBlock()->getNameInLayout();
        }
        $layout->getUpdate()->addUpdate('
            <reference name="'.$referenceName.'">
                <block type="core/template" name="'.$block->getNameInLayout().'" template="" class="'.get_class($block).'"/>
             </reference>
        ');
    }

    /**
     * Get current requested block for ESI request
     *
     * @return string
     */
    public function getESIRequestBlockName()
    {
        $req = Mage::app()->getRequest();
        $esiHelper = Mage::helper('turpentine/esi');
        $dataHelper = Mage::helper('turpentine/data');
        if ($esiHelper === null || $dataHelper === null) {
            return '';
        }
        $esiDataParamValue = $req->getParam( $esiHelper->getEsiDataParam() );
        $esiDataArray = $dataHelper->thaw( $esiDataParamValue );
        if (!isset($esiDataArray['name_in_layout'])) {
            return '';
        }
        return $esiDataArray['name_in_layout'];
    }
}
