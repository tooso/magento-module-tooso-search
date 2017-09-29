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
        $session = Mage::getSingleton('adminhtml/session');
        
        if (Mage::getModel('tooso/indexer')->rebuildIndex()) {
            $session->addSuccess(Mage::helper('tooso')->__('Catalog data have been successfully sent to Tooso.'));
        } else {
            $session->addNotice(Mage::helper('tooso')->__('Can not sent data to Tooso, please see log for details.'));
        }
        
        $this->_redirect('*/system_config/edit', array('section' => 'tooso'));
        
        return;
    }

    /**
     * TailLog
     */
    public function taillogAction() {
        $path = Mage::getBaseDir('log').'/'.$this->_logger->getLogFile();
        $this->getResponse()->setBody(file_get_contents($path));
        return;
    }

    /**
     * ClearLog
     */
    public function clearlogAction() {
        $path = Mage::getBaseDir('log').'/'.$this->_logger->getLogFile();
        fclose(fopen($path,'w'));
        $this->getResponse()->setBody("");
        return;
    }
}