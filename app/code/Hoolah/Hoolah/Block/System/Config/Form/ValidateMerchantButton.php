<?php
    namespace Hoolah\Hoolah\Block\System\Config\Form;
    
    use \Magento\Backend\Block\Template\Context;
    use \Magento\Framework\Data\Form\Element\AbstractElement;
    
    class ValidateMerchantButton extends \Magento\Config\Block\System\Config\Form\Field
    {
        /**
         * @var string
         */
        protected $_template = 'Hoolah_Hoolah::system/config/validate_merchant_button.phtml';
    
        /**
         * @param Context $context
         * @param array $data
         */
        public function __construct(
            Context $context,
            array $data = []
        ) {
            parent::__construct($context, $data);
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
         * Return ajax url for collect button
         *
         * @return string
         */
        public function getAjaxUrl()
        {
            return $this->getUrl('hoolah/system_config/validatemerchant');
        }
    
        /**
         * Generate collect button html
         *
         * @return string
         */
        public function getButtonHtml()
        {
            $button = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setData(
                [
                    'id' => 'validate_merchant_button',
                    'label' => __('Validate merchant credentials'),
                ]
            );
    
            return $button->toHtml();
        }
    }