<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_TrackingPixel_Product extends Mage_Core_Block_Template
{
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

    public function _construct()
    {
        parent::_construct();

        $this->_logger = Mage::helper('tooso/log');

        $this->setBlockId('tooso_tracking_pixel');
        $this->addCacheTag(array(
            Mage::app()->getStore()->getId(),
            Mage_Catalog_Model_Product::CACHE_TAG
        ));
    }

    protected function _toHtml()
    {
        if($this->_productId == null){
            $this->_logger->warn('Tracking script: product_id not set');
            return;
        }
        $url = self::SCRIPT_ENDPOINT."id/".$this->_productId;
        $this->_logger->debug('Tracking script: added tracking script');
        return "<script id='".self::SCRIPT_ID."' async type='text/javascript' src='".$url."'></script>";
    }

    public function setProductID($id){
        $this->_productId = $id;
    }
}