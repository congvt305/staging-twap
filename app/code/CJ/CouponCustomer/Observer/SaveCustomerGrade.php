<?php

namespace CJ\CouponCustomer\Observer;

use Amore\PointsIntegration\Model\CustomerPointsSearch;
use Magento\Framework\Event\ObserverInterface;
use CJ\CouponCustomer\Logger\Logger;
use Magento\Customer\Api\CustomerRepositoryInterface;
use CJ\CouponCustomer\Helper\Data;
use CJ\CouponCustomer\Model\PosCustomerGradeUpdater;

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
     * @var Data
     */
    protected $helperData;

    /**
     * @var PosCustomerGradeUpdater
     */
    protected $posCustomerGradeUpdater;


    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerPointsSearch $customerPointsSearch
     * @param Logger $logger
     * @param Data $helperData
     * @param PosCustomerGradeUpdater $posCustomerGradeUpdater
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerPointsSearch        $customerPointsSearch,
        Logger                      $logger,
        Data $helperData,
        PosCustomerGradeUpdater $posCustomerGradeUpdater
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerPointsSearch = $customerPointsSearch;
        $this->logger = $logger;
        $this->helperData = $helperData;
        $this->posCustomerGradeUpdater = $posCustomerGradeUpdater;
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
                    $customer = $this->customerRepository->getById($customerId);
                    $gradeData = $this->posCustomerGradeUpdater->getCustomerGrade($customerId, $customer->getWebsiteId());
                    $gradeName = '';
                    if (isset($gradeData['cstmGradeCD']) && isset($gradeData['cstmGradeNM'])) {
                        $prefix = $this->helperData->getPrefix($gradeData['cstmGradeCD']);
                        $gradeName = $prefix . '_' . $gradeData['cstmGradeNM'];
                        $this->logger->info("Call API POS customer grade: " . $gradeName . " customer ID: " . $customerId);
                    }
                    $posCustomerGroupId = $this->helperData->getCustomerGroupIdByName($gradeName);
                    if ($posCustomerGroupId && $posCustomerGroupId != $customer->getGroupId()) {
                        $this->logger->info("Update POS customer grade - Customer Id:" . $customerData->getId() . "Grade: " . $gradeName);
                        $customer->setGroupId($posCustomerGroupId);
                        $this->customerRepository->save($customer);
                    }
                }
            } catch (\Exception $exception) {
                $this->logger->info("FAIL TO SAVE POS CUSTOMER GRADE WHEN CUSTOMER LOGIN:" . $exception->getMessage());
                $this->logger->info("Customer Id:" . $customerData->getId());
            }
        }
    }


}
