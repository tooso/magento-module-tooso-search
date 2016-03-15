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

    public function setDebugInfo($debugInfo)
    {
        $this->_debugInfo = $debugInfo;
    }

    public function getDebugInfo()
    {
        return $this->_debugInfo;
    }
}