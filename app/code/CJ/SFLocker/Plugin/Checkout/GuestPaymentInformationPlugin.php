<?php

namespace CJ\SFLocker\Plugin\Checkout;

use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Staging\Model\VersionManager;
use Magento\Store\Model\StoreManagerInterface;

class GuestPaymentInformationPlugin
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var VersionManager
     */
    private $versionManager;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var SerializerInterface
     */
    private $_serializer;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * GuestPaymentInformationManagement constructor
     *
     * @param VersionManager $versionManager
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param CartRepositoryInterface $cartRepository
     * @param SerializerInterface $serializer
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        VersionManager          $versionManager,
        QuoteIdMaskFactory      $quoteIdMaskFactory,
        CartRepositoryInterface $cartRepository,
        SerializerInterface     $serializer,
        StoreManagerInterface   $storeManager
    )
    {
        $this->versionManager = $versionManager;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->cartRepository = $cartRepository;
        $this->_serializer = $serializer;
        $this->storeManager = $storeManager;
    }

    /**
     * @param GuestPaymentInformationManagementInterface $subject
     * @param $cartId
     * @param $email
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        GuestPaymentInformationManagementInterface $subject,
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    )
    {
        if ($this->storeManager->getStore()->getCode() == self::MY_SWS_STORE_CODE) {
            if ($this->versionManager->isPreviewVersion()) {
                throw new LocalizedException(__("The order can't be submitted in preview mode."));
            }

            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
            /** @var Quote $quote */
            $quote = $this->cartRepository->getActive($quoteIdMask->getQuoteId());
            $shippingAddress = $quote->getShippingAddress();

            if (!empty($billingAddress)) {
                $sameAsBillingFlag = $this->checkIfShippingAddressMatchesWithBillingAddress($quote, $billingAddress);
            } else {
                $sameAsBillingFlag = 0;
            }

            if ($sameAsBillingFlag) {
                $shippingAddress->setSameAsBilling(1);
            }
        }

    }

    /**
     * Returns true if shipping address is same as billing address
     *
     * @param Quote $quote
     * @param AddressInterface $billingAddress
     * @return bool
     */
    private function checkIfShippingAddressMatchesWithBillingAddress(Quote $quote, AddressInterface $billingAddress): bool
    {
        $quoteShippingAddressData = $quote->getShippingAddress()->getData();
        $billingData = $this->convertAddressValueToFlatArray($billingAddress->getData());
        $billingKeys = array_flip(array_keys($billingData));
        $shippingData = array_intersect_key($quoteShippingAddressData, $billingKeys);
        $removeKeys = ['region_code', 'save_in_address_book'];
        $billingData = array_diff_key($billingData, array_flip($removeKeys));
        $billingDataExtensionAttributes = null;
        $shippingDataExtensionAttributes = null;

        //avoid error has objects when array_diff
        if (isset($billingData['extension_attributes'])) {
            $billingDataExtensionAttributes = $billingData['extension_attributes'];
            unset($billingData['extension_attributes']);
        }
        if (isset($shippingData['extension_attributes'])) {
            $shippingDataExtensionAttributes = $shippingData['extension_attributes'];
            unset($shippingData['extension_attributes']);
        }

        $difference = array_diff($billingData, $shippingData) && ($billingDataExtensionAttributes == $shippingDataExtensionAttributes);
        return empty($difference);
    }

    /**
     * Convert $address value to flat array
     *
     * @param array $address
     * @return array
     */
    private function convertAddressValueToFlatArray(array $address): array
    {
        array_walk(
            $address,
            function (&$value) {
                if (is_array($value) && isset($value['value'])) {
                    $value = (string)$value['value'];
                }
            }
        );
        return $address;
    }
}
