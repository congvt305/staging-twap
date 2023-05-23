<?php
declare(strict_types=1);

namespace Amore\StaffReferral\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class ReferralComponentProcessor implements LayoutProcessorInterface
{

    const CONFIG_PATH = 'checkout/options/referral_component';

    const STORE_VN_CODE = 'vn_laneige';

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * ReferralComponentProcessor constructor.
     * @param ArrayManager $arrayManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ArrayManager $arrayManager,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->arrayManager = $arrayManager;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        if ($this->scopeConfig->isSetFlag(self::CONFIG_PATH, ScopeInterface::SCOPE_STORE)) {
            if ($this->storeManager->getStore()->getCode() == self::STORE_VN_CODE
                && $this->arrayManager->findPath('shippingAddress', $jsLayout, 'components/checkout/children/steps')) {
                $jsLayout = $this->arrayManager->merge(
                    $this->arrayManager->findPath('shippingAddress', $jsLayout, 'components/checkout/children/steps'),
                    $jsLayout,
                    [
                        'children' => [
                            'before-shipping-method-form' => [
                                'children' => [
                                    'referral' => [
                                        'component' => 'Amore_StaffReferral/js/view/checkout/referral-code',
                                        'template' => 'Amore_StaffReferral/checkout/referral-code',
                                        'children' => [
                                            'errors' => [
                                                'component' => 'Amore_StaffReferral/js/view/checkout/referral-messages',
                                                'displayArea' => 'messages',
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                );
            } elseif ($this->arrayManager->findPath('payment', $jsLayout, 'components/checkout/children/steps')) {
                $jsLayout = $this->arrayManager->merge(
                    $this->arrayManager->findPath('payment', $jsLayout, 'components/checkout/children/steps'),
                    $jsLayout,
                    [
                        'children' => [
                            'beforeMethods' => [
                                'children' => [
                                    'referral' => [
                                        'component' => 'Amore_StaffReferral/js/view/checkout/referral-code',
                                        'template' => 'Amore_StaffReferral/checkout/referral-code',
                                        'children' => [
                                            'errors' => [
                                                'component' => 'Amore_StaffReferral/js/view/checkout/referral-messages',
                                                'displayArea' => 'messages',
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                );
            }
        }
        return $jsLayout;
    }
}
