<?php
/**
 * @package Bitbull_Tooso
 * @author Gennaro Vietri <gennaro.vietri@bitbull.it>
 */
class Bitbull_Tooso_Model_Search
{
    /**
     * Represents a Tooso response.
     */
    protected $_response;
    
    /**
     * Constructor, retrieve config for connection to Tooso API.
     */
    public function __construct()
    {
        $_host = Mage::getStoreConfig('tooso/server/host');
        $_port = Mage::getStoreConfig('tooso/server/port');
        $_path = Mage::getStoreConfig('tooso/server/path');
        
        parent::__construct($_host,$_port,$_path);
    }
    
    /**
     * Load query and set response
     * 
     * @param string $query
     * @param int $storeId
     * @param int $limit
     * @return Bitbull_Tooso_Model_Search
     */
    public function loadQuery($query, $storeId = 0, $limit = 10)
    {
        $query = preg_quote($query); // Quote regular expression characters . \ + * ? [ ^ ] $ ( ) { } = ! < > | : -
        if(!$this->_response && $query) {
            $params = array(
                'fl' => 'product_id,score',
                'fq' => 'store_id:'.$storeId,
            );
            $response = $this->search($query,0,$limit,$params,'POST');
            $this->setResponse($response->response);
        }
        
        return $this;
    }
    
    /**
     * Delete All documents
     *
     * @return Bitbull_Tooso_Model_Search
     * @todo implement me
     */
    public function deleteAllDocuments()
    {
        return $this;
    }
    
    /**
     * Delete specific document
     * 
     * @param int $productId
     * @return Bitbull_Tooso_Model_Search
     * @todo implement me
     */
    public function deleteDocument($productId)
    {
        return $this;
    }
    
    /**
     * Delete specifics document
     * 
     * @param array $productIds
     * @return Bitbull_Tooso_Model_Search
     */
    public function deleteDocuments(array $productIds)
    {
        foreach($productIds as $id) {
            $this->deleteDocument($id);
        }
        
        return $this;
    }
    
    /**
     * Set Tooso response
     * 
     * @param $response
     */
    public function setResponse($response)
    {
        $this->_response = $response;
    }
    
    /**
     * Extract product ids and score in Tooso response
     * 
     * @return array $ids
     */
    public function getProducts()
    {
        $products = array();
        foreach($this->_response->docs as $doc) {
            $products[] = array('product_id' => $doc->product_id,
                                'relevance'  => $doc->score);
        }
        return $products;
    }
    
    /**
     * Retreive documents count
     * 
     * @return int
     */
    public function count()
    {
        return count($this->_response->docs);
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
}