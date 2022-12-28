<?php
    namespace Hoolah\Hoolah\Controller\Orders;
    
    class Update extends \Magento\Framework\App\Action\Action
    {
        // protected
        protected $horder;
        
        // public
        /**
         * @param Context $context
         * @param JsonFactory $resultJsonFactory
         * @param Data $helper
         */
        public function __construct(
            \Magento\Framework\App\Action\Context $context,
            
            \Hoolah\Hoolah\Helper\Order $horder
        )
        {
            parent::__construct($context);
            
            $this->horder = $horder;
        }
        
        public function execute()
        {
            echo json_encode($this->horder->cron());
        }
    }