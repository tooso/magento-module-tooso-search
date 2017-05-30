<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Gennaro Vietri <gennaro.vietri@bitbull.it>
 */
class Bitbull_Tooso_Suggest_Result extends Bitbull_Tooso_Response
{
    const FALLBACK_RESPONSE_SUGGESTIONS = "";

    public function __construct(Bitbull_Tooso_Response $response = null)
    {
        if ($response) {
            $rawResponse = $response->getResponse();
            $this->setResponse($rawResponse);
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