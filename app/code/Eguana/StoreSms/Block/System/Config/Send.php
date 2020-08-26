<?php
namespace Eguana\StoreSms\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Widget\Button;
use Psr\Log\LoggerInterface;

/**
 * This class is used for button in admin to send test message
 *
 * Class Send
 */
class Send extends Field
{
    /**
     * constants
     */
    const  XML_AJAX_URL = 'storesms/system_config/send';

    /**
     * @var string
     */
    protected $_template = 'Eguana_StoreSms::system/config/send.phtml';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Send constructor.
     * @param Context $context
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->logger = $logger;
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
        parent::_getElementHtml($element);
        return $this->_toHtml();
    }

    /**
     * Return ajax url for collect button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        if ($this->_request->getParam('store')) {
            $id = $this->_request->getParam('store');
            return $this->getUrl('storesms/system_config/send/store/'.$id.'/');
        }
        if ($this->_request->getParam('website')) {
            $id = $this->_request->getParam('website');
            return $this->getUrl('storesms/system_config/send/website/'.$id.'/');
        }
        return $this->getUrl('storesms/system_config/send/');
    }

    /**
     * set button content
     *
     * @return mixed
     */
    public function getButtonHtml()
    {
        try {
            $button = $this->getLayout()->createBlock(
                Button::class
            )->setData(
                [
                    'id' => 'send-sms',
                    'label' => __('Send'),
                ]
            );
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $button->toHtml();
    }
}
