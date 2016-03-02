<?php
/**
 * @package Bitbull_Tooso
 * @author Gennaro Vietri <gennaro.vietri@bitbull.it>
 */

class Bitbull_Tooso_Block_Delete extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = $this->getUrl('*/tooso/clean');

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                     ->setType('button')
                     ->setClass('delete')
                     ->setLabel('Clean index')
                     ->setOnClick("setLocation('".$url."')")
                     ->toHtml();

        return $html;
    }
}