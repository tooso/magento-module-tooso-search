<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Gennaro Vietri <gennaro.vietri@bitbull.it>
 */
class Bitbull_Tooso_Helper_Log_Send extends Mage_Core_Helper_Abstract implements Bitbull_Tooso_Log_SendInterface
{
    const DEBUG_EMAIL_ADDRESS = 'gennaro.vietri@bitbull.it';

    const DEBUG_EMAIL_ADDRESS_CC = 'kesonno@gmail.com';

    const XML_PATH_SEND_REPORT = 'tooso/server/send_report';

    /**
     * Send report via email
     *
     * @param string $url
     * @param string $httpMethod
     * @param string $apiKey
     * @param string $language
     * @param string $message
     */
    public function sendReport($url, $httpMethod, $apiKey, $language, $message)
    {
        $sendReport = Mage::getStoreConfigFlag(self::XML_PATH_SEND_REPORT);

        if ($sendReport) {

            $reportSubject = 'Magento / Tooso - API Error occurred';
            $reportText = "An error occurred \n\n"
                . "Information for current environment:\n\n"
                . "- Date: " . Mage::getModel('core/date')->date('Y-m-d H:i:s') . "\n"
                . "- Store name: " . Mage::app()->getStore()->getFrontendName() . "\n"
                . "- Current URL: " . Mage::helper('core/url')->getCurrentUrl() . "\n"
                . "- API language: " . $language . "\n"
                . "- API key: " . $apiKey . "\n\n"
                . "The following API call was performed (with HTTP method " . $httpMethod . "):\n\n"
                . $url . "\n\n"
                . "The result was : \n\n"
                . $message
            ;

            $mail = new Zend_Mail();

            $mail
                ->setFrom(self::DEBUG_EMAIL_ADDRESS, 'Tooso report')
                ->addTo(self::DEBUG_EMAIL_ADDRESS, 'Tooso report')
                ->addTo(self::DEBUG_EMAIL_ADDRESS_CC)
                ->setBodyText($reportText)
                ->setSubject($reportSubject)
            ;

            try {
                $mail->send();
            }
            catch(Exception $error) {}
        }
    }
}