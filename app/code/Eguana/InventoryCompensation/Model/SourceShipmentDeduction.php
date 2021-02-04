<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2021-01-27
 * Time: 오후 5:06
 */

namespace Eguana\InventoryCompensation\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Model\IsSingleSourceModeInterface;
use Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface;
use Magento\InventoryShipping\Model\GetItemsToDeductFromShipment;
use Magento\InventoryShipping\Model\SourceDeductionRequestFromShipmentFactory;
use Magento\InventorySourceDeductionApi\Model\SourceDeductionRequestInterface;
use Magento\InventorySourceDeductionApi\Model\SourceDeductionServiceInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory;
use Magento\Sales\Model\Order;

/**
 * Source Shipment Deduction when order shiped
 *
 * Class SourceShipmentDeduction
 */
class SourceShipmentDeduction
{
    /**
     * @var IsSingleSourceModeInterface
     */
    private $isSingleSourceMode;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var GetItemsToDeductFromShipment
     */
    private $getItemsToDeductFromShipment;

    /**
     * @var SourceDeductionRequestFromShipmentFactory
     */
    private $sourceDeductionRequestFromShipmentFactory;

    /**
     * @var SourceDeductionServiceInterface
     */
    private $sourceDeductionService;

    /**
     * @var ItemToSellInterfaceFactory
     */
    private $itemsToSellFactory;

    /**
     * @var PlaceReservationsForSalesEventInterface
     */
    private $placeReservationsForSalesEvent;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param GetItemsToDeductFromShipment $getItemsToDeductFromShipment
     * @param SourceDeductionRequestFromShipmentFactory $sourceDeductionRequestFromShipmentFactory
     * @param SourceDeductionServiceInterface $sourceDeductionService
     * @param ItemToSellInterfaceFactory $itemsToSellFactory
     * @param PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        IsSingleSourceModeInterface $isSingleSourceMode,
        DefaultSourceProviderInterface $defaultSourceProvider,
        GetItemsToDeductFromShipment $getItemsToDeductFromShipment,
        SourceDeductionRequestFromShipmentFactory $sourceDeductionRequestFromShipmentFactory,
        SourceDeductionServiceInterface $sourceDeductionService,
        ItemToSellInterfaceFactory $itemsToSellFactory,
        PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent,
        ShipmentRepositoryInterface $shipmentRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->getItemsToDeductFromShipment = $getItemsToDeductFromShipment;
        $this->sourceDeductionRequestFromShipmentFactory = $sourceDeductionRequestFromShipmentFactory;
        $this->sourceDeductionService = $sourceDeductionService;
        $this->itemsToSellFactory = $itemsToSellFactory;
        $this->placeReservationsForSalesEvent = $placeReservationsForSalesEvent;
        $this->shipmentRepository = $shipmentRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Shipment deduction method
     *
     * @param Order $order
     */
    public function shipmentDeduction($order)
    {
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $this->getShipmentListByOrder($order->getEntityId());

        if (!empty($shipment->getExtensionAttributes())
            && !empty($shipment->getExtensionAttributes()->getSourceCode())) {
            $sourceCode = $shipment->getExtensionAttributes()->getSourceCode();
        } elseif ($this->isSingleSourceMode->execute()) {
            $sourceCode = $this->defaultSourceProvider->getCode();
        }

        $shipmentItems = $this->getItemsToDeductFromShipment->execute($shipment);

        if (!empty($shipmentItems)) {
            $sourceDeductionRequest = $this->sourceDeductionRequestFromShipmentFactory->execute(
                $shipment,
                $sourceCode,
                $shipmentItems
            );
            $this->sourceDeductionService->execute($sourceDeductionRequest);
            $this->placeCompensatingReservation($sourceDeductionRequest);
        }
    }

    /**
     * Place compensating reservation after source deduction
     *
     * @param SourceDeductionRequestInterface $sourceDeductionRequest
     */
    private function placeCompensatingReservation(SourceDeductionRequestInterface $sourceDeductionRequest): void
    {
        $items = [];
        foreach ($sourceDeductionRequest->getItems() as $item) {
            $items[] = $this->itemsToSellFactory->create([
                'sku' => $item->getSku(),
                'qty' => $item->getQty()
            ]);
        }
        $this->placeReservationsForSalesEvent->execute(
            $items,
            $sourceDeductionRequest->getSalesChannel(),
            $sourceDeductionRequest->getSalesEvent()
        );
    }

    /**
     * Get Shipment List by Order
     *
     * @param int $orderId
     * @return ShipmentInterface
     */
    public function getShipmentListByOrder($orderId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_id', $orderId)->create();

        $shipments = $this->shipmentRepository->getList($searchCriteria);
        $shipmentRecords = $shipments->getItems();

        return reset($shipmentRecords);
    }
}
