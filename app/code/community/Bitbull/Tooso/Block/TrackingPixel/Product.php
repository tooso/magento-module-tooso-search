<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_TrackingPixel_Product extends Bitbull_Tooso_Block_TrackingPixel
{
    const BLOCK_ID = 'tooso_tracking_pixel_product';
    const SCRIPT_ID = 'tooso-tracking-product';
    const SCRIPT_ENDPOINT = '/tooso/tracking/product/';

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

        $this->_logger->debug('Tracking product: elaborating product view..');
        $sku = $currentProduct->getSku();

        $url = self::SCRIPT_ENDPOINT."sku/$sku".'/'.$this->_getPageParams();
        return "<script id='".self::SCRIPT_ID."' async type='text/javascript' src='".$url."'></script>";
    }

    /**
     * @param $id
     */
    public function setProductID($id){
        $this->_productId = $id;
    }
}