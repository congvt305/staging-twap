<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: silentarmy
 * Date: 13/6/20
 * Time: 1:03 AM
 */
namespace Eguana\SocialLogin\Model;

use Eguana\SocialLogin\Model\ResourceModel\SocialLogin\CollectionFactory as LineCollectionFactory;
use Eguana\SocialLogin\Model\SocialLoginFactory as LineModelFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface as CustomerInterfaceAlias;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Visitor;
use Magento\Framework\Event\ManagerInterface as ManagerInterfaceAlias1;
use Magento\Framework\Exception\InputException as InputExceptionAlias;
use Magento\Framework\Exception\LocalizedException as LocalizedExceptionAlias;
use Magento\Framework\Exception\NoSuchEntityException as NoSuchEntityExceptionAlias;
use Magento\Framework\Message\ManagerInterface as ManagerInterfaceAlias;
use Magento\Framework\Session\SessionManagerInterface as SessionManagerInterfaceAlias;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\FailureToSendException as FailureToSendExceptionAlias;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime as DateTimeAlias;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;

/**
 * Handler class
 *
 * Class SocialLoginHandler
 */
class SocialLoginHandler
{
    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var ManagerInterfaceAlias
     */
    protected $messageManager;
    /**
     * @var CookieManagerInterface
     */
    private $_cookieManager;
    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;
    /**
     * @var Visitor
     */
    protected $visitor;
    /**
     * @var Customer
     */
    protected $customerModel;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepositoryInterface;
    /**
     * @var LineCollectionFactory
     */
    protected $lineCustomerCollectionFactory;
    /**
     * @var LineModelFactory
     */
    protected $lineCustomerModelFactory;

    /**
     * @var ManagerInterfaceAlias1
     */
    protected $_eventManager;

    /**
     * @var DateTimeAlias
     */
    private $dateTime;

    /**
     * @var SocialLoginRepository
     */
    private $socialLoginRepository;

    /**
     * SocialLoginHandler constructor.
     * @param CustomerFactory $customerFactory
     * @param ManagerInterfaceAlias $messageManager
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param Session $customerSession
     * @param Visitor $visitor
     * @param Customer $customerModel
     * @param LoggerInterface $logger
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param LineCollectionFactory $lineCustomerCollectionFactory
     * @param SocialLoginFactory $lineCustomerModelFactory
     * @param SessionManagerInterfaceAlias $coreSession
     * @param PageFactory $resultPageFactory
     * @param ManagerInterfaceAlias1 $eventManager
     * @param DateTimeAlias $dateTime
     * @param SocialLoginRepository $socialLoginRepository
     */
    public function __construct(
        CustomerFactory $customerFactory,
        ManagerInterfaceAlias $messageManager,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        Session $customerSession,
        Visitor $visitor,
        Customer $customerModel,
        LoggerInterface $logger,
        CustomerRepositoryInterface $customerRepositoryInterface,
        LineCollectionFactory $lineCustomerCollectionFactory,
        LineModelFactory $lineCustomerModelFactory,
        SessionManagerInterfaceAlias $coreSession,
        PageFactory $resultPageFactory,
        ManagerInterfaceAlias1 $eventManager,
        DateTimeAlias $dateTime,
        SocialLoginRepository $socialLoginRepository
    ) {
        $this->_customerRepositoryInterface      = $customerRepositoryInterface;
        $this->customerFactory                   = $customerFactory;
        $this->messageManager                    = $messageManager;
        $this->cookieMetadataFactory             = $cookieMetadataFactory;
        $this->_cookieManager                    = $cookieManager;
        $this->session                           = $customerSession;
        $this->visitor                           = $visitor;
        $this->customerModel                     = $customerModel;
        $this->logger                            = $logger;
        $this->lineCustomerCollectionFactory     = $lineCustomerCollectionFactory;
        $this->lineCustomerModelFactory          = $lineCustomerModelFactory;
        $this->coreSession                       = $coreSession;
        $this->_resultPageFactory                = $resultPageFactory;
        $this->_eventManager                     = $eventManager;
        $this->dateTime                          = $dateTime;
        $this->socialLoginRepository             = $socialLoginRepository;
    }

    /**
     * Get session instance
     * @return SessionManagerInterfaceAlias
     */
    public function getCoreSession()
    {
        return $this->session;
    }

    /**
     * Set data in session
     * @param $customerData
     */
    private function setDataInSession($customerData)
    {
        $this->getCoreSession()->start();
        $this->getCoreSession()->unsSocialUserData();
        $this->getCoreSession()->setData('social_user_data', $customerData);
    }

