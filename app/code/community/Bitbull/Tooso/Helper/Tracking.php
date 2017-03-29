<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
class Bitbull_Tooso_Helper_Tracking extends Mage_Core_Helper_Abstract
{

    /**
     * Get Tracking Pixel
     *
     * @param string $tracking_url
     * @return strin
     */
    public function getTrackingImageHTML($tracking_url)
    {
        return '<img id="tooso-tracking-pixel" style="height: 1;width: 1;position: fixed;left: -99999px;" src="'.$tracking_url.'"></img>';
    }

    /**
     * Check if last url is a search page
     *
     * @return boolean
     */
    public function isLastUrlSearch(){
        $lastUrl = Mage::helper('core/http')->getHttpReferer();
        $searchUrl =  Mage::helper('catalogsearch')->getResultUrl();

        if($lastUrl && $searchUrl){
            $lastUrlParsed = parse_url($lastUrl);
            $searchUrlParsed = parse_url($searchUrl);

            $lastUrlPath = str_replace("index/", "", $lastUrlParsed["path"]);
            $searchUrlPath =  $searchUrlParsed["path"];

            return $lastUrlPath == $searchUrlPath;
        }else{
            return false;
        }
    }

}