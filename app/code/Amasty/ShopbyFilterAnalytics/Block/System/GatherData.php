<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Block\System;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class GatherData extends Field
{
    /**
     * Render element value
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _renderValue(AbstractElement $element)
    {
        $element->setComment(
            sprintf(
                'When enabled extension will start collecting filter usage statistics.' .
                'Manage Navigation Filters %s will have an analytics block with information about filter usage.',
                $this->getPageLinkHtml()
            )
        );

        return parent::_renderValue($element);
    }

    /**
     * @return string
     */
    private function getPageLinkHtml(): string
    {
        return sprintf('<a href="%s">page</a>', $this->getUrl('amasty_shopbyconfig/filters/index'));
    }
}
