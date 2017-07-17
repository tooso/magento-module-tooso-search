<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_TrackingPixel_Checkout extends Bitbull_Tooso_Block_TrackingPixel
{
    const BLOCK_ID = 'tooso_tracking_pixel_checkout';
    const SCRIPT_ID = 'tooso-tracking-checkout';
    const SCRIPT_ENDPOINT = '/tooso/tracking/checkout/';

    /**
     * @var null|string
     */
    protected $_orderId = null;


    protected function _toHtml()
    {
        if($this->_orderId == null){
            $this->_logger->warn('Tracking script: _orderId not set');
            return;
        }

        $url = self::SCRIPT_ENDPOINT."order/".$this->_orderId.'/'.$this->_getPageParams();
        return "<script id='".self::SCRIPT_ID."' async type='text/javascript' src='".$url."'></script>";
    }

    /**
     * @param $orderId string
     */
    public function setOrderId($orderId){
        $this->_orderId = $orderId;
    }
}