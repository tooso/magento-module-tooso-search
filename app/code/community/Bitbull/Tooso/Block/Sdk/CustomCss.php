<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_Sdk_CustomCss extends Bitbull_Tooso_Block_Sdk
{
    const BLOCK_ID = 'tooso_sdk_customCss';
    const STYLE_ID = 'tooso-sdk-style-init';

    protected function _toHtml()
    {
        $this->_logger->debug('initializing custom CSS block');

        ob_start();
        ?>
        <style id='<?=self::STYLE_ID?>'>
            <?=$this->_helper->getCustomCSS() ?>
        </style>
        <?php

        return ob_get_clean();
    }
}
