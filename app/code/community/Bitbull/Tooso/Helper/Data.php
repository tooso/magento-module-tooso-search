<?php
/**
 * @package Bitbull_Tooso
 * @author Gennaro Vietri <gennaro.vietri@bitbull.it>
 */

class Bitbull_Tooso_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Retrieve Tooso Log File
     * 
     * @return string
     */
    public function getLogFile()
    {
        return 'tooso_search.log';
    }
}