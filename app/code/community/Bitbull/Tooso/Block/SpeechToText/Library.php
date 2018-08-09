<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_SpeechToText_Library extends Bitbull_Tooso_Block_SpeechToText
{
    const BLOCK_ID = 'tooso_tooso_speech_to_text_library';
    const SCRIPT_ID = 'tooso-tooso-speech-to-text-library';

    protected function _toHtml()
    {
        $endpoint = $this->_helper->getJSLibraryEndpoint();
        $this->_logger->debug('including speech to text library from '.$endpoint);

        ob_start();
        ?>
        <script id='<?=self::SCRIPT_ID?>' type='text/javascript'>
            (function (d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) return;
                js = d.createElement(s);
                js.id = id;
                js.src = "<?=$endpoint?>";
                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'tooso-jssdk'));
        </script>
        <?php
        return ob_get_clean();

    }
}