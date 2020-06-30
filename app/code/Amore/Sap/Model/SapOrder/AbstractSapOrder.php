<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-25
 * Time: 오후 8:18
 */

namespace Amore\Sap\Model\SapOrder;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Amore\Sap\Model\Source\Config;

abstract class AbstractSapOrder
{
    // 정상 주문
    const NORMAL_ORDER = 'ZA01';
    // 반품
    const RETURN_ORDER = 'ZR01';
    // 잡출 주문
    const SAMPLE_ORDER = 'ZFA1';
    // 잡출 반품
    const SAMPLE_RETURN = 'ZFR1';


    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;
    /**
     * @var Config
     */
    protected $config;

    /**
     * AbstractSapOrder constructor.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param StoreRepositoryInterface $storeRepository
     * @param Config $config
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        StoreRepositoryInterface $storeRepository,
        Config $config
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->storeRepository = $storeRepository;
        $this->config = $config;
    }

    public function singleOrderData($incrementId)
    {
        return null;
    }

    public function getStore($storeId)
    {
        try {
            return $this->storeRepository->get($storeId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return $e->getMessage();
        }
    }

    public function getOrderInfo($incrementId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId, 'eq')
            ->create();

        $orderList = $this->orderRepository->getList($searchCriteria)->getItems();
        $orderCount = $this->orderRepository->getList($searchCriteria)->getTotalCount();

        if ($orderCount == 1) {
            return reset($orderList);
        } else {
            return null;
        }
    }

}
