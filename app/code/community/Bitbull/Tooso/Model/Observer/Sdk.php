<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
class Bitbull_Tooso_Model_Observer_Sdk extends Bitbull_Tooso_Model_Observer
{

    /**
     * Include javascript library
     */
    public function includeLibrary()
    {
        if(!Mage::helper('tooso')->isSdkEnabled() || !Mage::helper('tooso')->isTrackingEnabled()){
            return;
        }

        $parentBlock = Mage::helper('tooso/sdk')->getScriptContainerBlock();
        if($parentBlock){
            $blockLibrary = Mage::helper('tooso/sdk')->getLibraryBlock();
            $parentBlock->append($blockLibrary);
            $this->_logger->debug('Javascript SDK: added sdk library');
        }else{
            $this->_logger->warn('Cannot include library block, parent container not found');
        }
    }

    /**
     * Init javascript library
     */
    public function initLibrary()
    {
        if(!Mage::helper('tooso')->isSdkEnabled() || !Mage::helper('tooso')->isTrackingEnabled()){
            return;
        }

        $parentBlock = Mage::helper('tooso/sdk')->getInitScriptContainerBlock();
        if($parentBlock){
            $blockInitLibrary = Mage::helper('tooso/sdk')->getLibraryInitBlock();
            $parentBlock->append($blockInitLibrary);
            $this->_logger->debug('Javascript SDK: added SDK library init');
        }else{
            $this->_logger->warn('Cannot include library init block, parent container not found');
        }
    }

    /**
     * Include custom CSS configurations
     */
    public function addCustomCSS()
    {
        if(!Mage::helper('tooso/sdk')->isCustomCSSEnabled()){
            return;
        }

        $parentBlock = Mage::helper('tooso/sdk')->getScriptContainerBlock();
        if($parentBlock){
            $blockInitLibrary = Mage::helper('tooso/sdk')->getCustomCSSBlock();
            $parentBlock->append($blockInitLibrary);
            $this->_logger->debug('Javascript SDK: added custom CSS block');
        }else{
            $this->_logger->warn('Cannot include custom CSS block, parent container not found');
        }
    }


}
