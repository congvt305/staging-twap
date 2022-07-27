<?php

namespace CJ\SFLocker\Plugin\Quote;

use Magento\InventoryInStorePickupApi\Model\GetPickupLocationInterface;
use Magento\InventoryInStorePickupQuote\Model\GetWebsiteCodeByStoreId;
use Magento\InventoryInStorePickupQuote\Model\IsPickupLocationShippingAddress;
use Magento\InventoryInStorePickupQuote\Model\ToQuoteAddress;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\ShippingAddressManagementInterface;
use Magento\Store\Model\StoreManagerInterface;

class ShippingAddressManagementPlugin
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var ToQuoteAddress
     */
    private $addressConverter;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param GetPickupLocationInterface $getPickupLocation
     * @param IsPickupLocationShippingAddress $isPickupLocationShippingAddress
     * @param ToQuoteAddress $addressConverter
     * @param GetWebsiteCodeByStoreId $getWebsiteCodeByStoreId
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        GetPickupLocationInterface $getPickupLocation,
        IsPickupLocationShippingAddress $isPickupLocationShippingAddress,
        ToQuoteAddress $addressConverter,
        GetWebsiteCodeByStoreId $getWebsiteCodeByStoreId,
        StoreManagerInterface $storeManager
    ) {
        $this->cartRepository = $cartRepository;
        $this->addressConverter = $addressConverter;
        $this->storeManager = $storeManager;
    }

    public function beforeAssign(
        ShippingAddressManagementInterface $subject,
        int $cartId,
        AddressInterface $address
    ): array {

        if ($this->storeManager->getStore()->getCode() == self::MY_SWS_STORE_CODE) {
            $address->setData('customer_address_id', null);
        }
        return [$cartId, $address];
    }
}
