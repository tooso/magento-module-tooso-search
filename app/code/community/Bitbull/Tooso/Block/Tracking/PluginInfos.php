<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_Tracking_PluginInfos extends Bitbull_Tooso_Block_Tracking
{
    const BLOCK_ID = 'tooso_tracking_plugininfos';
    const SCRIPT_ID = 'tooso-tracking-plugininfos';

    protected function _toHtml()
    {
        $version = Mage::getConfig()->getNode()->modules->Bitbull_Tooso->version;
        return "<!-- Tooso Plugin v$version -->";
    }
}