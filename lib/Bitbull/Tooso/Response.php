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
        return $this->_response->metadata->objectId;
    }

    public function isValid()
    {
        return !isset($this->_response->data->error);
    }

    public function getErrorCode()
    {
        return $this->_response->metadata->code;
    }

    public function getErrorDescription()
    {
        return $this->_response->data->error->description;
    }

    public function getErrorDebugInfo()
    {
        if(isset($this->_response->data->error->details)){
            return $this->_response->data->error->details;
        }else{
            return null;
        }
    }

}