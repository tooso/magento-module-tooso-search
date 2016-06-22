<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Gennaro Vietri <gennaro.vietri@bitbull.it>
 */
class Bitbull_Tooso_Helper_Log_Send extends Mage_Core_Helper_Abstract implements Bitbull_Tooso_Log_SendInterface
{
    const DEBUG_EMAIL_ADDRESS = 'alert@tooso.ai';

    const DEBUG_EMAIL_ADDRESS_CC = 'gennaro.vietri@bitbull.it';

    const XML_PATH_SEND_REPORT = 'tooso/server/send_report';

    const EMAIL_TEMPLATE = 'tooso_alert_email_template';

    /**
     * Send report via email
     *
     * @param string $url
     * @param string $httpMethod
     * @param string $apiKey
     * @param string $language
     * @param string $storeCode
     * @param string $message
     */
    public function sendReport($url, $httpMethod, $apiKey, $language, $storeCode, $message)
    {
        $sendReport = Mage::getStoreConfigFlag(self::XML_PATH_SEND_REPORT);

        if ($sendReport) {

            $data = array(
                'currentDate' => Mage::getModel('core/date')->date('Y-m-d H:i:s'),
                'storeName' => Mage::app()->getStore()->getFrontendName(),
                'currentUrl' => Mage::helper('core/url')->getCurrentUrl(),
                'language' => $language,
                'storeCode' => $storeCode,
                'apiKey' => $apiKey,
                'url' => $url,
                'message' => $message,
                'httpMethod' => $httpMethod,
            );

            $dataObject = new Varien_Object();
            $dataObject->setData($data);

            /* @var $mailTemplate Mage_Core_Model_Email_Template */
            $mailTemplate = Mage::getModel('core/email_template');

            try {
                $mailTemplate
                    ->setDesignConfig(array('area' => 'admin'))
                    ->sendTransactional(
                        self::EMAIL_TEMPLATE,
                        array(
                            'name' => 'Tooso report',
                            'email' => self::DEBUG_EMAIL_ADDRESS,
                        ),
                        array(
                            self::DEBUG_EMAIL_ADDRESS,
                            self::DEBUG_EMAIL_ADDRESS_CC
                        ),
                        null,
                        array('data' => $dataObject)
                    );
            }
            catch(Exception $error) {}
        }
    }
}