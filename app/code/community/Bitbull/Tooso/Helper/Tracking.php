<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
class Bitbull_Tooso_Helper_Tracking extends Mage_Core_Helper_Abstract
{

    /**
     * Create Product TrackingPixel Block
     *
     * @param $product_id
     * @return Bitbull_Tooso_Block_TrackingPixel
     */
    public function getProductTrackingPixelBlock($product_id){
        $layout = Mage::app()->getLayout();
        $block = $layout->createBlock('tooso/trackingPixel_product');
        $block->setProductID($product_id);
        return $block;
    }

    /**
     * Create Page TrackingPixel Block
     *
     * @param $product_id
     * @return Bitbull_Tooso_Block_TrackingPixel
     */
    public function getPageTrackingPixelBlock($page_id){
        $layout = Mage::app()->getLayout();
        $block = $layout->createBlock('tooso/trackingPixel_page');
        $block->setCurrentPage($this->getCurrentPage());
        $block->setLastPage($this->getLastPage());
        return $block;
    }

    /**
     * Check if user is coming from a search page or not
     *
     * @return boolean
     */
    public function isUserComingFromSearch(){
        $sessionId = Mage::helper('tooso/session')->getSearchId();
        return $sessionId != null;
    }

    /**
     * Detect if request comes from mobile device
     *
     * @return integer
     */
    public function isMobile(){
        $detect = new Bitbull_Mobile_Detect();
        return (int) $detect->isMobile();
    }

    /**
     * Get client remote address, if server is behind proxy use forwarded http
     *
     * @return string
     */
    public function getRemoteAddr(){
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[count($ips) - 1]);
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    /**
     * Get last page visited
     */
    public function getLastPage(){
        return Mage::app()->getRequest()->getServer('HTTP_REFERER');
    }

    /**
     * Get current page
     */
    public function getCurrentPage(){
        return Mage::helper('core/url')->getCurrentUrl();
    }

}