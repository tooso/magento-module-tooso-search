<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Gennaro Vietri <gennaro.vietri@bitbull.it>
 */
class Bitbull_Tooso_Index_Result
{
    protected $_response = null;
    protected $_code = null;

    public function __construct($response = null, $code = null)
    {
        $this->setResponse($response);
        $this->setCode($response);
    }

    public function setCode($code)
    {
        $this->_code = $code;
    }

    public function getCode()
    {
        return $this->_code;
    }

    public function setResponse($response)
    {
        $this->_response = $response;
    }

    public function getResponse()
    {
        return $this->_response;
    }

    public function isValid()
    {
        return $this->getCode() != 204;
    }

    public function getErrorMessage()
    {
        $error = substr($this->_response, strpos($this->_response, '<Code>') + 6);
        $error = substr($error, 0, strpos($error, '</Code>'));
        return $error;
    }

    public function getErrorCode()
    {
        return $this->getCode();
    }

}