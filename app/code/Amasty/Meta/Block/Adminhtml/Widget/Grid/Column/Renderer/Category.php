<?php
namespace Amasty\Meta\Block\Adminhtml\Widget\Grid\Column\Renderer;
class Category
    extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
        return $row->getData('category_name');
    }
}
