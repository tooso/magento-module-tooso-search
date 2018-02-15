<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_Tracking_PageView extends Bitbull_Tooso_Block_Tracking
{
    const BLOCK_ID = 'tooso_tracking_pageview';
    const SCRIPT_ID = 'tooso-tracking-pageview';

    protected function _toHtml()
    {
        ob_start();
        ?>
        <script id='<?=self::SCRIPT_ID?>' type='text/javascript'>
            ta('send', 'pageview');
        </script>
        <?php
        return ob_get_clean();
    }
}