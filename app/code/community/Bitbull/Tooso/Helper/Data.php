<?php
/**
 * @package Bitbull_Tooso
 * @author Gennaro Vietri <gennaro.vietri@bitbull.it>
 */

class Bitbull_Tooso_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_fixedSearchString = null;
    
    protected $_searchId = null;

    protected $_products = null;

    /**
     * @return array
     */
    public function getProducts()
    {
        return $this->_products;
    }

    /**
     * @param array $products
     */
    public function setProducts($products)
    {
        $this->_products = $products;
    }

    /**
     * @param string $fixedSearchString
     */
    public function setFixedSearchString($fixedSearchString)
    {
        $this->_fixedSearchString = $fixedSearchString;
    }

    /**
     * @return string
     */
    public function getFixedSearchString()
    {
        return $this->_fixedSearchString;
    }

    /**
     * @param string $searchId
     */
    public function setSearchId($searchId)
    {
        $this->_searchId = $searchId;
    }

    /**
     * @return string
     */
    public function getSearchId()
    {
        return $this->_searchId;
    }

    public function isTypoCorrectedSearch()
    {
        return Mage::app()->getRequest()->getParam('typoCorrection', 'true') == 'true';
    }
    
    /**
     * Create and configure a Tooso API Client instance
     * 
     * @return Bitbull_Tooso_Client
    */
    public function getClient()
    {
        $apiKey = Mage::getStoreConfig('tooso/server/api_key');
        $language = 'en'; // @todo make configurable

        $client = new Bitbull_Tooso_Client($apiKey, $language);

        $client->setReportSender(Mage::helper('tooso/log_send'));

        return $client;
    }
    
    /**
     * @return array
    */
    public function getProfilingParams()
    {
        $customerSession = Mage::getSingleton('customer/session');
        $sessionId = Mage::getSingleton('core/session')->getSessionId();

        if ($customerSession->isLoggedIn()) {
            $userId = $customerSession->getCustomerId();
        } else {
            $userId = $sessionId;
        }

        return array(
            'userId' => $userId,
            'sessionId' => $sessionId,
        );
    }
}