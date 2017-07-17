<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_TrackingPixel extends Mage_Core_Block_Template
{
    const BLOCK_ID = 'tooso_tracking_pixel_page';
    const SCRIPT_ID = 'tooso-tracking-script';
    const SCRIPT_ENDPOINT = '/tooso/tracking/';

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

        $this->setBlockId(self::BLOCK_ID);
        $this->addCacheTag(array(
            Mage::app()->getStore()->getId(),
            Mage_Catalog_Model_Product::CACHE_TAG
        ));
    }

    protected function _toHtml()
    {
        return;
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

    /**
     * @return string
     */
    protected function _getPageParams(){
        $url = "currentPage/".base64_encode($this->_currentPage);
        if($this->_lastPage != null){
            $url .= "/lastPage/".base64_encode($this->_lastPage);
        }
        return $url;
    }
}