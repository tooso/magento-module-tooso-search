<?php
/**
 * @package Bitbull_Tooso
 * @author Gennaro Vietri <gennaro.vietri@bitbull.it>
 */

class Bitbull_Tooso_Model_CatalogSearch_Resource_Fulltext extends Mage_CatalogSearch_Model_Resource_Fulltext
{
    /**
     * @var Bitbull_Tooso_Helper_Log
    */
    protected $_logger = null;

    public function _construct()
    {
        parent::_construct();

        $this->_logger = Mage::helper('tooso/log');
    }

    /**
     * Overloaded method prepareResult.
     * Prepare results for query.
     * Replaces the traditional fulltext search with a Tooso search (if active).
     *
     * @param Mage_CatalogSearch_Model_Fulltext $object
     * @param string $queryText
     * @param Mage_CatalogSearch_Model_Query $query
     * @return Bitbull_Tooso_Model_CatalogSearch_Resource_Fulltext
     */
    public function prepareResult($object, $queryText, $query)
    {
        if (!Mage::getStoreConfigFlag('tooso/active/frontend')) {
            return parent::prepareResult($object, $queryText, $query);
        }
        
        $adapter = $this->_getWriteAdapter();
        if (!$query->getIsProcessed()) {
            
            try {
                $search = Mage::getModel('tooso/search')->search($queryText, (int)$query->getStoreId());

                if ($search->isSearchAvailable()) {
                    if ($search->count()) {
                        $products = $search->getProducts();

                        $data = array();
                        foreach ($products as $product) {
                            $data[] = array(
                                'query_id'   => $query->getId(),
                                'product_id' => $product['product_id'],
                                'relevance'  => $product['relevance']
                            );
                        }

                        $adapter->insertMultiple($this->getTable('catalogsearch/result'), $data);
                    }

                    $query->setIsProcessed(1);
                } else {
                    parent::prepareResult($object, $queryText, $query);
                }

            } catch (Exception $e) {
                $this->_logger->logException($e);

                parent::prepareResult($object, $queryText, $query);
            }
            
        }

        return $this;
    }
    
    /**
     * Overloaded method rebuildIndex.
     * Regenerate search index for store(s)
     *
     * @param  int|null $storeId
     * @param  int|array|null $productIds
     * @return Bitbull_Tooso_Model_CatalogSearch_Resource_Fulltext
     */
    public function rebuildIndex($storeId = null, $productIds = null)
    {
        parent::rebuildIndex($storeId,$productIds);

        if (Mage::getStoreConfigFlag('tooso/active/admin')) {
            Mage::getModel('tooso/indexer')->rebuildIndex($productIds);
        }

        return $this;
    }
    
    /**
     * Overloaded method cleanIndex.
     * Delete search index data for store
     *
     * @param int $storeId Store View Id
     * @param int|array|null $productIds Product Entity Id
     * @return Mage_CatalogSearch_Model_Resource_Fulltext
     */
    public function cleanIndex($storeId = null, $productIds = null)
    {
        parent::cleanIndex($storeId, $productIds);
        
        if (Mage::getStoreConfigFlag('tooso/active/admin')) {
            Mage::getModel('tooso/indexer')->cleanIndex($productIds);
        }

        return $this;
    }
}