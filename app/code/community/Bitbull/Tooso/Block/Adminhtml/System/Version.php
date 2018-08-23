<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_Adminhtml_System_Version extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }

    protected function _toHtml()
    {
        $version = Mage::getConfig()->getNode()->modules->Bitbull_Tooso->version;
        $apiCompatibility = Mage::getConfig()->getNode()->modules->Bitbull_Tooso->api_compatibility;
        $notes = Mage::getConfig()->getNode()->modules->Bitbull_Tooso->notes;

        ob_start();
        ?>
        <tr>
            <td class="label">Module Version</td>
            <td class="value"><?=$version?></td>
        </tr>
        <tr>
            <td class="label">API Compatibility</td>
            <td class="value"><?=$apiCompatibility?></td>
        </tr>
        <tr>
            <td class="label">Notes</td>
            <td class="value"><?=$notes?></td>
        </tr>
        <?php
        return ob_get_clean();
    }
}