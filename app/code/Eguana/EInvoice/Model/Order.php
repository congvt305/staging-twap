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
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Eguana\EInvoice\Model\Source\Config;

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
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var Config
     */
    private $config;

    /**
     * Order constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param DateTime $dateTime
     * @param Config $config
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        DateTime $dateTime,
        Config $config
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->dateTime = $dateTime;
        $this->config = $config;
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
        $daysLimit = $this->config->getDaysLimit($storeId);
        $dateFrom = $this->dateTime->date('Y-m-d H:i:s', strtotime('now -'.$daysLimit.'days'));
        $dateLimitFilter = $this->filterBuilder->setField('created_at')
            ->setValue($dateFrom)
            ->setConditionType('gteq')
            ->create();
        $andFilter2 = $this->filterGroupBuilder
            ->addFilter($dateLimitFilter)
            ->create();

        $statusFilterShipmentProcessing = $this->filterBuilder->setField('status')
            ->setValue('shipment_processing')
            ->setConditionType('eq')
            ->create();
        $statusFilterComplete = $this->filterBuilder->setField('status')
            ->setValue('complete')
            ->setConditionType('eq')
            ->create();
        $orFilter = $this->filterGroupBuilder
            ->addFilter($statusFilterShipmentProcessing)
            ->addFilter($statusFilterComplete)
            ->create();

        $this->searchCriteriaBuilder->setFilterGroups([$andFilter, $andFilter2, $orFilter]);
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
