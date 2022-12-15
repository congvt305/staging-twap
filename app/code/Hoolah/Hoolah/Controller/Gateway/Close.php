<?php
    namespace Hoolah\Hoolah\Controller\Gateway;
    
    class Close extends \Magento\Framework\App\Action\Action
    {
        protected $checkoutSession;
        protected $hlog;
        protected $horder;
        
        /**
         * @param Context $context
         * @param Data $helper
         */
        public function __construct(
            \Magento\Framework\App\Action\Context $context,
            \Magento\Checkout\Model\Session $checkoutSession,
            \Hoolah\Hoolah\Helper\Log $hlog,
            \Hoolah\Hoolah\Helper\Order $horder
        )
        {
            parent::__construct($context);
            
            $this->checkoutSession = $checkoutSession;
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
            $this->hlog->notice('close page called for quote '.@$_REQUEST['quote_id'], $_REQUEST);
            if (isset($_REQUEST['quote_id']))
            {
                $quote_id = intval($_REQUEST['quote_id']);
                
                $this->hlog->notice('so closing the payment');
                if ($this->horder->closePayment($quote_id))
                {
                    $this->hlog->notice('and restoring the quote');
                    $this->horder->restoreQuote($quote_id, $this->checkoutSession);
                }
                
                $this->_redirect('checkout', ['_fragment' => 'payment']);
            }
        }
    }