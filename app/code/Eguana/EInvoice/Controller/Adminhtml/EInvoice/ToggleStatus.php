<?php

namespace Eguana\EInvoice\Controller\Adminhtml\EInvoice;

/**
 * Class ToggleStatus
 * @package Eguana\EInvoice\Controller\Adminhtml\EInvoice
 */
class ToggleStatus extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Eguana\EInvoice\Model\EinvoiceIssue
     */
    protected $einvoiceIssue;

    /**
     * MarkEinvoiceIssued constructor.
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Eguana\EInvoice\Model\EinvoiceIssue $einvoiceIssue,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->einvoiceIssue = $einvoiceIssue;
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        foreach ($collection->getItems() as $order) {
            try {
                $status = $this->einvoiceIssue->toggleStatus($order);
                if ($status == 0) {
                    $this->messageManager->addNoticeMessage(__('Changed Einvoice Issue\'s status: Issued to Not issued. Order id %1',
                        $order->getIncrementId()));
                } elseif ($status == 1) {
                    $this->messageManager->addNoticeMessage(__('Changed Einvoice Issue\'s status: Not issued to issued. Order id %1',
                        $order->getIncrementId()));
                }
            } catch (\Exception $e) {
                $this->messageManager->addNoticeMessage($e->getMessage());
            }
        }

        $this->_redirect('sales/order/index');
    }
}