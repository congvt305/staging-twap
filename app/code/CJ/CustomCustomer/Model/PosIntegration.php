<?php

namespace CJ\CustomCustomer\Model;

use CJ\CustomCustomer\Setup\Patch\Data\AddCustomerCustomAttributes;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class PosIntegration
 */
class PosIntegration
{
    const CSTM_NO = 'cstmNO';

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Amore\CustomerRegistration\Model\POSSystem
     */
    protected $posSystem;


    /**
     * @param \Amore\CustomerRegistration\Model\POSSystem $posSystem
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Amore\CustomerRegistration\Model\POSSystem $posSystem
    ) {
        $this->customerRepository = $customerRepository;
        $this->posSystem = $posSystem;
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function synPosCstmNO(\Magento\Customer\Model\Customer $customer)
    {
        $storeId = $customer->getStoreId();
        $firstName = $customer->getFirstname();
        $lastName = $customer->getLastname();
        $customerData = $this->customerRepository->getById($customer->getId());
        try {
            $mobileNumber = $customerData->getCustomAttribute('mobile_number')->getValue();
        } catch (\Throwable $e) {
            throw new \Exception(__("Cannot get mobile number for the customer #%1. %2", $customer->getId(), $e->getMessage()));
        }
        $posData = $this->posSystem->getMemberInfo($firstName, $lastName, $mobileNumber, $storeId);
        $customerData->setCustomAttribute(AddCustomerCustomAttributes::POS_CSTM_NO, $posData[self::CSTM_NO]);
        $this->customerRepository->save($customerData);
        return $posData[self::CSTM_NO];
    }
}
