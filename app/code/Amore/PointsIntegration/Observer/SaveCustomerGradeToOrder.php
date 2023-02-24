<?php

namespace Amore\PointsIntegration\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Amore\PointsIntegration\Model\Source\Config as PointConfig;
use Magento\Sales\Model\Order;
use \Magento\Sales\Model\OrderRepository;
use Amore\PointsIntegration\Model\CustomerPointsSearch;
use Amore\PointsIntegration\Model\Source\Config;
use Amore\PointsIntegration\Logger\Logger;

class SaveCustomerGradeToOrder implements ObserverInterface
{
    const STORE_WEBSITE_CODE = ['default', 'tw_laneige'];

    /**
     * const POS_CUSTOMER_GRADE
     */
    const POS_CUSTOMER_GRADE = 'pos_customer_grade';

    /**
     * @var PointConfig
     */
    protected $pointConfig;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

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
     * @var RequestInterface
     */
    private $request;

    /**
     * @param PointConfig $pointConfig
     * @param OrderRepository $orderRepository
     */
    public function __construct(
        PointConfig $pointConfig,
        OrderRepository $orderRepository,
        CustomerPointsSearch $customerPointsSearch,
        Config $config,
        Logger $logger,
        RequestInterface $request
    ){
        $this->pointConfig = $pointConfig;
        $this->orderRepository = $orderRepository;
        $this->customerPointsSearch = $customerPointsSearch;
        $this->config = $config;
        $this->logger = $logger;
        $this->request = $request;
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
        if ($moduleActive && in_array($order->getStore()->getCode(), self::STORE_WEBSITE_CODE)) {
            try {
                if ($customerId) {
                    //if in case from place order so check customer grade in order also
                    if (preg_match('/payment-information/', $this->request->getUri()->getPath()) && !$order->getData(self::POS_CUSTOMER_GRADE)
                        || !preg_match('/payment-information/', $this->request->getUri()->getPath())) {
                        $websiteId = $order->getStore()->getWebsiteId();
                        $customerGrade = $this->getCustomerGrade($customerId, $websiteId);
                        if ($customerGrade && ($customerGrade != $order->getData(self::POS_CUSTOMER_GRADE))) {
                            $order->setData(self::POS_CUSTOMER_GRADE, $customerGrade);
                            $this->orderRepository->save($order);
                        }
                    }
                } else {
                    $this->logger->info("CUSTOMER POINTS INFO WHEN CALL API TO GET CUSTOMER GRADE FAILED: " . "customerID: " . $customerId . ";" . "orderID: " . $order->getIncrementId());
                }
            } catch (\Exception $exception) {
                $this->logger->info("CUSTOMER POINTS INFO WHEN CALL API TO GET CUSTOMER GRADE FAILED: " . "message" . $exception->getMessage());
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
            if(isset($customerPointsInfo['data']['cstmGradeNM']) && !empty($customerPointsInfo['data']['cstmGradeNM']) ) {
                $grade = $customerPointsInfo['data']['cstmGradeNM'];
            }
        }catch (\Exception $exception) {
            $this->logger->info("CUSTOMER POINTS INFO WHEN CALL API TO GET CUSTOMER GRADE FAILED");
            $this->logger->error($exception->getMessage());
        }

        return $grade;
    }
}
