<?php

namespace CJ\SFLocker\Plugin\Checkout\Model;


use Magento\InventoryInStorePickupQuote\Model\IsPickupLocationShippingAddress;
use Magento\InventoryInStorePickupApi\Api\Data\PickupLocationInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Store\Model\StoreManagerInterface;

class PickupLocationShippingAddressPlugin
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
     * @param IsPickupLocationShippingAddress $subject
     * @param $result
     * @param PickupLocationInterface $pickupLocation
     * @param AddressInterface $shippingAddress
     * @return false|mixed
     */
    public function afterExecute(
        IsPickupLocationShippingAddress $subject,
        $result,
        PickupLocationInterface $pickupLocation,
        AddressInterface $shippingAddress
    ) {
        if ($this->storeManager->getStore()->getCode() != self::MY_SWS_STORE_CODE) {
            return $result;
        }
        if (!$shippingAddress->getExtensionAttributes() ||
            !$shippingAddress->getExtensionAttributes()->getPickupLocationCode()
        ) {
            return false;
        }
        return true;
    }

}
