<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_Tracking_Library extends Bitbull_Tooso_Block_Tracking
{
    const BLOCK_ID = 'tooso_tracking_library';
    const SCRIPT_ID = 'tooso-tracking-library';

    protected function _toHtml()
    {
        $endpoint = Mage::helper('tooso/tracking')->getTrackingLibraryEndpoint();
        $this->_logger->debug('including library from '.$endpoint);
        return "<script id='".self::SCRIPT_ID."' async type='text/javascript' src='".$endpoint."'></script>";
    }
}