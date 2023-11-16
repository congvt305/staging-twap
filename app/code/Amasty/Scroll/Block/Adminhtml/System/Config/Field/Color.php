<?php

namespace Amasty\Scroll\Block\Adminhtml\System\Config\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Color extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = $element->getElementHtml();
        $value = str_replace('#', '', $element->getData('value'));
        $inverseHex = $value ? '#' . dechex(16777215 - hexdec($value)) : "";

        $html .= '<script type ="text/x-magento-init">
            {
                "*": {
                    "Amasty_Scroll/js/color": {
                        "htmlId":"' . $element->getHtmlId() . '",
                        "value":"#' . $this->escapeHtml($value) . '",
                        "inverseHex": "' . $inverseHex . '"
                    }
                }
            }
        </script>';

        return $html;
    }
}
