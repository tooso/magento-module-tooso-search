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
        return "
            <!-- Tooso tracking pixel -->
            <script type='text/javascript'>
                var trackingScript = document.createElement('script');
                trackingScript.type = 'text/javascript';
                trackingScript.src = '$tracking_url';
                document.getElementsByTagName('body')[0].appendChild(trackingScript);
            </script>
            <noscript>
                <img id='tooso-tracking-pixel' style='display:none' src='$tracking_url'>
            </noscript>
        ";
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

}