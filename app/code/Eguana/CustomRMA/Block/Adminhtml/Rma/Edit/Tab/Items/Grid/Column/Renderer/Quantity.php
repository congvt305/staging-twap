<?php
declare(strict_types=1);

namespace Eguana\CustomRMA\Block\Adminhtml\Rma\Edit\Tab\Items\Grid\Column\Renderer;

class Quantity extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Renders quantity as integer, remove BUNDLE logic from origin class
     * @see \Magento\Rma\Block\Adminhtml\Rma\Edit\Tab\Items\Grid\Column\Renderer\Quantity::_getValue
     *
     * @param \Magento\Framework\DataObject $row
     * @return int|string
     */
    public function _getValue(\Magento\Framework\DataObject $row)
    {
        $quantity = parent::_getValue($row);
        if ($row->getIsQtyDecimal()) {
            return sprintf("%01.4f", $quantity);
        } else {
            return intval($quantity);
        }
    }
}
