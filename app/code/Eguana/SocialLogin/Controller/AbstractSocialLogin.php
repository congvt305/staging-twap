<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 22/6/20
 * Time: 10:58 AM
 */
namespace Eguana\SocialLogin\Controller;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Visitor;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime as DateTimeAlias;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractSocialLogin
 *
 * Login customer
 */
abstract class AbstractSocialLogin extends Action
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepositoryInterface;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var
     */
    private $customerSession;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var Visitor
     */
    protected $visitor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DateTimeAlias
     */
    private $dateTime;

    /**
     * AbstractSocialLogin constructor.
     * @param Context $context
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param Session $customerSession
     * @param ManagerInterface $eventManager
     * @param Visitor $visitor
     * @param LoggerInterface $logger
     * @param DateTimeAlias $dateTime
     */
    public function __construct(
        Context $context,
        CustomerRepositoryInterface $customerRepositoryInterface,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        Session $customerSession,
        ManagerInterface $eventManager,
        Visitor $visitor,
        LoggerInterface $logger,
        DateTimeAlias $dateTime
    ) {
        $this->customerRepositoryInterface       = $customerRepositoryInterface;
        $this->cookieManager                     = $cookieManager;
        $this->cookieMetadataFactory             = $cookieMetadataFactory;
        $this->customerSession                   = $customerSession;
        $this->eventManager                      = $eventManager;
        $this->visitor                           = $visitor;
        $this->logger                            = $logger;
        $this->dateTime                          = $dateTime;
        parent::__construct($context);
    }

    /**
     * Login already existed customer
     * @param $customerId
     */
    protected function redirectToLogin($customerId)
    {
        try {
            $customer = $this->customerRepositoryInterface->getById($customerId);
            if ($customer->getConfirmation()) {
                try {
                    $customer1->setConfirmation(null);
                    $this->customerRepositoryInterface->save($customer);
                } catch (\Exception $e) {
                    $this->messageManager->addError(
                        __('We can\'t process your request right now. Sorry, that\'s all we know.')
                    );
                }
            }
            if ($this->cookieManager->getCookie('mage-cache-sessid')) {
                $metadata = $this->cookieMetadataFactory->createCookieMetadata();
                $metadata->setPath('/');
                $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
            }
            $this->customerSession->setCustomerDataAsLoggedIn($customer);
            $this->messageManager->addSuccess(__('Login successful.'));
            $this->customerSession->regenerateId();
            $this->eventManager->dispatch('customer_data_object_login', ['customer' => $customer]);
            $this->eventManager->dispatch('customer_login', ['customer' => $customer]);

            $this->saveVisitor();
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __('We can\'t process your request right now. Sorry, that\'s all we know.')
            );
        }
    }

    /**
     * Save visitor data
     */
    private function saveVisitor()
    {
        /** VISITOR */
        $visitor = $this->visitor;
        $visitor->setData($this->customerSession->getVisitorData());
        $visitor->setLastVisitAt($this->dateTime->gmtDate());
        $visitor->setSessionId($this->customerSession->getSessionId());

        try {
            $visitor->save();
        } catch (\Exception $exception) {
            $this->logger->info($exception->getMessage());
        }
        $this->eventManager->dispatch('visitor_init', ['visitor' => $visitor]);
        $this->eventManager->dispatch('visitor_activity_save', ['visitor' => $visitor]);
    }
}
