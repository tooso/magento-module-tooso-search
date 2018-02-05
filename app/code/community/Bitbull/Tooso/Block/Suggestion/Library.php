<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_Suggestion_Library extends Bitbull_Tooso_Block_Suggestion
{
    const BLOCK_ID = 'tooso_suggestion_library';
    const SCRIPT_ID = 'tooso-suggestion-library';

    protected function _toHtml()
    {
        $endpoint = $this->_helper->getSuggestionJSLibraryEndpoint();
        $this->_logger->debug('including suggestion library from '.$endpoint);

        ob_start();
        ?>
        <script id='<?=self::SCRIPT_ID?>' async type='text/javascript' onload="ts_suggestion_callback();" src='<?=$endpoint?>'></script>
        <?php
        return ob_get_clean();

    }
}