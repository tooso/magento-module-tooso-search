<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Gennaro Vietri <gennaro.vietri@bitbull.it>
*/
class Bitbull_Tooso_Model_Suggest
{
    const XML_PATH_SERVER_API_BASEURL_SUGGESTIONS = 'tooso/server/api_base_url_suggestions';

    /**
     * Client for API comunication
     *
     * @var Bitbull_Tooso_Client
     */
    protected $_client;

    /**
     * Represents a Tooso search result.
     *
     * @var Bitbull_Tooso_Suggest_Result
     */
    protected $_result;

    /**
     * @var Bitbull_Tooso_Helper_Log
     */
    protected $_logger = null;

    protected $_maxResults = 10;

    /**
     * Constructor, retrieve config for connection to Tooso API.
     */
    public function __construct()
    {
        $this->_client = Mage::helper('tooso')->getClient();

        $this->_logger = Mage::helper('tooso/log');

        $this->_maxResults = (int) Mage::helper('tooso')->getSuggestMaxResults();
    }

    /**
     * @param string $query
     * @return Bitbull_Tooso_Model_Suggest
     */
    public function suggest($query)
    {
        $query = urlencode($query);
        if ($query) {
            try {
                $params = Mage::helper('tooso')->getProfilingParams();
                $customBaseUrl = Mage::getStoreConfig(self::XML_PATH_SERVER_API_BASEURL_SUGGESTIONS);

                $result = $this->_client->suggest($query, $this->_maxResults, $params, $customBaseUrl);
                $this->setResult($result);
            } catch (Exception $e) {
                $this->_logger->logException($e);
            }
        }

        return $this;
    }

    /**
     * Set Tooso response
     *
     * @param Bitbull_Tooso_Suggest_Result $result
     */
    public function setResult(Bitbull_Tooso_Suggest_Result $result)
    {
        $this->_result = $result;
    }

    public function getSuggestions()
    {
        $suggestions = array();

        if (!is_null($this->_result)) {
            $results = $this->_result->getSuggestions();

            $customBaseUrl = Mage::getStoreConfig(self::XML_PATH_SERVER_API_BASEURL_SUGGESTIONS);
            if($customBaseUrl != null && $customBaseUrl != ""){
                foreach ($results as $suggestedString => $occurrence) {
                    $suggestions[] = new Varien_Object(array(
                        'query_text' => $suggestedString,
                        'num_results' => (int)$occurrence
                    ));
                }
            }else{
                foreach ($results as $occurrence => $suggestedString) {
                    $suggestions[] = new Varien_Object(array(
                        'query_text' => $suggestedString,
                        'num_results' => (int)$occurrence
                    ));
                }
            }

        }

        return $suggestions;
    }
}