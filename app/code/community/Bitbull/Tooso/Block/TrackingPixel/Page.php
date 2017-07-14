<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_TrackingPixel_Page extends Mage_Core_Block_Template
{
    const SCRIPT_ID = 'tooso-tracking-page';
    const SCRIPT_ENDPOINT = '/tooso/tracking/page/';

    /**
     * @var Bitbull_Tooso_Helper_Log
     */
    protected $_logger = null;

    /**
     * @var null|string
     */
    protected $_currentPage = null;

    /**
     * @var null|string
     */
    protected $_lastPage = null;

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
        if($this->_currentPage == null){
            $this->_logger->warn('Tracking script: _currentPage not set');
            return;
        }

        $url = self::SCRIPT_ENDPOINT."?current=".urlencode($this->_currentPage);
        if($this->_lastPage != null){
            $url .= "&last=".urlencode($this->_lastPage);
        }

        $this->_logger->debug('Tracking script: added tracking script');
        return "<script id='".self::SCRIPT_ID."' async type='text/javascript' src='".$url."'></script>";
    }

    /**
     * @param $url string
     */
    public function setCurrentPage($url){
        $this->_currentPage = $url;
    }

    /**
     * @param $url string
     */
    public function setLastPage($url){
        $this->_lastPage = $url;
    }
}