<?php

namespace Amasty\CPS\Block\Adminhtml\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;

class AttributeCodeChecker extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @return \Magento\Config\Block\System\Config\Form\Field
     */
    protected function _prepareLayout()
    {
        $this->setTemplate('Amasty_CPS::system/config/modal_message.phtml');

        return parent::_prepareLayout();
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element = clone $element;
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
}
