<?php

namespace Amore\PointsIntegration\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Amore\PointsIntegration\Model\Source\Config as PointConfig;
use Magento\Rma\Model\ResourceModel\Rma\CollectionFactory;
use Magento\Sales\Model\Order;
use \Magento\Sales\Model\OrderRepository;
use Amore\PointsIntegration\Model\CustomerPointsSearch;
use Amore\PointsIntegration\Model\Source\Config;
use Amore\PointsIntegration\Logger\Logger;

class POSSaveCustomerGrade implements ObserverInterface
{
    /**
     * @var PointConfig
     */
    protected $pointConfig;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var CollectionFactory
     */
    protected $rmaCollectionFactory;

    /**
     * @var CustomerPointsSearch
     */
    protected $customerPointsSearch;
    /**
     * @var Config
     */
    protected $config;
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param PointConfig $pointConfig
     * @param OrderRepository $orderRepository
     */
    public function __construct(
        PointConfig          $pointConfig,
        OrderRepository      $orderRepository,
        CustomerPointsSearch $customerPointsSearch,
        Config               $config,
        Logger               $logger
    ){
        $this->pointConfig = $pointConfig;
        $this->orderRepository = $orderRepository;
        $this->customerPointsSearch = $customerPointsSearch;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        /**
         * @var Order $order
         */
        $order = $observer->getEvent()->getOrder();

        $moduleActive = $this->pointConfig->getActive($order->getStore()->getWebsiteId());
        if ($moduleActive) {
            try {
                $customerId = $order->getCustomerId();
                $websiteId = $order->getStore()->getWebsiteId();
                $customerPointData = $this->getCustomerGrade($customerId, $websiteId);
                if(empty($customerPointData)) {
                    $this->logger->info("CUSTOMER POINTS INFO WHEN CALL API TO GET CUSTOMER GRADE");
                    $this->logger->debug($customerPointData);
                    return;
                }
                $customerGrade = $customerPointData['cstmGradeNM'];
                $order->setData('pos_customer_grade', $customerGrade);
                $this->orderRepository->save($order);
            } catch (\Exception $e) {
                throw $e;
            }
        }
    }


    /**
     * get Customer Points Data use API
     * @param $customer
     * @return array|mixed
     */
    private function getCustomerGrade($customerId, $websiteId)
    {
        $customerPointsInfo = $this->customerPointsSearch->getMemberSearchResult($customerId, $websiteId);
        if ($this->config->getLoggerActiveCheck($websiteId)) {
            $this->logger->info("CUSTOMER POINTS INFO");
            $this->logger->debug($customerPointsInfo);
        }

        if ($this->customerPointsSearch->responseValidation($customerPointsInfo, $websiteId)) {
            return $customerPointsInfo['data'];
        } else {
            return [];
        }
    }
}
