<?php
    namespace Hoolah\Hoolah\Block\System\Config\Form;
    
    use \Magento\Backend\Block\Template\Context;
    use \Magento\Framework\Data\Form\Element\AbstractElement;
    
    use \Hoolah\Hoolah\Helper\Data as HoolahData;
    
    class PluginVersion extends \Magento\Config\Block\System\Config\Form\Field
    {
        /**
         * @var string
         */
        protected $_template = 'Hoolah_Hoolah::system/config/plugin_version.phtml';
        
        protected $hdata;
        
        /**
         * @param Context $context
         * @param array $data
         */
        public function __construct(
            Context $context,
            HoolahData $hdata,
            array $data = []
        ) {
            parent::__construct($context, $data);
            
            $this->hdata = $hdata;
        }
    
        /**
         * Remove scope label
         *
         * @param  AbstractElement $element
         * @return string
         */
        public function render(AbstractElement $element)
        {
            $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
            return parent::render($element);
        }
        
        /**
         * Return element html
         *
         * @param  AbstractElement $element
         * @return string
         */
        protected function _getElementHtml(AbstractElement $element)
        {
            return $this->_toHtml();
        }
    
        /**
         * Generate collect button html
         *
         * @return string
         */
        public function getVersionHtml()
        {
            return $this->hdata->getVersion();
        }
    }