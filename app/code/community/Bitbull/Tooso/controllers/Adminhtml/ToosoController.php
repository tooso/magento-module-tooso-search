<?php
/**
 * @package Bitbull_Tooso
 * @author Gennaro Vietri <gennaro.vietri@bitbull.it>
 */
class Bitbull_Tooso_Adminhtml_ToosoController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @var Bitbull_Tooso_Helper_Log
     */
    protected $_logger = null;


    public function _construct()
    {
        parent::_construct();

        $this->_logger = Mage::helper('tooso/log');
    }

    /**
     * Rebuild action
     */
    public function rebuildAction()
    {
        $this->_logger->debug("Requested manual rebuild action");
        if (Mage::getModel('tooso/indexer')->rebuildIndex()) {
            $this->getResponse()->setBody(Mage::helper('tooso')->__('Catalog data have been successfully sent to Tooso.'));
        } else {
            $this->getResponse()->setBody(Mage::helper('tooso')->__('Can not sent data to Tooso, please see log for details.'));
        }
        return;
    }

    /**
     * TailLog
     */
    public function taillogAction() {
        $this->_logger->debug("Requested log file");
        $path = Mage::getBaseDir('log').'/'.$this->_logger->getLogFile();
        $this->getResponse()->setBody(file_get_contents($path));
        return;
    }

    /**
     * ClearLog
     */
    public function clearlogAction() {
        $this->_logger->debug("Requested log clean action");
        $path = Mage::getBaseDir('log').'/'.$this->_logger->getLogFile();
        fclose(fopen($path,'w'));
        $this->getResponse()->setBody("");
        return;
    }
}