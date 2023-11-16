<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Advanced Search Base for Magento 2
*/

namespace Amasty\Xsearch\Block\Adminhtml\Form\Field;

use Amasty\Xsearch\Helper\Data;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class Attributes extends Select
{
    public const EXCLUDED_ATTRIBUTES = ['category_ids', 'visibility'];

    /**
     * @var Data
     */
    private $xSearchHelper;

    public function __construct(
        Data $xSearchHelper,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->xSearchHelper = $xSearchHelper;
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        $productAttributes = $this->xSearchHelper->getProductAttributes();
        foreach ($productAttributes as $attribute) {
            if (!in_array($attribute->getAttributeCode(), self::EXCLUDED_ATTRIBUTES)) {
                $this->addOption(
                    $attribute->getAttributeCode(),
                    $this->escapeQuote((string) $attribute->getFrontendLabel())
                );
            }
        }

        return parent::_toHtml();
    }

    /**
     * @param $value
     * @return mixed
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
