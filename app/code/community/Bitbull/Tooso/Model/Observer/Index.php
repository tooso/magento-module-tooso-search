<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
class Bitbull_Tooso_Model_Observer_Index extends Bitbull_Tooso_Model_Observer
{

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

}
