<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: brian
 * Date: 2020/07/14
 * Time: 10:24 AM
 */

namespace Eguana\ChangeStatus\Model;

use Eguana\ChangeStatus\Model\Source\Config;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class GetCompletedOrders
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
     * @var TimezoneInterface
     */
    private $timezone;
    /**
     * @var DateTime
     */
    private $dateTime;
    /**
     * @var Config
     */
    private $config;

    /**
     * GetCompletedOrders constructor.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param DateTime $dateTime
     * @param Config $config
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

    public function getCompletedOrder($storeId)
    {
        $toBeCompletedDays = empty($this->config->getAvailableReturnDays($storeId)) ? 7 : $this->config->getAvailableReturnDays($storeId);
        $coveredDate = $this->dateTime->date('Y-m-d H:i:s', strtotime('now -' . $toBeCompletedDays . ' day'));

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('status', 'shipment_processing', 'eq')
            ->addFilter('updated_at', $coveredDate, 'lteq')
            ->create();

        $orderList = $this->orderRepository->getList($searchCriteria);

        $completeOrderList = [];
        foreach ($orderList->getItems() as $order) {
            $payment = $order->getPayment();
            $eInvoiceData = json_decode($payment->getAdditionalData(), true);

            if (!empty($eInvoiceData) && $eInvoiceData["RtnCode"] == 1) {
                $completeOrderList[] = $order;
            }
        }

        return $completeOrderList;
    }
}
