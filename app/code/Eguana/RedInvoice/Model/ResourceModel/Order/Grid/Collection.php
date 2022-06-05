<?php

namespace Eguana\RedInvoice\Model\ResourceModel\Order\Grid;

use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OriginalCollection;
use Eguana\RedInvoice\Ui\Component\Listing\Column\RedInvoiceColumnRender;

class Collection extends OriginalCollection
{
    const RED_INVOICE_TABLE = 'eguana_red_invoice_data';

    /**
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $redInvoiceTable = $this->getTable(self::RED_INVOICE_TABLE);
        $this->getSelect()->joinLeft(
            ['rid' => $redInvoiceTable],
            'rid.order_id = main_table.entity_id',
            ['red_invoice' => 'id']
        );
        parent::_renderFiltersBefore();
    }
}
