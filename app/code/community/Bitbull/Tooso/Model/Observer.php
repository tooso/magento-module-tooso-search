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
        if (Mage::getStoreConfigFlag('tooso/active/admin')) {
            $this->_logger->log('Start scheduled reindex', Zend_Log::DEBUG);

            Mage::getModel('tooso/indexer')->rebuildIndex();

            $this->_logger->log('End scheduled reindex', Zend_Log::DEBUG);
        }

        return $this;
    }
}