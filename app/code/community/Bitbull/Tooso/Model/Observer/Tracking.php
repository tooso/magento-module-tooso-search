<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
* @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
class Bitbull_Tooso_Model_Observer_Tracking extends Bitbull_Tooso_Model_Observer
{
    /**
     * Add product tracking script that point to relative controller action endpoint
     * @param  Varien_Event_Observer $observer
     */
    public function includeProductTrackingScript(Varien_Event_Observer $observer){
        if(!Mage::helper('tooso')->isTrackingEnabled()){
            return;
        }
        $currentProduct = Mage::registry('current_product');
        if($currentProduct != null) {

            $block = Mage::helper('tooso/tracking')->getProductTrackingPixelBlock($currentProduct->getId());
            $parentBlock = Mage::helper('tooso/tracking')->getScriptContainerBlock();
            if($parentBlock){
                $parentBlock->append($block);
                $this->_logger->debug('Tracking product: added tracking script');
            }else{
                $this->_logger->warn('Cannot add ProductTrackingPixel block, parent container not found');
            }

        }else{
            $this->_logger->warn('Tracking product: product not found in request');
        }
    }

    /**
     * Add page tracking script that point to relative controller action endpoint
     * @param  Varien_Event_Observer $observer
     */
    public function includePageTrackingScript(Varien_Event_Observer $observer){
        if(!Mage::helper('tooso')->isTrackingEnabled()){
            return;
        }
        $block = Mage::helper('tooso/tracking')->getPageTrackingPixelBlock();
        $parentBlock = Mage::helper('tooso/tracking')->getScriptContainerBlock();
        if($parentBlock){
            $parentBlock->append($block);
            $this->_logger->debug('Tracking page view: added tracking script');
        }else{
            $this->_logger->warn('Cannot add PageTrackingPixel block, parent container not found');
        }
    }

    /**
     * Track checkout event
     * @param Varien_Event_Observer $observer
     */
    public function trackCheckout(Varien_Event_Observer $observer)
    {
        if(!Mage::helper('tooso')->isTrackingEnabled()){
            return;
        }

        $orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        if($orderId != null){
            $block = Mage::helper('tooso/tracking')->getCheckoutTrackingPixelBlock($orderId);
            $parentBlock = Mage::helper('tooso/tracking')->getScriptContainerBlock();
            if($parentBlock){
                $parentBlock->append($block);
                $this->_logger->debug('Tracking checkout: added tracking script');
            }else{
                $this->_logger->warn('Cannot add CheckoutTrackingPixel block, parent container not found');
            }
        }else{
            $this->_logger->warn('Tracking checkout: can\'t find order id in session');
        }
    }

    /**
     * Track add to cart event
     * not using tracking script to track also ajax 'add to cart' call
     * @param Varien_Event_Observer $observer
     */
    public function trackAddToCart(Varien_Event_Observer $observer)
    {
        if(!Mage::helper('tooso')->isTrackingEnabled()){
            return;
        }

        $product = $observer->getEvent()->getProduct();
        if($product != null){
            $sku = $product->getSku();
            $profilingParams = Mage::helper('tooso')->getProfilingParams();
            $this->_client->productAddedToCart($sku, $profilingParams);
            $this->_logger->debug('Tracking cart: tracked '.$sku);
        }else{
            $this->_logger->warn('Tracking cart: product param not found');
        }
    }

}
