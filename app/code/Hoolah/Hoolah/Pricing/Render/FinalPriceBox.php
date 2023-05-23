<?php
    namespace Hoolah\Hoolah\Pricing\Render;
    
    use \Magento\Catalog\Pricing\Price;
    use \Magento\Framework\Pricing\Render\PriceBox as BasePriceBox;
    use \Magento\Msrp\Pricing\Price\MsrpPrice;
    use \Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface;
    use \Magento\Framework\View\Element\Template\Context;
    use \Magento\Framework\Pricing\SaleableInterface;
    use \Magento\Framework\Pricing\Price\PriceInterface;
    use \Magento\Framework\Pricing\Render\RendererPool;
    use \Magento\Framework\App\ObjectManager;
    
    use \Hoolah\Hoolah\Controller\Main as HoolahMain;
    use \Hoolah\Hoolah\Helper\Data as HoolahData;
    
    class FinalPriceBox extends \Magento\Catalog\Pricing\Render\FinalPriceBox
    {
        // protected
        protected $hdata;
        
        protected function wrapResult($html)
        {
            if ($this->hdata->isEnabled() && $this->getData('zone') != 'item_view')
            {
                $price_val = $this->getPrice()->getValue();
                $currency = @$this->_storeManager->getStore()->getCurrentCurrency()->getCode();
                if (!in_array($currency, array('SGD', 'MYR', 'THB')))
                    $currency = 'SGD';
                
                if ($price_val && $this->hdata->getMerchantCDNID())
                    $html .= HoolahMain::inc('view/widget/collection', array(
                        'price' => $price_val,
                        'currency' => $currency
                    ), false);
            }
            
            return parent::wrapResult($html);
        }
        
        // public
        public function __construct(
            Context $context,
            SaleableInterface $saleableItem,
            PriceInterface $price,
            RendererPool $rendererPool,
            HoolahData $hdata,
            array $data = [],
            SalableResolverInterface $salableResolver = null
        ) {
            parent::__construct($context, $saleableItem, $price, $rendererPool, $data, $salableResolver);
            
            $this->hdata = $hdata;
        }
    }