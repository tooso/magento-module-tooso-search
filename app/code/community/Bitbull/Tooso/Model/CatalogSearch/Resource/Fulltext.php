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
     * Replaces the built-in fulltext search with a Tooso search (if active).
     *
     * @param Mage_CatalogSearch_Model_Fulltext $object
     * @param string $queryText
     * @param Mage_CatalogSearch_Model_Query $query
     * @return Bitbull_Tooso_Model_CatalogSearch_Resource_Fulltext
     */
    public function prepareResult($object, $queryText, $query)
    {
        if (!Mage::helper('tooso')->isSearchEnabled()) {
            return parent::prepareResult($object, $queryText, $query);
        }

        if (trim($queryText) === ''){
            return $this;
        }

        $search = null;

        if (!Mage::helper('tooso')->getSearchAlreadyDone()) {
            Mage::helper('tooso')->setSearchAlreadyDone();
            try {
                $typoCorrection = Mage::helper('tooso')->isTypoCorrectedSearch();
                $parentSearchId = null;
                if($typoCorrection == false){
                    $parentSearchId = Mage::helper('tooso')->getParentSearchId();
                }
                $search = Mage::getModel('tooso/search')->search($queryText, $typoCorrection, $parentSearchId);

                // Add similar result alert message
                $similarResultMessage = $search->getSimilarResultsAlert();
                if($similarResultMessage != null && $similarResultMessage != "") {
                    Mage::helper('catalogsearch')->addNoteMessage($similarResultMessage);
                }

                // It's true if no errors was given by API call
                if ($search->isSearchAvailable()) {

                    Mage::helper('tooso/session')->setLastSearchPage(Mage::helper('tooso/tracking')->getCurrentPage());

                    // If this query was automatically typo-corrected, save in request scope the searchId for link
                    // this query (the parent) with the following one forced as not typo-correct
                    if (Mage::helper('tooso')->isTypoCorrectedSearch()) {
                        Mage::helper('tooso')->setSearchId($search->getSearchId());
                    }

                    if ($search->getFixedSearchString() && Mage::helper('catalogsearch')->getQueryText() == $search->getOriginalSearchString()) {

                        $queryString = array(
                            'q' => $search->getOriginalSearchString(),
                            'typoCorrection' => 'false',
                            Bitbull_Tooso_Model_Search::SEARCH_PARAM_PARENT_SEARCH_ID => Mage::helper('tooso')->getSearchId()
                        );

                        $message = sprintf(
                            Mage::helper('catalogsearch')->__('Search instead for "<a href="%s">%s</a>"'),
                            Mage::getUrl('catalogsearch/result', array('_query' => $queryString)),
                            $search->getOriginalSearchString()
                        );
                        Mage::helper('catalogsearch')->addNoteMessage($message);
                        Mage::helper('tooso')->setFixedSearchString($search->getFixedSearchString());
                    }

                    if ($search->isResultEmpty() && Mage::helper('tooso/search')->isFallbackEnable()) {
                        if ($search->getFixedSearchString() && Mage::helper('catalogsearch')->getQueryText() == $search->getOriginalSearchString()) {
                            return parent::prepareResult($object, $search->getFixedSearchString(), $query);
                        }else{
                            return parent::prepareResult($object, $queryText, $query);
                        }
                    }

                    $products = array();
                    foreach ($search->getProducts() as $product) {
                        $products[] = $product['product_id'];
                    }

                    // Store products ids for later use them to build database query
                    Mage::helper('tooso')->setProducts($products);

                } else {
                    $redirect = $search->getRedirect();
                    if(!is_null($redirect)){
                        Mage::app()->getFrontController()->getResponse()->setRedirect($redirect);
                        return;
                    }

                    return parent::prepareResult($object, $queryText, $query);
                }

            } catch (Exception $e) {
                $this->_logger->logException($e);

                return parent::prepareResult($object, $queryText, $query);
            }
        }

        return $this;
    }
}