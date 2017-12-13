<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
class Bitbull_Tooso_Helper_Indexer extends Mage_Core_Helper_Abstract
{
    /**
     * @var array|null
     */
    protected $_excludeAttributes = null;
    /**
     * @var array|null
     */
    protected $_attributeFrontendTypes = null;
    /**
     * @var array|null
     */
    protected $_attributeBackendTypes = null;
    /**
     * @var array|null
     */
    protected $_customAttributes = null;
    /**
     * @var array|null
     */
    protected $_systemAttributes = null;
    /**
     * @var array|null
     */
    protected $_preserveAttributeValue = null;


    /**
     * Bitbull_Tooso_Model_System_Config_Source_Attributes constructor.
     */
    public function __construct()
    {
        /**
         * Exclude unused system attributes
         */
        $this->_excludeAttributes = array(
            'image_label',
            'old_id',
            'small_image_label',
            'thumbnail_label',
            'uf_product_link',
            'url_path',
            'custom_layout_update',
            'recurring_profile',
            'group_price',
            'is_recurring',
            'minimal_price',
            'msrp',
            'msrp_display_actual_price_type',
            'msrp_enabled',
            'options_container',
            'page_layout',
            'price_view',
            'country_of_manufacture',
            'gift_message_available',
            'tax_class_id',
            'tier_price',
            'custom_design'
        );

        /**
         * Attribute frontend types to load
         *
         * Excluding:
         *   'date'
         *   'media_image'
         *   'image'
         *   'gallery'
         */
        $this->_attributeFrontendTypes = array(
            'text',
            'textarea',
            'multiselect',
            'select',
            'boolean',
            'price'
        );


        /**
         * Attribute backend types to load
         *
         * Excluding:
         *   'date',
         *   'datetime',
         *   'static'
         */
        $this->_attributeBackendTypes = array(
            'varchar',
            'int',
            'text',
            'decimal',
        );

        /**
         * Custom indexer attributes
         */
        $this->_customAttributes = [
            "is_in_stock" => 'Is in stock',
            "variants" => 'Variants',
            "categories" => 'Categories',
            "image" => "Image",
            "qty" => "Stock Quantity"
        ];

        /**
         * System Attributes
         */

        $this->_systemAttributes = array(
            'sku' => 'sku',
            'name' => 'name',
            'description' => 'description',
            'short_description' => 'short_description',
            'status' => 'status'
        );

        /**
         * Not use getAttributeText to get value of data
         */
        $this->_preserveAttributeValue = array(
            'status',
            'visibility'
        );
    }

    public function getExcludeAttributes(){
        return $this->_excludeAttributes;
    }

    public function getAttributeFrontendTypes(){
        return $this->_attributeFrontendTypes;
    }

    public function getAttributeBackendTypes(){
        return $this->_attributeBackendTypes;
    }

    public function getCustomAttributes(){
        return $this->_customAttributes;
    }

    public function getSystemAttributes(){
        return $this->_systemAttributes;
    }

    public function getPreservedAttributeType(){
        return $this->_preserveAttributeValue;
    }

}