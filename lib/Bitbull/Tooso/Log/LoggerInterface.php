<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Gennaro Vietri <gennaro.vietri@bitbull.it>
 */
interface Bitbull_Tooso_Log_LoggerInterface
{
    /**
     * Retrieve Tooso Log File
     *
     * @return string
     */
    public function getLogFile();

    /**
     * Logging facility
     *
     * @param string $message
     * @param string $level
     */
    public function log($message, $level = null);

    /**
     * @param Exception $e
    */
    public function logException(Exception $e);

    /**
     * @param string $message
     */
    public function debug($message);
}