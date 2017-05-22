<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Model_PageCache_Container_TrackingPixel extends Enterprise_PageCache_Model_Container_Abstract
{
    /**
     * Override _saveCache method to prevent caching
     *
     * @param $data
     * @param $id
     * @param array $tags
     * @param null $lifetime
     * @return bool
     */
    protected function _saveCache($data, $id, $tags = array(), $lifetime = null)
    {
        return false;
    }
}