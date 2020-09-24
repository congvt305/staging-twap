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
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Message\ManagerInterface as ManagerInterfaceAlias;
use Magento\Framework\Session\SessionManagerInterface as SessionManagerInterfaceAlias;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
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
    private $cookieManager;
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
    protected $customerRepositoryInterface;
    /**
     * @var LineCollectionFactory
     */
    protected $lineCustomerCollectionFactory;
    /**
     * @var LineModelFactory
     */
    protected $lineCustomerModelFactory;
    /**
     * @var SocialLoginRepository
     */
    private $socialLoginRepository;
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * SocialLoginHandler constructor.
     * @param CustomerFactory $customerFactory
     * @param ManagerInterfaceAlias $messageManager
     * @param Session $customerSession
     * @param Customer $customerModel
     * @param LoggerInterface $logger
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param LineCollectionFactory $lineCustomerCollectionFactory
     * @param SocialLoginFactory $lineCustomerModelFactory
     * @param SessionManagerInterfaceAlias $coreSession
     * @param PageFactory $resultPageFactory
     * @param SocialLoginRepository $socialLoginRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CustomerFactory $customerFactory,
        ManagerInterfaceAlias $messageManager,
        Session $customerSession,
        Customer $customerModel,
        LoggerInterface $logger,
        CustomerRepositoryInterface $customerRepositoryInterface,
        LineCollectionFactory $lineCustomerCollectionFactory,
        LineModelFactory $lineCustomerModelFactory,
        SessionManagerInterfaceAlias $coreSession,
        PageFactory $resultPageFactory,
        SocialLoginRepository $socialLoginRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->customerRepositoryInterface       = $customerRepositoryInterface;
        $this->customerFactory                   = $customerFactory;
        $this->messageManager                    = $messageManager;
        $this->session                           = $customerSession;
        $this->customerModel                     = $customerModel;
        $this->logger                            = $logger;
        $this->lineCustomerCollectionFactory     = $lineCustomerCollectionFactory;
        $this->lineCustomerModelFactory          = $lineCustomerModelFactory;
        $this->coreSession                       = $coreSession;
        $this->resultPageFactory                 = $resultPageFactory;
        $this->socialLoginRepository             = $socialLoginRepository;
        $this->storeManager                      = $storeManager;
    }

    /**
     * Get session instance
     * @return SessionManagerInterfaceAlias
     */
    public function getCoreSession()
    {
        return $this->coreSession;
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
     * Save social media customer data
     * @param $socialId
     * @param $customerId
     * @param $username
     * @param $socialMediaType
     */
    public function setSocialMediaCustomer($socialId, $customerId, $username, $socialMediaType)
    {
        $websiteId = null;
        try {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        $lineCustomer = $this->lineCustomerModelFactory->create();
        $lineCustomer->setData('social_id', $socialId);
        $lineCustomer->setData('username', $username);
        $lineCustomer->setData('socialmedia', $socialMediaType);
        $lineCustomer->setData('customer_id', $customerId);
        $lineCustomer->setData('website_id', $websiteId);
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
     * @param $socialMediaType
     */
    public function redirectCustomer($dataUser, $userid, $socialMediaType)
    {
        $customerData = $this->getCustomerData($dataUser, $userid, $socialMediaType);
        if ($customerData) {
            $this->setDataInSession($customerData);
        }
    }
}
