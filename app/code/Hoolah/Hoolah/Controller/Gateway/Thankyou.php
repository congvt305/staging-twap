<?php
    namespace Hoolah\Hoolah\Controller\Gateway;
    
    class Thankyou extends \Magento\Framework\App\Action\Action
    {
        protected $checkoutSession;
        protected $pageFactory;
        protected $extSettings;
        protected $horder;
        protected $hlog;
        
        /**
         * @param Context $context
         * @param Data $helper
         */
        public function __construct(
            \Magento\Framework\App\Action\Context $context,
            \Magento\Checkout\Model\Session $checkoutSession, 
            \Magento\Framework\View\Result\PageFactory $pageFactory,
            \Hoolah\Hoolah\Helper\ExtSettings $extSettings,
            \Hoolah\Hoolah\Helper\Order $horder,
            \Hoolah\Hoolah\Helper\Log $hlog
        )
        {
            parent::__construct($context);
            
            $this->checkoutSession = $checkoutSession;
            $this->pageFactory = $pageFactory;
            $this->extSettings = $extSettings;
            $this->horder = $horder;
            $this->hlog = $hlog;
        }
     
        /**
         * Collect relations data
         *
         * @return \Magento\Framework\Controller\Result\Json
         */
        public function execute()
        {
            $this->hlog->notice('thankyou page opened for quote '.@$_REQUEST['quote_id']);
            
            if (isset($_REQUEST['quote_id']))
            {
                $quote_id = $_REQUEST['quote_id'];
                
                $updateResult = $this->horder->updateStateFromHoolah($quote_id);
                if ($updateResult === true || $updateResult === null)
                {
                    $this->hlog->notice('so show thank you page');
                    $this->horder->prepareSessionForThankyou($quote_id, $this->checkoutSession);
                }
                else if ($updateResult === false)
                {
                    $this->hlog->notice('so will close the payment and restore the quote');
                    
                    if ($this->horder->closePayment($quote_id))
                        $this->horder->restoreQuote($quote_id, $this->checkoutSession);
                    
                    $this->_redirect('checkout', ['_fragment' => 'payment']);
                }
            }
            
            $result = $this->pageFactory->create();
            
            $layout = $result->getLayout();
            $block = $layout->getBlock('hoolah.successinfo');
            $block->setData('orderConfirmation', $this->extSettings->orderConfirmation());
            
            return $result;
        }
    }