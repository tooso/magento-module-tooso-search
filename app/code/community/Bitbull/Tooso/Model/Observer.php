<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Gennaro Vietri <gennaro.vietri@bitbull.it>
 */
class Bitbull_Tooso_Model_Observer
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
     * Regenerate search index
     *
     * @param  Mage_Cron_Model_Schedule $schedule
     * @return Bitbull_Tooso_Model_Observer
     */
    public function rebuildIndex(Mage_Cron_Model_Schedule $schedule)
    {
        if (Mage::helper('tooso')->isIndexEnabled()) {
            $this->_logger->log('Start scheduled reindex', Zend_Log::DEBUG);

            Mage::getModel('tooso/indexer')->rebuildIndex();

            $this->_logger->log('End scheduled reindex', Zend_Log::DEBUG);
        }

        return $this;
    }

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
}