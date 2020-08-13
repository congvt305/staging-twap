<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 13/8/20
 * Time: 4:35 PM
 */
namespace Amore\CustomerRegistration\Plugin;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Message\ManagerInterface;

/**
 * This class is used for before save plugin which validate if the customer
 * accepts the term and services policy
 *
 * Class ValidateCustomer
 */
class ValidateCustomer
{
    /**
     * @var RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * ValidateCustomer constructor.
     * @param RedirectFactory $resultRedirectFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        RedirectFactory $resultRedirectFactory,
        ManagerInterface $messageManager
    ) {
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * beforeSave
     * Check if the customer accepts the term and services policy
     * @param CustomerRepository $subject
     * @param CustomerInterface $customer
     * @param null $passwordHash
     * @return array
     * @throws InputException
     */
    public function beforeSave(
        CustomerRepository $subject,
        CustomerInterface $customer,
        $passwordHash = null
    ) {
        $termsValue = $customer->getCustomAttribute('terms_and_services_policy')->getValue();
        $customerId = $customer->getId();
        if (!$termsValue) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addError(__('Please accept the terms and services policy.'));
            throw new InputException();
        }
        return [$customer ,$passwordHash];
    }
}
