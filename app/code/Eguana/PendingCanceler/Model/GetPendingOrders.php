<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-08-18
 * Time: 오후 4:30
 */

namespace Eguana\PendingCanceler\Model;

use Eguana\PendingCanceler\Model\Source\Config;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\OrderRepositoryInterface;

class GetPendingOrders
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
     * @var DateTime
     */
    private $dateTime;
    /**
     * @var Source\Config
     */
    private $config;

    /**
     * GetCompletedOrders constructor.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param DateTime $dateTime
     * @param Source\Config $config
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        DateTime $dateTime,
        Config $config
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->dateTime = $dateTime;
        $this->config = $config;
    }

    public function getPendingOrders($storeId)
    {
        $daysLeftToCancel = $this->config->getDaysToCancel($storeId);
        if (empty($daysLeftToCancel)) {
            $daysLeftToCancel = 0;
        }

        $coveredDate = $this->dateTime->date('Y-m-d H:i:s', strtotime('now -' . $daysLeftToCancel . ' day'));

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('status', 'pending', 'eq')
            ->addFilter('created_at', $coveredDate, 'lteq')
            ->create();

        return $this->orderRepository->getList($searchCriteria);
    }
}
