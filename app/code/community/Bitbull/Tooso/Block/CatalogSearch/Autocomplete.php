<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Gennaro Vietri <gennaro.vietri@bitbull.it>
*/ 
class Bitbull_Tooso_Block_CatalogSearch_Autocomplete extends Mage_CatalogSearch_Block_Autocomplete
{
    public function getSuggestData()
    {
        Mage::helper('tooso/profiler')->start('tooso::suggestion');
        if (!$this->helper('tooso')->isSearchEnabled()) {
            Mage::helper('tooso/profiler')->stop('tooso::suggestion');
            return parent::getSuggestData();
        }

        if (!$this->_suggestData) {
            $query = $this->helper('catalogsearch')->getQueryText();
            $suggest = Mage::getModel('tooso/suggest')->suggest($query);
            $counter = 0;
            $data = array();
            foreach ($suggest->getSuggestions() as $item) {
                $_data = array(
                    'title' => $item->getQueryText(),
                    'row_class' => (++$counter)%2?'odd':'even',
                    'num_of_results' => null
                );

                if ($item->getQueryText() == $query) {
                    array_unshift($data, $_data);
                }
                else {
                    $data[] = $_data;
                }
            }
            $this->_suggestData = $data;
        }

        Mage::helper('tooso/profiler')->stop('tooso::suggestion');
        return $this->_suggestData;
    }
}