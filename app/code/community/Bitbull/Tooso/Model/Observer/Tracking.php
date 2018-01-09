<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
* @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
class Bitbull_Tooso_Model_Observer_Tracking extends Bitbull_Tooso_Model_Observer
{
    /**
     * Include javascript library
     */
    public function includeLibrary()
    {
        if(!Mage::helper('tooso')->isTrackingEnabled() || !Mage::helper('tooso/tracking')->includeTrackingJSLibrary()){
            return;
        }

        $parentBlock = Mage::helper('tooso/tracking')->getScriptContainerBlock();
        if($parentBlock){
            $blockLibrary = Mage::helper('tooso/tracking')->getTrackingLibraryBlock();
            $parentBlock->append($blockLibrary);
            $blockInit = Mage::helper('tooso/tracking')->getTrackingLibraryInitBlock();
            $parentBlock->append($blockInit);
            $this->_logger->debug('Tracking: added tracking library');
        }else{
            $this->_logger->warn('Cannot include library block, parent container not found');
        }
    }

    /**
     * Add page tracking script
     * @param  Varien_Event_Observer $observer
     */
    public function includePageTrackingScript(Varien_Event_Observer $observer){
        if(!Mage::helper('tooso')->isTrackingEnabled()){
            return;
        }
        $parentBlock = Mage::helper('tooso/tracking')->getScriptContainerBlock();
        if($parentBlock){
            $block = Mage::helper('tooso/tracking')->getPageTrackingBlock();
            $parentBlock->append($block);
            $this->_logger->debug('Tracking page view: added tracking script');
        }else{
            $this->_logger->warn('Cannot add PageTracking block, parent container not found');
        }
    }

    /**
     * Add product tracking script
     * @param  Varien_Event_Observer $observer
     */
    public function includeProductTrackingScript(Varien_Event_Observer $observer){
        if(!Mage::helper('tooso')->isTrackingEnabled()){
            return;
        }
        $currentProduct = Mage::registry('current_product');
        if($currentProduct != null) {

            $parentBlock = Mage::helper('tooso/tracking')->getScriptContainerBlock();
            if($parentBlock){
                $block = Mage::helper('tooso/tracking')->getProductTrackingBlock($currentProduct->getId());
                $parentBlock->append($block);
                $this->_logger->debug('Tracking product: added tracking script');
            }else{
                $this->_logger->warn('Cannot add ProductTracking block, parent container not found');
            }

        }else{
            $this->_logger->warn('Tracking product: product not found in request');
        }
    }

    /**
     * Add track checkout script
     * @param Varien_Event_Observer $observer
     */
    public function includeCheckoutTrackingScript(Varien_Event_Observer $observer)
    {
        if(!Mage::helper('tooso')->isTrackingEnabled()){
            return;
        }

        $orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        if($orderId != null){
            $parentBlock = Mage::helper('tooso/tracking')->getScriptContainerBlock();
            if($parentBlock){
                $block = Mage::helper('tooso/tracking')->getCheckoutTrackingBlock($orderId);
                $parentBlock->append($block);
                $this->_logger->debug('Tracking checkout: added tracking script');
            }else{
                $this->_logger->warn('Cannot add CheckoutTracking block, parent container not found');
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
