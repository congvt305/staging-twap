<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Block\System;

use Amasty\ShopbyFilterAnalytics\Controller\Adminhtml\Config\FlushData;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class FlushButton extends Field
{
    /**
     * Set template to itself
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('Amasty_ShopbyFilterAnalytics::config/flushButton.phtml');
        return $this;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        if (!$this->_authorization->isAllowed(FlushData::ADMIN_RESOURCE)) {
            return '';
        }
        
        $element = clone $element;
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        
        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        // button_label field contained only in the OriginalData
        $originalData = $element->getOriginalData();
        $this->addData(
            [
                'button_label' => __($originalData['button_label']),
                'ajax_url' => $this->_urlBuilder->getUrl('amasty_shopbyanalytic/config/flushData'),
            ]
        );

        return $this->_toHtml();
    }
}
