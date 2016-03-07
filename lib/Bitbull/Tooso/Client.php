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
    protected $_connectTimeout = 500;

    /**
     * Timeout for API response wait
     * in milliseconds
     *
     * @var int
     */
    protected $_timeout = 500;

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
    */
    public function search($query)
    {
        $result = $this->_doRequest('search', array('query' => $query));
    }

    /**
     * Build and execute request via CURL.
     *
     * @param string $path
     * @param array $params
     * @return stdClass
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

        try {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $this->_connectTimeout);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->_timeout);

            $output = curl_exec($ch);

            curl_close($ch);

            return json_decode($output);

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}