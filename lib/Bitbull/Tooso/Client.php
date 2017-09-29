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
    const FORCE_ERROR = false; //DEBUG: force client to trigger error

    const INDEX_DOC_TYPE = 1;
    const INDEX_EXTENSION = "csv";

    const ARRAY_VALUES_SEPARATOR = ',';

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
     * Secret API key
     *
     * @var null|string
     */
    protected $_secretKey;

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
    protected $_timeout = 4000;

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
     * @var Bitbull_Tooso_Storage_SessionInterface
     */
    protected $_sessionStorage;

    /**
     * @param string $apiKey
     * @param string $apiBaseUrl
     * @param string $language
     * @param string $storeCode
     * @param Bitbull_Tooso_Log_LoggerInterface $logger
    */
    public function __construct($apiKey, $secretKey, $apiBaseUrl, $language, $storeCode)
    {
        $this->_apiKey = $apiKey;
        $this->_secretKey = $secretKey;
        $this->_baseUrl = $apiBaseUrl;
        $this->_language = strtolower($language);
        $this->_storeCode = $storeCode;
    }

    /**
     * @param Bitbull_Tooso_Log_LoggerInterface $reportSender
     */
    public function setLogger(Bitbull_Tooso_Log_LoggerInterface $logger)
    {
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
     * @param Bitbull_Tooso_Storage_SessionInterface $sessionStorage
     */
    public function setSessionStorage(Bitbull_Tooso_Storage_SessionInterface $sessionStorage)
    {
        $this->_sessionStorage = $sessionStorage;
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
        $query = str_replace(array("+", "%2B"), " ", $query);

        if(self::FORCE_ERROR){
            $query = null;
        }

        $path = '/search';
        $params = array_merge(
            array('query' => $query, 'typoCorrection' => ($typoCorrection ? 'true' : 'false')),
            (array)$extraParams
        );

        try {
            $response = $this->_doRequest($path, self::HTTP_METHOD_GET, $params);
            $result = new Bitbull_Tooso_Search_Result($response);
            if ($this->_sessionStorage) {
                $searchId = $result->getSearchId();
                $this->_sessionStorage->setSearchId($searchId);
                if($this->_logger){
                    $this->_logger->debug('Session: set search id to '.$searchId);
                }
            }

            // In the early adopter phase, even a 0 result query need to be treated as an error
            /*
            if ($result->getTotalResults() == 0 && $typoCorrection) {
                $message = 'No result found for query "' . $query . '""';

                throw new Bitbull_Tooso_Exception($message, 0);
            }*/

        } catch (Bitbull_Tooso_Exception $e) {
            $response = $e->getResponse();
            if($response != null && $this->_sessionStorage){
                $result = new Bitbull_Tooso_Search_Result($response);
                $searchId = $result->getSearchId();
                $this->_sessionStorage->setSearchId($searchId);
                if($this->_logger){
                    $this->_logger->debug('Session: set search id to '.$searchId);
                }
            }
            throw $e;
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
    public function suggest($query, $limit = 10, $extraParams = array(), $apiV1VersionBaseURL = null)
    {
        $query = str_replace(array("+", "%2B"), " ", $query);

        $params = array_merge(
            array('query' => $query, 'limit' => $limit),
            (array)$extraParams
        );

        if($apiV1VersionBaseURL != null && $apiV1VersionBaseURL != ""){
            $path = '/Search/suggest';
            $oldBaseUrl = $this->_baseUrl;
            $this->_baseUrl = $apiV1VersionBaseURL;
            $response = $this->_doRequest($path, self::HTTP_METHOD_GET, $params);
            $this->_baseUrl = $oldBaseUrl;
        }else{
            $path = '/suggest';
            $response = $this->_doRequest($path, self::HTTP_METHOD_GET, $params);
        }

        $result = new Bitbull_Tooso_Suggest_Result($response);
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

        if($this->_logger){
            $this->_logger->debug("Temporary zip file: " . $tmpZipFile);
        }
        
        $zip = new ZipArchive;
        if ($zip->open($tmpZipFile, ZipArchive::CREATE)) {
            $zip->addFromString('magento_catalog.csv', $csvContent);
            $zip->close();
        } else {
            throw new Bitbull_Tooso_Exception('Error creating zip file for reindex', 0);
        }

        if($this->_logger){
            $this->_logger->debug("Start uploading zipfile");
        }

        $path = '/index';
        $params = array(
            "docType" => self::INDEX_DOC_TYPE,
            "extension" => self::INDEX_EXTENSION,
            "secretKey" => $this->_secretKey
        );
        $response = $this->_doRequest($path, self::HTTP_METHOD_POST, $params, $tmpZipFile, 300000);
        if($this->_logger){
            $this->_logger->debug("End uploading zipfile, raw response: " . print_r($response->getResponse(), true));
        }

        unlink($tmpZipFile);

        $result = new Bitbull_Tooso_Index_Result($response);

        return $result;
    }

    /**
     * Send product added to cart event
     *
     * @param string $sku product sku
     * @param array $profilingParams
     * @return Bitbull_Tooso_Suggest_Result
     */
    public function productAddedToCart($sku, $profilingParams)
    {
        $path = '/addToCart';
        $params = array_merge(
            array(
                'objectId' => $sku
            ),
            (array)$profilingParams
        );

        return $this->_doRequest($path, self::HTTP_METHOD_GET, $params);
    }

    /**
     * Tracking result
     *
     * @param string $searchId search ID
     * @param string $sku product SKU
     * @param string $rank result position
     * @param array $order search order
     * @param array $profilingParams
     * @return Bitbull_Tooso_Response
     */
    public function resultTracking($searchId, $sku, $rank, $order, $profilingParams)
    {
        $path = '/feedback';
        $params = array_merge(
            array(
                "searchId" => $searchId,
                "objectId" => $sku,
                "rank" => $rank,
                "order" => $order
            ),
            (array)$profilingParams
        );
        return $this->_doRequest($path, self::HTTP_METHOD_GET, $params);
    }

    /**
     * Tracking product view
     *
     * @param string $sku product SKU
     * @param array $profilingParams
     * @return Bitbull_Tooso_Response
     */
    public function productViewTracking($sku, $profilingParams)
    {
        $path = '/productView';
        $params = array_merge(
            array(
                'objectId' => $sku
            ),
            (array)$profilingParams
        );
        return $this->_doRequest($path, self::HTTP_METHOD_GET, $params);
    }

    /**
     * Tracking page view
     *
     * @param string $currentPage current page
     * @param string $lastPage last page
     * @param array $profilingParams
     * @return Bitbull_Tooso_Response
     */
    public function pageViewTracking($currentPage, $lastPage, $profilingParams)
    {
        $path = '/pageView';
        $params = array_merge(
            array(
                'currentPage' => $currentPage,
                'lastPage' => $lastPage
            ),
            (array)$profilingParams
        );
        return $this->_doRequest($path, self::HTTP_METHOD_GET, $params);
    }

    /**
     * Checkout page view
     *
     * @param array $skus skus
     * @param array $prices prices
     * @param array $qtys quantities
     * @return Bitbull_Tooso_Response
     */
    public function checkoutTracking($skus, $prices, $qtys, $trackingParams)
    {
        if(is_array($skus) && is_array($prices) && is_array($qtys)){
            if(sizeof($skus) == sizeof($prices) && sizeof($skus) == sizeof($qtys)){

                // Parse eventually float value in quantities to integer
                for($i = 0; $i < sizeof($qtys); $i++){
                    $qtys[$i] = intval($qtys[$i]);
                }

                $path = '/checkOut';
                $params = array_merge(
                    array(
                        'objectIds' => implode(self::ARRAY_VALUES_SEPARATOR, $skus),
                        'prices' => implode(self::ARRAY_VALUES_SEPARATOR, $prices),
                        'qtys' => implode(self::ARRAY_VALUES_SEPARATOR, $qtys),
                    ),
                    (array)$trackingParams
                );
                return $this->_doRequest($path, self::HTTP_METHOD_GET, $params);

            }else{
                throw new Bitbull_Tooso_Exception('Invalid checkout tracking parameters, they must have the same length');
            }
        }else{
            throw new Bitbull_Tooso_Exception('Invalid checkout tracking parameters, they must be array');
        }
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

        if($this->_logger) {
            $this->_logger->debug("Performing API request to url: " . $url . " with method: " . $httpMethod);
            $this->_logger->debug("Params: " . print_r($params, true));
        }

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

        if($this->_logger) {
            $this->_logger->debug("Raw response: " . print_r($output, true));
        }

        if (false === $output) {
            
            if ($this->_reportSender) {
                $message = 'cURL error = ' . $error . ' - Error number = ' . $errorNumber;

                $this->_reportSender->sendReport($url, $httpMethod, $this->_apiKey, $this->_language, $this->_storeCode, $message);
            }

            throw new Bitbull_Tooso_Exception('cURL error = ' . $error, $errorNumber);

        }else{
            $response = json_decode($output);
            $result = new Bitbull_Tooso_Response();
            $result->setResponse($response);

            if ($httpStatusCode != 200) {

                if ($this->_reportSender) {
                    $message = 'API unavailable, HTTP STATUS CODE = ' . $httpStatusCode;

                    if ($result->getErrorDebugInfo() != null) {
                        $message .= "\n\nDebugInfo: " . $result->getErrorDebugInfo();
                    }

                    $this->_reportSender->sendReport($url, $httpMethod, $this->_apiKey, $this->_language, $this->_storeCode, $message);
                }

                $e = new Bitbull_Tooso_Exception('API unavailable, HTTP STATUS CODE = ' . $httpStatusCode, 0);
                $e->setResponse($result);
                throw $e;

            } else if ($httpStatusCode == 200 && !$result->isValid()) {

                if ($this->_reportSender) {
                    $message = 'Error description = ' . $result->getErrorDescription() . "\n"
                        . "Error code = " . $result->getErrorCode() . "\n"
                        . "Debug info = " . $result->getErrorDebugInfo();

                    $this->_reportSender->sendReport($url, $httpMethod, $this->_apiKey, $this->_language, $this->_storeCode, $message);
                }

                $e = new Bitbull_Tooso_Exception($result->getErrorDescription(), $result->getErrorCode());
                $e->setDebugInfo($result->getErrorDebugInfo());
                $e->setResponse($result);
                throw $e;

            }

            return $result;

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
        $this->_logger->log(print_r($params, true));

        if (filter_var($this->_baseUrl, FILTER_VALIDATE_URL) === false) {
            $message = 'API base URL missing or invalid: "' . $this->_baseUrl . '"';

            if ($this->_reportSender) {
                $this->_reportSender->sendReport('', '', $this->_apiKey, $this->_language, $this->_storeCode, $message);
            }

            throw new Bitbull_Tooso_Exception($message, 0);
        }

        $baseUrl = $this->_baseUrl;
        if(substr($baseUrl, -1) != '/'){
            $baseUrl .= '/';
        }

        $url = $baseUrl . $this->_apiKey . $path;

        $language = $this->_language;
        if($language == null){
            $language = $this->_storeCode;
        }
        $queryString = array(
            'language=' . $language
        );

        foreach ($params as $key => $value) {
            $queryString[] = $key . '=' . urlencode($value);
        }

        $url .= '?' . implode('&', $queryString);

        return $url;
    }
}