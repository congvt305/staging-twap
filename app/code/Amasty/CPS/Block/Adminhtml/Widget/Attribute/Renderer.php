<?php

namespace Amasty\CPS\Block\Adminhtml\Widget\Attribute;

/**
 * @method string getLabel()
 * @method string getValue()
 */
class Renderer extends \Magento\Framework\DataObject
{
    /**
     * @var \Magento\Framework\Escaper
     */
    public $escaper;

    /**
     * @param \Magento\Framework\Escaper $escaper
     * @param array $data
     */
    public function __construct(\Magento\Framework\Escaper $escaper, array $data = [])
    {
        parent::__construct($data);
        $this->escaper = $escaper;
    }

    /**
     * @return string
     */
    public function render()
    {
        return $this->escaper->escapeHtml($this->getValue());
    }
}
