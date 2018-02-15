<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
class Bitbull_Tooso_Model_Observer_Suggestion extends Bitbull_Tooso_Model_Observer
{
    /**
     * Include javascript library
     */
    public function initLibrary()
    {
        if(!Mage::helper('tooso')->isSuggestionEnabled()){
            return;
        }

        $parentBlock = Mage::helper('tooso/suggestion')->getInitScriptContainerBlock();
        if($parentBlock){
            $blockInitLibrary = Mage::helper('tooso/suggestion')->getSuggestionLibraryInitBlock();
            $parentBlock->append($blockInitLibrary);
            $this->_logger->debug('Suggestion frontend: added tracking library init');
        }else{
            $this->_logger->warn('Cannot include library block, parent container not found');
        }
    }

    /**
     * Include javascript library
     */
    public function includeLibrary()
    {
        if(!Mage::helper('tooso')->isSuggestionEnabled() || !Mage::helper('tooso/suggestion')->includeSuggestionJSLibrary()){
            return;
        }

        $parentBlock = Mage::helper('tooso/suggestion')->getScriptContainerBlock();
        if($parentBlock){
            $blockLibrary = Mage::helper('tooso/suggestion')->getSuggestionLibraryBlock();
            $parentBlock->append($blockLibrary);
            $this->_logger->debug('Suggestion frontend: added tracking library');
        }else{
            $this->_logger->warn('Cannot include library init block, parent container not found');
        }
    }




}
