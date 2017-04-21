<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Model_System_Config_Backend_Attributes extends Mage_Core_Model_Config_Data
{
    protected $_limitAttributeCount = 35;

    public function save()
    {
        $value = $this->getValue();

        if(sizeof($value) > $this->_limitAttributeCount){
            Mage::throwException("Tooso Search Engine: Indexer has too many attributes");
            return;
        }

        return parent::save();
    }
}
