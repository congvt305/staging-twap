<?php

declare(strict_types=1);

namespace Amasty\ShopByQuickConfig\Block\Adminhtml\Setting;

/**
 * Disallow to collapse fieldset
 */
class Fieldset extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getHeaderTitleHtml($element)
    {
        return '';
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getExtraJs($element)
    {
        return '';
    }

    protected function _getFrontendClass($element)
    {
        return parent::_getFrontendClass($element) . ' active';
    }
}
