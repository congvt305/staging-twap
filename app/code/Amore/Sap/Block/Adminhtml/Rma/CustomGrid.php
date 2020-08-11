<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-07-27
 * Time: 오후 10:51
 */

namespace Amore\Sap\Block\Adminhtml\Rma;

class CustomGrid extends \Magento\Rma\Block\Adminhtml\Rma\Grid
{
    /**
     * @var \Amore\Sap\Ui\Component\Listing\Column\SapRma\Options
     */
    private $rmaOptions;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Rma\Model\ResourceModel\Rma\Grid\CollectionFactory $collectionFactory,
        \Magento\Rma\Model\RmaFactory $rmaFactory,
        \Amore\Sap\Ui\Component\Listing\Column\SapRma\Options $rmaOptions,
        array $data = []
    ) {
        $this->rmaOptions = $rmaOptions;
        parent::__construct($context, $backendHelper, $collectionFactory, $rmaFactory, $data);
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'increment_id',
            [
                'header' => __('RMA'),
                'index' => 'increment_id',
                'type' => 'text',
                'header_css_class' => 'col-rma-number',
                'column_css_class' => 'col-rma-number'
            ]
        );

        $this->addColumn(
            'date_requested',
            [
                'header' => __('Requested'),
                'index' => 'date_requested',
                'type' => 'datetime',
                'html_decorators' => ['nobr'],
                'header_css_class' => 'col-period',
                'column_css_class' => 'col-period'
            ]
        );

        $this->addColumn(
            'order_increment_id',
            [
                'header' => __('Order'),
                'type' => 'text',
                'index' => 'order_increment_id',
                'header_css_class' => 'col-order-number',
                'column_css_class' => 'col-order-number'
            ]
        );

        $this->addColumn(
            'order_date',
            [
                'header' => __('Ordered'),
                'index' => 'order_date',
                'type' => 'datetime',
                'html_decorators' => ['nobr'],
                'header_css_class' => 'col-period',
                'column_css_class' => 'col-period'
            ]
        );

        $this->addColumn(
            'customer_name',
            [
                'header' => __('Customer'),
                'index' => 'customer_name',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]
        );
        /** @var $rmaModel \Magento\Rma\Model\Rma */
        $rmaModel = $this->_rmaFactory->create();
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $rmaModel->getAllStatuses(),
                'header_css_class' => 'col-status',
                'column_css_class' => 'col-status'
            ]
        );

        $this->addColumnAfter(
            'sap_response',
            [
                'header' => __('SAP Response'),
                'index' => 'sap_response',
                'type' => 'text',
                'filter' => false,
                'header_css_class' => 'col-rma-number',
                'column_css_class' => 'col-rma-number'
            ],
            'status'
        );

        $this->addColumnAfter(
            'sap_return_send_check',
            [
                'header' => __('SAP Return Status'),
                'index' => 'sap_return_send_check',
                'type' => 'options',
                'options' => $this->rmaOptions->toOptionArray(),
                'header_css_class' => 'col-rma-number',
                'column_css_class' => 'col-rma-number'
            ],
            'sap_response'
        );

        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('View'),
                        'url' => ['base' => $this->_getControllerUrl('edit')],
                        'field' => 'id',
                    ],
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
                'header_css_class' => 'col-actions',
                'column_css_class' => 'col-actions'
            ]
        );

        return parent::_prepareColumns();
    }
}
