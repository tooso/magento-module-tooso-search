<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Gennaro Vietri <gennaro.vietri@bitbull.it>
*/ 
class Bitbull_Tooso_Model_Resource_CatalogSearch_Fulltext_Collection extends Mage_CatalogSearch_Model_Resource_Fulltext_Collection
{
    /**
     * Filter database query using the products ids
     * retrieved from API call.
     *
     * @param string $query
     * @return Mage_CatalogSearch_Model_Resource_Fulltext_Collection
     */
    public function addSearchFilter($query)
    {
        if (!Mage::helper('tooso')->isSearchEnabled()) {
            return parent::addSearchFilter($query);
        }

        Mage::getSingleton('catalogsearch/fulltext')->prepareResult();

        $products = Mage::helper('tooso')->getProducts();
        if($products != null){
            $this->addFieldToFilter('entity_id', array('in' => (sizeof($products) > 0) ? $products : array(0)));
            return $this;
        }else{
            return parent::addSearchFilter($query);
        }

    }

    /**
     * Set Order field
     *
     * @param string $attribute
     * @param string $dir
     * @return Mage_CatalogSearch_Model_Resource_Fulltext_Collection
     */
    public function setOrder($attribute, $dir = 'desc')
    {

        if (!Mage::helper('tooso')->isSearchEnabled()) {
            return parent::setOrder($attribute, $dir);
        }

        Mage::helper('tooso/session')->setSearchOrder($attribute.'_'.$dir);

        $products = Mage::helper('tooso')->getProducts();
        if($products == null){
            return parent::setOrder($attribute, $dir);
        }else{
            if ($attribute == 'relevance' || $attribute == 'position') {
                $products = Mage::helper('tooso')->getProducts();

                // If the order criteria is the relevance, we need to respect the order of products ids given by API call
                if (sizeof($products) > 0) {
                    $this->getSelect()->order(new Zend_Db_Expr('FIELD(e.entity_id, ' . implode(',', $products) . ')'));
                }
            } else {
                parent::setOrder($attribute, $dir);
            }
            return $this;
        }

    }

    /**
     * Needed for Magento version >= 1.9.3.1
     * Get found products ids
     *
     * @return array
     */
    public function getFoundIds()
    {
        $products = Mage::helper('tooso')->getProducts();

        if (!Mage::helper('tooso')->isSearchEnabled() || $products == null) {
            return parent::getFoundIds();
        }

        if (is_null($this->_foundData)) {
            /** @var Mage_CatalogSearch_Model_Fulltext $preparedResult */
            $preparedResult = Mage::getSingleton('catalogsearch/fulltext');
            $preparedResult->prepareResult();
            $productsIds = Mage::helper('tooso')->getProducts();
            $this->_foundData = $productsIds ? array_flip($productsIds) : array();
        }
        if (isset($this->_orders[self::RELEVANCE_ORDER_NAME])) {
            $this->_resortFoundDataByRelevance();
        }
        return array_keys($this->_foundData);
    }
}