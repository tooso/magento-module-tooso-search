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
    protected $_client = null;

    public function __construct()
    {
        $this->_logger = Mage::helper('tooso/log');

        $this->_client = Mage::helper('tooso')->getClient();
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

    /**
     * Add conversion pixel on the end of product page
     * @param  Varien_Event_Observer $observer
     */
    public function showTrackingPixel(Varien_Event_Observer $observer)
    {
        $current_product = Mage::registry('current_product');
        if($current_product) {

            if(Mage::helper('tooso/tracking')->isLastUrlSearch()){ //request from search page

                $this->_logger->debug('Tracking pixel: elaborating result tracking pixel..');
                $sku = $current_product->getSku();
                $toosoSearchId = Mage::helper('tooso/session')->getSearchId();

                if($toosoSearchId){
                    // Get rank collection from search collection
                    $searchRankCollection = Mage::helper('tooso/session')->getRankCollection();
                    $rank = -1;
                    if($searchRankCollection != null && isset($searchRankCollection[$sku])){
                        $rank = $searchRankCollection[$sku];
                    }else{
                        if($searchRankCollection == null){
                            $this->_logger->debug('Tracking pixel: rank collection not found in session');
                        }else{
                            $this->_logger->debug('Tracking pixel: sku not found in rank collection, printing..');
                            foreach ($searchRankCollection as $sku => $rank){
                                $this->_logger->debug('Tracking pixel: '.$sku.' => '.$rank);
                            }
                        }
                    }

                    $order = Mage::helper('tooso/session')->getSearchOrder();
                    if($order == null){
                        $order = "relevance";
                    }

                    $tracking_url = $this->_client->getResultTrackingUrl(array(
                        "searchId" => $toosoSearchId,
                        "resultId" => $sku,
                        "rank" => $rank,
                        "order" => $order,
                    ));
                    $this->_logger->debug('Tracking pixel: searchId '.$toosoSearchId);
                    $this->_logger->debug('Tracking pixel: resultId '.$sku);
                    $this->_logger->debug('Tracking pixel: rank '.$rank);
                    $this->_logger->debug('Tracking pixel: order '.$order);

                    $layout = Mage::app()->getLayout();
                    $block = $layout->createBlock('core/text');
                    $block->setText(Mage::helper('tooso/tracking')->getTrackingImageHTML($tracking_url));
                    $layout->getBlock('before_body_end')->append($block);

                    $this->_logger->debug('Tracking pixel: pixel added into layout');
                }else{
                    $this->_logger->debug('Tracking pixel: search id not found in session');
                }

            }else{ // request not from search page

                $this->_logger->debug('Tracking pixel: elaborating product view tracking pixel..');

                $sku = $current_product->getSku();
                $profilingParams = Mage::helper('tooso')->getProfilingParams();

                $tracking_url = $this->_client->getProductViewTrackingUrl(array(
                    "sku" => $sku,
                    "sessionId" => $profilingParams["sessionId"],
                    "userId" => $profilingParams["userId"]
                ));
                $this->_logger->debug('Tracking pixel: sku '.$sku);
                $this->_logger->debug('Tracking pixel: sessionId '.$profilingParams["sessionId"]);
                $this->_logger->debug('Tracking pixel: userId '.$profilingParams["userId"]);

                $layout = Mage::app()->getLayout();
                $block = $layout->createBlock('core/text');
                $block->setText(Mage::helper('tooso/tracking')->getTrackingImageHTML($tracking_url));
                $layout->getBlock('before_body_end')->append($block);
            }
        }else{
            $this->_logger->debug('Tracking pixel: no product find in registry current_product');
        }

    }

    /**
     * Save rank collection with SKU and their position from collection
     * @param  Varien_Event_Observer $observer
     */
    public function elaborateRankCollection(Varien_Event_Observer $observer){
        $this->_logger->debug('Tracking pixel: elaborating rank collection..');
        $collection = Mage::registry('current_layer')->getProductCollection()->addAttributeToSelect('sku');
        $rankCollection = array();
        $i = 0;
        foreach ($collection as $product) {
            $sku = $product->getSku();
            $rankCollection[$sku] = $i;
            $this->_logger->debug('Tracking pixel: rank collection '.$sku.' => '.$i);
            $i++;
        }

        if(sizeof($rankCollection) == 0){
            $this->_logger->debug('Tracking pixel: rank collection empty');
        }

        Mage::helper('tooso/session')->setRankCollection($rankCollection);
        $this->_logger->debug('Tracking pixel: rank collection saved into session');
    }

}
