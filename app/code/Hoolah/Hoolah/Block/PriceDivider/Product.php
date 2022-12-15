<?php
    namespace Hoolah\Hoolah\Block\PriceDivider;
    
    use \Hoolah\Hoolah\Controller\Main as HoolahMain;
    use \Hoolah\Hoolah\Helper\Data as HoolahData;
    use Magento\Framework\Pricing\Price\PriceInterface;
    
    class Product extends \Magento\Catalog\Block\Product\View\AbstractView
    {
        /**
        * @var \Magento\Framework\View\Asset\Repository
        */
        //protected $assetRepository;
        
        // protected
        protected $hdata;
        
        protected function _toHtml()
        {
            if ($this->hdata->isEnabled())
            {
                $currency = @$this->_storeManager->getStore()->getCurrentCurrency()->getCode();
                if (!in_array($currency, array('SGD', 'MYR', 'THB')))
                    $currency = 'SGD';
                
                $price_val = $this->getProduct()->getFinalPrice();
                
                if ($price_val && $this->hdata->getMerchantCDNID())
                    return HoolahMain::inc('view/widget/product', array(
                        'price' => $price_val,
                        'currency' => $currency
                    ), false);
            }
        }
        
        /**
        * Header constructor.
        * @param Template\Context $context
        * @param array $data
        */
        public function __construct(
            \Magento\Catalog\Block\Product\Context $context,
            \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
            HoolahData $hdata,
            array $data = []
        )
        {
            parent::__construct($context, $arrayUtils);
            
            $this->hdata = $hdata;
            //$this->assetRepository = $context->getAssetRepository();
        }
        
        /**
        * @return string
        */
        
    }