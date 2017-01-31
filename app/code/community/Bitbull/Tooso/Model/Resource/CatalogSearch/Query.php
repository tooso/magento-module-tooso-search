<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Gennaro Vietri <gennaro.vietri@bitbull.it>
*/ 
class Bitbull_Tooso_Model_Resource_CatalogSearch_Query extends Mage_CatalogSearch_Model_Resource_Query
{
    /**
     * Custom load model by search query string.
     * Extended to alter sort direction for synonym_for
     *
     * @param Mage_Core_Model_Abstract $object
     * @param string $value
     * @return Mage_CatalogSearch_Model_Resource_Query
     */
    public function loadByQuery(Mage_Core_Model_Abstract $object, $value)
    {
        if (!Mage::helper('tooso')->isSearchEnabled()) {
            return parent::loadByQuery($object, $value);
        }

        $readAdapter = $this->_getReadAdapter();
        $select = $readAdapter->select();

        $synonymSelect = clone $select;
        $synonymSelect
            ->from($this->getMainTable())
            ->where('store_id = ?', $object->getStoreId());

        $querySelect = clone $synonymSelect;
        $querySelect->where('query_text = ?', $value);

        $synonymSelect->where('synonym_for = ?', $value);

        $select->union(array($querySelect, "($synonymSelect)"), Zend_Db_Select::SQL_UNION_ALL)
            ->order('synonym_for ' . (Mage::helper('tooso')->isTypoCorrectedSearch() ? 'DESC' : 'ASC'))
            ->limit(1);

        $data = $readAdapter->fetchRow($select);
        if ($data) {
            $object->setData($data);
            $this->_afterLoad($object);
        }

        return $this;
    }
}