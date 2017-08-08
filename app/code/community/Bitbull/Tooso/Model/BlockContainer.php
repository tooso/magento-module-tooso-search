<?php

/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

if(class_exists('Enterprise_PageCache_Model_Container_Abstract')){

    class Bitbull_Tooso_Model_BlockContainer extends Enterprise_PageCache_Model_Container_Abstract
    {
        protected function _getCacheId()
        {
            return $this->_placeholder->getAttribute('cache_id');
        }

        protected function _renderBlock()
        {
            $block = $this->_getPlaceHolderBlock();
            $block->setNameInLayout($this->_placeholder->getAttribute('name'));
            $block->setObjectID($this->_placeholder->getAttribute('object_id'));
            return $block->toHtml();
        }

        protected function _saveCache($data, $id, $tags = array(), $lifetime = null) {
            return false;
        }
    }

}

