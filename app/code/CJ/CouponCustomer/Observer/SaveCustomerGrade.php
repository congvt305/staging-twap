<?php

namespace CJ\CouponCustomer\Observer;

use Amore\PointsIntegration\Model\CustomerPointsSearch;
use Magento\Framework\Event\ObserverInterface;
use CJ\CouponCustomer\Logger\Logger;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\CustomerFactory;

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
        CustomerFactory $customerFactory
    ){
        $this->customerRepository = $customerRepository;
        $this->customerPointsSearch = $customerPointsSearch;
        $this->logger = $logger;
        $this->customer = $customer;
        $this->customerFactory = $customerFactory;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customerData = $observer->getEvent()->getCustomer();
        try {
            if (isset($customerData)) {
                $customerId = $customerData->getId();
                $customer = $this->customer->load($customerId);
                $posCustomerGroup = $customer->getData(self::POS_CUSTOMER_GRADE);
                $grade = $this->getCustomerGrade($customer->getId(), $customer->getWebsiteId());
                //$grade = 'Thanh Dat Group';
                if($grade && $grade != $posCustomerGroup) {
                    $customer->setData(self::POS_CUSTOMER_GRADE, $grade);
                    $customerResource = $this->customerFactory->create();
                    $customerResource->save($customer);
                }
            }
        }catch (\Exception $exception) {
            $this->logger->info("FAIL TO SAVE POS CUSTOMER GRADE WHEN CUSTOMER LOGIN:" . $exception->getMessage());
            $this->logger->info("Customer Id:" . $customerData->getId());
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
            if (isset($customerPointsInfo['data']['cstmGradeNM']) && !empty($customerPointsInfo['data']['cstmGradeNM'])) {
                $grade = $customerPointsInfo['data']['cstmGradeNM'];
            }
        } catch (\Exception $exception) {
            $this->logger->info("FAIL TO GET POS CUSTOMER GRADE WHEN CUSTOMER LOGIN");
            $this->logger->error($exception->getMessage());
        }

        return $grade;
    }

}
