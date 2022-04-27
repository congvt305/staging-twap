<?php

namespace CJ\CouponCustomer\Helper;

use Amore\PointsIntegration\Model\CustomerPointsSearch;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use CJ\CouponCustomer\Logger\Logger;
use CJ\CouponCustomer\Helper\Data;

class UpdatePOSCustomerGradeHelper extends AbstractHelper
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
     * @param Context $context
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerPointsSearch $customerPointsSearch
     * @param Logger $logger
     * @param Customer $customer
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        CustomerRepositoryInterface $customerRepository,
        CustomerPointsSearch $customerPointsSearch,
        Logger $logger,
        Customer $customer,
        Data $helperData
    ) {
        parent::__construct($context);
        $this->customerRepository = $customerRepository;
        $this->customerPointsSearch = $customerPointsSearch;
        $this->logger = $logger;
        $this->customer = $customer;
        $this->helperData = $helperData;
    }

    /**
     * Update POS customer grade
     *
     * @param $customerId
     * @return void
     */
    public function updatePOSCustomerGrade($customerId)
    {
        $isPOSCustomerGradeSyncEnabled = $this->helperData->isPOSCustomerGradeSyncEnabled();
        if ($isPOSCustomerGradeSyncEnabled) {
            try {
                $customer = $this->customer->load($customerId);
                $gradeData = $this->getCustomerGrade($customer->getId(), $customer->getWebsiteId());
                $gradeName = '';
                if (isset($gradeData['cstmGradeCD']) && isset($gradeData['cstmGradeNM'])) {
                    $prefix = $this->helperData->getPrefix($gradeData['cstmGradeCD']);
                    $gradeName = $prefix . '_' . $gradeData['cstmGradeNM'];
                    $this->logger->info("Call API POS customer grade " . $gradeName . "customer Id" . $customerId);
                }
                $posCustomerGroupId = $this->helperData->getCustomerGroupIdByName($gradeName);
                if ($posCustomerGroupId && $posCustomerGroupId != $customer->getGroupId()) {
                    $this->logger->info("Update POS customer grade - Customer Id:" . $customerId . "Grade: " . $gradeName);
                    $customerData = $this->customerRepository->getById($customerId);
                    $customerData->setGroupId($posCustomerGroupId);
                    $this->customerRepository->save($customerData);
                }
            } catch (\Exception $exception) {
                $this->logger->info("FAIL TO SAVE POS CUSTOMER GRADE WHEN CRON SEND ORDER TO POS:" . $exception->getMessage());
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
    public function getCustomerGrade($customerId, $websiteId)
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
