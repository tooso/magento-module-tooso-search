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

        $productId = $this->getRequest()->getParam('id');
        if($productId == null){
            $this->_logger->warn('Tracking: product param not found');
            return;
        }

        $currentProduct = Mage::getModel('catalog/product')->load($productId);
        if($currentProduct == null){
            $this->_logger->warn('Tracking: product not found with id '.$productId);
            return;
        }

        if(Mage::helper('tooso/tracking')->isUserComingFromSearch()){ //request from search page

            $this->_logger->debug('Tracking: elaborating result..');
            $id = $currentProduct->getId();
            $sku = $currentProduct->getSku();
            $toosoSearchId = Mage::helper('tooso/session')->getSearchId();

            if($toosoSearchId){
                // Get rank collection from search collection
                $searchRankCollection = Mage::helper('tooso/session')->getRankCollection();
                $rank = -1;
                if($searchRankCollection != null && isset($searchRankCollection[$id])){
                    $rank = $searchRankCollection[$id];
                }else{
                    if($searchRankCollection == null){
                        $this->_logger->debug('Tracking: rank collection not found in session');
                    }else{
                        $this->_logger->debug('Tracking: sku not found in rank collection, printing..');
                        foreach ($searchRankCollection as $rankId => $rankPos){
                            $this->_logger->debug('Tracking: '.$rankId.' => '.$rankPos);
                        }
                    }
                }

                $order = Mage::helper('tooso/session')->getSearchOrder();
                if($order == null){
                    $order = "relevance";
                }

                $profilingParams = Mage::helper('tooso')->getProfilingParams($this->_getPageParams());
                $params = array(
                    "searchId" => $toosoSearchId,
                    "objectId" => $sku,
                    "rank" => $rank,
                    "order" => $order
                );

                $this->_client->resultTracking($params, $profilingParams);

            }else{
                $this->_logger->warn('Tracking: search id not found in session');
                return;
            }

        }else{ // request not from search page

            $this->_logger->debug('Tracking: elaborating product view..');

            $sku = $currentProduct->getSku();
            $profilingParams = Mage::helper('tooso')->getProfilingParams($this->_getPageParams());
            $params = array(
                "objectId" => $sku
            );

            $this->_client->productViewTracking($params, $profilingParams);

        }

        $this->_logger->debug('Tracking: tracked product view '.$sku);
        $this->_setEmptyScriptResponse();
    }

    /**
     * Tracking page view
     */
    public function pageAction(){

        $pages = $this->_getPageParams();
        $profilingParams = Mage::helper('tooso')->getProfilingParams($pages);
        $params = array();
        $this->_client->pageViewTracking($params, $profilingParams);

        $this->_logger->debug('Tracking: tracked page view '.$pages['currentPage']);
        $this->_setEmptyScriptResponse();
    }

    /**
     * Tracking checkout success page
     */
    public function checkoutAction(){
        $orderId = $this->getRequest()->getParam('order');
        $order = Mage::getSingleton('sales/order')->loadByIncrementId($orderId);

        $objectIds = array();
        $prices = array();
        $qtys = array();

        $items = $order->getAllItems();
        foreach ($items as $item) {
            array_push($objectIds, $item->getSku());
            array_push($prices, $item->getPrice());
            array_push($qtys, $item->getQtyOrdered());
        }

        $profilingParams = Mage::helper('tooso')->getProfilingParams($this->_getPageParams());
        $this->_client->checkoutTracking($objectIds, $prices, $qtys, $profilingParams);

        $this->_logger->debug('Tracking: tracked checkout order '.$orderId);
        $this->_setEmptyScriptResponse();
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
            'lastPage' => $currentPageIdentifier,
            'currentPage' => $lastPageIdentifier
        );
    }

}