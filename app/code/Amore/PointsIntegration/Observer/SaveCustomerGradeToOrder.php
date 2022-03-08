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
use Magento\TestFramework\Inspection\Exception;

class SaveCustomerGradeToOrder implements ObserverInterface
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
     * const POS_CUSTOMER_GRADE
     */
    const POS_CUSTOMER_GRADE = 'pos_customer_grade';

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

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /**
         * @var Order $order
         */
        $order = $observer->getEvent()->getOrder();
        $customerId = $order->getCustomerId();
        $moduleActive = $this->pointConfig->getActive($order->getStore()->getWebsiteId());
        if ($moduleActive) {
            try {
                if ($customerId && !$order->getData(self::POS_CUSTOMER_GRADE)) {
                    $websiteId = $order->getStore()->getWebsiteId();
                    $customerGrade = $this->getCustomerGrade($customerId, $websiteId);
                    if($customerGrade) {
                        $order->setData(self::POS_CUSTOMER_GRADE, $customerGrade);
                        $this->orderRepository->save($order);
                    }
                    else {
                        $this->logger->info("CUSTOMER POINTS INFO WHEN CALL API TO GET CUSTOMER GRADE FAILED: " . "customerID: " . $customerId . ";" . "orderID: " . $order->getIncrementId());
                    }
                }
            } catch (\Exception $exception) {
                $this->logger->info("CUSTOMER POINTS INFO WHEN CALL API TO GET CUSTOMER GRADE FAILED: ". "message". $exception->getMessage());
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
        $grade = null;
        try {
            $customerPointsInfo = $this->customerPointsSearch->getMemberSearchResult($customerId, $websiteId);
            $grade = isset($customerPointsInfo['data']['grade']) ?? null;
        }catch (\Exception $exception) {
            $this->logger->info("CUSTOMER POINTS INFO WHEN CALL API TO GET CUSTOMER GRADE FAILED");
            $this->logger->error($exception->getMessage());
        }

        return $grade;
    }
}
