<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Gennaro Vietri <gennaro.vietri@bitbull.it>
*/
class Bitbull_Tooso_Exception extends Exception
{
    /**
     * @var string
    */
    protected $_debugInfo = null;

    /**
     * @var Bitbull_Tooso_Response
     */
    protected $_response = null;

    public function setDebugInfo($debugInfo)
    {
        $this->_debugInfo = $debugInfo;
    }

    public function getDebugInfo()
    {
        return $this->_debugInfo;
    }

    public function setResponse(Bitbull_Tooso_Response $response)
    {
        $this->_response = $response;
    }

    public function getResponse()
    {
        return $this->_response;
    }
}