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
        $apiKey = $this->_helper->getApiKey();
        $zindex = $this->_helper->getSuggestionZIndex();
        $buckets = $this->_helper->getSuggestionBuckets();
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
                                .attr('data-ts-api-key', '<?=$apiKey?>')
                                .attr('data-ts-zindex', <?=$zindex?>)
                                .attr('data-ts-buckets', '<?=$buckets?>')
                                .ts();
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