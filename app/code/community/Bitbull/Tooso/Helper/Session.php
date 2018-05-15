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
        Mage::getSingleton('core/cookie')->set('ToosoSearchId', $value, null, null, null, null, false);
    }

    /**
     * Set last search page URL
     *
     * @param string $value
     */
    public function setLastSearchPage($value)
    {
        Mage::getSingleton('core/cookie')->set('ToosoLastSearchPage', $value, null, null, null, null, false);
    }

    /**
     * Get Search ID from session
     *
     * @return string
     */
    public function getSearchId()
    {
        return Mage::getSingleton('core/cookie')->get('ToosoSearchId');
    }

    /**
     * Get last search page URL
     *
     * @return string
     */
    public function getLastSearchPage()
    {
        return Mage::getSingleton('core/cookie')->get('ToosoLastSearchPage');
    }

    /**
     * Get Client ID from cookie
     *
     * @return string
     */
    public function getClientId()
    {
        $cid = Mage::getSingleton('core/cookie')->get('_ta');
        if($cid == null || $cid == ''){
            return "";
        }

        return substr($cid, -36);
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