<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 29/6/20
 * Time: 1:40 PM
 */
namespace Eguana\MobileLogin\Plugin;

use Eguana\MobileLogin\Helper\Data;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerSearchResultsInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ValidateCustomer
 *
 * Validate customer data class
 */
class ValidateCustomer
{
    /**
     * @var Data
     */
    private $mobileLoginHelper;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * ValidateCustomer constructor.
     * @param Data $mobileLoginHelper
     * @param CustomerRepositoryInterface $customerRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Data $mobileLoginHelper,
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager
    ) {
        $this->mobileLoginHelper = $mobileLoginHelper;
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
    }

    /**
     * Validate customer data before login
     * @param AccountManagement $subject
     * @param $username
     * @param $password
     * @return array
     * @throws InputException
     */
    public function beforeAuthenticate(
        AccountManagement $subject,
        $username,
        $password
    ) {
        $customerData = $username;
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        if ($this->mobileLoginHelper->isEnabledInFrontend($websiteId)) {
            if (!filter_var($customerData, FILTER_VALIDATE_EMAIL)) {
                $username = $this->checkIfCustomerExists($customerData);
            }
        }
        return [$username, $password];
    }

    /**
     * Get customer by mobile number
     * @param $customerData
     * @return CustomerSearchResultsInterface
     * @throws LocalizedException
     */
    public function getCustomerByMobileNumber($customerData)
    {
        $customerCount = null;
        $customerEmail = null;
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('mobile_number', $customerData, 'eq')
            ->create();
        $customerObj = $this->customerRepository->getList($searchCriteria);
        $customerCount = $customerObj->getTotalCount();
        $customer = $customerObj->getItems();
        if ($customerCount > 0) {
            foreach ($customer as $value) {
                $customerEmail = $value->getEmail();
            }
            return $customerEmail;
        }
        return $customerEmail;
    }

    /**
     * Check if customer exists based on data
     * @param $customerData
     * @return string|null
     * @throws InputException
     */
    private function checkIfCustomerExists($customerData)
    {
        $customerEmail = null;
        try {
            $customerEmail = $this->getCustomerByMobileNumber($customerData);
            return $customerEmail;
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
        throw new InputException(__('Invalid login or password.'));
    }
}
