<?php
/**
 * @package Bitbull_Tooso
 * @author Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */

class Bitbull_Tooso_Block_Tracking_ProductView extends Bitbull_Tooso_Block_Tracking
{
    const BLOCK_ID = 'tooso_tracking_productview';
    const SCRIPT_ID = 'tooso-tracking-productview';

    /**
     * @var null|integer
     */
    protected $_productId = null;

    protected function _toHtml()
    {
        if($this->_productId == null){
            $this->_logger->warn('Tracking product: product_id not set');
            return;
        }

        $trackingProductParams = $this->_helper->getProductTrackingParams($this->_productId);
        if($trackingProductParams == null){
            $this->_logger->warn('Tracking product: product not found with id '.$this->_productId);
            return;
        }

        ob_start();

        if(Mage::helper('tooso/tracking')->isUserComingFromSearch()){
            $this->_logger->debug('Tracking product: elaborating result..');

            // Get rank collection from search collection
            $searchRankCollection = Mage::helper('tooso/session')->getRankCollection();
            $rank = -1;
            if($searchRankCollection != null && isset($searchRankCollection[$this->_productId])){
                $rank = $searchRankCollection[$this->_productId];
            }else{
                if($searchRankCollection == null){
                    $this->_logger->debug('Tracking product: rank collection not found in session');
                }else{
                    $this->_logger->debug('Tracking product: sku not found in rank collection, printing..');
                    foreach ($searchRankCollection as $rankId => $rankPos){
                        $this->_logger->debug('Tracking product: '.$rankId.' => '.$rankPos);
                    }
                }
            }
            $trackingProductParams['position'] = $rank;

            $order = Mage::helper('tooso/session')->getSearchOrder();
            if($order == null){
                $order = "relevance";
            }
            $trackingProductParams['order'] = $order;

            if ($this->_helper->includeTrackingJSLibrary()) {
                ?>
                <script id='<?=self::SCRIPT_ID?>' type='text/javascript'>
                    ta('ec:addProduct', <?=json_encode($trackingProductParams);?>);
                    ta('ec:setAction', 'click', {
                        'list': '<?=Mage::helper('tooso/tracking')->getSearchIdWithFallback();?>'
                    });
                </script>
                <?php
            }else{
                ?>
                <script id='<?=self::SCRIPT_ID?>' type='text/javascript'>
                    window.ToosoTrackingData = {
                        "product": <?=json_encode($trackingProductParams);?>,
                        "action": "click",
                    };
                </script>
                <?php
            }
        }else{
            $this->_logger->debug('Tracking product: elaborating product view..');

            ?>
            <script id='<?=self::SCRIPT_ID?>' type='text/javascript'>
                window.ToosoTrackingData = {
                    "product": <?=json_encode($trackingProductParams);?>,
                    "action": 'detail',
                };
            </script>
            <?php
        }

        return ob_get_clean();
    }

    /**
     * Get Cache Key Info
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $info = parent::getCacheKeyInfo();
        $info['object_id'] = $this->_productId;
        return $info;
    }

    /**
     * @param $id
     */
    public function setObjectID($id){
        $this->setProductID($id);
    }

    /**
     * @param $id
     */
    public function setProductID($id){
        $this->_productId = $id;
    }
}