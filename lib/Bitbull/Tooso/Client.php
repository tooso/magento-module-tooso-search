<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Gennaro Vietri <gennaro.vietri@bitbull.it>
*/
class Bitbull_Tooso_Client
{
    const HTTP_METHOD_GET = 'GET';
    const HTTP_METHOD_POST = 'POST';

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
        $rawResponse = $this->_doRequest('/Search/search', self::HTTP_METHOD_GET, array('query' => $query, 'typoCorrection' => ($typoCorrection ? 'true' : 'false')));

        $result = new Bitbull_Tooso_Search_Result();
        $result->setResponse($rawResponse);

        return $result;
    }

    /**
     * Perform a search for suggestions
     *
     * @param string $query
     * @param int $limit
     * @return Bitbull_Tooso_Suggest_Result
     */
    public function suggest($query, $limit = 10)
    {
        $rawResponse = $this->_doRequest('/Search/suggest', self::HTTP_METHOD_GET, array('query' => $query, 'limit' => $limit));

        $result = new Bitbull_Tooso_Suggest_Result();
        $result->setResponse($rawResponse);

        return $result;
    }

    /**
     * Send data to index
     *
     * @param string $csvContent
     * @return Bitbull_Tooso_Index_Result
     * @throws Bitbull_Tooso_Exception
    */
    public function index($csvContent)
    {
        $tmpZipFile = sys_get_temp_dir() . '/tooso_index_' . microtime() . '.zip';

        $zip = new ZipArchive;
        if ($zip->open($tmpZipFile, ZipArchive::CREATE)) {
            $zip->addFromString('magento_catalog.csv', $csvContent);
            $zip->close();
        } else {
            throw new Bitbull_Tooso_Exception('Error creating zip file for reindex', 0);
        }

        $rawResponse = $this->_doRequest('/Index/index', self::HTTP_METHOD_POST, array(), $tmpZipFile);

        unlink($tmpZipFile);

        $result = new Bitbull_Tooso_Index_Result();
        $result->setResponse($rawResponse);

        return $result;
    }

    /**
     * Build and execute request via CURL.
     *
     * @param string $path
     * @param string $httpMethod
     * @param array $params
     * @param string $attachment
     * @return stdClass
     * @throws Bitbull_Tooso_Exception
    */
    protected function _doRequest($path, $httpMethod = self::HTTP_METHOD_GET, $params = array(), $attachment = '')
    {
        $url = $this->_baseUrl . '/' . $this->_apiKey . $path;

        $queryString = array(
            'language=' . $this->_language
        );

        foreach ($params as $key => $value) {
            $queryString[] = $key . '=' . $value;
        }

        $url .= '?' . implode('&', $queryString);

        $ch = curl_init();

        if ($httpMethod == self::HTTP_METHOD_POST) {
            curl_setopt($ch, CURLOPT_POST, true);
        }

        if (strlen($attachment) > 0) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, array(
                'file' => '@' . realpath($attachment)
            ));
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
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