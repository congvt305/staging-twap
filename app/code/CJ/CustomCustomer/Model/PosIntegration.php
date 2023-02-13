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
            throw new \Exception("Can't get mobile phone number");
        }
        $posData = $this->posSystem->getMemberInfo($firstName, $lastName, $mobileNumber, $storeId);
        if (!isset($posData[self::CSTM_NO])) {
            throw new \Exception("POS has no data cstmNO");
        }
        try {
            $customerData->setCustomAttribute(AddCustomerCustomAttributes::POS_CSTM_NO, $posData[self::CSTM_NO]);
            $this->customerRepository->save($customerData);
        } catch (\Exception $e) {
            throw new \Exception(__("Could not save pos cstmNO %1 to customer. Error message: %2", $posData[self::CSTM_NO], $e->getMessage()));
        }
        return $posData[self::CSTM_NO];
    }
}
