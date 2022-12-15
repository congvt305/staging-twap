<?php
    namespace Hoolah\Hoolah\Controller\Gateway;
    
    class CallbackConnector extends \Magento\Framework\App\Action\Action
    {
        protected $resultJsonFactory;
        protected $hlog;
        protected $horder;
        
        /**
         * @param Context $context
         * @param JsonFactory $resultJsonFactory
         * @param Data $helper
         */
        public function __construct(
            \Magento\Framework\App\Action\Context $context,
            \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, 
            \Hoolah\Hoolah\Helper\Log $hlog,
            \Hoolah\Hoolah\Helper\Order $horder
        )
        {
            parent::__construct($context);
            
            $this->resultJsonFactory = $resultJsonFactory;
            $this->hlog = $hlog;
            $this->horder = $horder;
        }
     
        /**
         * Collect relations data
         *
         * @return \Magento\Framework\Controller\Result\Json
         */
        public function execute()
        {
            $result = array('success' => true);
            
            $jsonf = $this->resultJsonFactory->create();
            
            try
            {
                $this->hlog->notice('callback called for quote '.@$_REQUEST['quote_id'], $_REQUEST);
                
                $quote_id = intval($_REQUEST['quote_id']);
                
                if (!$quote_id)
                {
                    $this->hlog->notice('quote id incorrect');
                    return $jsonf->setData(array(
                        'success' => false,
                        'message' => 'quote id incorrect'
                    ));
                }
                
                if ($this->horder->updateStateFromHoolah($quote_id))
                {
                    $this->hlog->notice('callback see that payment completed successfully');
                    
                    $result['url'] = $this->_url->getUrl('hoolah/gateway/thankyou/').'?quote_id='.$quote_id;
                }
                else
                {
                    $this->hlog->notice('callback see that payment failed');
                    
                    $result['url'] = $this->_url->getUrl('checkout', ['_secure' => true]);
                }
            }
            catch (\Throwable $e)
            {
                $message = $e->getMessage();
                
                $this->hlog->error('some exception: '.$message);
                
                $result = array(
                    'success' => false,
                    'message' => $message,
                    'redirect' => ''
                );
            }
            
            $this->hlog->notice('callback end');
            
            return $jsonf->setData($result);
        }
    }
    
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
    
    if (version_compare($productMetadata->getVersion(), '2.3', '>='))
    {
        class Callback extends CallbackConnector implements \Magento\Framework\App\Action\HttpPostActionInterface //m2.3
        {
            
        }
    }
    else
    {
        class Callback extends CallbackConnector //m2.2
        {
            public function __construct(
                \Magento\Framework\App\Action\Context $context,
                \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, 
                \Hoolah\Hoolah\Helper\Log $hlog,
                \Hoolah\Hoolah\Helper\Order $horder
            )
            {
                parent::__construct($context, $resultJsonFactory, $hlog, $horder);
            }
        }
    }