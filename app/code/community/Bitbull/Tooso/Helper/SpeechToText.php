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

    const XML_PATH_SPEECHTOTEXT_INPUT = 'tooso/speech_to_text/input_selector';
    const XML_PATH_SPEECHTOTEXT_LANGUAGE = 'tooso/speech_to_text/language';
    const XML_PATH_SPEECHTOTEXT_EXAMPLE_TEMPLATE = 'tooso/speech_to_text/example_template';

    /**
     * Get javascript library initialization params
     *
     * @param null $store
     * @return array
     */
    public function getInitParams($store = null)
    {
        $data = [];

        $language = Mage::getStoreConfig(self::XML_PATH_SPEECHTOTEXT_LANGUAGE, $store);
        if($language != null){
            $data['language'] = $language;
        }

        $inputSelector = Mage::getStoreConfig(self::XML_PATH_SPEECHTOTEXT_INPUT, $store);
        if($inputSelector != null){
            $data['input'] = $inputSelector;
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
        return Mage::helper('tooso')->isSpeechToTextEnabled() && Mage::getStoreConfigFlag(self::XML_PATH_SPEECHTOTEXT_EXAMPLE_TEMPLATE, $store);
    }

}
