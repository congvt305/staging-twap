<?php
    namespace Hoolah\Hoolah\Model\Config\Structure\Element;
    
    use \Magento\Config\Model\Config\Structure\Element\Group as OriginalGroup;
    
    use \Hoolah\Hoolah\Controller\Main as HoolahMain;
    use \Hoolah\Hoolah\Helper\Data as HoolahData;
    
    class Group
    {
        // const
        const HOOLAH_GROUP = 'hoolah';
        const HOOLAH_GROUP_MC = 'hoolah_mc';
        
        // protected
        protected $hdata;
        
        // public
        public function __construct(HoolahData $hdata) {
            $this->hdata = $hdata;
        }
        
        public function aroundSetData(OriginalGroup $subject, callable $proceed, array $data, $scope)
        {
            if (!HoolahMain::is_dev())
                switch ($data['id'])
                {
                    case self::HOOLAH_GROUP:
                        unset($data['children']['mode']);
                        break;
                    case self::HOOLAH_GROUP_MC:
                        unset($data['children']['merchant_secret_test_mode']);
                        $data['children']['merchant_secret']['label'] = __('Merchant Secret');
                        break;
                }
            
            return $proceed($data, $scope);
        }
    }