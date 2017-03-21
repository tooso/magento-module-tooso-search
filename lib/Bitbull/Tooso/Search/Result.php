<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Gennaro Vietri <gennaro.vietri@bitbull.it>
*/
class Bitbull_Tooso_Search_Result
{
    const FALLBACK_RESPONSE_TOTAL_TIME = 0;
    const FALLBACK_RESPONSE_SEARCH_ID = null;
    const FALLBACK_RESPONSE_TOTAL_RESULTS = 0;
    const FALLBACK_RESPONSE_ORIGINAL_SEARCH_STRING = "";
    const FALLBACK_RESPONSE_FIXED_SEARCH_STRING = "";
    const FALLBACK_RESPONSE_PARENT_SEARCH_ID = NULL;
    const FALLBACK_RESPONSE_SUGGESTIONS = "";

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
        if(isset($response->ToosoError->DebugInfo)){
            return $this->_response->ToosoError->DebugInfo;
        }else{
            return null;
        }
    }

    public function getResults()
    {
        if($this->isValid()){
            return $this->_response->Content->Results;
        }else{
            return array();
        }
    }

    public function getTotalTime()
    {
        if($this->isValid()){
            return $this->_response->Content->TotalTime;
        }else{
            return self::FALLBACK_RESPONSE_TOTAL_TIME;
        }
    }

    public function getSearchId()
    {
        if($this->isValid()){
            return $this->_response->Content->SearchId;
        }else{
            if(isset($this->_response->Content) && isset($this->_response->Content->SearchId)){
                return $this->_response->Content->SearchId;
            }else{
                return self::FALLBACK_RESPONSE_SEARCH_ID;
            }
        }
    }

    public function getTotalResults()
    {
        if($this->isValid()){
            return $this->_response->Content->TotalResults;
        }else{
            return self::FALLBACK_RESPONSE_TOTAL_RESULTS;
        }
    }

    public function getOriginalSearchString()
    {
        if($this->isValid()){
            return $this->_response->Content->OriginalSearchString;
        }else{
            return self::FALLBACK_RESPONSE_ORIGINAL_SEARCH_STRING;
        }
    }

    public function getFixedSearchString()
    {
        if($this->isValid()){
            return $this->_response->Content->FixedSearchString;
        }else{
            return self::FALLBACK_RESPONSE_FIXED_SEARCH_STRING;
        }
    }

    public function getParentSearchId()
    {
        if($this->isValid()){
            return $this->_response->Content->ParentSearchId;
        }else{
            return self::FALLBACK_RESPONSE_PARENT_SEARCH_ID;
        }
    }

    public function getSuggestions()
    {
        if($this->isValid()){
            return $this->_response->Content->Suggestions;
        }else{
            return self::FALLBACK_RESPONSE_SUGGESTIONS;
        }
    }
}