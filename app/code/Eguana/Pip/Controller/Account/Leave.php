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

use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Eguana\Pip\Api\TerminatedCustomerRepositoryInterface;
use Eguana\Pip\Model\TerminatedCustomerFactory;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
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
     * @var SessionFactory
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
     * @var Registry
     */
    private $registry;

    /**
     * @var TerminatedCustomerRepositoryInterface
     */
    private $terminatedCustomerRepository;

    /**
     * @var TerminatedCustomerFactory
     */
    private $terminatedCustomerFactory;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * Leave constructor.
     * @param Context $context
     * @param RedirectFactory $redirectFactory
     * @param SessionFactory $customerSession
     * @param CustomerRepository $customerRepository
     * @param ManagerInterface $message
     * @param UrlInterface $url
     * @param LoggerInterface $logger
     * @param Registry $registry
     * @param TerminatedCustomerRepositoryInterface $terminatedCustomerRepository
     * @param TerminatedCustomerFactory $terminatedCustomerFactory
     * @param RemoteAddress $remoteAddress
     */
    public function __construct(
        Context $context,
        RedirectFactory $redirectFactory,
        SessionFactory $customerSession,
        CustomerRepository $customerRepository,
        ManagerInterface $message,
        UrlInterface $url,
        LoggerInterface $logger,
        Registry $registry,
        TerminatedCustomerRepositoryInterface $terminatedCustomerRepository,
        TerminatedCustomerFactory $terminatedCustomerFactory,
        RemoteAddress $remoteAddress
    ) {
        $this->redirectFactory = $redirectFactory->create();
        $this->customerSession = $customerSession->create();
        $this->customerRepo = $customerRepository;
        $this->message = $context->getMessageManager();
        $this->url = $url;
        $this->logger = $logger;
        $this->registry = $registry;
        $this->terminatedCustomerRepository = $terminatedCustomerRepository;
        $this->terminatedCustomerFactory = $terminatedCustomerFactory;
        $this->remoteAddress = $remoteAddress;
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
        if ($customerId) {
            try {
                $customer = $this->customerRepo->getById($customerId);
                $customerIntegrationNumber = $customer->getCustomAttribute('integration_number')->getValue();
                $customerIpAddress = $this->remoteAddress->getRemoteAddress();
                if ($customer) {
                    $this->message->addSuccessMessage(__('Thank you for using our services'));
                    $this->customerSession->logout();
                    $this->registry->register('isSecureArea', true);
                    if ($this->customerRepo->deleteById($customerId)) {
                        $model = $this->terminatedCustomerFactory->create();
                        $model->setData('customer_id', $customerId);
                        $model->setData('integration_number', $customerIntegrationNumber);
                        $model->setData('ip_address', $customerIpAddress);
                        $this->terminatedCustomerRepository->save($model);
                    }
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
