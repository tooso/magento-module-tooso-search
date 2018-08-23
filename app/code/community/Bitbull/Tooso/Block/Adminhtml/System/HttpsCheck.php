<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_Adminhtml_System_HttpsCheck extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }

    protected function _toHtml()
    {
        $baseUrl = Mage::getBaseUrl();

        ob_start();
        if (strpos($baseUrl, 'https://') !== 0) {
            ?>
            <tr>
                <td style="color: #d20000;" colspan="2" class="label">WARNING: Your website not use HTTPS for all the pages, this feature will not works properly</td>
            </tr>
            <?php
        }
        return ob_get_clean();

    }
}