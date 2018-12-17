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

    /**
     * Constructor
     */
    public function _construct(){
        parent::_construct();

        $this->addData([
            'cache_lifetime' => null,
            'esi_options' => [
                'access' => 'private',
                'ttl' => 0
            ]
        ]);
    }

    protected function _toHtml()
    {
        $trackingCheckoutParams = [];

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
        $trackingCheckoutParams['id'] = $order->getId();
        $trackingCheckoutParams['shipping'] = $order->getShippingAmount();
        $trackingCheckoutParams['coupon'] = $order->getCouponCode();
        $trackingCheckoutParams['tax'] = $order->getTaxAmount();
        $trackingCheckoutParams['revenue'] = $order->getGrandTotal();

        $items = $order->getAllVisibleItems();
        $trackingProductParams = [];

        foreach ($items as $item) {
            $productId = $item->getProductId();
            $productData = $this->_helper->getProductTrackingParams($productId);
            if ($productData == null) {
                $this->_logger->warn('Tracking checkout: product not found with id ' . $productId);
                continue;
            }
            $productData['quantity'] = round($item->getQtyOrdered());
            $productData['price'] = $item->getPrice();
            array_push($trackingProductParams, $productData);
        }

        ob_start();

        if ($this->_helper->includeTrackingJSLibrary()) {

            ?>
            <script id='<?= self::SCRIPT_ID ?>' type='text/javascript'>
                <?php foreach ($trackingProductParams as $productData) { ?>
                    ta('ec:addProduct', <?=json_encode($productData);?>);
                <?php } ?>
                ta('ec:setAction', 'purchase', <?=json_encode($trackingCheckoutParams);?>);
            </script>
            <?php

        }else{

            ?>
            <script id='<?= self::SCRIPT_ID ?>' type='text/javascript'>
                window.ToosoTrackingData = {
                  "products": <?=json_encode($trackingProductParams);?>,
                  "checkout": <?=json_encode($trackingCheckoutParams);?>,
                  "action": 'purchase',
                };
            </script>
            <?php
        }

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