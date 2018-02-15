<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Gennaro Vietri <gennaro.vietri@bitbull.it>
*/
class Bitbull_Tooso_Search_Result extends Bitbull_Tooso_Response
{
    const FALLBACK_RESPONSE_TOTAL_TIME = 0;
    const FALLBACK_RESPONSE_TOTAL_RESULTS = 0;
    const FALLBACK_RESPONSE_ORIGINAL_SEARCH_STRING = "";
    const FALLBACK_RESPONSE_FIXED_SEARCH_STRING = "";
    const FALLBACK_RESPONSE_PARENT_SEARCH_ID = null;

    public function __construct(Bitbull_Tooso_Response $response)
    {
        if ($response) {
            $rawResponse = $response->getResponse();
            $this->setResponse($rawResponse);
        }
    }

    public function getResults()
    {
        if($this->isValid()){
            return $this->_response->data->results;
        }else{
            return array();
        }
    }

    public function getTotalTime()
    {
        if($this->isValid()){
            return $this->_response->metadata->time;
        }else{
            return self::FALLBACK_RESPONSE_TOTAL_TIME;
        }
    }

    public function getSearchId()
    {
        return $this->getObjectId();
    }

    public function getTotalResults()
    {
        if($this->isValid()){
            return $this->_response->data->hits;
        }else{
            return self::FALLBACK_RESPONSE_TOTAL_RESULTS;
        }
    }

    public function getOriginalSearchString()
    {
        if($this->isValid()){
            return $this->_response->metadata->q;
        }else{
            return self::FALLBACK_RESPONSE_ORIGINAL_SEARCH_STRING;
        }
    }

    public function getFixedSearchString()
    {
        if($this->isValid()){
            return $this->_response->data->fixedQuery;
        }else{
            return self::FALLBACK_RESPONSE_FIXED_SEARCH_STRING;
        }
    }

    public function getRankCollection()
    {
        if($this->isValid()){
            $rankCollection = array();
            $results = $this->getResults();
            foreach ($results as $key => $result) {
                $rankCollection[$result] = $key;
            }
            return $rankCollection;
        }else{
            return array();
        }
    }

}