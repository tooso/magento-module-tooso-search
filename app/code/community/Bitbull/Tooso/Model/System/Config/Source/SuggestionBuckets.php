<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Model_System_Config_Source_SuggestionBuckets
{
    /**
     * Return stores for backend multiselect options
     */
    public function toOptionArray() {
        return [
            [
                "label" => "Relevance",
                "value" => "relevance"
            ],
            [
                "label" => "Trending",
                "value" => "trending"
            ]
        ];
    }
}
