<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Gennaro Vietri <gennaro.vietri@bitbull.it>
*/
class Bitbull_Tooso_Search_Result
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

    public function getResults()
    {
        return $this->_response->Content->Results;
    }

    public function getTotalTime()
    {
        return $this->_response->Content->TotalTime;
    }

    public function getSearchId()
    {
        return $this->_response->Content->SearchId;
    }

    public function getTotalResults()
    {
        return $this->_response->Content->TotalResults;
    }

    public function getOriginalSearchString()
    {
        return $this->_response->Content->OriginalSearchString;
    }

    public function getFixedSearchString()
    {
        return $this->_response->Content->FixedSearchString;
    }

    public function getParentSearchId()
    {
        return $this->_response->Content->ParentSearchId;
    }
}