<?php
    namespace Hoolah\Hoolah\Block;
    
    use \Hoolah\Hoolah\Controller\Main as HoolahMain;
    use \Hoolah\Hoolah\Helper\Data as HoolahData;
    use Hoolah\Hoolah\Helper\ExtSettings as HoolahExtSettings;
    
    class Head extends \Magento\Framework\View\Element\Template
    {
        /**
        * @var \Magento\Framework\View\Asset\Repository
        */
        //protected $assetRepository;
        
        // protected
        protected $hdata;
        protected $extSettings;
        
        /**
        * Header constructor.
        * @param Template\Context $context
        * @param array $data
        */
        public function __construct(
            \Magento\Framework\View\Element\Template\Context $context,
            HoolahData $hdata,
            HoolahExtSettings $extSettings,
            array $data = []
        )
        {
            parent::__construct($context, $data);
            
            $this->hdata = $hdata;
            $this->extSettings = $extSettings;
            //$this->assetRepository = $context->getAssetRepository();
        }
        
        /**
        * @return string
        */
        public function getCDNLink()
        {
            if (($this->hdata->isEnabled() && isset($_GET['test_hljs'])) && $this->hdata->getMerchantCDNID())
                return HoolahMain::get_cdn_url($this->hdata->getMerchantCDNID());
            
            return null;
        }
        
        public function getCDNLinks()
        {
            if ($this->hdata->isEnabled() && $this->hdata->getMerchantCDNID())
                return HoolahMain::get_cdn_urls($this->hdata->getMerchantCDNID());
            
            return null;
        }
        
        public function isEnabled()
        {
            return $this->hdata->isEnabled();
        }
        
        public function getVersion()
        {
            return $this->hdata->getVersion();
        }
        
        public function getDebugInfo()
        {
            if (!empty(@$_GET['hoolah_debug']))
            {
                echo '<!--HOOLAH_DEBUG';
                @var_dump(
                    $this->extSettings->gatewayEnabledCountries(HoolahMain::get_countries()),
                    $this->extSettings->gatewayEnabledMinAmount(),
                    $this->extSettings->gatewayEnabledMaxAmount()
                );
                echo '-->';
            }
        }
    }