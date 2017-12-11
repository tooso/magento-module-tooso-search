<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
class Bitbull_Tooso_Model_Observer_Search extends Bitbull_Tooso_Model_Observer
{

    /**
     * Change title in some places, replacing original query
     * with fixed search string.
     *
     * @param Varien_Event_Observer $observer
     */
    public function showFixedSearchStringOnSearchResults(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();
        $fixedSearchString = Mage::helper('tooso')->getFixedSearchString();
        $title = $block->__("Search results for: '%s'", $fixedSearchString ? $fixedSearchString : Mage::helper('catalogsearch')->getEscapedQueryText());

        if (Mage::helper('tooso')->isSearchEnabled() && $fixedSearchString) {

            if ($block instanceof Mage_CatalogSearch_Block_Result) {
                $block->setHeaderText($title);
            }

            // modify page title
            if ($block instanceof Mage_Page_Block_Html_Head) {
                $block->setTitle($title);
            }

            // add Home breadcrumb
            if ($block instanceof Mage_Page_Block_Html_Breadcrumbs) {
                $block->addCrumb('home', array(
                    'label' => $block->__('Home'),
                    'title' => $block->__('Go to Home Page'),
                    'link'  => Mage::getBaseUrl()
                ))->addCrumb('search', array(
                    'label' => $title,
                    'title' => $title
                ));
            }
        }
    }

    /**
     * Save rank collection with SKU and their position from collection
     * @param  Varien_Event_Observer $observer
     */
    public function elaborateRankCollection(Varien_Event_Observer $observer)
    {
        if(!Mage::helper('tooso')->isTrackingEnabled()){
            return;
        }

        $this->_logger->debug('Rank Collection: elaborating collection..');
        $currentLayer = Mage::registry('current_layer');
        if($currentLayer == null){
            $this->_logger->warn('Registry current_layer is null, cannot get current search results');
            return;
        }
        $collection = $currentLayer->getProductCollection();

        $collection->addAttributeToSelect('name');
        $rankCollection = array();
        $i = 0;
        $curPage = (int) $collection->getCurPage();
        $pageSize = (int) $collection->getPageSize();
        $this->_logger->debug('Rank Collection: page '.$curPage.' size '.$pageSize);
        foreach ($collection as $product) {
            $id = $product->getId();
            $pos = $i + (($curPage-1) * $pageSize);
            $rankCollection[$id] = $pos;
            $this->_logger->debug('Rank Collection: ['.$id.'] '.$product->getName().' => '.$pos);
            $i++;
        }

        if(sizeof($rankCollection) == 0){
            $this->_logger->debug('Rank Collection: collection empty');
        }

        Mage::helper('tooso/session')->setRankCollection($rankCollection);
        $this->_logger->debug('Rank Collection: collection saved into session');

    }

    /**
     * Clear searchId session variable if no longer used
     * @param  Varien_Event_Observer $observer
     */
    public function clearSearchId(Varien_Event_Observer $observer)
    {
        if(!Mage::helper('tooso')->isSearchEnabled()){
            return;
        }

        $routeName = Mage::app()->getRequest()->getRouteName();
        $exclude = array("catalog", "catalogsearch", "enterprise_pagecache");
        if(!in_array($routeName, $exclude)){
            $block = Mage::helper('tooso/tracking')->getClearSearchIDBlock();
            $parentBlock = Mage::helper('tooso/tracking')->getScriptContainerBlock();
            if($parentBlock){
                $parentBlock->append($block);
            }else{
                $this->_logger->warn('Cannot add ClearSearchId block, parent container not found');
            }
        }
    }

    /**
     * Call search API and populate products ids list in memory
     *
     * @param Varien_Event_Observer $observer
     */
    public function prepareResults(Varien_Event_Observer $observer)
    {
        if (Mage::helper('tooso')->isSearchEnabled()) {
            $query = Mage::helper('catalogsearch')->getQuery();
            $query->save();
            Mage::getSingleton('catalogsearch/fulltext')->prepareResult();
        }
    }

}
