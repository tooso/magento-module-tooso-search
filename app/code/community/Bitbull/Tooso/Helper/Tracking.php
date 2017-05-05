<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
class Bitbull_Tooso_Helper_Tracking extends Mage_Core_Helper_Abstract
{

    /**
     * Get Tracking Pixel
     *
     * @param string $tracking_url
     * @return strin
     */
    public function getTrackingImageHTML($tracking_url)
    {
        return '<img id="tooso-tracking-pixel" style="height: 1;width: 1;position: fixed;left: -99999px;" src="'.$tracking_url.'"></img>';
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
        $detect = new Mobile_Detect();
        return (int) $detect->isMobile();
    }

}