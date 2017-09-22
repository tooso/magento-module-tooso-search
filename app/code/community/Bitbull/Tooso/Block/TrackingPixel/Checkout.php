<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_TrackingPixel_Checkout extends Mage_Core_Block_Template
{
    const SCRIPT_ID = 'tooso-tracking-checkout';
    const SCRIPT_ENDPOINT = 'tooso/tracking/checkout/';

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
        $this->addData(array(
            'cache_lifetime' => null,
        ));
    }

    protected function _toHtml()
    {
        if($this->_orderId == null){
            $this->_logger->warn('Tracking checkout: _orderId not set, getting from session');
            $idFromSession = Mage::getSingleton('checkout/session')->getLastRealOrderId();
            if($idFromSession == null){
                $this->_logger->warn('Tracking checkout: can\'t find order id in session');
                return;
            }
            $this->_orderId = $idFromSession;
        }

        $url =  Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB,true).self::SCRIPT_ENDPOINT."order/".$this->_orderId;
        return "<script id='".self::SCRIPT_ID."' async type='text/javascript' src='".$url."'></script>";
    }

    /**
     * Get cache identifier
     *
     * @return string
     */
    protected function _getCacheId()
    {
        return strtoupper(self::BLOCK_ID);
    }

    /**
     * @param $orderId string
     */
    public function setOrderId($orderId){
        $this->_orderId = $orderId;
    }

    /**
     * @param $id
     */
    public function setObjectID($id){

    }
}
