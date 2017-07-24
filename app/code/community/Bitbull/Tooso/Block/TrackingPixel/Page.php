<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_TrackingPixel_Page extends Bitbull_Tooso_Block_TrackingPixel
{
    const BLOCK_ID = 'tooso_tracking_pixel_page';
    const SCRIPT_ID = 'tooso-tracking-page';
    const SCRIPT_ENDPOINT = 'tooso/tracking/page/';

    protected function _toHtml()
    {
        if($this->_currentPage == null){
            $this->_logger->warn('Tracking page view: _currentPage not set');
            return;
        }

        $url = Mage::getBaseUrl().self::SCRIPT_ENDPOINT.$this->_getPageParams();
        return "<script id='".self::SCRIPT_ID."' async type='text/javascript' src='".$url."'></script>";
    }

}