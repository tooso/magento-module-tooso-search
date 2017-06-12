<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_TrackingPixel extends Mage_Core_Block_Template
{
    const SCRIPT_ENDPOINT = 'tooso/tracking/pixel/';

    /**
     * @var Bitbull_Tooso_Helper_Log
     */
    protected $_logger = null;

    /**
     * @var null|integer
     */
    protected $_product_id = null;

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
        if($this->_product_id == null){
            $this->_logger->warn('Tracking script: product_id not set');
            return;
        }
        $url = Mage::getBaseUrl().self::SCRIPT_ENDPOINT."product/".$this->_product_id;
        $this->_logger->debug('Tracking script: added tracking script');
        return "<script async type='text/javascript' src='".$url."'></script>";
    }

    public function setProductID($id){
        $this->_product_id = $id;
    }
}