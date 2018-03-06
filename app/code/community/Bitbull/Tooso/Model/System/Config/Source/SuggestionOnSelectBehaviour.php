<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Model_System_Config_Source_SuggestionOnSelectBehaviour
{
    /**
     * Return stores for backend multiselect options
     */
    public function toOptionArray() {
        return [
            [
                "label" => "Submit the form",
                "value" => "submit"
            ],
            [
                "label" => "Do nothing",
                "value" => "nothing"
            ],
            [
                "label" => "Custom",
                "value" => "custom"
            ]
        ];
    }
}
