<?php
/**
 * @package Bitbull_Tooso
 * @author Gennaro Vietri <gennaro.vietri@bitbull.it>
 */

class Bitbull_Tooso_Model_Indexer
{
    /**
     * Client for API comunication
     *
     * @var Bitbull_Tooso_Client
     */
    protected $_client;

    /**
     * @var Bitbull_Tooso_Helper_Log
     */
    protected $_logger = null;

    /**
     * @var Mage_ImportExport_Model_Export_Adapter_Csv
    */
    protected $_writer;

    public function __construct()
    {
        $this->_client = Mage::helper('tooso')->getClient();

        $this->_logger = Mage::helper('tooso/log');
    }

    /**
     * Rebuild Tooso Index
     *
     * @return boolean
    */
    public function rebuildIndex()
    {
        $data = $this->_getStoresByLang();
        foreach ($data as $lang => $stores) {
            foreach ($stores as $storeId) {
                try {
                    $this->_client->index($this->_getCsvContent($storeId));
                } catch (Exception $e) {
                    $this->_logger->logException($e);

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Clean Tooso Index
     *
     * @todo Should be implemented, but so far Tooso don't support index cleaning
     *
     * @return boolean
    */
    public function cleanIndex()
    {
        return true;
    }

    /**
     * return string
    */
    protected function _getCsvContent($storeId)
    {
        /*
        $this->_logger->debug('Start generating CSV content');

        $model = Mage::getModel('importexport/export');

        $this->_logger->debug('Export Model class: ' . get_class($model));

        $model->setData(array(
            'entity' => 'catalog_product',
            'file_format' => 'tooso_csv',
            'export_filter' => array(),
        ));

        $csvContent = $model->export();

        $this->_logger->debug('End generating CSV content');

        return $csvContent;*/

        // Elenco di attributi standard di Magento da non tradurre
        $excludeAttributes = array(
            'image_label',
            'old_id',
            'small_image_label',
            'thumbnail_label',
            'uf_product_link',
            'url_path',
        );

        $attributes = array('sku' => 'SKU');
        $headers = array('sku' => 'sku');

        // Recupero tutti gli attributi di tipo testo, cercando di escludere quelli di sistema
        $attributesCollection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addFieldToFilter('frontend_input', array('in' => array('textarea', 'text')))
            ->addFieldToFilter('backend_model', array('null' => true))
            ->addFieldToFilter('backend_type', array('neq' => 'static'))
            ->addFieldToFilter('attribute_code', array('nin' => $excludeAttributes))
        ;

        $productCollection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addStoreFilter($storeId)
            ->addAttributeToSelect('sku')
        ;

        foreach ($attributesCollection as $attribute) {
            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
            $headers[$attribute->getAttributeCode()] = $attribute->getAttributeCode();

            $productCollection->addAttributeToSelect($attribute->getAttributeCode());

            // Occorre mettere in join esplicitamente gli attributi in base allo store id
            // addStoreFilter sulla collection sembra impattare solo l'associazione prodotto <-> website
            $productCollection->joinAttribute(
                $attribute->getAttributeCode(),
                'catalog_product/' . $attribute->getAttributeCode(),
                'entity_id',
                null,
                'left',
                $storeId
            );
        }

        $this->_getWriter()->setHeaderCols($headers);

        foreach ($productCollection as $product) {
            $row = array();
            foreach ($attributes as $attributeCode => $attributeLabel) {
                $row[$attributeCode] = $product->getData($attributeCode);
            }

            $this->_getWriter()->writeRow($row);
        }

        return $this->_getWriter()->getContents();
    }

    /**
     * Get stores grouped by lang code
     * @return array stores
     */
    protected function _getStoresByLang()
    {
        $stores = array();
        $collection = Mage::getModel('core/store')->getCollection();
        foreach ($collection as $store) {
            $lang= Mage::getStoreConfig('general/locale/code', $store->getId());
            if (!isset($stores[$lang])) {
                $stores[$lang] = array();
            }
            array_push($stores[$lang], $store->getId());
        }

        return $stores;
    }

    /**
     * @return Mage_ImportExport_Model_Export_Adapter_Csv
    */
    protected function _getWriter()
    {
        if (!$this->_writer) {
            $this->_writer = Mage::getModel('importexport/export_adapter_csv', array());
        }

        return $this->_writer;
    }

    /**
     * Return stores for backend multiselect options
     */
    public function toOptionArray() {
        return Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true);
    }
}
