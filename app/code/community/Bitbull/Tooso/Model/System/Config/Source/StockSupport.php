<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Model_System_Config_Source_StockSupport
{
    /**
     * Return inventory stock system for backend multiselect options
     */
    public function toOptionArray() {
        return [
            [
                "label" => "Defaut (base Magento management)",
                "value" => "default"
            ],
            [
                "label" => "Multi Warehouse Inventory",
                "value" => "warehouse_store"
            ]
        ];
    }
}
