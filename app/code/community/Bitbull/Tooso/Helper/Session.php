<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
class Bitbull_Tooso_Helper_Session
    extends Mage_Core_Helper_Abstract
    implements Bitbull_Tooso_Storage_SessionInterface
{

    /**
     * Store Search ID into session
     *
     * @param string $value
     */
    public function setSearchId($value)
    {
        Mage::getSingleton('core/session')->setToosoSearchId($value);
    }

    /**
     * Get Search ID from session
     *
     * @return string
     */
    public function getSearchId()
    {
        return Mage::getSingleton('core/session')->getToosoSearchId();
    }

    /**
     * Store Rank Collection into session
     *
     * @param string $value
     */
    public function setRankCollection($value)
    {
        Mage::getSingleton('core/session')->setToosoRankCollection($value);
    }

    /**
     * Get Rank Collection from session
     *
     * @return string
     */
    public function getRankCollection()
    {
        return Mage::getSingleton('core/session')->getToosoRankCollection();
    }

    /**
     * Store Search Order into session
     *
     * @param string $value
     */
    public function setSearchOrder($value)
    {
        Mage::getSingleton('core/session')->setToosoSearchOrder($value);
    }

    /**
     * Get Search Order from session
     *
     * @return string
     */
    public function getSearchOrder()
    {
        return Mage::getSingleton('core/session')->getToosoSearchOrder();
    }

}