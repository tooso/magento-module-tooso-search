<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_TrackingPixel_Checkout extends Mage_Core_Block_Template
{
    const SCRIPT_ID = 'tooso-tracking-checkout';
    const SCRIPT_ENDPOINT = '/tooso/tracking/checkout/';

    /**
     * @var Bitbull_Tooso_Helper_Log
     */
    protected $_logger = null;

    /**
     * @var null|string
     */
    protected $_orderId = null;

    public function _construct()
    {
        parent::_construct();

        $this->_logger = Mage::helper('tooso/log');

        $this->setBlockId('tooso_tracking_pixel_checkout');
        $this->addCacheTag(array(
            Mage::app()->getStore()->getId(),
            Mage_Catalog_Model_Product::CACHE_TAG
        ));
    }

    protected function _toHtml()
    {
        if($this->_orderId == null){
            $this->_logger->warn('Tracking script: _orderId not set');
            return;
        }

        $url = self::SCRIPT_ENDPOINT."order/".$this->_orderId;
        return "<script id='".self::SCRIPT_ID."' async type='text/javascript' src='".$url."'></script>";
    }

    /**
     * @param $orderId string
     */
    public function setOrderId($orderId){
        $this->_orderId = $orderId;
    }
}