<?php
/**
 * @package Bitbull_Tooso
 * @author Gennaro Vietri <gennaro.vietri@bitbull.it>
 */

class Bitbull_Tooso_Model_Indexer
{
    /**
     * Client for API comunication
     *
     * @var Bitbull_Tooso_Client
     */
    protected $_client;

    /**
     * @var Bitbull_Tooso_Helper_Log
     */
    protected $_logger = null;

    public function __construct()
    {
        $apiKey = Mage::getStoreConfig('tooso/server/api_key');
        $language = 'en'; // @todo make configurable

        $this->_client = new Bitbull_Tooso_Client($apiKey, $language);

        $this->_logger = Mage::helper('tooso/log');
    }

    /**
     * Rebuild Tooso Index
     *
     * @return boolean
    */
    public function rebuildIndex()
    {
        try {
            $this->_client->index($this->_getCsvContent());
        } catch (Exception $e) {
            $this->_logger->logException($e);

            return false;
        }

        return true;
    }
    
    /**
     * Clean Tooso Index
     *
     * @todo Should be implemented, but so far Tooso don't support index cleaning
     *
     * @return boolean
    */
    public function cleanIndex()
    {
        return true;
    }

    /**
     * return string
    */
    protected function _getCsvContent()
    {
        /** @var $model Mage_ImportExport_Model_Export */
        $model = Mage::getModel('importexport/export');
        $model->setData(array(
            'entity' => 'catalog_product',
            'file_format' => 'csv',
            'export_filter' => array(),
        ));

        return $model->export();
    }
}