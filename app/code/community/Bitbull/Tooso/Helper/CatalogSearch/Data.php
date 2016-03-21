<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Gennaro Vietri <gennaro.vietri@bitbull.it>
*/ 
class Bitbull_Tooso_Helper_CatalogSearch_Data extends Mage_CatalogSearch_Helper_Data
{
    /**
     * Retrieve query model object
     *
     * @return Mage_CatalogSearch_Model_Query
     */
    public function getQuery()
    {
        if (!$this->_query) {
            if (Mage::helper('tooso')->isTypoCorrectedSearch()) {
                $this->_query = Mage::getModel('catalogsearch/query')
                    ->loadByQuery($this->getQueryText());
            } else {
                $this->_query = Mage::getModel('catalogsearch/query')
                    ->loadByQueryText($this->getQueryText());
            }

            if (!$this->_query->getId()) {
                $this->_query->setQueryText($this->getQueryText());
            }
        }
        return $this->_query;
    }


}