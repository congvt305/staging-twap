<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 18/6/20
 * Time: 8:31 PM
 */
namespace Eguana\SocialLogin\Model;

use Eguana\SocialLogin\Api\Data\SocialLoginInterface;
use Eguana\SocialLogin\Api\SocialLoginRepositoryInterface;
use Eguana\SocialLogin\Model\ResourceModel\SocialLogin;
use Eguana\SocialLogin\Model\ResourceModel\SocialLogin\CollectionFactory as SocialLoginCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Repository for social login user's
 *
 * Class SocialLoginRepository
 *
 */
class SocialLoginRepository implements SocialLoginRepositoryInterface
{

    /**
     * @var SocialLoginFactory
     */
    private $socialLoginFactory;

    /**
     * @var SocialLoginCollectionFactory
     */
    private $socialLoginCollectionFactory;

    /**
     * @var
     */
    private $searchResultFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * SocialLoginRepository constructor.
     * @param SocialLoginFactory $socialLoginFactory
     * @param SocialLogin $sociallogin
     * @param SocialLoginCollectionFactory $socialLoginCollectionFactory
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        SocialLoginFactory $socialLoginFactory,
        SocialLogin $sociallogin,
        SocialLoginCollectionFactory $socialLoginCollectionFactory,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager
    ) {
        $this->socialLoginFactory = $socialLoginFactory;
        $this->sociallogin = $sociallogin;
        $this->socialLoginCollectionFactory = $socialLoginCollectionFactory;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
    }

    /**
     * Get social media user by id
     * @param int $id
     * @return SocialLoginInterface|mixed
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $socialLoginUser = $this->socialLoginFactory->create();
        $this->sociallogin->load($socialLoginUser, $id);
        if (!$socialLoginUser->getSocialloginId()) {
            throw new NoSuchEntityException(__('Unable to find Social Login User with ID "%1"', $id));
        }
        return $socialLoginUser;
    }

    /**
     * Save social media user
     * @param SocialLoginInterface $socialLoginUser
     * @return SocialLoginInterface
     */
    public function save(SocialLoginInterface $socialLoginUser)
    {
        try {
            $this->sociallogin->save($socialLoginUser);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $socialLoginUser;
    }

    /**
     * Delete social media user
     * @param SocialLoginInterface $socialLoginUser
     */
    public function delete(SocialLoginInterface $socialLoginUser)
    {
        try {
            $this->sociallogin->delete($socialLoginUser);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Get social media customer data
     * @param $socialId
     * @param $socialMediaType
     * @return |null
     */
    public function getSocialMediaCustomer($socialId, $socialMediaType)
    {
        $websiteId = null;
        try {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        $customer = $this->socialLoginCollectionFactory->create();
        $dataUser = $customer->addFieldToFilter('social_id', $socialId)
            ->addFieldToFilter('socialmedia', $socialMediaType)
            ->addFieldToFilter('website_id', $websiteId)
            ->getFirstItem();
        if ($dataUser && $dataUser->getId()) {
            return $dataUser->getCustomerId();
        } else {
            return null;
        }
    }

    /**
     * Get already linked customer with social media account
     * @param $customerId
     * @param $socialMediaType
     * @return |null
     */
    public function getAlreadyLinkedCustomer($customerId, $socialMediaType, $socialId)
    {
        $websiteId = null;
        try {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        $customer = $this->socialLoginCollectionFactory->create();
        $dataUser = $customer->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('socialmedia', $socialMediaType)
            ->addFieldToFilter('social_id', ['neq' => $socialId])
            ->addFieldToFilter('website_id', $websiteId);
        if ($dataUser && $dataUser->getItems()) {
            return $dataUser->getItems();
        } else {
            return null;
        }
    }
}
