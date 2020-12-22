<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 12/6/20
 * Time: 10:59 PM
 */
namespace Eguana\SocialLogin\Observer;

use Eguana\SocialLogin\Model\SocialLoginHandler as SocialLoginModel;
use Eguana\SocialLogin\Model\SocialLoginRepository;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer as ObserverAlias;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManagerInterface as SessionManagerInterfaceAlias;
use Psr\Log\LoggerInterface;

/**
 * Class CustomerLogin
 *
 * Customer login observer
 */
class CustomerLogin implements ObserverInterface
{

    /**
     * @var SocialLoginModel
     */
    protected $socialLoginModel;

    /**
     * @var SocialLoginRepository
     */
    private $socialLoginRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * CustomerLogin constructor.
     * @param SessionManagerInterfaceAlias $customerSession
     * @param SocialLoginModel $socialLoginModel
     * @param SocialLoginRepository $socialLoginRepository
     * @param LoggerInterface $logger
     * @param RequestInterface $request
     * @param CustomerRepository $customerRepository
     */
    public function __construct(
        SessionManagerInterfaceAlias $customerSession,
        SocialLoginModel $socialLoginModel,
        SocialLoginRepository $socialLoginRepository,
        LoggerInterface $logger,
        RequestInterface $request,
        CustomerRepository $customerRepository
    ) {
        $this->session                           = $customerSession;
        $this->socialLoginModel                  = $socialLoginModel;
        $this->socialLoginRepository             = $socialLoginRepository;
        $this->logger                            = $logger;
        $this->request                           = $request;
        $this->customerRepository                = $customerRepository;
    }

    /**
     * Save social media customer data after login successfully
     * @param ObserverAlias $observer
     */
    public function execute(ObserverAlias $observer)
    {
        $lineAgreement = $this->request->getParam('line_messages_agreement_checkbox');
        $customer = $observer->getEvent()->getCustomer();
        $lineAgreement = $lineAgreement ? 1 : 0;
        if ($this->session->getData('social_user_data')) {
            $customerData = $this->session->getData('social_user_data');
            $username = $customerData['name'];
            $appid = $customerData['appid'];
            $socialMediaType = $customerData['socialmedia_type'];
            if ($customer->getId()) {
                $customerId = $customer->getId();
                if ($this->socialLoginRepository->getAlreadyLinkedCustomer($customerId, $socialMediaType, $appid)) {
                    $users = $this->socialLoginRepository->getAlreadyLinkedCustomer($customerId, $socialMediaType, $appid);
                    if ($users) {
                        foreach ($users as $user) {
                            try {
                                $socialLoginUser = $this->socialLoginRepository->getById($user->getData('sociallogin_id'));
                            } catch (\Exception $e) {
                                $this->logger->error($e->getMessage());
                            }
                            $this->socialLoginRepository->delete($socialLoginUser);
                        }
                    }
                }
                try {
                    $customer = $this->customerRepository->getById($customerId);
                    $customer->setCustomAttribute(
                        'line_id',
                        $appid
                    );
                    $customer->setCustomAttribute('line_message_agreement', $lineAgreement);
                    $this->customerRepository->save($customer);
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(
                        __('We can not save LINE Information. ') . $e->getMessage()
                    );
                }
                $this->socialLoginModel->setSocialMediaCustomer($appid, $customerId, $username, $socialMediaType);
            }
        } else {
            $customerId = $customer->getId();
            $socialMediaType = $this->socialLoginModel->getCoreSession()->getSocialmediaType();
            $socialId = $this->socialLoginModel->getCoreSession()->getSocialmediaId();
            try {
                $customer = $this->customerRepository->getById($customerId);
                $customer->setCustomAttribute(
                    'line_id',
                    $socialId
                );
                $customer->setCustomAttribute('line_message_agreement', $lineAgreement);
                $this->customerRepository->save($customer);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can not save LINE Information. ') . $e->getMessage()
                );
            }
            $users = $this->socialLoginRepository->getAlreadyLinkedCustomer($customerId, $socialMediaType, $socialId);
            if ($users) {
                foreach ($users as $user) {
                    try {
                        $socialLoginUser = $this->socialLoginRepository->getById($user->getData('sociallogin_id'));
                    } catch (\Exception $e) {
                        $this->logger->error($e->getMessage());
                    }
                    $this->socialLoginRepository->delete($socialLoginUser);
                }
            }
        }
    }
}
