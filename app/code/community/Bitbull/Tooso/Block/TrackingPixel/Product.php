<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_TrackingPixel_Product extends Bitbull_Tooso_Block_TrackingPixel
{
    const BLOCK_ID = 'tooso_tracking_pixel_product';

    const RESULT_SCRIPT_ID = 'tooso-tracking-result';
    const RESULT_SCRIPT_ENDPOINT = 'tooso/tracking/result/';

    const PRODUCT_SCRIPT_ID = 'tooso-tracking-product';
    const PRODUCT_SCRIPT_ENDPOINT = 'tooso/tracking/product/';


    /**
     * @var Bitbull_Tooso_Helper_Log
     */
    protected $_logger = null;

    /**
     * @var null|integer
     */
    protected $_productId = null;

    protected function _toHtml()
    {
        if($this->_productId == null){
            $this->_logger->warn('Tracking product: product_id not set');
            return;
        }

        $currentProduct = Mage::getModel('catalog/product')->load($this->_productId);

        if($currentProduct == null){
            $this->_logger->warn('Tracking product: product not found with id '.$this->_productId);
            return;
        }

        if(Mage::helper('tooso/tracking')->isUserComingFromSearch()){
            $this->_logger->debug('Tracking product: elaborating result..');
            $sku = $currentProduct->getSku();

            // Get rank collection from search collection
            $searchRankCollection = Mage::helper('tooso/session')->getRankCollection();
            $rank = -1;
            if($searchRankCollection != null && isset($searchRankCollection[$this->_productId])){
                $rank = $searchRankCollection[$this->_productId];
            }else{
                if($searchRankCollection == null){
                    $this->_logger->debug('Tracking product: rank collection not found in session');
                }else{
                    $this->_logger->debug('Tracking product: sku not found in rank collection, printing..');
                    foreach ($searchRankCollection as $rankId => $rankPos){
                        $this->_logger->debug('Tracking product: '.$rankId.' => '.$rankPos);
                    }
                }
            }

            $order = Mage::helper('tooso/session')->getSearchOrder();
            if($order == null){
                $order = "relevance";
            }

            $url = Mage::getBaseUrl().self::RESULT_SCRIPT_ENDPOINT."sku/$sku/rank/$rank/order/$order".'/'.$this->_getPageParams();
            return "<script id='".self::RESULT_SCRIPT_ID."' async type='text/javascript' src='".$url."'></script>";
        }else{
            $this->_logger->debug('Tracking product: elaborating product view..');
            $sku = $currentProduct->getSku();

            $url = Mage::getBaseUrl().self::PRODUCT_SCRIPT_ENDPOINT."sku/$sku".'/'.$this->_getPageParams();
            return "<script id='".self::PRODUCT_SCRIPT_ID."' async type='text/javascript' src='".$url."'></script>";
        }
    }

    /**
     * Get Cache Key Info
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $info = parent::getCacheKeyInfo();
        $info['object_id'] = $this->_productId;
        return $info;
    }

    /**
     * @param $id
     */
    public function setObjectID($id){
        $this->setProductID($id);
    }

    /**
     * @param $id
     */
    public function setProductID($id){
        $this->_productId = $id;
    }
}