<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 23/7/20
 * Time: 6:45 PM
 */
namespace Eguana\Pip\Controller\Account;

use Eguana\Pip\Model\Customers;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Leave
 *
 * Leave customer account
 */
class Leave extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\Redirect
     */
    private $redirectFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var CustomerRepository
     */
    private $customerRepo;

    /**
     * @var ManagerInterface
     */
    private $message;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Customers
     */
    private $customers;

    /**
     * Leave constructor.
     * @param Context $context
     * @param RedirectFactory $redirectFactory
     * @param SessionFactory $customerSession
     * @param CustomerRepository $customerRepository
     * @param ManagerInterface $message
     * @param UrlInterface $url
     * @param LoggerInterface $logger
     * @param Customers $customers
     */
    public function __construct(
        Context $context,
        RedirectFactory $redirectFactory,
        SessionFactory $customerSession,
        CustomerRepository $customerRepository,
        ManagerInterface $message,
        UrlInterface $url,
        LoggerInterface $logger,
        Customers $customers
    ) {
        $this->redirectFactory = $redirectFactory->create();
        $this->customerSession = $customerSession->create();
        $this->customerRepo = $customerRepository;
        $this->message = $context->getMessageManager();
        $this->url = $url;
        $this->logger = $logger;
        $this->customers = $customers;
        return parent::__construct($context);
    }

    /**
     * function is for implementing secession
     * this will change custom attribute which takes record either customer is secessioned or not
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $customerId = $this->customerSession->getId();
        $this->customers->updateCustomerInformation($customerId);
        if ($customerId) {
            try {
                $customer = $this->customerRepo->getById($customerId);
                $customer->setCustomAttribute('is_secessioned', 1);
                if ($customer) {
                    $this->customerRepo->save($customer);
                    $this->message->addSuccessMessage(__('Thank you for using our services'));
                    $this->customers->updateCustomerInformation($customerId);
                    $this->customerSession->logout();
                } else {
                    $this->message->addErrorMessage(__("we can't implement secession. please try again later"));
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
        $this->redirectFactory->setUrl('/customer/account/login');
        return $this->redirectFactory;
    }
}
