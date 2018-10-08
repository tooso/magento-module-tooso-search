<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_Tracking_CustomerTracking extends Bitbull_Tooso_Block_Tracking
{
    const BLOCK_ID = 'tooso_tracking_customer';
    const SCRIPT_ID = 'tooso-tracking-customer';

    protected function _toHtml()
    {
        if ($this->_helper->isUserIdTrakingEnable() === false || Mage::getSingleton('customer/session')->isLoggedIn() === false){
            return;
        }

        $customerId = Mage::getSingleton('customer/session')->getCustomerId();

        ob_start();
        ?>
        <script id='<?=self::SCRIPT_ID?>' type='text/javascript'>
            ta('set', 'userId', '<?=$customerId?>');
        </script>
        <?php
        return ob_get_clean();
    }
}