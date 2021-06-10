<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/5/20
 * Time: 10:41 AM
 */

namespace Eguana\CustomerRefund\Model;

use Eguana\CustomerRefund\Api\RefundOnlineManagementInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Sales\Api\Data\CreditmemoItemCreationInterfaceFactory;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\RefundInvoiceInterface;
use Magento\Sales\Api\RefundOrderInterface;
use Psr\Log\LoggerInterface;

class RefundOnlineManagement implements RefundOnlineManagementInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var RefundInvoiceInterface
     */
    private $refundInvoice;
    /**
     * @var RefundOrderInterface
     */
    private $refundOrder;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var CreditmemoItemCreationInterfaceFactory
     */
    private $creditmemoItemFactory;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CreditmemoItemCreationInterfaceFactory $creditmemoItemFactory,
        RefundInvoiceInterface $refundInvoice,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        RefundOrderInterface $refundOrder, // for offline creditmemo like checkmo todo: remove after test
        LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->refundInvoice = $refundInvoice;
        $this->refundOrder = $refundOrder;
        $this->logger = $logger;
        $this->invoiceRepository = $invoiceRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->creditmemoItemFactory = $creditmemoItemFactory;
        $this->messageManager = $messageManager;
    }


    /**
     * @param string $orderId
     * @return bool
     * @throws CouldNotSaveException
     */
    public function process(string $orderId): bool
    {
        try {
            $order = $this->orderRepository->get($orderId);
            $this->refund($order);
            $this->messageManager->addSuccessMessage(__('You Refunded the order'));
        } catch (\Exception $e) {
            $this->logger->info('customerRefund | something went wrong ', [$e->getMessage()]);
            $this->messageManager->addErrorMessage(__('Something is wrong with refund. Please contact our customer service.'));
            return false;
        }
        return true;
    }

    /**
     * @param OrderInterface $order
     */
    private function refund($order)
    {
        if($order->getPayment()->getMethod() === 'checkmo') { //todo remove after test
            return $this->refundOffline($order);
        }

        if ($order->getPayment()->getMethod() === 'cashondelivery') {
            return $this->refundOffline($order);
        }
        /** @var InvoiceInterface $invoice */
        $invoice = $this->getInvoice($order->getEntityId());
        $invoiceId = $invoice->getEntityId();
        $creditmemoItems = $this->buildCreditmemoItems($order);
        $isOnline = true;
        $notify = false;
        $appendComment = false;
        $comment = null;
        $argument = null;
        $this->refundInvoice->execute(
            $invoiceId,
            $creditmemoItems,
            $isOnline,
            $notify,
            $appendComment,
            $comment,
            $argument
        );
    }

    /**
     * @param int $orderId
     * @return InvoiceInterface
     */
    private function getInvoice($orderId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_id', $orderId, 'eq')->create();
        $invoice = current($this->invoiceRepository->getList($searchCriteria)->getItems());
        return $invoice;
    }

    /**
     * @param  \Magento\Sales\Model\Order $order
     * @return array
     */
    private function buildCreditmemoItems($order)
    {
        $creditmemoItems = [];
        $bundleItemIds = [];
        /** @var OrderItemInterface $orderItem */
        $myItems = $order->getItems();
        foreach ($order->getItems() as $orderItem) {
            //if item is dynamic price type bundle, we have to add children
            if ($orderItem->getProductType() === 'bundle' && $orderItem->isDummy())
            {
                $bundleItemIds[] = $orderItem->getItemId();
                continue;
            }
            if ($orderItem->getParentItemId()) {
                continue;
            }
            /** @var CreditmemoItemInterface $creditmemoItem */
            $creditmemoItem = $this->creditmemoItemFactory->create();
            $creditmemoItem->setOrderItemId($orderItem->getItemId());
            $creditmemoItem->setQty($orderItem->getQtyInvoiced());
            $creditmemoItems[] = $creditmemoItem;
        }
        //handle simple items in dynamic price type bundle
        foreach ($order->getItems() as $orderItem) {
            if (!$orderItem->getParentItemId() || !in_array($orderItem->getParentItemId(), $bundleItemIds)) {
                continue;
            }
            /** @var CreditmemoItemInterface $creditmemoItem */
            $creditmemoItem = $this->creditmemoItemFactory->create();
            $creditmemoItem->setOrderItemId($orderItem->getItemId());
            $creditmemoItem->setQty($orderItem->getQtyInvoiced());
            $creditmemoItems[] = $creditmemoItem;
        }
        return $creditmemoItems;
    }


    /**
     * @param OrderInterface $order
     */
    private function refundOffline($order)
    {
        $orderId = $order->getEntityId();
        $creditmemoItems = $this->buildCreditmemoItems($order);
        $notify = false;
        $appendComment = false;
        $comment = null;
        $argument = null;
        $this->refundOrder->execute(
            $orderId,
            $creditmemoItems,
            $notify,
            $appendComment,
            $comment,
            $argument
        );
    }
}
