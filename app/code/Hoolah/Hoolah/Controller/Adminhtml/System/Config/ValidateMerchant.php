<?php
    namespace Hoolah\Hoolah\Controller\Adminhtml\System\Config;
    
    use \Magento\Backend\App\Action;
    use \Magento\Backend\App\Action\Context;
    use \Magento\Framework\Controller\Result\JsonFactory;
    
    use \Hoolah\Hoolah\Controller\Main as HoolahMain;
    use \Hoolah\Hoolah\Helper\API as HoolahAPI;
     
    class ValidateMerchant extends Action
    {
        protected $resultJsonFactory;
        
        /**
         * @param Context $context
         * @param JsonFactory $resultJsonFactory
         * @param Data $helper
         */
        public function __construct(
            Context $context,
            JsonFactory $resultJsonFactory
        )
        {
            $this->resultJsonFactory = $resultJsonFactory;
            parent::__construct($context);
        }
     
        /**
         * Collect relations data
         *
         * @return \Magento\Framework\Controller\Result\Json
         */
        public function execute()
        {
            HoolahMain::load_configs();
            
            $result = array(
                'ok' => false
            );
            
            $for_check = array(
                'ok' => array(@trim($_POST['merchant_id']), @trim($_POST['merchant_secret']), HOOLAH_API_HOST_PROD)
            );
            
            if (HoolahMain::is_dev())
                $for_check['ok_test'] = array(@trim($_POST['merchant_id']), @trim($_POST['merchant_secret_test']), HOOLAH_API_HOST_SANDBOX);
            
            foreach ($for_check as $key => $credentials)
            {
                try
                {
                    if (empty($credentials[0]) || empty($credentials[1]) || empty($credentials[2]))
                        $result[$key] = false;
                    else
                    {
                        $api = new HoolahAPI($credentials[0], $credentials[1], $credentials[2]);
                        $response = $api->merchant_auth_login();
                        $result[$key] = HoolahAPI::is_200($response);
                    }
                }
                catch (\Throwable $e)
                {
                    $result[$key] = false;
                }
            }
            
            if (@trim($_POST['merchant_cdn_id']))
            {
                try
                {
                    $result['ok_cdn'] = HoolahAPI::url_exists(sprintf(HOOLAH_WIDGET_URL_CUSTOM, trim($_POST['merchant_cdn_id'])));
                }
                catch (\Throwable $e)
                {
                    $result['ok_cdn'] = false;
                }
            }
            
            $jsonf = $this->resultJsonFactory->create();
            
            return $jsonf->setData($result);
        }
    }