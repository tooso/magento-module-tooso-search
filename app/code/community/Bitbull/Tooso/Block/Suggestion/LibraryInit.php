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
        $inputSelector          = $this->_helper->getSuggestionInputSelector();
        $apiKey                 = $this->_helper->getApiKey();
        $zindex                 = $this->_helper->getSuggestionZIndex();
        $buckets                = $this->_helper->getSuggestionBuckets();
        $noCache                = $this->_helper->getSuggestionNoCache();
        $language               = $this->_helper->getSuggestionLanguage();
        $uid                    = $this->_helper->getSuggestionUID();
        $limit                  = $this->_helper->getSuggestionLimit();
        $groupBy                = $this->_helper->getSuggestionGroupBy();
        $submitOnSelect         = $this->_helper->getSuggestionSubmitOnSelect();
        $autoCompleteMinChars   = $this->_helper->getSuggestionAutocompleteMinChars();
        $autocompleteMaxHeight  = $this->_helper->getSuggestionAutocompleteMaxHeight();
        $autocompleteWidth      = $this->_helper->getSuggestionAutocompleteWidth();
        $autocompleteOnSelect   = $this->_helper->getSuggestionAutocompleteOnSelect();
        $this->_logger->debug('initializing suggestion library');

        ob_start();
        ?>
        <script id='<?=self::SCRIPT_ID?>'>
            window.ts_suggestion_callback = function() {
                if(window.jQuery){
                    jQuery(document).ready(function ($) {
                        var element = $('<?=$inputSelector?>');
                        if(element){
                            element.attr('data-ts', '')
                                .ts({
                                <? if (!empty($apiKey))     : ?>apiKey:'<?=$apiKey?>',<? endif; ?>
                                <? if (!empty($language))   : ?>language: '<?=$language?>',<? endif; ?>
                                <? if (!empty($uid))        : ?>uid: '<?=$uid?>',<? endif; ?>
                                <? if (!empty($buckets))    : ?>buckets: '<?=$buckets?>',<? endif; ?> 
                                <? if (!empty($limit))      : ?>limit: '<?=$limit?>',<? endif; ?>
                                <? if (!empty($groupBy))    : ?>groupBy: <?=$groupBy?>,<? endif; ?>
                                <? if (!empty($noCache))    : ?>noCache: <?=$noCache?>,<? endif; ?>
                                    autocomplete: {
                                    <? if ($autocompleteOnSelect) : ?>
                                        onSelect: <?=$autocompleteOnSelect?>,
                                    <? elseif ($submitOnSelect) : ?>
                                        onSelect: function() { this.form.submit(); },
                                    <? endif; ?>
                                        minChars: <?=($autoCompleteMinChars ?: 'undefined')?>,
                                        maxHeight: <?= ($autocompleteMaxHeight ?: 'undefined')?>,
                                        width: <?= ($autocompleteWidth ?: 'undefined')?>,
                                        zIndex: <?= ($zindex ?: 'undefined')?>,
                                    },
                                });
                        }else{
                            console.error("Tooso: Suggestion search input not found");
                        }
                    });
                }else{
                    console.error("Tooso: Suggestion script require jQuery");
                }
            }
        </script>
        <?php
        return ob_get_clean();
    }
}