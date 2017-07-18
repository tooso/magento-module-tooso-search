<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_TrackingPixel_Result extends Bitbull_Tooso_Block_TrackingPixel
{
    const BLOCK_ID = 'tooso_tracking_pixel_result';
    const SCRIPT_ID = 'tooso-tracking-result';
    const SCRIPT_ENDPOINT = '/tooso/tracking/result/';

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
            $this->_logger->warn('Tracking result: product_id not set');
            return;
        }

        $currentProduct = Mage::getModel('catalog/product')->load($this->_productId);
        if($currentProduct == null){
            $this->_logger->warn('Tracking result: product not found with id '.$this->_productId);
            return;
        }

        $this->_logger->debug('Tracking result: elaborating result..');
        $sku = $currentProduct->getSku();

        // Get rank collection from search collection
        $searchRankCollection = Mage::helper('tooso/session')->getRankCollection();
        $rank = -1;
        if($searchRankCollection != null && isset($searchRankCollection[$this->_productId])){
            $rank = $searchRankCollection[$this->_productId];
        }else{
            if($searchRankCollection == null){
                $this->_logger->debug('Tracking result: rank collection not found in session');
            }else{
                $this->_logger->debug('Tracking result: sku not found in rank collection, printing..');
                foreach ($searchRankCollection as $rankId => $rankPos){
                    $this->_logger->debug('Tracking result: '.$rankId.' => '.$rankPos);
                }
            }
        }

        $order = Mage::helper('tooso/session')->getSearchOrder();
        if($order == null){
            $order = "relevance";
        }

        $url = self::SCRIPT_ENDPOINT."sku/$sku/rank/$rank/order/$order".'/'.$this->_getPageParams();
        return "<script id='".self::SCRIPT_ID."' async type='text/javascript' src='".$url."'></script>";
    }

    /**
     * @param $id
     */
    public function setProductID($id){
        $this->_productId = $id;
    }
}