    /**
     * Get customer data for session
     * @param $dataUser
     * @param $userId
     * @param $socialMediaType
     * @return array|null
     */
    private function getCustomerData($dataUser, $userId, $socialMediaType)
    {
        if ($dataUser) {
            $customerData = [];
            $customerData['appid'] = $userId;
            if (array_key_exists('name', $dataUser)) {
                $customerData['name'] = $dataUser['name'];
            }
            if (array_key_exists('picture', $dataUser)) {
                $customerData['picture'] = $dataUser['picture'];
            }
            if (array_key_exists('email', $dataUser)) {
                $customerData['email'] = $dataUser['email'];
            }
            $customerData['socialmedia_type'] = $socialMediaType;
            return $customerData;
        }
        return null;
    }

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @param $email
     * @param null $websiteId
     * @return bool|CustomerInterfaceAlias
     * @throws LocalizedExceptionAlias
     * @throws NoSuchEntityExceptionAlias
     */
    public function getCustomerByEmail($email, $websiteId = null)
    {
        $customer = null;
        $this->customerModel->setWebsiteId($this->storeManager->getWebsite()->getId());
        if (!$websiteId) {
            $this->customerModel->setWebsiteId($this->storeManager->getWebsite()->getId());
        } else {
            $this->customerModel->setWebsiteId($websiteId);
        }
        try {
            $customer = $this->customerModel->loadByEmail($email);
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
        }
        try {
            $customerId = $customer->getId();
            if ($customer->getId()) {
                $customer = $this->_customerRepositoryInterface->getById($customerId);
                return $customer;
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
        }

        return false;
    }

    /**
     * Save social media customer data
     * @param $lineId
     * @param $customerId
     * @param $username
     * @param $socialMediaType
     */
    public function setSocialMediaCustomer($socialId, $customerId, $username, $socialMediaType)
    {
        $lineCustomer = $this->lineCustomerModelFactory->create();
        $lineCustomer->setData('social_id', $socialId);
        $lineCustomer->setData('username', $username);
        $lineCustomer->setData('socialmedia', $socialMediaType);
        $lineCustomer->setData('customer_id', $customerId);
        try {
            $this->socialLoginRepository->save($lineCustomer);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Redirect customer to other page
     * If customer exists then login and close popup else close pop and redirect to social login page
     * @param $customerId
     * @param $dataUser
     * @param $userid
     * @param $socialMediaType`
     * @throws InputExceptionAlias
     * @throws LocalizedExceptionAlias
     * @throws NoSuchEntityExceptionAlias
     * @throws FailureToSendExceptionAlias
     */
    public function redirectCustomer($customerId, $dataUser, $userid, $socialMediaType)
    {
        if ($customerId) {
            $this->checkIfCustomerExists($customerId);
        } else {
            $customerData = $this->getCustomerData($dataUser, $userid, $socialMediaType);

            if ($customerData) {
                $this->setDataInSession($customerData);
            }
        }
    }

    /**
     * Check if customer exists
     * @param $customerId
     * @throws InputExceptionAlias
     * @throws LocalizedExceptionAlias
     * @throws NoSuchEntityExceptionAlias
     * @throws FailureToSendExceptionAlias
     */
    private function checkIfCustomerExists($customerId)
    {
        $customer = $this->_customerRepositoryInterface->getById($customerId);
        if ($customer->getConfirmation()) {
            try {
                $customer1->setConfirmation(null);
                $this->_customerRepositoryInterface->save($customer);
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('We can\'t process your request right now. Sorry, that\'s all we know.')
                );
            }
        }
        if ($this->_cookieManager->getCookie('mage-cache-sessid')) {
            $metadata = $this->cookieMetadataFactory->createCookieMetadata();
            $metadata->setPath('/');
            $this->_cookieManager->deleteCookie('mage-cache-sessid', $metadata);
        }
        $this->session->setCustomerDataAsLoggedIn($customer);
        $this->messageManager->addSuccess(__('Login successful.'));
        $this->session->regenerateId();
        $this->_eventManager->dispatch('customer_data_object_login', ['customer' => $customer]);
        $this->_eventManager->dispatch('customer_login', ['customer' => $customer]);
        $this->saveVisitor();
    }

    /**
     * Save visitor data
     */
    public function saveVisitor()
    {
        /** VISITOR */
        $visitor = $this->visitor;
        $visitor->setData($this->session->getVisitorData());
        $visitor->setLastVisitAt($this->dateTime->gmtDate());
        $visitor->setSessionId($this->session->getSessionId());
        try {
            $visitor->save();
        } catch (\Exception $exception) {
            $this->logger->info($exception->getMessage());
        }
        $this->_eventManager->dispatch('visitor_init', ['visitor' => $visitor]);
        $this->_eventManager->dispatch('visitor_activity_save', ['visitor' => $visitor]);
    }
}
