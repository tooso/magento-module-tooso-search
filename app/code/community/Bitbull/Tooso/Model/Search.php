<?php
/**
 * @package Bitbull_Tooso
 * @author Gennaro Vietri <gennaro.vietri@bitbull.it>
 */
class Bitbull_Tooso_Model_Search
{
    const SEARCH_PARAM_PARENT_SEARCH_ID = 'parentSearchId';

    /**
     * Client for API comunication
     *
     * @var Bitbull_Tooso_Client
    */
    protected $_client;

    /**
     * Represents a Tooso search result.
     *
     * @var Bitbull_Tooso_Search_Result
     */
    protected $_result;

    /**
     * Read connection
     *
     * @var Varien_Db_Adapter_Pdo_Mysql
     */
    protected $_readAdapter;

    /**
     * @var Bitbull_Tooso_Helper_Log
     */
    protected $_logger = null;

    /**
     * @var Bitbull_Tooso_Helper_Search
     */
    protected $_helper = null;

    /**
     * Constructor, retrieve config for connection to Tooso API.
     */
    public function __construct()
    {
        $this->_client = Mage::helper('tooso')->getClient();

        $this->_logger = Mage::helper('tooso/log');
        $this->_helper = Mage::helper('tooso/search');
    }
    
    /**
     * Load query and set response
     * 
     * @param string $query
     * @param boolean $typoCorrection
     * @param string $parentSearchId
     * @return Bitbull_Tooso_Model_Search
     */
    public function search($query, $typoCorrection = true, $parentSearchId = null)
    {
        $query = urldecode($query);
        if ($query) {
            try {
                $params = Mage::helper('tooso')->getProfilingParams();

                if (!is_null($parentSearchId)) {
                    $params[self::SEARCH_PARAM_PARENT_SEARCH_ID] = $parentSearchId;
                }

                $result = $this->_client->search($query, $typoCorrection, $params, $this->_helper->isSearchEnriched());

                if($result->isValid()){
                    $this->setResult($result);
                }

            } catch (Exception $e) {
                $this->_logger->logException($e);
            }
        }
        
        return $this;
    }
    
    /**
     * Set Tooso response
     *
     * @param Bitbull_Tooso_Search_Result $result
     */
    public function setResult(Bitbull_Tooso_Search_Result $result)
    {
        $this->_result = $result;
        $this->_helper->storeResponse($result);

    }
    
    /**
     * Extract product ids and score from Tooso response
     * 
     * @return array
     */
    public function getProducts()
    {
        $products = array();

        if (!is_null($this->_result)) {

            $skus = [];
            if($this->_helper->isSearchEnriched()){
                $resultProducts = $this->_result->getResults();
                foreach ($resultProducts as $product) {
                    if(!is_object($product)){
                        $skus = $this->_result->getResults();
                        break;
                    }
                    array_push($skus, $product->sku);
                }
            }else{
                $skus = $this->_result->getResults();
            }

            $i = 1;
            $productIds = $this->_getIdsBySkus($skus);

            foreach ($skus as $sku) {
                if (isset($productIds[$sku])) {
                    $products[] = array(
                        'sku' => $sku,
                        'product_id' => $productIds[$sku],
                        'relevance' => $i
                    );
                }

                $i++;
            }
        }

        return $products;
    }

    public function getFixedSearchString()
    {
        return (!is_null($this->_result) ? $this->_result->getFixedSearchString() : null);
    }

    public function getOriginalSearchString()
    {
        return (!is_null($this->_result) ? $this->_result->getOriginalSearchString() : null);
    }

    public function getParentSearchId()
    {
        return (!is_null($this->_result) ? $this->_result->getParentSearchId() : null);
    }

    public function getSearchId()
    {
        return (!is_null($this->_result) ? $this->_result->getSearchId() : null);
    }

    /**
     * Retreive documents count
     * 
     * @return int
     */
    public function count()
    {
        return (!is_null($this->_result) ? $this->_result->getTotalResults() : 0);
    }

    /**
     * Is search available?
     *
     * @return boolean
     */
    public function isSearchAvailable()
    {
        return !is_null($this->_result) && is_null($this->_result->getRedirect());
    }

    /**
     * Retrieve Store Id
     * 
     * @return int
     */
    public function getStoreId()
    {
        return Mage::app()->getStore()->getId();
    }

    /**
     * Get products identifiers by skus
     *
     * @param array $skus
     * @return array
     */
    protected function _getIdsBySkus($skus)
    {
        $adapter = $this->_getReadAdapter();

        $skusCount = count($skus);

        if ($skusCount == 0) return array();

        $where = 'sku IN (';
        $bind = array();

        // Build the where clause with all the required placeholder for binding
        for ($i=0; $i<$skusCount; $i++) {
            $bind[':sku' . $i] = $skus[$i];
        }

        $where .= implode(',', array_keys($bind)) . ')';

        $select = $adapter->select()
            ->from(Mage::getResourceSingleton('catalog/product')->getEntityTable(), array('sku', 'entity_id'))
            ->where($where);

        return $adapter->fetchPairs($select, $bind);
    }

    /**
     * Retrieve connection for read data
     *
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getReadAdapter()
    {
        if (is_null($this->_readAdapter)) {
            $this->_readAdapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        }

        return $this->_readAdapter;
    }

    /**
     * Return true if results length is equal to 0
     *
     * @return bool
     */
    public function isResultEmpty(){
        if (!is_null($this->_result)) {
            $products = $this->_result->getResults();
            return sizeof($products) <= 0;
        }else{
            return false;
        }
    }

    /**
     * Get redirect
     *
     * @return null|string
     */
    public function getRedirect()
    {
        if(!is_null($this->_result)){
            return $this->_result->getRedirect();
        }

        return null;
    }

    /**
     * Return similar result alert
     *
     * @return bool
     */
    public function getSimilarResultsAlert(){
        if(!is_null($this->_result)){
            $additionalData = $this->_result->getSimilarResultsAlert();
            return $additionalData;
        }

        return null;
    }
}