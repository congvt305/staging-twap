<?php
    namespace Hoolah\Hoolah\Controller\ExtSettings;
    
    class Update extends \Magento\Framework\App\Action\Action
    {
        // protected
        protected $extSettings;
        
        // public
        /**
         * @param Context $context
         * @param JsonFactory $resultJsonFactory
         * @param Data $helper
         */
        public function __construct(
            \Magento\Framework\App\Action\Context $context,
            
            \Hoolah\Hoolah\Helper\ExtSettings $extSettings
        )
        {
            parent::__construct($context);
            
            $this->extSettings = $extSettings;
        }
        
        public function execute()
        {
            echo $this->extSettings->cron(true);
        }
    }