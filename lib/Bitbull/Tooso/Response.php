<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
class Bitbull_Tooso_Response
{
    protected $_response = null;

    public function __construct($response = null)
    {
        if ($response) {
            $this->setResponse($response);
        }
    }

    public function setResponse($response)
    {
        $this->_response = $response;
    }

    public function getResponse()
    {
        return $this->_response;
    }

    public function getObjectId()
    {
        return $this->_response->ObjectId;
    }

    public function isValid()
    {
        return !isset($this->_response->ToosoError);
    }

    public function getErrorCode()
    {
        return $this->_response->Code;
    }

    public function getErrorDescription()
    {
        return $this->_response->ToosoError->Description;
    }

    public function getErrorDebugInfo()
    {
        if(isset($this->_response->ToosoError->DebugInfo)){
            return $this->_response->ToosoError->DebugInfo;
        }else{
            return null;
        }
    }

}