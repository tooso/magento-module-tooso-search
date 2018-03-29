<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Model_System_Config_Source_SearchResponseTypes
{
    /**
     * Return serch response type
     */
    public function toOptionArray() {
        return [
            "normal" => "Normal",
            "enriched" => "Enriched",
        ];
    }
}
