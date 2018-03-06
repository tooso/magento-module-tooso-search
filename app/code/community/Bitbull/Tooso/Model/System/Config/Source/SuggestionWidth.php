<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Model_System_Config_Source_SuggestionWidth
{
    /**
     * Return stores for backend multiselect options
     */
    public function toOptionArray() {
        return [
            [
                "label" => "Auto (input field width)",
                "value" => "auto"
            ],
            [
                "label" => "Flex (max suggestion size)",
                "value" => "flex"
            ],
            [
                "label" => "Custom pixel value",
                "value" => "custom"
            ]
        ];
    }
}
