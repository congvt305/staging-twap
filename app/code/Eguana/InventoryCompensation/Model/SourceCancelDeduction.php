<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2021-01-27
 * Time: 오후 5:58
 */

namespace Eguana\InventoryCompensation\Model;

use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\InventorySales\Model\GetItemsToCancelFromOrderItem;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory;
use Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface;
use Magento\InventorySalesApi\Model\GetSkuFromOrderItemInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory;

/**
 * Source Cancel Deduction if order is canceld
 *
 * Class SourceCancelDeduction
 */
class SourceCancelDeduction
{
    /**
     * @var Processor
     */
    private $priceIndexer;

    /**
     * @var SalesEventInterfaceFactory
     */
    private $salesEventFactory;

    /**
     * @var PlaceReservationsForSalesEventInterface
     */
    private $placeReservationsForSalesEvent;

    /**
     * @var SalesChannelInterfaceFactory
     */
    private $salesChannelFactory;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var GetItemsToCancelFromOrderItem
     */
    private $getItemsToCancelFromOrderItem;

    /**
     * @var GetSkuFromOrderItemInterface
     */
    private $getSkuFromOrderItem;

    /**
     * @var ItemToSellInterfaceFactory
     */
    private $itemsToSellFactory;

    /**
     * @var Json
     */
    private $json;

    /**
     * @param Processor $priceIndexer
     * @param SalesEventInterfaceFactory $salesEventFactory
     * @param PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent
     * @param SalesChannelInterfaceFactory $salesChannelFactory
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param GetItemsToCancelFromOrderItem $getItemsToCancelFromOrderItem
     * @param GetSkuFromOrderItemInterface $getSkuFromOrderItem
     * @param ItemToSellInterfaceFactory $itemsToSellFactory
     * @param Json $json
     */
    public function __construct(
        Processor $priceIndexer,
        SalesEventInterfaceFactory $salesEventFactory,
        PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent,
        SalesChannelInterfaceFactory $salesChannelFactory,
        WebsiteRepositoryInterface $websiteRepository,
        GetItemsToCancelFromOrderItem $getItemsToCancelFromOrderItem,
        GetSkuFromOrderItemInterface $getSkuFromOrderItem,
        ItemToSellInterfaceFactory $itemsToSellFactory,
        Json $json
    ) {
        $this->priceIndexer = $priceIndexer;
        $this->salesEventFactory = $salesEventFactory;
        $this->placeReservationsForSalesEvent = $placeReservationsForSalesEvent;
        $this->salesChannelFactory = $salesChannelFactory;
        $this->websiteRepository = $websiteRepository;
        $this->getItemsToCancelFromOrderItem = $getItemsToCancelFromOrderItem;
        $this->getSkuFromOrderItem = $getSkuFromOrderItem;
        $this->itemsToSellFactory = $itemsToSellFactory;
        $this->json = $json;
    }

    /**
     * Source deduction on basis of canceled order
     *
     * @param Order $order
     */
    public function cancelDeduction($order)
    {
        $orderItems = $order->getAllItems();

        /** @var OrderItem $orderItem */
        foreach ($orderItems as $orderItem) {
            $canceledItem = $this->getOrderItems($orderItem);

            $websiteId = $orderItem->getStore()->getWebsiteId();
            $websiteCode = $this->websiteRepository->getById($websiteId)->getCode();
            $salesChannel = $this->salesChannelFactory->create([
                'data' => [
                    'type' => SalesChannelInterface::TYPE_WEBSITE,
                    'code' => $websiteCode
                ]
            ]);
            $salesEvent = $this->salesEventFactory->create([
                'type' => SalesEventInterface::EVENT_ORDER_CANCELED,
                'objectType' => SalesEventInterface::OBJECT_TYPE_ORDER,
                'objectId' => (string)$orderItem->getOrderId()
            ]);

            $this->placeReservationsForSalesEvent->execute($canceledItem, $salesChannel, $salesEvent);

            $this->priceIndexer->reindexRow($orderItem->getProductId());
        }
    }

    /**
     * Get order items
     *
     * @param OrderItem $orderItem
     * @return ItemToSellInterface[]
     */
    public function getOrderItems(OrderItem $orderItem)
    {
        $itemsData = [];
        if ($orderItem->getHasChildren()) {
            if (!$orderItem->isDummy(true)) {
                foreach ($this->processComplexItem($orderItem) as $item) {
                    $itemsData[] = $item;
                }
            }
        } elseif (!$orderItem->isDummy(true)) {
            $itemSku = $this->getSkuFromOrderItem->execute($orderItem);
            $itemsData[] = $this->itemsToSellFactory->create([
                'sku' => $itemSku,
                'qty' => $orderItem->getQtyCanceled()
            ]);
        }
        return $this->groupItemsBySku($itemsData);
    }

    /**
     * Get group items by SKU
     *
     * @param ItemToSellInterface[] $canceledItems
     * @return ItemToSellInterface[]
     */
    private function groupItemsBySku(array $canceledItems): array
    {
        $processingItems = $groupedItems = [];
        foreach ($canceledItems as $item) {
            if ($item->getQuantity() == 0) {
                continue;
            }
            if (empty($processingItems[$item->getSku()])) {
                $processingItems[$item->getSku()] = $item->getQuantity();
            } else {
                $processingItems[$item->getSku()] += $item->getQuantity();
            }
        }

        foreach ($processingItems as $sku => $qty) {
            $groupedItems[] = $this->itemsToSellFactory->create([
                'sku' => $sku,
                'qty' => $qty
            ]);
        }
        return $groupedItems;
    }

    /**
     * Process complex items
     *
     * @param OrderItem $orderItem
     * @return ItemToSellInterface[]
     */
    private function processComplexItem(OrderItem $orderItem): array
    {
        $itemsToCancel = [];
        foreach ($orderItem->getChildrenItems() as $item) {
            $productOptions = $item->getProductOptions();
            if (isset($productOptions['bundle_selection_attributes'])) {
                $bundleSelectionAttributes = $this->json->unserialize(
                    $productOptions['bundle_selection_attributes']
                );
                if ($bundleSelectionAttributes) {
                    $shippedQty = $bundleSelectionAttributes['qty'] * $orderItem->getQtyShipped();
                    $qty = $item->getQtyOrdered() - max($shippedQty, $item->getQtyInvoiced()) - $item->getQtyCanceled();
                    $itemSku = $this->getSkuFromOrderItem->execute($item);
                    $itemsToCancel[] = $this->itemsToSellFactory->create([
                        'sku' => $itemSku,
                        'qty' => $qty
                    ]);
                }
            } else {
                // configurable product
                $itemSku = $this->getSkuFromOrderItem->execute($orderItem);
                $itemsToCancel[] = $this->itemsToSellFactory->create([
                    'sku' => $itemSku,
                    'qty' => $orderItem->getQtyCanceled()
                ]);
            }
        }
        return $itemsToCancel;
    }
}
