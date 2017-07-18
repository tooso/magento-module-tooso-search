<?php
class Bitbull_Tooso_TrackingController extends Mage_Core_Controller_Front_Action {

    /**
     * @var Bitbull_Tooso_Helper_Log
     */
    protected $_logger = null;

    /**
     * @var Bitbull_Tooso_Client
     */
    protected $_client = null;


    public function _construct(){
        parent::_construct();

        $this->_logger = Mage::helper('tooso/log');
        $this->_client = Mage::helper('tooso')->getClient();
    }

    /**
     * Tracking product view
     */
    public function productAction() {

        $sku = $this->getRequest()->getParam('sku');
        if($sku == null){
            $this->_logger->warn('Tracking product: product sku param not found');
            return;
        }

        $profilingParams = Mage::helper('tooso')->getProfilingParams($this->_getPageParams());
        $this->_client->productViewTracking($sku, $profilingParams);
        $this->_logger->debug('Tracking product: tracked search result '.$sku);
        $this->_setEmptyScriptResponse();
        return;
    }

    /**
     * Tracking result action
     */
    public function resultAction(){
        $sku = $this->getRequest()->getParam('sku');
        if($sku == null){
            $this->_logger->warn('Tracking result: product sku param not found');
            return;
        }

        $rank = $this->getRequest()->getParam('rank');
        if($rank == null){
            $rank = -1;
        }

        $order = $this->getRequest()->getParam('order');
        if($order == null){
            $order = "relevance";
        }

        $toosoSearchId = Mage::helper('tooso/session')->getSearchId();
        if($toosoSearchId){
            $profilingParams = Mage::helper('tooso')->getProfilingParams($this->_getPageParams());
            $this->_client->resultTracking($toosoSearchId, $sku, $rank, $order, $profilingParams);
            $this->_logger->debug('Tracking result: tracked search result '.$sku);
            $this->_setEmptyScriptResponse();
            return;
        }else{
            $this->_logger->warn('Tracking result: search id not found in session');
            return;
        }
    }

    /**
     * Tracking page view
     */
    public function pageAction(){
        $currentPageIdentifier = base64_decode($this->getRequest()->getParam('currentPage'));
        $lastPageIdentifier = base64_decode($this->getRequest()->getParam('lastPage'));

        $profilingParams = Mage::helper('tooso')->getProfilingParams($this->_getPageParams());
        $this->_client->pageViewTracking($currentPageIdentifier, $lastPageIdentifier, $profilingParams);

        $this->_logger->debug('Tracking page view: tracked page view '.$currentPageIdentifier);
        $this->_setEmptyScriptResponse();
        return;
    }

    /**
     * Tracking checkout success page
     */
    public function checkoutAction(){

        $delimiter = Bitbull_Tooso_Block_TrackingPixel_Checkout::ARRAY_VALUES_SEPARATOR;

        $skusStr = $this->getRequest()->getParam('skus');
        $skus = array();
        if($skusStr != null){
            $skus = explode($delimiter, $skusStr);
        }

        $pricesStr = $this->getRequest()->getParam('prices');
        $prices = array();
        if($pricesStr != null){
            $prices = explode($delimiter, $pricesStr);
        }

        $qtysStr = $this->getRequest()->getParam('qtys');
        $qtys = array();
        if($qtysStr != null){
            $qtys = explode($delimiter, $qtysStr);
        }

        $profilingParams = Mage::helper('tooso')->getProfilingParams($this->_getPageParams());
        $this->_client->checkoutTracking($skus, $prices, $qtys, $profilingParams);

        $this->_logger->debug('Tracking checkout: tracked checkout order');
        $this->_setEmptyScriptResponse();
        return;
    }

    /**
     * Response with empty script
     */
    protected function _setEmptyScriptResponse(){
        // Prevent browser cache
        $this->getResponse()->setHeader('Expires', '0');
        $this->getResponse()->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
        $this->getResponse()->setHeader('Pragma','no-cache');
        $this->getResponse()->setHeader('Cache-Control', 'post-check=0, pre-check=0');

        // Set javascript content type
        $this->getResponse()->setHeader('Content-type', 'application/javascript');

        // Response with empty script
        $this->getResponse()->setBody("");
    }

    /**
     * @return array
     */
    protected function _getPageParams(){
        $currentPageIdentifier = base64_decode($this->getRequest()->getParam('currentPage'));
        $lastPageIdentifier = base64_decode($this->getRequest()->getParam('lastPage'));

        return array(
            'currentPage' => $currentPageIdentifier,
            'lastPage' => $lastPageIdentifier
        );
    }

}