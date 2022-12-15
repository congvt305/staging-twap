<?php
    namespace Hoolah\Hoolah\Controller\Instalments;
    
    use \Hoolah\Hoolah\Controller\Main as HoolahMain;
    
    class Show extends \Magento\Framework\App\Action\Action
    {
        protected $pageFactory;
        protected $extSettings;
        
        /**
         * @param Context $context
         * @param Data $helper
         */
        public function __construct(
            \Magento\Framework\App\Action\Context $context,
            \Magento\Framework\View\Result\PageFactory $pageFactory,
            \Hoolah\Hoolah\Helper\ExtSettings $extSettings
        )
        {
            parent::__construct($context);
            
            $this->pageFactory = $pageFactory;
            $this->extSettings = $extSettings;
        }
     
        /**
         * Collect relations data
         *
         * @return \Magento\Framework\Controller\Result\Json
         */
        public function execute()
        {
            $result = $this->pageFactory->create();
            
            $layout = $result->getLayout();
            
            $block = $layout->getBlock('hoolah.instalments');
            
            HoolahMain::load_configs();
            
            $block->setData('explainerPreview', sprintf(HOOLAH_EXPLAINER_PREVIEW, $this->extSettings->getMerchantCDNID()));
            $block->setData('explainerCSS', HOOLAH_EXPLAINER_CSS);
            
            return $result;
        }
    }