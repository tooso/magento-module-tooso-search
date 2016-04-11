<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Gennaro Vietri <gennaro.vietri@bitbull.it>
*/
class Bitbull_Tooso_Helper_Log extends Mage_Core_Helper_Abstract
{
    const LOG_FILENAME = 'tooso_search.log';

    const XML_PATH_FORCE_LOG = 'tooso/server/force_log';

    /**
     * Retrieve Tooso Log File
     *
     * @return string
     */
    public function getLogFile()
    {
        return self::LOG_FILENAME;
    }

    /**
     * Logging facility
     *
     * @param string $message
     * @param string $level
    */
    public function log($message, $level = null)
    {
        $forceLog = Mage::getStoreConfigFlag(self::XML_PATH_FORCE_LOG);

        Mage::log($message, $level, $this->getLogFile(), $forceLog);
    }

    public function logException(Exception $e)
    {
        if ($e instanceof Bitbull_Tooso_Exception) {
            $message = $e->getMessage() . ' - ERROR CODE = ' . $e->getCode() . ($e->getDebugInfo() ? ' - DEBUG INFO = ' . $e->getDebugInfo() : '');

            $this->log($message, Zend_Log::ERR);
        }

        Mage::logException($e);
    }
}