<?php
    namespace Hoolah\Hoolah\Block\System\Config\Form;
    
    use \Magento\Backend\Block\Template\Context;
    use \Magento\Framework\Data\Form\Element\AbstractElement;
    
    use \Hoolah\Hoolah\Controller\Main as HoolahMain;
    
    class SendLogsButton extends \Magento\Config\Block\System\Config\Form\Field
    {
        /**
         * @var string
         */
        protected $_template = 'Hoolah_Hoolah::system/config/send_logs_button.phtml';
    
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
            return $this->getUrl('hoolah/system_config/sendlogs');
        }
        
        public function getInfoHtml()
        {
            HoolahMain::load_configs();
            
            $filesize = 0;
            foreach (\Hoolah\Hoolah\Controller\Adminhtml\System\Config\SendLogs::logsPath() as $logPath)
                if (file_exists($logPath))
                    $filesize += filesize($logPath);
            
            $result = '<div class="send_logs_button_messages">';
            
            $result .= '<!-- \Hoolah\Hoolah\Controller\Adminhtml\System\Config\SendLogs::logPath() -->'; 
            
            if ($filesize)
            {
                $result .= '<!--Full size: '.number_format($filesize / 1000000, 2, ',', '').' MB.-->';
                //$result .= '<br/>We will send the last '.HOOLAH_LOG_LAST_LINES.' lines.';
            }
            else
                $result .= 'Magento log could not be accessed';
            
            $result .= '</div>';
            
            return $result;
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
                    'id' => 'send_logs_button',
                    'label' => __('Send logs'),
                ]
            );
    
            return $button->toHtml();
        }
    }