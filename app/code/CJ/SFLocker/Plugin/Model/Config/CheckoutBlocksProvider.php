<?php
declare(strict_types=1);

namespace CJ\SFLocker\Plugin\Model\Config;

use Amasty\CheckoutLayoutBuilder\Model\Config\CheckoutBlocksProvider as AmastyCheckoutBlocksProvider;
use Magento\Store\Model\StoreManagerInterface;

class CheckoutBlocksProvider
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
     * @param \Amasty\CheckoutLayoutBuilder\Model\Config\CheckoutBlocksProvider $sub
     * @param $result
     * @return array
     */
    public function afterGetDefaultBlockTitles(AmastyCheckoutBlocksProvider $sub, $result): array
    {
        if ($this->storeManager->getStore()->getCode() == self::MY_SWS_STORE_CODE) {
            $result[] = ['delivery_method' => __('Delivery Method')];
        }

        return $result;
    }
}
