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
        $customerObserver = $observer->getEvent()->getCustomer();
        if(isset($customerObserver)) {
            $customerId = $customerObserver->getId();
            $customer = $this->customer->load($customerId);
            $data = "posgrade";
            $customerData = $customer->getDataModel();
            $customerData->setCustomAttribute(self::POS_CUSTOMER_GRADE,$data);
            $customer->updateData($customerData);
            $customerResource = $this->customerFactory->create();
            $customerResource->saveAttribute($customer, self::POS_CUSTOMER_GRADE);
        }
    }
}
