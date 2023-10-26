<?php
namespace Magenest\Popup\Block\Adminhtml\Popup\FloatingButton;

use Magento\Backend\Block\Template;

class PreviewButton extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Magenest_Popup::popup/floating_button_preview.phtml';
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * PreviewButton constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_registry = $registry;
    }

    /**
     * Get Popup
     *
     * @return mixed|null
     */
    public function getPopup()
    {
        return $this->_registry->registry('popup');
    }

    /**
     * Get Display Button
     *
     * @return string
     */
    public function getDisplayButton()
    {
        $enabled = $this->getPopup()->getEnableFloatingButton();
        if ($enabled == 0) {
            $display = "display: none;";
        } else {
            $display = "display: table-footer-group;";
        }
        return $display;
    }
}
