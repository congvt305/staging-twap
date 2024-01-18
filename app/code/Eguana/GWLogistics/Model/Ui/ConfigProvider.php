<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 11/25/20
 * Time: 4:56 PM
 */
declare(strict_types=1);

namespace Eguana\GWLogistics\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Model\Session;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'gwlogistics';

    /**
     * @var \Eguana\GWLogistics\Helper\Data
     */
    private $helper;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @param \Eguana\GWLogistics\Helper\Data $helper
     * @param Session $customerSession
     */
    public function __construct(
        \Eguana\GWLogistics\Helper\Data $helper,
        Session $customerSession
    ) {
        $this->helper = $helper;
        $this->customerSession = $customerSession;
    }

    public function getConfig()
    {
        $isActive = $this->helper->isActive();
        if (!$isActive) {
            return [];
        }
        $firstName = '';
        $lastName = '';
        $mobileNumber = '';
        if ($this->customerSession->isLoggedIn()) {
            $customer = $this->customerSession->getCustomer();
            $firstName = $customer->getFirstname();
            $lastName = $customer->getLastname();
            $mobileNumber = $customer->getMobileNumber();
        }


        return [
                self::CODE => [
                    'isActive' => $isActive,
                    'shipping_message' => $this->helper->getCarrierShippingMessage(),
                    'guest_cvs_shipping_method_enabled' => $this->helper->isGuestCVSShippingMethodEnabled(),
                    'cvs_first_name' => $firstName,
                    'cvs_last_name' => $lastName,
                    'mobile_number' => $mobileNumber
                ],
        ];
    }
}
