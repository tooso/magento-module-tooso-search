<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_Tracking_Checkout extends Bitbull_Tooso_Block_Tracking
{
    const BLOCK_ID = 'tooso_tracking_checkout';
    const SCRIPT_ID = 'tooso-tracking-checkout';

    /**
     * @var null|integer
     */
    protected $_orderId = null;

    protected function _toHtml()
    {
        $trackingProductParams = [];

        if($this->_orderId == null){
            $this->_logger->warn('Tracking checkout: _orderId not set, getting from session');
            $idFromSession = Mage::getSingleton('checkout/session')->getLastRealOrderId();
            if($idFromSession == null){
                $this->_logger->warn('Tracking checkout: can\'t find order id in session');
                return;
            }
            $this->_orderId = $idFromSession;
        }

        $order = Mage::getSingleton('sales/order')->loadByIncrementId($this->_orderId);
        if($order->getId() == null){
            $this->_logger->warn('Tracking checkout: order not found with id '.$this->_orderId);
            return;
        }
        $trackingProductParams['id'] = $order->getId();
        $trackingProductParams['shipping'] = $order->getShippingAmount();
        $trackingProductParams['coupon'] = $order->getCouponCode();
        $trackingProductParams['tax'] = $order->getTaxAmount();

        ob_start();

        ?>
        <script id='<?=self::SCRIPT_ID?>' type='text/javascript'>
        <?php

        $items = $order->getAllVisibleItems();
        foreach ($items as $item) {
            $productId = $item->getProductId();
            $trackingProductParams = $this->_helper->getProductTrackingParams($productId);
            if($trackingProductParams == null){
                $this->_logger->warn('Tracking checkout: product not found with id '.$productId);
                continue;
            }
            $trackingProductParams['quantity'] = round($item->getQtyOrdered());
            $trackingProductParams['price'] = $item->getPrice();
            ?>
            ta('ec:addProduct', <?=json_encode($trackingProductParams);?>);
            <?php
        }

        ?>
            ta('ec:setAction', 'purchase', <?=json_encode($trackingProductParams);?>);
        </script>
        <?php

        return ob_get_clean();
    }

    /**
     * Get Cache Key Info
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $info = parent::getCacheKeyInfo();
        $info['object_id'] = $this->_orderId;
        return $info;
    }

    /**
     * @param $id
     */
    public function setObjectID($id){
        $this->setOrderId($id);
    }

    /**
     * @param $orderId string
     */
    public function setOrderId($orderId){
        $this->_orderId = $orderId;
    }
}