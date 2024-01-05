<?php
declare(strict_types=1);

namespace CJ\AmastyCheckoutCore\Block\Onepage;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;

class MobileNumberProcessor implements LayoutProcessorInterface
{
    const NEW_THEME_TW_LNG_PATH = 'AmorePacific/sapt_tw_laneige';
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    private $design;

    /**
     * @param CustomerSession $session
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        CustomerSession             $session,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\View\DesignInterface $design
    )
    {
        $this->customerSession = $session;
        $this->customerRepository = $customerRepository;
        $this->design = $design;
    }

    /**
     * @param array $jsLayout
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function process($jsLayout): array
    {
        if ($this->design->getDesignTheme()->getThemePath() == self::NEW_THEME_TW_LNG_PATH) {
            if ($this->customerSession->isLoggedIn()) {
                $customerId = $this->customerSession->getCustomer()->getId();
                $customer = $this->customerRepository->getById($customerId);
                if (isset($jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]["telephone"])) {
                    $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]["telephone"]["value"] = $customer->getCustomAttribute('mobile_number')->getValue();
                }
            }

            $telephoneConfig = $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]["telephone"];
            $telephoneConfig["component"] = 'CJ_AmastyCheckoutCore/js/telephone';
            $telephoneConfig['config']['elementTmpl'] = 'CJ_AmastyCheckoutCore/telephone';
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]["telephone"] = $telephoneConfig;

            if(isset($jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]['city_id']['options'])) {
                foreach ($jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]['city_id']['options'] as $key => $value) {
                    if (empty($value['value'])) {
                        continue;
                    }
                    $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]['city_id']['options'][$key]['label'] = __($value['label']);
                }
            }
        }
        return $jsLayout;
    }
}
