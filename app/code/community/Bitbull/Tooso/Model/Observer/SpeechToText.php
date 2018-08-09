<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
class Bitbull_Tooso_Model_Observer_SpeechToText extends Bitbull_Tooso_Model_Observer
{

    /**
     * Include javascript library
     */
    public function includeLibrary()
    {
        if(!Mage::helper('tooso')->isSpeechToTextEnabled()){
            return;
        }

        $parentBlock = Mage::helper('tooso/speechToText')->getScriptContainerBlock();
        if($parentBlock){
            $blockLibrary = Mage::helper('tooso/speechToText')->getLibraryBlock();
            $parentBlock->append($blockLibrary);
            $this->_logger->debug('SpeechToText frontend: added speech to text library');
        }else{
            $this->_logger->warn('Cannot include library init block, parent container not found');
        }
    }

    /**
     * Include javascript library
     */
    public function initLibrary()
    {
        if(!Mage::helper('tooso')->isSpeechToTextEnabled()){
            return;
        }

        $parentBlock = Mage::helper('tooso/speechToText')->getInitScriptContainerBlock();
        if($parentBlock){
            $blockInitLibrary = Mage::helper('tooso/speechToText')->getLibraryInitBlock();
            $parentBlock->append($blockInitLibrary);
            $this->_logger->debug('SpeechToText frontend: added speech to text library init');
        }else{
            $this->_logger->warn('Cannot include library block, parent container not found');
        }
    }


}
