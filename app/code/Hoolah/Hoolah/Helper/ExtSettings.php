<?php
    namespace Hoolah\Hoolah\Helper;
    
    use \Magento\Framework\App\Helper\AbstractHelper;
    use \Magento\Framework\App\Helper\Context;
    use \Magento\Store\Model\ScopeInterface;
    
    use \Hoolah\Hoolah\Controller\Main as HoolahMain;
    use \Hoolah\Hoolah\Helper\API as HoolahAPI;
    use \Hoolah\Hoolah\Helper\Data as HoolahData;
    
    class ExtSettings extends AbstractHelper
    {
        // const
        const OPTION_LAST_TIME = 'payment/hoolah/es/last_time';
        const OPTION_SETTINGS = 'payment/hoolah/es/data';
        
        // static
        private $data = null;
        
        // private
        protected $hlog;
        protected $storeManager;
        protected $configWriter;
        
        // public
        public function __construct(
            Context $context,
            \Hoolah\Hoolah\Helper\Log $hlog,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
        )
        {
            parent::__construct($context);
            
            $this->hlog = $hlog;
            $this->storeManager = $storeManager;
            $this->configWriter = $configWriter;
        }
        
        public function get_es($path, $store = null)
        {
            return $this->scopeConfig->getValue(
                $path,
                ScopeInterface::SCOPE_STORE,
                $store
            );
        }
        
        public function getMerchantCDNID($scope = ScopeInterface::SCOPE_STORE, $store = null)
        {
            return $this->scopeConfig->getValue(
                'payment/hoolah/hoolah_mc/merchant_cdn_id',
                $scope,
                $store
            );
        }
        
        public function getSettings()
        {
            if ($this->data == null)
            {
                $this->data = $this->get_es(self::OPTION_SETTINGS);
                if ($this->data)
                    $this->data = json_decode($this->data, true);
            }
            
            return $this->data;
        }
        
        public function getField($name, $default = null)
        {
            $this->getSettings();
            
            if (is_array($this->data) && array_key_exists($name, $this->data))
                return $this->data[$name];
            
            return $default;
        }
        
        //public function gatewayName($default = null)
        //{
        //    return $this->getField('gatewayName', $default);
        //}
        
        public function gatewayDescription($default = null)
        {
            return $this->getField('gatewayDescription', $default);
        }
        
        public function gatewayEnabledCountries($default = null)
        {
            $result = $this->getField('gatewayEnabledCountries', $default);
            
            if ($result && !is_array($result))
                if (trim($result))
                    $result = explode(';', trim($result));
            
            return $result;
        }
        
        public function gatewayEnabledMinAmount($default = null)
        {
            return floatval($this->getField('gatewayEnabledMinAmount', $default));
        }
        
        public function gatewayEnabledMaxAmount($default = null)
        {
            return floatval($this->getField('gatewayEnabledMaxAmount', $default));
        }
        
        public function orderConfirmation($default = null)
        {
            return $this->getField('orderConfirmation', $default);
        }
        //public function orderError($default = null)
        //{
        //    return $this->getField('orderError', $default);
        //}
        
        public function apiHost($default = null)
        {
            return $this->getField('apiHost', $default);
        }
        
        public function redirectHost($default = null)
        {
            return $this->getField('redirectHost', $default);
        }
        
        //public function updateRepository($default = null)
        //{
        //    return $this->getField('updateRepository', $default);
        //}
        
        public function cron($immediately = false)
        {
            $result = null;
            
            HoolahMain::load_configs();
            
            foreach ($this->storeManager->getStores() as $store)
            {
                $LAST_TIME = $this->get_es(self::OPTION_LAST_TIME, $store);
                
                $passed = (time() - intval($LAST_TIME)) / (60 * 60);
                if ($passed >= HOOLAH_EXT_SETTINGS_CHECKUP_INTERVAL || $immediately)
                {
                    if (!empty($this->getMerchantCDNID(ScopeInterface::SCOPE_STORE, $store)))
                    {
                        $url = sprintf(HOOLAH_EXT_SETTINGS, $this->getMerchantCDNID(ScopeInterface::SCOPE_STORE, $store));
                        $settings = HoolahAPI::get_content($url);
                        if (HoolahAPI::is_200($settings))
                        {
                            $settings = json_decode($settings['body'], true);
                            if ($settings)
                            {
                                $this->hlog->notice('hoolah settings: loaded from '.$url);
                                
                                $this->configWriter->save(
                                    self::OPTION_SETTINGS,
                                    json_encode($settings),
                                    ScopeInterface::SCOPE_WEBSITES,
                                    $store->getWebsiteId()
                                );
                                
                                $result = 'hoolah settings: loaded from '.$url;
                                $result.= json_encode($settings);
                            }
                            else
                            {
                                $result = 'hoolah settings: error while loading from '.$url;
                                $this->hlog->notice($result);
                            }
                        }
                        else
                        {
                            $result = 'hoolah settings: error while loading from '.$url;
                            $this->hlog->notice($result);
                        }
                    }
                    else
                    {
                        $result = 'hoolah settings: CDN ID is empty';
                        $this->hlog->notice($result);
                    }
                    
                    $this->configWriter->save(
                        self::OPTION_LAST_TIME,
                        time(),
                        ScopeInterface::SCOPE_WEBSITES,
                        $store->getWebsiteId()
                    );
                    $store->resetConfig();
                }
            }
            
            return $result;
        }
    }