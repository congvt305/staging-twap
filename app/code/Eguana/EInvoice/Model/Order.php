<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: david
 * Date: 2020/07/20
 * Time: 1:12 PM
 */

namespace Eguana\EInvoice\Model;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class Order
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var FilterBuilder
     */
    private $filterBuilder;
    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * Order constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
    }

    /**
     * @param $storeId
     * @return OrderSearchResultInterface
     */
    public function getCompletedOrders($storeId)
    {
        $storeFilter = $this->filterBuilder->setField('store_id')
            ->setValue($storeId)
            ->setConditionType('eq')
            ->create();
        $andFilter = $this->filterGroupBuilder
            ->addFilter($storeFilter)
            ->create();

        $statusFilterShipmentProcessing = $this->filterBuilder->setField('status')
            ->setValue('shipment_processing')
            ->setConditionType('eq')
            ->create();
        $statusFilterComplete = $this->filterBuilder->setField('status')
            ->setValue('processing')
            ->setConditionType('eq')
            ->create();
        $orFilter = $this->filterGroupBuilder
            ->addFilter($statusFilterShipmentProcessing)
            ->addFilter($statusFilterComplete)
            ->create();

        $this->searchCriteriaBuilder->setFilterGroups([$andFilter, $orFilter]);
        $searchCriteria = $this->searchCriteriaBuilder->create();

        return $this->orderRepository->getList($searchCriteria);
    }

    /**
     * @param $storeId
     * @return array
     */
    public function getNotIssuedOrders($storeId)
    {
        $orderList = $this->getCompletedOrders($storeId);

        $notIssuedOrderList = [];
        foreach ($orderList->getItems() as $order) {
            $payment = $order->getPayment();
            $eInvoiceData = json_decode($payment->getAdditionalData(), true);

            if (empty($eInvoiceData) || $eInvoiceData["RtnCode"] != 1) {
                $notIssuedOrderList[] = $order;
            }
        }

        return $notIssuedOrderList;
    }
}
