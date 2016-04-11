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

        // query ancora non processata oppure già processata ma typoCorrection = 1

        if (!$query->getIsProcessed()) {
            
            try {
                $search = Mage::getModel('tooso/search')->search($queryText, Mage::helper('tooso')->isTypoCorrectedSearch());

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

                    if (Mage::helper('tooso')->isTypoCorrectedSearch() && $search->getFixedSearchString()) {
                        $query->setQueryText($search->getFixedSearchString());
                        $query->setSynonymFor($search->getOriginalSearchString());
                    }

                    $query->setIsProcessed(1);

                } else {
                    return parent::prepareResult($object, $queryText, $query);
                }

            } catch (Exception $e) {
                $this->_logger->logException($e);

                return parent::prepareResult($object, $queryText, $query);
            }
        }

        if (Mage::helper('catalogsearch')->getQueryText() == $query->getSynonymFor()) {
            $message = sprintf(
                'Search instead for "<a href="%s">%s</a>"',
                Mage::getUrl('catalogsearch/result', array('_query' => array('q' => $query->getSynonymFor(), 'typoCorrection' => 'false'))),
                $query->getSynonymFor()
            );
            Mage::helper('catalogsearch')->addNoteMessage($message);
            Mage::helper('tooso')->setFixedSearchString($query->getQueryText());
        }

        return $this;
    }

    // Following methods can't be implemented so far, because reindex
    // is ever performed async with a cronjob, and can be forced only
    // with the button in the config admin panel

//    /**
//     * Regenerate search index for store(s)
//     *
//     * @param  int|null $storeId
//     * @param  int|array|null $productIds
//     * @return Bitbull_Tooso_Model_CatalogSearch_Resource_Fulltext
//     */
//    public function rebuildIndex($storeId = null, $productIds = null)
//    {
//        if (Mage::getStoreConfigFlag('tooso/active/admin')) {
//            Mage::getModel('tooso/indexer')->rebuildIndex();
//        }
//
//        parent::rebuildIndex($storeId, $productIds);
//
//        return $this;
//    }
//
//    /**
//     * Clean index for store(s)
//     *
//     * @param int $storeId Store View Id
//     * @param int|array|null $productIds Product Entity Id
//     * @return Mage_CatalogSearch_Model_Resource_Fulltext
//     */
//    public function cleanIndex($storeId = null, $productIds = null)
//    {
//        parent::cleanIndex($storeId, $productIds);
//
//        if (Mage::getStoreConfigFlag('tooso/active/admin')) {
//            Mage::getModel('tooso/indexer')->cleanIndex();
//        }
//
//        return $this;
//    }
}