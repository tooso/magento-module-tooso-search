<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Gennaro Vietri <gennaro.vietri@bitbull.it>
 */
interface Bitbull_Tooso_Log_SendInterface
{
    /**
     * Send report API
     *
     * @param string $url
     * @param string $httpMethod
     * @param string $apiKey
     * @param string $language
     * @param string $message
     */
    public function sendReport($url, $httpMethod, $apiKey, $language, $message);
}