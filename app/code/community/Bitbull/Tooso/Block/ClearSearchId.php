<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_ClearSearchId extends Mage_Core_Block_Template
{
    const SCRIPT_ID = 'tooso-clear-searchid';

    /**
     * @var Bitbull_Tooso_Helper_Log
     */
    protected $_logger = null;

    /**
     * @var null|string
     */
    protected $_orderId = null;

    public function _construct()
    {
        parent::_construct();

        $this->_logger = Mage::helper('tooso/log');

        $this->setBlockId('tooso_clear_search_id');
        $this->addCacheTag(array(
            Mage::app()->getStore()->getId()
        ));
    }

    protected function _toHtml()
    {
        return "<script id='".self::SCRIPT_ID."' async type='text/javascript' >Mage.Cookies.set('ToosoSearchId', '', new Date(0))</script>";
    }

}