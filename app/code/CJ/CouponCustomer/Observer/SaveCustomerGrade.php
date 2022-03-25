<?php

namespace CJ\CouponCustomer\Observer;

use CJ\CouponCustomer\Logger\Logger;
use Amore\PointsIntegration\Model\CustomerPointsSearch;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

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
        Logger                      $logger){
        $this->customerRepository = $customerRepository;
        $this->customerPointsSearch = $customerPointsSearch;
        $this->logger = $logger;

    }

    /**
     * @param $observer
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function execute($observer)
    {
        try {
            /** @var \Magento\Customer\Model\Data\Customer $customer */
            $customer = $observer->getEvent()->getCustomer();
            $customerRepository = $this->customerRepository->getById($customer->getId());
            $grade = $this->getCustomerGrade($customer->getId(), $customer->getWebsiteId());
            $customerRepository->setCustomAttribute(self::POS_CUSTOMER_GRADE, $grade);
            $this->customerRepository->save($customerRepository);
        } catch (\Exception $exception) {
            $this->logger->info("FAIL TO SAVE POS CUSTOMER GRADE WHEN CUSTOMER LOGIN:" . $exception->getMessage());
            $this->logger->info("Customer Id:" . $customer->getId());
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
