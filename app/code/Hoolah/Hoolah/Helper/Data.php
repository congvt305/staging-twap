<?php
    namespace Hoolah\Hoolah\Helper;
    
    use \Magento\Framework\App\Helper\AbstractHelper;
    use \Magento\Framework\App\Helper\Context;
    //use \Magento\Framework\App\Config\ScopeConfigInterface;
    use \Magento\Store\Model\ScopeInterface;
    //use Magento\Framework\Encryption\EncryptorInterface;
    
    use \Magento\Framework\Module\ModuleListInterface;
    
    use \Hoolah\Hoolah\Controller\Main as HoolahMain;
    use \Hoolah\Hoolah\Model\Config\Source\OperationMode as OperationMode;
    
    class Data extends AbstractHelper
    {
        const MODULE_NAME = 'Hoolah_Hoolah';
        
        protected $moduleList;
        //protected $encryptor;
        protected $extSettings;
    
        /**
         * @param Context $context
         * @param EncryptorInterface $encryptor
         */
        public function __construct(
            Context $context,
            ModuleListInterface $moduleList,
            //EncryptorInterface $encryptor
            \Hoolah\Hoolah\Helper\ExtSettings $extSettings
        )
        {
            parent::__construct($context);
            
             $this->moduleList = $moduleList;
             $this->extSettings = $extSettings;
            //$this->encryptor = $encryptor;
        }
        
        public function getVersion()
        {
            return $this->moduleList->getOne(self::MODULE_NAME)['setup_version'];
        }
        
        /*
         * @return bool
         */
        public function isEnabled($scope = ScopeInterface::SCOPE_STORE)
        {
            return $this->scopeConfig->isSetFlag(
                'payment/hoolah/active',
                $scope
            );
        }
    
        /*
         * @return string
         */
        public function getTitle($scope = ScopeInterface::SCOPE_STORE, $store = null)
        {
            return $this->scopeConfig->getValue(
                'payment/hoolah/title',
                $scope,
                $store
            );
        }
        
        /*
         * @return string
         */
        public function getMerchantCDNID($scope = ScopeInterface::SCOPE_STORE, $store = null)
        {
            return $this->scopeConfig->getValue(
                'payment/hoolah/hoolah_mc/merchant_cdn_id',
                $scope,
                $store
            );
        }
        
        public function getOrderStatus($scope = ScopeInterface::SCOPE_STORE, $store = null)
        {
            return $this->scopeConfig->getValue(
                'payment/hoolah/order_status',
                $scope,
                $store
            );
        }
        
        public function getOrderMode($scope = ScopeInterface::SCOPE_STORE, $store = null)
        {
            return $this->scopeConfig->getValue(
                'payment/hoolah/order_mode',
                $scope,
                $store
            );
        }
        
        public function get_mode($store = null)
        {
            if (HoolahMain::is_dev() && !$this->extSettings->apiHost())
            {
                $option = $this->scopeConfig->getValue(
                    'payment/hoolah/mode',
                    ScopeInterface::SCOPE_STORE,
                    $store
                );
                if ($option)
                    return $option;
            }
            
            return OperationMode::MODE_LIVE;
        }
        
        public function get_merchant_id($store = null)
        {
            return $this->scopeConfig->getValue(
                'payment/hoolah/hoolah_mc/merchant_id',
                ScopeInterface::SCOPE_STORE,
                $store
            );
        }
        
        public function get_merchant_secret($store = null)
        {
            switch ($this->get_mode($store))
            {
                case OperationMode::MODE_TEST:
                    return $this->scopeConfig->getValue(
                        'payment/hoolah/hoolah_mc/merchant_secret_test_mode',
                        ScopeInterface::SCOPE_STORE,
                        $store
                    );
                    break;
                case OperationMode::MODE_LIVE:
                    return $this->scopeConfig->getValue(
                        'payment/hoolah/hoolah_mc/merchant_secret',
                        ScopeInterface::SCOPE_STORE,
                        $store
                    );
                    break;
            }
            
            return null;
        }
        
        public function get_hoolah_url($store = null)
        {
            switch ($this->get_mode($store))
            {
                case OperationMode::MODE_TEST: return HOOLAH_API_HOST_SANDBOX; break;
                case OperationMode::MODE_LIVE: return $this->extSettings->apiHost(HOOLAH_API_HOST_PROD); break;
            }
            
            return null;
        }
        
        public function credentials_are_empty($store = null)
        {
            return
                empty($this->get_merchant_id($store)) ||
                empty($this->get_merchant_secret($store)) ||
                empty($this->get_hoolah_url($store));
        }
        
        public function get_hoolah_jsurl($country = null, $store = null)
        {
            $name = null;
            
            switch ($this->get_mode($store))
            {
                case OperationMode::MODE_TEST: $name = 'HOOLAH_CHECKOUT_URL_SANDBOX'; break;
                case OperationMode::MODE_LIVE:
                    if ($this->extSettings->redirectHost())
                        return $this->extSettings->redirectHost();
                    
                    $name = 'HOOLAH_CHECKOUT_URL_PROD';
                    break;
            }
            
            if ($name && $country && defined($name.'_'.$country))
                $name = $name.'_'.$country;
            else
                $name = $name.'_SG';
            
            if ($name)
                return constant($name);
            
            return null;
        }
        
        public function get_billing_city_field_title($store = null)
        {
            return $this->scopeConfig->getValue(
                'payment/hoolah/hoolah_cfm/billing_city_field_title',
                ScopeInterface::SCOPE_STORE,
                $store
            );
        }
        
        public function get_create_email_notification($store = null)
        {
            return $this->scopeConfig->getValue(
                'payment/hoolah/create_email_notification',
                ScopeInterface::SCOPE_STORE,
                $store
            );
        }
    }