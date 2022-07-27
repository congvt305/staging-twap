<?php

namespace CJ\SFLocker\Plugin\Address;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class LayoutProcessor
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ){
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $result
     * @param $jsLayout
     * @return array
     * @throws NoSuchEntityException
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array                                            $result,
                                                         $jsLayout
    ){
        if ($this->storeManager->getStore()->getCode() == self::MY_SWS_STORE_CODE) {
            $shippingAddress = &$result['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children'];
            $shippingAddress['country_pos_code']['sortOrder'] = 30;
            $shippingAddress['country_pos_code']['label'] = __('State/Province');
            if ($this->storeManager->getWebsite()->getCode() == 'base') {
                foreach ($shippingAddress['country_pos_code']['options'] as $key => $value) {
                    if ($value['value'] == 'CN') {
                        unset($shippingAddress['country_pos_code']['options'][$key]);
                        break;
                    }
                }
            }
        }

        return $result;
    }

}
