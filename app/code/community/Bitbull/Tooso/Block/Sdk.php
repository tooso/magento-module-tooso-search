<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_Sdk extends Mage_Core_Block_Template
{
    const BLOCK_ID = 'tooso_sdk';

    /**
     * @var Bitbull_Tooso_Helper_Log
     */
    protected $_logger = null;

    /**
     * @var Bitbull_Tooso_Helper_Sdk
     */
    protected $_helper = null;

    /**
     * @var Bitbull_Tooso_Helper_SpeechToText
     */
    protected $_helperSpeechToText = null;

    public function _construct(){
        parent::_construct();

        $this->_logger = Mage::helper('tooso/log');
        $this->_helper = Mage::helper('tooso/sdk');
        $this->_helperSpeechToText = Mage::helper('tooso/speechToText');
    }

    /**
     * Get cache identifier
     *
     * @return string
     */
    protected function _getCacheId()
    {
        return strtoupper(self::BLOCK_ID);
    }
}
