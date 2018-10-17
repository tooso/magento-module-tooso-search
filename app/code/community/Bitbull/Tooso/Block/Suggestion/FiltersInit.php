<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_Suggestion_FiltersInit extends Bitbull_Tooso_Block_Suggestion
{
    const BLOCK_ID = 'tooso_suggestion_filters_init';
    const SCRIPT_ID = 'tooso-suggestion-filters-init';

    protected function _toHtml()
    {
        $this->_logger->debug('set filter value');
        $filterParamValue = $this->_searchHelper->getFilterParamValue();

        if ($filterParamValue === null) {
            $this->_logger->debug('no filter parameter found');
            return '';
        }

        ob_start();
        ?>
        <script id='<?=self::SCRIPT_ID?>'>
            window.ts_suggestion_filter_set('<?=$filterParamValue?>');
        </script>
        <?php
        return ob_get_clean();
    }
}