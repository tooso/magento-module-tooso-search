<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Gennaro Vietri <gennaro.vietri@bitbull.it>
*/ 
class Bitbull_Tooso_Block_CatalogSearch_Result extends Mage_CatalogSearch_Block_Result
{
    /**
     * Prepare layout
     *
     * @return Mage_CatalogSearch_Block_Result
     */
    protected function _prepareLayout()
    {
        $fixedSearchString = Mage::helper('tooso')->getFixedSearchString();

        // add Home breadcrumb
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
            $title = $this->__("Search results for: '%s'", $fixedSearchString ? $fixedSearchString : $this->helper('catalogsearch')->getQueryText());

            $breadcrumbs->addCrumb('home', array(
                'label' => $this->__('Home'),
                'title' => $this->__('Go to Home Page'),
                'link'  => Mage::getBaseUrl()
            ))->addCrumb('search', array(
                    'label' => $title,
                    'title' => $title
                ));
        }

        // modify page title
        $title = $this->__("Search results for: '%s'", $fixedSearchString ? $fixedSearchString : $this->helper('catalogsearch')->getEscapedQueryText());
        $this->getLayout()->getBlock('head')->setTitle($title);

        $this->setHeaderText($title);

        return Mage_Core_Block_Template::_prepareLayout();
    }
}