<?php
/**
 * @package Bitbull_Tooso
 * @author Gennaro Vietri <gennaro.vietri@bitbull.it>
 */

class Bitbull_Tooso_Model_Indexer
{
    /**
     * @var Bitbull_Tooso_Helper_Log
     */
    protected $_logger = null;

    public function __construct()
    {
        $this->_logger = Mage::helper('tooso/log');
    }

    /**
    * Rebuild Tooso Index
    * 
    * @param int|array|null $productIds
    * @return boolean
    */
    public function rebuildIndex($productIds = null)
    {
        $products = $this->_getConnection()->query($this->_buildQuery($productIds));
        
        $documents = array();
        
        while($product = $products->fetch()) {
            $document = Mage::getModel('tooso/document');

            $document->addField('id',$product['fulltext_id']);
            $document->addField('product_id',$product['product_id']);
            $document->addField('store_id',$product['store_id']);
            $document->addField('fulltext',$product['data_index']);
            
            $documents[] = $document;
        }
        
        try {
            $search = Mage::getModel('tooso/search');

            if(count($documents)) { $search->addDocuments($documents); }
            
            $search->commit();
            $search->optimize();
        } catch (Exception $e) {
            $this->_logger->logException($e);

            return false;
        }

        return true;
    }
    
    /**
    * Clean Tooso Index
    * 
    * @param int|array|null $productIds
    * @return boolean
    */
    public function cleanIndex($productIds = null)
    {
        try {
            $search = Mage::getModel('tooso/search');
            
            if (is_numeric($productIds)) {
                $search->deleteDocument($productIds);
            } else if (is_array($productIds)) {
                $search->deleteDocuments($productIds);
            } else {
                $search->deleteAllDocuments();
            }

            $search->commit();
            $search->optimize();
        } catch (Exception $e) {
            $this->_logger->logException($e);

            return false;
        }

        return true;
    }
    
    /**
     * Build Query
     * 
     * @param int|array|null $productIds
     * @return string
     */
    public function _buildQuery($productIds = null)
    {
        $query = 'SELECT * FROM '.$this->_getTable('catalogsearch/fulltext');

        $where = '';
        
        if($productIds) {
            if(is_numeric($productIds)) {
                $where .= ' WHERE product_id = '.$productIds;
            }
            if(is_array($productIds)) {
                $where .= ' WHERE product_id IN('.implode(',',$productIds).')';
            }
        }
        
        $query .= $where;
        
        return $query;
    }
    
    /**
    * Retrieve resource
    * 
    * @return Mage_Core_Model_Resource
    */
    public function _getResource()
    {
        return Mage::getSingleton('core/resource');
    }
    
    /**
    * Retrieve connection
    * 
    * @return Varien_Db_Adapter_Pdo_Mysql
    */
    public function _getConnection()
    {
        return $this->_getResource()->getConnection('core_read');
    }
    
    /**
     * Retrieve table name
     *
     * @param string $tableName
     * @return string
    */
    public function _getTable($tableName)
    {
        return $this->_getResource()->getTableName($tableName);
    }
}