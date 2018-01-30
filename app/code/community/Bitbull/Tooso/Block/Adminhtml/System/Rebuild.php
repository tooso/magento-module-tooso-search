<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_Adminhtml_System_Rebuild extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for button
     *
     * @return string
     */
    public function getAjaxCheckUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/tooso/rebuild');
    }

    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'id'        => 'tooso_rebuild',
                'label'     => $this->helper('adminhtml')->__('Reindex'),
                'onclick'   => 'javascript:toosoRebuild(); return false;'
            ));

        return $button->toHtml();
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }

    protected function _toHtml()
    {
        ob_start();
        ?>
        <script type="text/javascript">
            //<![CDATA[
            function toosoRebuild() {
                new Ajax.Request('<?php echo $this->getAjaxCheckUrl() ?>', {
                    method:  'get',
                    onSuccess: function(transport){
                        alert(transport.responseText);
                    }
                });
            }
            //]]>
        </script>
        <tr>
            <td class="label">Reindex catalog</td>
            <td class="value"><?=$this->getButtonHtml() ?></td>
        </tr>
        <?php
        return ob_get_clean();
    }
}