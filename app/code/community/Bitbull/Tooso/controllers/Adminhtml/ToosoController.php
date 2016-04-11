<?php
/**
 * @package Bitbull_Tooso
 * @author Gennaro Vietri <gennaro.vietri@bitbull.it>
 */
class Bitbull_Tooso_Adminhtml_ToosoController extends Mage_Adminhtml_Controller_Action
{
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
}