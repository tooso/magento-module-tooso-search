<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_Tracking_LibraryInit extends Bitbull_Tooso_Block_Tracking
{
    const BLOCK_ID = 'tooso_tracking_library_init';
    const SCRIPT_ID = 'tooso-tracking-library-init';

    protected function _toHtml()
    {
        $debugMode = $this->_helper->isDebugMode();
        $trackingKey = $this->_helper->getTrackingKey();

        ob_start();
        ?>
        <script id='<?=self::SCRIPT_ID?>' type='text/javascript'>
            window.ta=window.ta||function(){(ta.q=ta.q||[]).push(arguments)};ta.l=+new Date;
            ta('create', '<?=$trackingKey?>', 'auto');
            <?php if($debugMode): ?>
            ta('set','debug','true');
            <?php endif; ?>
        </script>
        <?php
        return ob_get_clean();
    }
}