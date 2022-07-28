<?php

namespace CJ\AmastyCheckoutCore\Plugin;

use Magento\Checkout\Block\Checkout\LayoutProcessor;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class AfterLayoutProcessor
 * @package CJ\AmastyCheckoutCore\Plugin
 */
class AfterLayoutProcessor
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @param CustomerSession $session
     * @param CustomerRepositoryInterface $customerRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CustomerSession             $session,
        CustomerRepositoryInterface $customerRepository,
        StoreManagerInterface $storeManager
    )
    {
        $this->customerSession = $session;
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * @param LayoutProcessor $subject
     * @param array $result
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(LayoutProcessor $subject, array $result, $jsLayout): array
    {
        if ($this->storeManager->getStore()->getCode() == self::MY_SWS_STORE_CODE) {
            if ($this->customerSession->isLoggedIn()) {
                $customerId = $this->customerSession->getCustomer()->getId();
                $customer = $this->customerRepository->getById($customerId);
                if (isset($result["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]["telephone"])) {
                    $result["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]["telephone"]["value"] = $customer->getCustomAttribute('mobile_number')->getValue();
                }
            }

            $telephoneConfig = $result["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]["telephone"];
            $telephoneConfig["component"] = 'CJ_AmastyCheckoutCore/js/telephone';
            $telephoneConfig['config']['elementTmpl'] = 'CJ_AmastyCheckoutCore/telephone';
            $result["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]["telephone"] = $telephoneConfig;

            if (isset($result["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]['city_id']['options'])) {
                foreach ($result["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]['city_id']['options'] as $key => $value) {
                    if (empty($value['value'])) {
                        continue;
                    }
                    $result["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]['city_id']['options'][$key]['label'] = __($value['label']);
                }
            }
        }
        return $result;
    }
}
