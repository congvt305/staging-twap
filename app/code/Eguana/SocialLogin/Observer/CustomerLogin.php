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
use Magento\Framework\Event\Observer as ObserverAlias;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManagerInterface as SessionManagerInterfaceAlias;

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
     * CustomerLogin constructor.
     * @param Session $customerSession
     * @param SocialLoginModel $socialLoginModel
     */
    public function __construct(
        SessionManagerInterfaceAlias $customerSession,
        SocialLoginModel $socialLoginModel
    ) {
        $this->session          = $customerSession;
        $this->socialLoginModel = $socialLoginModel;
    }

    /**
     * Save social media customer data after login successfully
     * @param ObserverAlias $observer
     */
    public function execute(ObserverAlias $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        if ($this->session->getData('social_user_data')) {
            $customerData = $this->session->getData('social_user_data');
            $username = $customerData['name'];
            $appid = $customerData['appid'];
            $socialMediaType = $customerData['socialmedia_type'];
            if ($customer->getId()) {
                $customerId = $customer->getId();
                $this->socialLoginModel->setSocialMediaCustomer($appid, $customerId, $username, $socialMediaType);
            }
        }
    }
}
