<?php

namespace CJ\CouponCustomer\Model;

use Amore\PointsIntegration\Model\CustomerPointsSearch;
use Magento\Customer\Api\CustomerRepositoryInterface;
use CJ\CouponCustomer\Logger\Logger;
use CJ\CouponCustomer\Helper\Data;

class PosCustomerGradeUpdater
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var CustomerPointsSearch
     */
    protected $customerPointsSearch;

    /**
     * @var Customer
     */
    protected $customer;


    /**
     * @var \CJ\CouponCustomer\Helper\Data
     */
    protected $helperData;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerPointsSearch $customerPointsSearch
     * @param Logger $logger
     * @param Data $helperData
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerPointsSearch $customerPointsSearch,
        Logger $logger,
        Data $helperData
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerPointsSearch = $customerPointsSearch;
        $this->logger = $logger;
        $this->helperData = $helperData;
    }

    /**
     * Update POS customer grade
     *
     * @param $customerId
     * @return void
     */
    public function updatePOSCustomerGrade($customerId, $websiteId)
    {
        $isPOSCustomerGradeSyncEnabled = $this->helperData->isPOSCustomerGradeSyncEnabled($websiteId);
        if ($isPOSCustomerGradeSyncEnabled) {
            $this->logger->info("-----Call API POS customer grade on Cronjob-----");
            try {
                $customerData = $this->customerRepository->getById($customerId);
                $gradeData = $this->getCustomerGrade($customerData->getId(), $customerData->getWebsiteId());
                $this->logger->info("Call API POS customer grade on cronjob gradeData");
                $this->logger->info("grade Data: ", $gradeData);
                $gradeName = '';
                if (isset($gradeData['cstmGradeCD']) && isset($gradeData['cstmGradeNM'])) {
                    $prefix = $this->helperData->getPrefix($gradeData['cstmGradeCD']);
                    $gradeName = $prefix . '_' . $gradeData['cstmGradeNM'];
                    $this->logger->info("Call API POS customer grade " . $gradeName . "customer Id" . $customerId);
                }
                $posCustomerGroupId = $this->helperData->getCustomerGroupIdByName($gradeName);
                if ($posCustomerGroupId && $posCustomerGroupId != $customerData->getGroupId()) {
                    $this->logger->info("Update POS customer grade - Customer Id:" . $customerId . "Grade: " . $gradeName);
                    $customerData->setGroupId($posCustomerGroupId);
                    $this->customerRepository->save($customerData);
                }
            } catch (\Exception $exception) {
                $this->logger->info("FAIL TO SAVE POS CUSTOMER GRADE WHEN CRON SEND ORDER TO POS:" . $exception->getMessage());
                $this->logger->info("Customer Id:" . $customerId);
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
    public function getCustomerGrade($customerId, $websiteId)
    {
        $customerGradeData = [];
        try {
            $customerPointsInfo = $this->customerPointsSearch->getMemberSearchResult($customerId, $websiteId);
            if (!empty($customerPointsInfo['data']['cstmGradeCD'])) {
                $customerGradeData['cstmGradeCD'] = $customerPointsInfo['data']['cstmGradeCD'];
            }
            if (!empty($customerPointsInfo['data']['cstmGradeNM'])) {
                $customerGradeData['cstmGradeNM'] = $customerPointsInfo['data']['cstmGradeNM'];
            }
        } catch (\Exception $exception) {
            $this->logger->info("FAIL TO GET POS CUSTOMER GRADE WHEN CUSTOMER LOGIN");
            $this->logger->error($exception->getMessage());
        }
        return $customerGradeData;
    }

}
