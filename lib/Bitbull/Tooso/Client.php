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
    protected $_connectTimeout = 20000;

    /**
     * Timeout for API response wait
     * in milliseconds
     *
     * @var int
     */
    protected $_timeout = 20000;

    /**
     * @var stdClass
    */
    protected $_response = null;

    /**
     * @var Bitbull_Tooso_Log_SendInterface
    */
    protected $_reportSender;

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
     * @param Bitbull_Tooso_Log_SendInterface $reportSender
     */
    public function setReportSender(Bitbull_Tooso_Log_SendInterface $reportSender)
    {
        $this->_reportSender = $reportSender;
    }

    /**
     * Perform a search
     *
     * @param string $query
     * @param boolean $typoCorrection
     * @param array $extraParams
     * @return Bitbull_Tooso_Search_Result
     * @throws Bitbull_Tooso_Exception
    */
    public function search($query, $typoCorrection = true, $extraParams = array())
    {
        $path = '/Search/search';
        $params = array_merge(
            array('query' => $query, 'typoCorrection' => ($typoCorrection ? 'true' : 'false')),
            (array)$extraParams
        );

        $rawResponse = $this->_doRequest($path, self::HTTP_METHOD_GET, $params);

        $result = new Bitbull_Tooso_Search_Result();
        $result->setResponse($rawResponse);

        // In the early adopter phase, even a 0 result query need to be treated as an error
        if ($result->getTotalResults() == 0 && $typoCorrection) {
            $message = 'No result found for query "' . $query . '""';

            if ($this->_reportSender) {
                $this->_reportSender->sendReport($this->_buildUrl($path, $params), self::HTTP_METHOD_GET, $this->_apiKey, $this->_language, $message);
            }

            throw new Bitbull_Tooso_Exception($message, 0);
        }

        return $result;
    }

    /**
     * Perform a search for suggestions
     *
     * @param string $query
     * @param int $limit
     * @param array $extraParams
     * @return Bitbull_Tooso_Suggest_Result
     */
    public function suggest($query, $limit = 10, $extraParams = array())
    {
        $params = array_merge(
            array('query' => $query, 'limit' => $limit),
            (array)$extraParams
        );

        $rawResponse = $this->_doRequest('/Search/suggest', self::HTTP_METHOD_GET, $params);

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
        $url = $this->_buildUrl($path, $params);

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
            
            if ($this->_reportSender) {
                $message = 'cURL error = ' . $error . ' - Error number = ' . $errorNumber;

                $this->_reportSender->sendReport($url, $httpMethod, $this->_apiKey, $this->_language, $message);
            }

            throw new Bitbull_Tooso_Exception('cURL error = ' . $error, $errorNumber);

        } else if ($httpStatusCode != 200) {

            if ($this->_reportSender) {
                $message = 'API unavailable, HTTP STATUS CODE = ' . $httpStatusCode;

                $this->_reportSender->sendReport($url, $httpMethod, $this->_apiKey, $this->_language, $message);
            }

            throw new Bitbull_Tooso_Exception('API unavailable, HTTP STATUS CODE = ' . $httpStatusCode, 0);

        } else {
            $response = json_decode($output);

            if (isset($response->ToosoError)) {
                $e = new Bitbull_Tooso_Exception($response->ToosoError->Description, $response->Code);
                $e->setDebugInfo($response->ToosoError->DebugInfo);

                if ($this->_reportSender) {
                    $message = 'Error description = ' . $response->ToosoError->Description . "\n"
                        . "Error code = " . $response->Code . "\n"
                        . "Debug info = " . $response->ToosoError->DebugInfo;

                    $this->_reportSender->sendReport($url, $httpMethod, $this->_apiKey, $this->_language, $message);
                }

                throw $e;
            } else {
                return $response;
            }
        }
    }

    /**
     * Build an url for an API call
     *
     * @param string $path
     * @param array $params
     * @return string
    */
    protected function _buildUrl($path, $params)
    {
        $url = $this->_baseUrl . '/' . $this->_apiKey . $path;

        $queryString = array(
            'language=' . $this->_language
        );

        foreach ($params as $key => $value) {
            $queryString[] = $key . '=' . $value;
        }

        $url .= '?' . implode('&', $queryString);

        return $url;
    }
}