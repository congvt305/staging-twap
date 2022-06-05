<?php

namespace Eguana\RedInvoice\Plugin\UiComponent\DataProvider;

use Magento\Framework\Api\Filter;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Collection;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterApplierInterface;
use Eguana\RedInvoice\Ui\Component\Listing\Column\RedInvoiceColumnRender;

class FilterApplier
{
    const SALES_ORDER_GRID_NAMESPACE = 'sales_order_grid';

    /**
     * @var Http
     */
    protected Http $request;

    public function __construct(
        Http $request
    ) {
        $this->request = $request;
    }

    /**
     * @param FilterApplierInterface $subject
     * @param Collection $collection
     * @param Filter $filter
     * @return array
     */
    public function beforeApply(FilterApplierInterface $subject, Collection $collection, Filter $filter)
    {
        $namespace = $this->request->getParam('namespace');
        if ($namespace == self::SALES_ORDER_GRID_NAMESPACE) {
            if ($filter->getField() == RedInvoiceColumnRender::COLUMN_NAME) {
                $filter->setField('rid.id');
                if ($filter->getValue() == 1) {
                    $filter->setConditionType('notnull');
                } else {
                    $filter->setConditionType('null');
                }
            }
        }
        return [$collection, $filter];
    }
}
