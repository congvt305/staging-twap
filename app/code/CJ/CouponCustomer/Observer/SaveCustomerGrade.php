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

    protected $customer;

    protected $customerFactory;

    protected $helperData;

    /**
     * const CUSTOMER_GRADE
     */
    const POS_CUSTOMER_GRADE = 'pos_customer_grade';

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerPointsSearch $customerPointsSearch
     * @param Logger $logger
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerPointsSearch        $customerPointsSearch,
        Logger                      $logger,
        Customer $customer,
        CustomerFactory $customerFactory,
        Data $helperData
    ){
        $this->customerRepository = $customerRepository;
        $this->customerPointsSearch = $customerPointsSearch;
        $this->logger = $logger;
        $this->customer = $customer;
        $this->customerFactory = $customerFactory;
        $this->helperData = $helperData;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $isEnableCouponList = $this->helperData->isEnableCouponListPopup();
        if($isEnableCouponList) {
            $customerData = $observer->getEvent()->getCustomer();
            try {
                if (isset($customerData)) {
                    $customerId = $customerData->getId();
                    $customer = $this->customer->load($customerId);
                    $posCustomerGroup = $customer->getData(self::POS_CUSTOMER_GRADE);
                    $gradeData = $this->getCustomerGrade($customer->getId(), $customer->getWebsiteId());
                    $gradeName = '';
                    if(isset($gradeData['cstmGradeCD']) && isset($gradeData['cstmGradeNM'])) {
                        $prefix = $this->helperData->getPrefix($gradeData['cstmGradeCD']);
                        $gradeName = $prefix.'_'.$gradeData['cstmGradeNM'];
                    }
                    // $gradeName = 'Thanh Dat Group';
                    if ($gradeName && $gradeName != $posCustomerGroup) {
                        $customer->setData(self::POS_CUSTOMER_GRADE, $gradeName);
                        $customerResource = $this->customerFactory->create();
                        $customerResource->save($customer);
                    }
                }
            } catch (\Exception $exception) {
                $this->logger->info("FAIL TO SAVE POS CUSTOMER GRADE WHEN CUSTOMER LOGIN:" . $exception->getMessage());
                $this->logger->info("Customer Id:" . $customerData->getId());
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
        $customerGradeData = [];
        $customerGradeData['cstmGradeCD'] = 'TWL0003';
        $customerGradeData['cstmGradeNM'] ='Snow Crystal';
        return $customerGradeData;
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


    }

}
