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
    protected $_baseUrl;

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
     * Store code
     *
     * @var string
     */
    protected $_storeCode;

    /**
     * Timeout for API connection wait
     * in milliseconds
     *
     * @var int
     */
    protected $_connectTimeout = 2000;

    /**
     * Timeout for API response wait
     * in milliseconds
     *
     * @var int
     */
    protected $_timeout = 2000;

    /**
     * @var stdClass
    */
    protected $_response = null;

    /**
     * @var Bitbull_Tooso_Log_SendInterface
    */
    protected $_reportSender;
    
    /**
     * @var Bitbull_Tooso_Log_LoggerInterface
    */
    protected $_logger;

    /**
     * @param string $apiKey
     * @param string $apiBaseUrl
     * @param string $language
     * @param string $storeCode
     * @param Bitbull_Tooso_Log_LoggerInterface $logger
    */
    public function __construct($apiKey, $apiBaseUrl, $language, $storeCode, Bitbull_Tooso_Log_LoggerInterface $logger)
    {
        $this->_apiKey = $apiKey;
        $this->_baseUrl = $apiBaseUrl;
        $this->_language = $language;
        $this->_storeCode = $storeCode;

        $this->_logger = $logger;
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
                $this->_reportSender->sendReport($this->_buildUrl($path, $params), self::HTTP_METHOD_GET, $this->_apiKey, $this->_language, $this->_storeCode, $message);
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
     * @param string $storeCode
     * @return Bitbull_Tooso_Index_Result
     * @throws Bitbull_Tooso_Exception
    */
    public function index($csvContent, $storeCode)
    {
        $tmpZipFile = sys_get_temp_dir() . '/tooso_index_' . microtime() . '.zip';
        
        $this->_logger->debug("Temporary zip file: " . $tmpZipFile);
        
        $zip = new ZipArchive;
        if ($zip->open($tmpZipFile, ZipArchive::CREATE)) {
            $zip->addFromString('magento_catalog.csv', $csvContent);
            $zip->close();
        } else {
            throw new Bitbull_Tooso_Exception('Error creating zip file for reindex', 0);
        }

        $this->_logger->debug("Start uploading zipfile");

        $rawResponse = $this->_doRequest('/Index/index', self::HTTP_METHOD_POST, array('storeCode' => $storeCode), $tmpZipFile, 300000);

        $this->_logger->debug("End uploading zipfile, raw response: " . print_r($rawResponse, true));

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
     * @param int $timeout
     * @return stdClass
     * @throws Bitbull_Tooso_Exception
    */
    protected function _doRequest($path, $httpMethod = self::HTTP_METHOD_GET, $params = array(), $attachment = '', $timeout = null)
    {
        $url = $this->_buildUrl($path, $params);

        $this->_logger->debug("Performing API request to url: " . $url . " with method: " . $httpMethod);
        $this->_logger->debug("Params: " . print_r($params, true));

        $ch = curl_init();

        if ($httpMethod == self::HTTP_METHOD_POST) {
            curl_setopt($ch, CURLOPT_POST, true);
        }

        if (strlen($attachment) > 0) {

            if (class_exists('CURLFile')) {
                $file = new CURLFile(realpath($attachment));
            } else {
                $file = '@' . realpath($attachment);
            }

            curl_setopt($ch, CURLOPT_POSTFIELDS, array(
                'file' => $file
            ));
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $this->_connectTimeout);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, !is_null($timeout) ? $timeout : $this->_timeout);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $output = curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $errorNumber = curl_errno($ch);

        curl_close($ch);

        $this->_logger->debug("Raw response: " . print_r($output, true));

        if (false === $output) {
            
            if ($this->_reportSender) {
                $message = 'cURL error = ' . $error . ' - Error number = ' . $errorNumber;

                $this->_reportSender->sendReport($url, $httpMethod, $this->_apiKey, $this->_language, $this->_storeCode, $message);
            }

            throw new Bitbull_Tooso_Exception('cURL error = ' . $error, $errorNumber);

        } else if ($httpStatusCode != 200) {

            if ($this->_reportSender) {
                $message = 'API unavailable, HTTP STATUS CODE = ' . $httpStatusCode;

                $response = json_decode($output);
                if (isset($response->ToosoError) && isset($response->ToosoError->DebugInfo)) {
                    $message .= "\n\nDebugInfo: " . $response->ToosoError->DebugInfo;
                }

                $this->_reportSender->sendReport($url, $httpMethod, $this->_apiKey, $this->_language, $this->_storeCode, $message);
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

                    $this->_reportSender->sendReport($url, $httpMethod, $this->_apiKey, $this->_language, $this->_storeCode, $message);
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
     * @throws Bitbull_Tooso_Exception
    */
    protected function _buildUrl($path, $params)
    {
        if (filter_var($this->_baseUrl, FILTER_VALIDATE_URL) === false) {
            $message = 'API base URL missing or invalid: "' . $this->_baseUrl . '"';

            if ($this->_reportSender) {
                $this->_reportSender->sendReport('', '', $this->_apiKey, $this->_language, $this->_storeCode, $message);
            }

            throw new Bitbull_Tooso_Exception($message, 0);
        }

        $url = $this->_baseUrl . '/' . $this->_apiKey . $path;

        $queryString = array(
            'language=' . $this->_language,
            'storeCode=' . $this->_storeCode,
        );

        foreach ($params as $key => $value) {
            $queryString[] = $key . '=' . $value;
        }

        $url .= '?' . implode('&', $queryString);

        return $url;
    }
}