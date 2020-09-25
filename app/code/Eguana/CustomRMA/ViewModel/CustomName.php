<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/9/20
 * Time: 2:11 PM
 */
namespace Eguana\CustomRMA\ViewModel;

use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepositoryInterfaceAlias;
use Magento\Framework\View\Element\Block\ArgumentInterface as ArgumentInterfaceAlias;
use Psr\Log\LoggerInterface;

/**
 * This class used to reverse the customer first and last name
 * Class CustomName
 */
class CustomName implements ArgumentInterfaceAlias
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CustomName constructor.
     * @param CustomerRepositoryInterfaceAlias $customerRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        CustomerRepositoryInterfaceAlias $customerRepository,
        LoggerInterface $logger
    ) {
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
    }

    /**
     * This Method is used to reverse the First and Last Name
     * @param $customerId
     * @return string
     */
    public function getCustomName($customerId)
    {
        $customCustomerName = '';
        try {
            $customer = $this->customerRepository->getById($customerId);
            $firstName = $customer->getFirstname();
            $lastName  = $customer->getLastname();
            $customCustomerName = $lastName.' '.$firstName;
            return $customCustomerName;
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $customCustomerName;
    }
}
