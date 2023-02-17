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
     * @var \Magento\Customer\Model\AddressRegistry
     */
    protected $addressRegistry;

    /**
     * @var \CJ\CustomCustomer\Helper\Data
     */
    protected $config;

    /**
     * @var \CJ\CustomCustomer\Logger\Logger
     */
    protected $logger;

    /**
     * @param \Amore\CustomerRegistration\Model\POSSystem $posSystem
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\AddressRegistry $addressRegistry
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Amore\CustomerRegistration\Model\POSSystem $posSystem,
        \Magento\Customer\Model\AddressRegistry $addressRegistry,
        \CJ\CustomCustomer\Helper\Data $config,
        \CJ\CustomCustomer\Logger\Logger $logger
    ) {
        $this->customerRepository = $customerRepository;
        $this->posSystem = $posSystem;
        $this->addressRegistry = $addressRegistry;
        $this->config = $config;
        $this->logger = $logger;
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
        $logEnabled = $this->config->getLoggingEnabled();
        $customerData = $this->customerRepository->getById($customer->getId());
        // No need to validate customer address while sync pos cstmNO
        $this->disableAddressValidation($customerData);
        try {
            $mobileNumber = $customerData->getCustomAttribute('mobile_number')->getValue();
        } catch (\Throwable $e) {
            throw new \Exception("Can't get mobile phone number");
        }
        $posData = $this->posSystem->getMemberInfo($firstName, $lastName, $mobileNumber, $storeId);

        if (!isset($posData[self::CSTM_NO])) {
            if ($logEnabled) {
                $this->logger->info("POST DATA INFO WHEN CALL API TO SYN POS CUSTOMER ID FAILED");
                $this->logger->info(json_encode($posData));
            }
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

    /**
     * Disable Customer Address Validation
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @throws NoSuchEntityException
     */
    protected function disableAddressValidation($customer)
    {
        foreach ($customer->getAddresses() as $address) {
            $addressModel = $this->addressRegistry->retrieve($address->getId());
            $addressModel->setShouldIgnoreValidation(true);
        }
    }
}
