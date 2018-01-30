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
     * API version
     *
     * @var null|string
     */
    protected $_version;

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
     * @param string $version
     * @param string $apiBaseUrl
     * @param string $language
     * @param string $storeCode
     * @param Bitbull_Tooso_Log_LoggerInterface $logger
    */
    public function __construct($apiKey, $version, $apiBaseUrl, $language, $storeCode)
    {
        $this->_apiKey = $apiKey;
        $this->_version = $version;
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
            array('q' => $query, 'typoCorrection' => ($typoCorrection ? 'true' : 'false')),
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
    public function suggest($query, $limit = 10, $extraParams = array())
    {
        $query = str_replace(array("+", "%2B"), " ", $query);
        $path = '/suggest';
        $params = array_merge(
            array('q' => $query, 'limit' => $limit),
            (array)$extraParams
        );

        $response = $this->_doRequest($path, self::HTTP_METHOD_GET, $params);

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
    public function index($csvContent, $params)
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

        if(!isset($params["ACCESS_KEY_ID"]) || !isset($params["SECRET_KEY"]) || !isset($params["BUCKET"]) || !isset($params["PATH"])){
            throw new Bitbull_Tooso_Exception('Index params are not correct', 0);
        }

        $accessKeyId = $params["ACCESS_KEY_ID"];
        $secretKey = $params["SECRET_KEY"];
        $bucket = $params["BUCKET"];
        $region = 'us-west-2';
        $fileName = $params["PATH"].round(microtime(true) * 1000)."_".$this->getUuid()."_".$this->_apiKey.'.zip';
        $fileType = 'application/zip';

        $policy = base64_encode(json_encode(array(
            'expiration' => gmdate('Y-m-d\TH:i:s\Z', time() + 86400),
            'conditions' => array(
                array('bucket' => $bucket),
                array('starts-with', '$key', ''),
                array('starts-with', '$Content-Type', '')
            )
        )));

        $signature = hash_hmac('sha1', $policy, $secretKey, true);
        $signature = base64_encode($signature);

        $url = 'https://' . $bucket . '.s3-' . $region . '.amazonaws.com';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            'key' => $fileName,
            'AWSAccessKeyId' =>  $accessKeyId,
            'policy' =>  $policy,
            'Content-Type' =>  $fileType,
            'signature' => $signature,
            'file' => new CurlFile(realpath($tmpZipFile), $fileType, $fileName)
        ));
        $response = curl_exec($ch);

        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 204) {
            if($this->_logger){
                $this->_logger->debug("End uploading zipfile to s3://".$bucket."/".$fileName);
            }
        } else {
            $error = substr($response, strpos($response, '<Code>') + 6);
            $error = substr($error, 0, strpos($error, '</Code>'));

            if ($this->_reportSender) {
                $message = 'Error description = ' . $error;
                $this->_reportSender->sendReport($url, "PUT", $accessKeyId, $this->_language, $this->_storeCode, $message);
            }

            $e = new Bitbull_Tooso_Exception($error, curl_getinfo($ch, CURLINFO_HTTP_CODE));
            $e->setDebugInfo($response);
            throw $e;
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
    protected function _doRequest($path, $httpMethod = self::HTTP_METHOD_GET, $params = array(), $timeout = null)
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

        $url = $baseUrl . $path;

        $language = $this->_language;
        if($language == null){
            $language = $this->_storeCode;
        }
        $queryString = array(
            'ul=' . $language,
            'tid=' . $this->_apiKey,
            'v=' . $this->_version,
        );

        foreach ($params as $key => $value) {
            $queryString[] = $key . '=' . urlencode($value);
        }

        $url .= '?' . implode('&', $queryString);

        return $url;
    }

    /**
     * Generate uuid
     *
     * @return string
     */
    public function getUuid(){
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0x0fff ) | 0x4000,
            mt_rand( 0, 0x3fff ) | 0x8000,
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }
}