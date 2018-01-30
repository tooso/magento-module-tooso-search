<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_Suggestion extends Mage_Core_Block_Template
{
    const BLOCK_ID = 'tooso_suggestion';

    /**
     * @var Bitbull_Tooso_Helper_Log
     */
    protected $_logger = null;

    /**
     * @var Bitbull_Tooso_Helper_Suggestion
     */
    protected $_helper = null;

    public function _construct(){
        parent::_construct();

        $this->_logger = Mage::helper('tooso/log');
        $this->_helper = Mage::helper('tooso/suggestion');
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