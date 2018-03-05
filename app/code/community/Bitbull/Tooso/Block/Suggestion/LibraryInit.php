<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_Suggestion_LibraryInit extends Bitbull_Tooso_Block_Suggestion
{
    const BLOCK_ID = 'tooso_suggestion_library_init';
    const SCRIPT_ID = 'tooso-suggestion-library-init';

    protected function _toHtml()
    {
        $inputSelector = $this->_helper->getSuggestionInputSelector();
        $this->_logger->debug('initializing suggestion library');

        $initParams = $this->_helper->getSuggestionInitParams();
        $onSelectCallback = $this->_helper->getOnSelectValue();

        ob_start();
        ?>
        <script id='<?=self::SCRIPT_ID?>'>
            window.ts_suggestion_callback = function() {
                if(window.jQuery){
                    jQuery(document).ready(function ($) {
                        var element = $('<?=$inputSelector?>');
                        if(element){
                            var params = <?=json_encode($initParams)?>;
                            params.autocomplete.onSelect = <?=$onSelectCallback?>;
                            element.attr('data-ts', '').ts(params);
                        }else{
                            console.error("Tooso: Suggestion search input not found");
                        }
                    });
                }else{
                    console.error("Tooso: Suggestion script require jQuery");
                }
            };
            if(Varien && Varien.searchForm && Varien.searchForm.prototype){
                Varien.searchForm.prototype.initAutocomplete = function () {}; //disable default Magento autocomplete
            }
        </script>
        <?php
        return ob_get_clean();
    }
}