<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Gennaro Vietri <gennaro.vietri@bitbull.it>
*/
class Bitbull_Tooso_Client
{
    /**
     * Base url for API calls
     *
     * @var string
     */
    protected $_baseUrl = 'http://toosopublicapi.cloudapp.net';

    /**
     * API key
     *
     * @var string
    */
    protected $_apiKey;

    /**
     * Store language
     *
     * @var string
    */
    protected $_language;

    /**
     * Timeout for API connection wait
     * in milliseconds
     *
     * @var int
     */
    protected $_connectTimeout = 10000;

    /**
     * Timeout for API response wait
     * in milliseconds
     *
     * @var int
     */
    protected $_timeout = 10000;

    /**
     * @var stdClass
    */
    protected $_response = null;

    /**
     * @param string $apiKey
     * @param string $language
    */
    public function __construct($apiKey, $language)
    {
        $this->_apiKey = $apiKey;
        $this->_language = $language;
    }

    /**
     * Perform a search
     *
     * @param string $query
     * @param boolean $typoCorrection
     * @return Bitbull_Tooso_Search_Result
    */
    public function search($query, $typoCorrection = true)
    {
        $rawResponse = $this->_doRequest('search', array('query' => $query, 'typoCorrection' => ($typoCorrection ? 'true' : 'false')));

        $result = new Bitbull_Tooso_Search_Result();
        $result->setResponse($rawResponse);

        return $result;
    }

    /**
     * Build and execute request via CURL.
     *
     * @param string $path
     * @param array $params
     * @return stdClass
     * @throws Bitbull_Tooso_Exception
    */
    protected function _doRequest($path, $params)
    {
        $url = $this->_baseUrl . '/' . $this->_apiKey . '/Search/' . $path;

        $queryString = array(
            'language=' . $this->_language
        );

        foreach ($params as $key => $value) {
            $queryString[] = $key . '=' . $value;
        }

        $url .= '?' . implode('&', $queryString);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $this->_connectTimeout);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->_timeout);

        $output = curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $errorNumber = curl_errno($ch);

        curl_close($ch);

        if (false === $output) {

            throw new Bitbull_Tooso_Exception('cURL error = ' . $error, $errorNumber);

        } else if ($httpStatusCode != 200) {

            throw new Bitbull_Tooso_Exception('API unavailable, HTTP STATUS CODE = ' . $httpStatusCode, 0);

        } else {
            $response = json_decode($output);

            if (isset($response->ToosoError)) {
                $e = new Bitbull_Tooso_Exception($response->ToosoError->Description, $response->Code);
                $e->setDebugInfo($response->ToosoError->DebugInfo);

                throw $e;
            } else {
                return $response;
            }
        }
    }
}