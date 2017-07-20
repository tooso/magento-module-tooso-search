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
    const ARRAY_VALUES_SEPARATOR = ',';

    /**
     * @var null|string
     */
    protected $_orderId = null;


    protected function _toHtml()
    {
        if($this->_orderId == null){
            $this->_logger->warn('Tracking checkout: _orderId not set');
            return;
        }

        $order = Mage::getSingleton('sales/order')->loadByIncrementId($this->_orderId);
        if($order->getId() == null){
            $this->_logger->warn('Tracking checkout: order not found with id '.$this->_orderId);
            return;
        }

        $skus = array();
        $prices = array();
        $qtys = array();
        $items = $order->getAllItems();
        foreach ($items as $item) {
            array_push($skus, $item->getSku());
            array_push($prices, $item->getPrice());
            array_push($qtys, $item->getQtyOrdered());
        }

        $skusStr = implode(self::ARRAY_VALUES_SEPARATOR, $skus);
        $pricesStr = implode(self::ARRAY_VALUES_SEPARATOR, $prices);
        $qtysStr = implode(self::ARRAY_VALUES_SEPARATOR, $qtys);

        $url = self::SCRIPT_ENDPOINT."skus/$skusStr/prices/$pricesStr/qtys/$qtysStr".'/'.$this->_getPageParams();
        return "<script id='".self::SCRIPT_ID."' async type='text/javascript' src='".$url."'></script>";
    }

    /**
     * @param $orderId string
     */
    public function setOrderId($orderId){
        $this->_orderId = $orderId;
    }
}