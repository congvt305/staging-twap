<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2021-01-04
 * Time: 오전 11:22
 */

namespace Amore\PointsIntegration\Model;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderRepositoryInterface;

class GetOrdersToSendPos
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var Source\Config
     */
    private $config;

    /**
     * GetOrdersToSendPos constructor.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param Source\Config $config
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        \Amore\PointsIntegration\Model\Source\Config $config
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->config = $config;
    }

    public function getOrders($storeId)
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilder
            ->addFilter('status', 'complete', 'eq')
            ->addFilter('state', 'complete', 'eq')
            ->addFilter('pos_order_send_check', 0, 'eq')
            ->addFilter('store_id', $storeId)
            ->create();

        return $this->orderRepository->getList($searchCriteriaBuilder)->getItems();
    }
}
