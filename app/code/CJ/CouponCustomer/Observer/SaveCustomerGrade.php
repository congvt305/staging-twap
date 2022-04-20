<?php

namespace CJ\CouponCustomer\Observer;

use Amore\PointsIntegration\Model\CustomerPointsSearch;
use Magento\Framework\Event\ObserverInterface;
use CJ\CouponCustomer\Logger\Logger;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\CustomerFactory;
use CJ\CouponCustomer\Helper\Data;

class SaveCustomerGrade implements ObserverInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var
     */
    protected $logger;
    /**
     * @var
     */
    protected $customerPointsSearch;

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerPointsSearch $customerPointsSearch
     * @param Logger $logger
     * @param Customer $customer
     * @param CustomerFactory $customerFactory
     * @param Data $helperData
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerPointsSearch        $customerPointsSearch,
        Logger                      $logger,
        Customer $customer,
        CustomerFactory $customerFactory,
        Data $helperData
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerPointsSearch = $customerPointsSearch;
        $this->logger = $logger;
        $this->customer = $customer;
        $this->customerFactory = $customerFactory;
        $this->helperData = $helperData;
    }

    /**
     * Observer save pos customer grade when customer login
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $isPOSCustomerGradeSyncEnabled = $this->helperData->isPOSCustomerGradeSyncEnabled();
        if ($isPOSCustomerGradeSyncEnabled) {
            $customerData = $observer->getEvent()->getCustomer();
            try {
                if (isset($customerData)) {
                    $customerId = $customerData->getId();
                    $customer = $this->customer->load($customerId);
                    $gradeData = $this->getCustomerGrade($customer->getId(), $customer->getWebsiteId());
                    $gradeName = '';
                    if (isset($gradeData['cstmGradeCD']) && isset($gradeData['cstmGradeNM'])) {
                        $prefix = $this->helperData->getPrefix($gradeData['cstmGradeCD']);
                        $gradeName = $prefix . '_' . $gradeData['cstmGradeNM'];
                    }
                    $posCustomerGroupId = $this->helperData->getCustomerGroupIdByName($gradeName);
                    if ($posCustomerGroupId && $posCustomerGroupId != $customer->getGroupId()) {
                       $customerData = $this->customerRepository->getById($customerId);
                       $customerData->setGroupId($posCustomerGroupId);
                       $this->customerRepository->save($customerData);
                    }
                }
            } catch (\Exception $exception) {
                $this->logger->info("FAIL TO SAVE POS CUSTOMER GRADE WHEN CUSTOMER LOGIN:" . $exception->getMessage());
                $this->logger->info("Customer Id:" . $customerData->getId());
            }
        }
    }

    /**
     * Get customer Points Data use API
     *
     * @param int $customerId
     * @param int $websiteId
     * @return array|mixed
     */
    private function getCustomerGrade($customerId, $websiteId)
    {
        $customerGradeData = [];
        try {
            $customerPointsInfo = $this->customerPointsSearch->getMemberSearchResult($customerId, $websiteId);
            if (isset($customerPointsInfo['data']['cstmGradeCD']) && !empty($customerPointsInfo['data']['cstmGradeCD'])) {
                $customerGradeData['cstmGradeCD'] = $customerPointsInfo['data']['cstmGradeCD'];
            }
            if (isset($customerPointsInfo['data']['cstmGradeNM']) && !empty($customerPointsInfo['data']['cstmGradeNM'])) {
                $customerGradeData['cstmGradeNM'] = $customerPointsInfo['data']['cstmGradeNM'];
            }
        } catch (\Exception $exception) {
            $this->logger->info("FAIL TO GET POS CUSTOMER GRADE WHEN CUSTOMER LOGIN");
            $this->logger->error($exception->getMessage());
        }
        return $customerGradeData;
    }

}
