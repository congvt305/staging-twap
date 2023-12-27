<?php
namespace Magenest\Popup\Block\Adminhtml\Popup\Edit\Instance;

use Magento\Backend\Block\Template;

class Layout extends Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * Layout constructor.
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
     * Get HTML
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _toHtml()
    {
        $layoutBlock = $this->getLayout()->createBlock(
            \Magenest\Popup\Block\Adminhtml\Popup\Instance\Edit\Tab\Main\Layout::class
        )->setWidgetInstance(
            $this->_registry->registry('current_widget_instance')
        );
        return $layoutBlock->toHtml();
    }
}
