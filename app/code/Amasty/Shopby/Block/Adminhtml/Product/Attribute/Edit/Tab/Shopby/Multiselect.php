<?php

namespace Amasty\Shopby\Block\Adminhtml\Product\Attribute\Edit\Tab\Shopby;

use Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

class Multiselect extends Element implements RendererInterface
{
    /**
     * @var string
     */
    protected $_template = 'form/renderer/fieldset/multiselect.phtml';
}
