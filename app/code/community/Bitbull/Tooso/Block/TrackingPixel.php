<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_TrackingPixel extends Mage_Core_Block_Template
{
    protected $_tracking_url = null;

    protected function _toHtml()
    {
        ob_start();
        ?>

        <!-- Tooso tracking pixel -->
        <script type='text/javascript'>
            var trackingScript = document.createElement('script');
            trackingScript.type = 'text/javascript';
            trackingScript.src = '<?=$this->_tracking_url?>';
            document.getElementsByTagName('body')[0].appendChild(trackingScript);
        </script>
        <noscript>
            <img id='tooso-tracking-pixel' style='display:none' src='<?=$this->_tracking_url?>'>
        </noscript>

        <?php
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    public function setTrackingURL($tracking_url)
    {
        $this->_tracking_url = $tracking_url;
    }
}