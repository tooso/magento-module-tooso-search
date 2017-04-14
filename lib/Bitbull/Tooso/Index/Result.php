<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Gennaro Vietri <gennaro.vietri@bitbull.it>
 */
class Bitbull_Tooso_Index_Result extends Bitbull_Tooso_Response
{

    public function __construct(Bitbull_Tooso_Response $response)
    {
        if ($response) {
            $rawResponse = $response->getResponse();
            $this->setResponse($rawResponse);
        }
    }

}