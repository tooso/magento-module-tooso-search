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
        <style>
            .autocomplete-suggestions { border-width: 0px 1px 1px 1px; border-color: #FFCC33; border-style: solid; background: #FFF; overflow: auto; font-size: 13px; padding: 10px 15px; text-transform: uppercase; }
            .autocomplete-suggestion { padding: 2px 5px; white-space: nowrap; overflow: hidden; }
            .autocomplete-suggestion-hint { display: block; margin-left: 15px; text-transform: initial }
            .autocomplete-suggestion-hint:before { content: 'in '; }
            .autocomplete-selected { background: #FFCC33; }
            .autocomplete-suggestions strong { font-family: muli-bold; }
            .autocomplete-group { padding: 7px 5px 5px 3px; }
            .autocomplete-group strong { display: block; border-bottom: 3px solid #FFCC33; }
        </style>
        <script id='<?=self::SCRIPT_ID?>' async type='text/javascript' onload="ts_suggestion_callback()" src='<?=$endpoint?>'></script>
        <?php
        return ob_get_clean();

    }
}