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

        $search = null;
        
        if (null == Mage::helper('tooso')->getProducts()) {
            
            try {
                $search = Mage::getModel('tooso/search')->search($queryText, Mage::helper('tooso')->isTypoCorrectedSearch());

                if ($search->isSearchAvailable()) {

                    $products = array();
                    foreach ($search->getProducts() as $product) {
                        $products[] = $product['product_id'];
                    }

                    Mage::helper('tooso')->setProducts($products);

                    if (Mage::helper('tooso')->isTypoCorrectedSearch()) {
                        Mage::helper('tooso')->setSearchId($search->getSearchId());
                    }

                } else {
                    return parent::prepareResult($object, $queryText, $query);
                }

            } catch (Exception $e) {
                $this->_logger->logException($e);

                return parent::prepareResult($object, $queryText, $query);
            }
        }

        if ($search
            && $search->getFixedSearchString()
            && Mage::helper('catalogsearch')->getQueryText() == $search->getOriginalSearchString()) {

            $queryString = array(
                'q' => $search->getOriginalSearchString(),
                'typoCorrection' => 'false',
                Bitbull_Tooso_Model_Search::SEARCH_PARAM_PARENT_SEARCH_ID => Mage::helper('tooso')->getSearchId()
            );

            $message = sprintf(
                'Search instead for "<a href="%s">%s</a>"',
                Mage::getUrl('catalogsearch/result', array('_query' => $queryString)),
                $search->getOriginalSearchString()
            );
            Mage::helper('catalogsearch')->addNoteMessage($message);
            Mage::helper('tooso')->setFixedSearchString($search->getFixedSearchString());
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