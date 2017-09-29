<?php
class Bitbull_Tooso_LogController extends Mage_Core_Controller_Front_Action {

    /**
     * @var Bitbull_Tooso_Helper_Log
     */
    protected $_logger = null;


    public function _construct(){
        parent::_construct();

        $this->_logger = Mage::helper('tooso/log');
    }

    /**
     * TailLog
     */
    public function tailAction() {
        $path = Mage::getBaseDir('log').'/'.$this->_logger->getLogFile();
        $this->getResponse()->setBody(file_get_contents($path));
        return;
    }

    /**
     * ClearLog
     */
    public function clearAction() {
        $path = Mage::getBaseDir('log').'/'.$this->_logger->getLogFile();
        fclose(fopen($path,'w'));
        $this->getResponse()->setBody("");
        return;
    }

}