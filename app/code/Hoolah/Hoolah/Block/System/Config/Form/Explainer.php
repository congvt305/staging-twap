<?php
    namespace Hoolah\Hoolah\Block\System\Config\Form;
    
    use \Magento\Framework\Data\Form\Element\AbstractElement;
    
    use \Hoolah\Hoolah\Helper\Data as HoolahData;
    use \Hoolah\Hoolah\Controller\Main as HoolahMain;
    
    class Explainer extends \Magento\Config\Block\System\Config\Form\Fieldset
    {
        // protected
        protected $urlBuilder;
        protected $hdata;
        
        protected $scopeId;
        
        /**
         * @var string
         */
        /**
         * @param Context $context
         * @param array $data
         */
        public function __construct(
            \Magento\Backend\Block\Context $context,
            \Magento\Backend\Model\Auth\Session $authSession,
            \Magento\Framework\View\Helper\Js $jsHelper,
            \Magento\Framework\Url $urlBuilder,
            \Magento\Framework\App\Request\Http $request,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            HoolahData $hdata,
            array $data = []
        ) {
            parent::__construct($context, $authSession, $jsHelper, $data);
            
            $this->scopeId = 0;
            if ($request->getParam('store'))
                $this->scopeId = $request->getParam('store');
            else
            {
                $store = null;
                if ($request->getParam('website') && $storeManager->getWebsite($request->getParam('website')))
                    $store = $storeManager->getWebsite($request->getParam('website'))->getDefaultStore();
                
                if (!$store)
                    $store = $storeManager->getStore();
                
                if ($store)
                    $this->scopeId = $store->getId();
            }
            
            $this->urlBuilder = $urlBuilder;
            $this->hdata = $hdata;
        }
    
        /**
         * Return element html
         *
         * @param  AbstractElement $element
         * @return string
         */
        protected function _getChildrenElementsHtml(AbstractElement $element)
        {
            HoolahMain::load_configs();
            
            return HoolahMain::inc('view/adminhtml/templates/system/config/explainer', array(
                'explainerPreview' => sprintf(HOOLAH_EXPLAINER_PREVIEW, $this->hdata->getMerchantCDNID()),
                'pageURL' => $this->urlBuilder->setScope($this->scopeId)->getUrl('hoolah/instalments/show/', ['_nosid' => true])
            ), false);
        }
    }