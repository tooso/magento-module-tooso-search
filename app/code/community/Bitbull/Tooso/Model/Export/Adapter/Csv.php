<?php
/**
 * @package Bitbull_Tooso
 * @author Gennaro Vietri <gennaro.vietri@bitbull.it>
*/
class Bitbull_Tooso_Model_Export_Adapter_Csv extends Mage_ImportExport_Model_Export_Adapter_Csv
{
    /**
     * Close file handler on shutdown
     * and delete the temporary file
     */
    public function destruct()
    {
        if (is_resource($this->_fileHandler)) {
            fclose($this->_fileHandler);
        }

        if (file_exists($this->_destination)) {
            unlink($this->_destination);
        }
    }
}