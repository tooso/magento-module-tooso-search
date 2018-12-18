<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_Tracking extends Mage_Core_Block_Template
{
    const BLOCK_ID = 'tooso_tracking';

    /**
     * @var Bitbull_Tooso_Helper_Log
     */
    protected $_logger = null;

    /**
     * @var Bitbull_Tooso_Helper_Tracking
     */
    protected $_helper = null;

    public function _construct(){
        parent::_construct();

        $this->_logger = Mage::helper('tooso/log');
        $this->_helper = Mage::helper('tooso/tracking');

        $this->setBlockId(self::BLOCK_ID);
        $this->addCacheTag([
            Mage::app()->getStore()->getId(),
            Mage_Catalog_Model_Product::CACHE_TAG
        ]);
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

    /**
     * Check if template is turpentine esi
     *
     * @return bool
     */
    protected function isTurpentineTemplateSet()
    {
        return $this->_template === 'turpentine/esi.phtml';
    }
}