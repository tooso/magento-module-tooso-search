<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Gennaro Vietri <gennaro.vietri@bitbull.it>
*/ 
class Bitbull_Tooso_Model_Resource_CatalogSearch_Fulltext_Collection extends Mage_CatalogSearch_Model_Resource_Fulltext_Collection
{
    /**
     * Add search query filter
     *
     * @param string $query
     * @return Mage_CatalogSearch_Model_Resource_Fulltext_Collection
     */
    public function addSearchFilter($query)
    {
        Mage::getSingleton('catalogsearch/fulltext')->prepareResult();

        $products = Mage::helper('tooso')->getProducts();
        $this->addFieldToFilter('entity_id', array('in' => (sizeof($products) > 0) ? $products : array(0)));

        return $this;
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
        if ($attribute == 'relevance') {
            $products = Mage::helper('tooso')->getProducts();
            if (sizeof($products) > 0) {
                $this->getSelect()->order(new Zend_Db_Expr('FIELD(e.entity_id, ' . implode(',', $products) . ')'));
            }
        } else {
            parent::setOrder($attribute, $dir);
        }
        return $this;
    }
}