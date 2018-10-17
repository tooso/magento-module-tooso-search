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

        $filterParamName = $this->_searchHelper->getFilterParamName();
        $filterParamValue = $this->_searchHelper->getFilterParamValue();
        $filterDebug = $this->_searchHelper->isFilterDebugEnable();

        ob_start();
        ?>
        <script id='<?=self::SCRIPT_ID?>'>
            window.ts_suggestion_callback = function() {
                if(window.jQuery){
                    jQuery(document).ready(function ($) {
                        var element = $('<?=$inputSelector?>');
                        if(element.length > 0){
                            var params = <?=json_encode($initParams)?>;
                            params.autocomplete.onSelect = <?=$onSelectCallback?>;
                            element.ts(params);
                        }else{
                            console.error("Tooso: Suggestion search input not found");
                        }
                    });
                }else{
                    console.error("Tooso: Suggestion script require jQuery");
                }
            };
            window.ts_suggestion_filter_set = function (filterValue) {
                var debug = <?=$filterDebug ? 'true' : 'false' ?>;
                if(debug) console.debug('Tooso: Request set of filter with value '+filterValue);
                if(window.jQuery){
                    jQuery(document).ready(function ($) {
                        var searchInput = $('<?=$inputSelector?>');
                        if(searchInput.length === 0){
                            console.error('Tooso: Suggestion search input not found');
                            return;
                        }

                        var form = searchInput.closest('form');
                        if (form.length === 0) {
                            console.error('Tooso: Suggestion search form not found');
                            return;
                        }

                        var filterInput = form.find('input[name="<?=$filterParamName?>"]');
                        if (filterInput.length > 0 && (!filterValue || filterValue.trim().length === 0)) {
                            if(debug) console.debug('Tooso: delete filter input');
                            filterInput.remove();
                            return;
                        }

                        if (filterInput.length > 0) {
                            if(debug) console.debug('Tooso: Set value '+filterValue+' on filter input');
                            filterInput.first().val(filterValue);
                        }else{
                            if(debug) console.debug('Tooso: Creating filter input with value '+filterValue);
                            filterInput = form.first().append('<input type="hidden" name="<?=$filterParamName?>" value="'+filterValue+'">');
                        }
                        console.log('Tooso: Hidden input element ', filterInput.first())
                    });
                }else{
                    console.error("Tooso: Suggestion filter script require jQuery");
                }
            }

            if(Varien && Varien.searchForm && Varien.searchForm.prototype){
                Varien.searchForm.prototype.initAutocomplete = function () {}; //disable default Magento autocomplete
            }
        </script>
        <?php
        return ob_get_clean();
    }
}