<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
class Bitbull_Tooso_Helper_SpeechToText extends Mage_Core_Helper_Abstract
{
    const CONTAINER_BLOCK_AFTER = 'after_body_start';
    const CONTAINER_BLOCK_BEFORE = 'before_body_end';

    const XML_PATH_SPEECHTOTEXT_CORE_KEY = 'tooso/speech_to_text/key';
    const XML_PATH_SPEECHTOTEXT_LIBRARY_ENDPOINT = 'tooso/speech_to_text/library_endpoint';
    const XML_PATH_SPEECHTOTEXT_LANGUAGE = 'tooso/speech_to_text/language';
    const XML_PATH_SPEECHTOTEXT_EXAMPLE_TEMPLATE = 'tooso/speech_to_text/example_template';

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
        $block = $layout->createBlock('tooso/speechToText_library');
        return $block;
    }

    /**
     * Create Speech to Text Library Init Block
     *
     * @return Bitbull_Tooso_Block_SpeechToText_LibraryInit
     */
    public function getLibraryInitBlock()
    {
        $layout = Mage::app()->getLayout();
        $block = $layout->createBlock('tooso/speechToText_libraryInit');
        return $block;
    }

    /**
     * Get Speech to Text library endpoint
     *
     * @param null $store
     * @return mixed
     */
    public function getJSLibraryEndpoint($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_SPEECHTOTEXT_LIBRARY_ENDPOINT, $store);
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
            'speech' => [
                'input' => Mage::helper('tooso/suggestion')->getSuggestionInputSelector()
            ],
        ];

        $coreKey = Mage::getStoreConfig(self::XML_PATH_SPEECHTOTEXT_CORE_KEY, $store);
        if($coreKey != null){
            $data['coreKey'] = $coreKey;
        }else{
            $data['coreKey'] = Mage::helper('tooso')->getApiKey();
        }

        $language = Mage::getStoreConfig(self::XML_PATH_SPEECHTOTEXT_LANGUAGE, $store);
        if($language != null){
            $data['language'] = $language;
        }else{
            $data['language'] = Mage::app()->getLocale()->getLocaleCode();
        }

        return $data;
    }

    /**
     * Include Example Template
     *
     * @param null $store
     * @return bool
     */
    public function includeExampleTemplate($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_SPEECHTOTEXT_EXAMPLE_TEMPLATE, $store);
    }

}