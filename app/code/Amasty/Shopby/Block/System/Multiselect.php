<?php

namespace Amasty\Shopby\Block\System;

class Multiselect extends \Magento\Config\Block\System\Config\Form\Field
{
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // phpcs:ignore
        return parent::_getElementHtml($element) . "
        <script>
            require([
                'jquery',
                'chosen'
            ], function ($, chosen) {
                $('#" . $element->getId() . "').chosen({
                    width: '100%',
                    placeholder_text: '" .  __('Select Options') . "'
                });
            })
        </script>";
    }
}
