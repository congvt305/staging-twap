<?php

namespace CJ\SFLocker\Plugin\Model;

use Amasty\CheckoutCore\Model\ConfigProvider as AmastyConfigProvider;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Add checkout blocks config to checkout config
 * @since 3.0.0
 */
class ConfigProvider
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }
    /**
     * @param AmastyConfigProvider $sub
     * @param $result
     * @return array
     */
    public function afterGetCheckoutBlocksConfig(AmastyConfigProvider $sub, $result): array
    {
        if ($this->storeManager->getStore()->getCode() == self::MY_SWS_STORE_CODE) {
            array_unshift($result[0], [
                'name' => 'delivery_method',
                'title' => 'Delivery Method',
            ]);
        }

        return $result;
    }
}
