<?php
/**
 * @package Bitbull_Tooso
 * @author Gennaro Vietri <gennaro.vietri@bitbull.it>
 */

class Bitbull_Tooso_Block_Rebuild extends Mage_Adminhtml_Block_System_Config_Form_Field
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
        $url = $this->getUrl('*/tooso/rebuild');

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                     ->setType('button')
                     ->setClass('rebuild')
                     ->setLabel('Rebuild index')
                     ->setOnClick("setLocation('".$url."')")
                     ->toHtml();

        return $html;
    }
}