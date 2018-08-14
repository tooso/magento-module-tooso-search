<?php
/**
 * @package Bitbull_Tooso
 * @author Gennaro Vietri <gennaro.vietri@bitbull.it>
 */

class Bitbull_Tooso_Helper_Sdk extends Mage_Core_Helper_Abstract
{
    const CONTAINER_BLOCK_AFTER = 'after_body_start';
    const CONTAINER_BLOCK_BEFORE = 'before_body_end';

    const XML_PATH_SDK_LIBRARY_ENDPOINT = 'tooso/sdk/library_endpoint';
    const XML_PATH_SDK_CORE_KEY = 'tooso/sdk/core_key';
    const XML_PATH_SDK_LANGUAGE = 'tooso/sdk/language';
    const XML_PATH_SDK_INPUT = 'tooso/sdk/input_selector';
    const XML_PATH_SDK_DEBUG = 'tooso/sdk/debug_mode';

    /**
     * Get SDK library endpoint
     *
     * @param null $store
     * @return mixed
     */
    public function getJSLibraryEndpoint($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_SDK_LIBRARY_ENDPOINT, $store);
    }

    /**
     * Get block to append suggestion library
     *
     */
    public function getScriptContainerBlock()
    {
        $layout = Mage::app()->getLayout();
        return $layout->getBlock(self::CONTAINER_BLOCK_BEFORE);
    }

    /**
     * Create Speech to Text Library Block
     *
     * @return Bitbull_Tooso_Block_SpeechToText_Library
     */
    public function getLibraryBlock()
    {
        $layout = Mage::app()->getLayout();
        $block = $layout->createBlock('tooso/sdk_library');
        return $block;
    }

    /**
     * Get block to append init suggestion library
     *
     */
    public function getInitScriptContainerBlock()
    {
        $layout = Mage::app()->getLayout();
        return $layout->getBlock(self::CONTAINER_BLOCK_AFTER);
    }

    /**
     * Create Speech to Text Library Init Block
     *
     * @return Bitbull_Tooso_Block_SpeechToText_LibraryInit
     */
    public function getLibraryInitBlock()
    {
        $layout = Mage::app()->getLayout();
        $block = $layout->createBlock('tooso/sdk_libraryInit');
        return $block;
    }

    /**
     * Get javascript library initialization params
     *
     * @param null $store
     * @return array
     */
    public function getInitParams($store = null)
    {
        $data = [
            'debug' => Mage::getStoreConfigFlag(self::XML_PATH_SDK_DEBUG, $store)
        ];

        $coreKey = Mage::getStoreConfig(self::XML_PATH_SDK_CORE_KEY, $store);
        if($coreKey != null){
            $data['coreKey'] = $coreKey;
        }else{
            $data['coreKey'] = Mage::helper('tooso')->getApiKey();
        }

        $language = Mage::getStoreConfig(self::XML_PATH_SDK_LANGUAGE, $store);
        if($language != null){
            $data['language'] = $language;
        }else{
            $data['language'] = Mage::app()->getLocale()->getLocaleCode();
        }

        $inputSelector = Mage::getStoreConfig(self::XML_PATH_SDK_INPUT, $store);
        if($inputSelector != null){
            $data['input'] = $inputSelector;
        }else{
            $data['input'] = Mage::helper('tooso/suggestion')->getSuggestionInputSelector();
        }

        // SDK functionalities properties

        if (Mage::helper('tooso')->isSpeechToTextEnabled($store)) {
            $data['speech'] = Mage::helper('tooso/speechToText')->getInitParams($store);
        }

        return $data;
    }
}
