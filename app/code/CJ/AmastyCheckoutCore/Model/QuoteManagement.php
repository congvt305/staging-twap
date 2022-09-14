<?php
declare(strict_types=1);

namespace CJ\AmastyCheckoutCore\Model;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Session;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\BillingAddressManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote\Address as ResourceAddress;
use Magento\Quote\Model\ShippingAddressManagementInterface;
use Psr\Log\LoggerInterface;

class QuoteManagement extends \Amasty\CheckoutCore\Model\QuoteManagement
{
    const CUSTOM_ATTRIBUTE = [
        'city_id',
        'ward_id',
        'ward'
    ];

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ShippingAddressManagementInterface
     */
    private $shippingAddressManagement;

    /**
     * @var BillingAddressManagementInterface
     */
    private $billingAddressManagement;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var ResourceAddress
     */
    private $address;

    /**
     * @var \Amazon\Login\Helper\Session|null
     */
    private $amazonSession = null;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @param LoggerInterface $logger
     * @param CartRepositoryInterface $quoteRepository
     * @param ShippingAddressManagementInterface $shippingAddressManagement
     * @param BillingAddressManagementInterface $billingAddressManagement
     * @param ResourceAddress $address
     * @param Session $session
     * @param ObjectManagerInterface $objectManager
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        LoggerInterface $logger,
        CartRepositoryInterface $quoteRepository,
        ShippingAddressManagementInterface $shippingAddressManagement,
        BillingAddressManagementInterface $billingAddressManagement,
        ResourceAddress $address, Session $session,
        ObjectManagerInterface $objectManager,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->logger = $logger;
        $this->quoteRepository = $quoteRepository;
        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->billingAddressManagement = $billingAddressManagement;
        $this->address = $address;
        $this->session = $session;
        $this->addressRepository = $addressRepository;
        parent::__construct(
            $logger,
            $quoteRepository,
            $shippingAddressManagement,
            $billingAddressManagement,
            $address,
            $session,
            $objectManager
        );
    }

    /**
     * Override save insert to set custom attribute
     *
     * @param $cartId
     * @param AddressInterface|null $shippingAddressFromData
     * @param AddressInterface|null $newCustomerBillingAddress
     * @param $selectedPaymentMethod
     * @param $selectedShippingRate
     * @param $validatedEmailValue
     * @return bool
     */
    public function saveInsertedInfo(
        $cartId,
        AddressInterface $shippingAddressFromData = null,
        AddressInterface $newCustomerBillingAddress = null,
        $selectedPaymentMethod = null,
        $selectedShippingRate = null,
        $validatedEmailValue = null
    ) {
        try {
            $quote = null;
            $isAmazonLoggedIn = false;

            if ($this->amazonSession) {
                if (method_exists($this->amazonSession, 'isAmazonLoggedIn')) {
                    $isAmazonLoggedIn = $this->amazonSession->isAmazonLoggedIn();
                } elseif (method_exists($this->amazonSession, 'isLoggedIn')) {
                    $isAmazonLoggedIn = $this->amazonSession->isLoggedIn();
                }
            }

            if (!$isAmazonLoggedIn
                && $this->session->isLoggedIn()
            ) {
                list($shippingAddressFromData, $newCustomerBillingAddress) = $this->retrieveAddressFromCustomer(
                    $cartId,
                    $shippingAddressFromData,
                    $newCustomerBillingAddress
                );
            }

            if ($validatedEmailValue) {
                $shippingAddressFromData->setEmail($validatedEmailValue);
            }

            if ($selectedShippingRate) {
                /** @var Quote $quote */
                $quote = $this->quoteRepository->getActive($cartId);
                $shippingAddressFromData->setShippingMethod($selectedShippingRate);
                $shippingAddressFromData->setShippingDescription(
                    $quote->getShippingAddress()->getShippingDescription()
                );
            }
            //customize here
            if ($shippingAddressFromData && $shippingAddressFromData->getCustomAttributes()) {
                foreach($shippingAddressFromData->getCustomAttributes() as $customAttribute) {
                    if (in_array($customAttribute->getAttributeCode(), self::CUSTOM_ATTRIBUTE)) {
                        $value = isset($customAttribute->getValue()['value']) ? $customAttribute->getValue()['value'] : $customAttribute->getValue();
                        $shippingAddressFromData->setCustomAttribute($customAttribute->getAttributeCode(), $value);
                    }
                }
            }

            if ($newCustomerBillingAddress && $newCustomerBillingAddress->getCustomAttributes()) {
                foreach($newCustomerBillingAddress->getCustomAttributes() as $customAttribute) {
                    if (in_array($customAttribute->getAttributeCode(), self::CUSTOM_ATTRIBUTE)) {
                        $value = isset($customAttribute->getValue()['value']) ? $customAttribute->getValue()['value'] : $customAttribute->getValue();
                        $newCustomerBillingAddress->setCustomAttribute($customAttribute->getAttributeCode(), $value);
                    }
                }
            }
            //end customize

            $this->saveInfo(
                $cartId,
                $shippingAddressFromData,
                $newCustomerBillingAddress,
                $selectedPaymentMethod,
                $quote
            );
        } catch (\Exception $e) {
            $this->logger->debug($e);
        }

        return true;
    }


    /**
     * @param int $cartId
     * @param AddressInterface|null $shippingAddressFromData
     * @param AddressInterface|null $newCustomerBillingAddress
     *
     * @return array
     */
    private function retrieveAddressFromCustomer(
        $cartId,
        AddressInterface $shippingAddressFromData = null,
        AddressInterface $newCustomerBillingAddress = null
    ) {
        if ($shippingAddressFromData && $newCustomerBillingAddress) {
            return [$shippingAddressFromData, $newCustomerBillingAddress];
        }

        $customerAddresses = $this->session->getCustomerData()->getAddresses();
        $billingAddress = [];
        $shippingAddress = [];

        /** @var Address $customerAddress */
        foreach ($customerAddresses as $customerAddress) {
            if ($customerAddress->isDefaultBilling()) {
                $billingAddress = $customerAddress->__toArray();
            }

            if ($customerAddress->isDefaultShipping()) {
                $shippingAddress = $customerAddress->__toArray();
            }
        }

        if ($newCustomerBillingAddress === null) {
            /** @var AddressInterface $newCustomerBillingAddress */
            $newCustomerBillingAddress = $this->billingAddressManagement->get($cartId);
            if (!$newCustomerBillingAddress->getRegion() || !$newCustomerBillingAddress->getCity()) {
                $newCustomerBillingAddress->addData($billingAddress);
            }
        }

        if ($shippingAddressFromData === null) {
            /** @var AddressInterface $shippingAddressFromData */
            $shippingAddressFromData = $this->shippingAddressManagement->get($cartId);
            if (!$shippingAddressFromData->getRegion() || !$shippingAddressFromData->getCity()) {
                $shippingAddressFromData->addData($shippingAddress);
            }
        }

        return [$shippingAddressFromData, $newCustomerBillingAddress];
    }

    /**
     * @param int $cartId
     * @param AddressInterface|null $shippingAddressFromData
     * @param AddressInterface|null $newCustomerBillingAddress
     * @param string|null $selectedPaymentMethod
     * @param Quote $quote
     */
    private function saveInfo(
        $cartId,
        AddressInterface $shippingAddressFromData = null,
        AddressInterface $newCustomerBillingAddress = null,
        $selectedPaymentMethod = null,
        Quote $quote = null
    ) {
        try {
            if ($shippingAddressFromData) {
                //customize save shipping address when set config same as billing for one page checkout amasty
                //if shipping is the same as billing new address cannot save in default magento when use onepage checkout
                if ($shippingAddressFromData->getSaveInAddressBook()) {
                    $shippingAddressData = $shippingAddressFromData->exportCustomerAddress();
                    //save here new customer address
                    $quote = $this->quoteRepository->getActive($cartId);
                    $shippingAddressData->setCustomerId($quote->getCustomerId());
                    $this->addressRepository->save($shippingAddressData);
                    $quote->addCustomerAddress($shippingAddressData);
                    $shippingAddressFromData->setCustomerAddressData($shippingAddressData);
                    $shippingAddressFromData->setCustomerAddressId($shippingAddressData->getId());
                }
                //end customize
                $this->shippingAddressManagement->assign($cartId, $shippingAddressFromData);
            }

            if ($newCustomerBillingAddress) {
                $newCustomerBillingAddress->setQuoteId($cartId);
                $this->address->save($newCustomerBillingAddress);
            }

            if ($selectedPaymentMethod) {
                if (!$quote) {
                    /** @var Quote $quote */
                    $quote = $this->quoteRepository->getActive($cartId);
                }

                $quote->getPayment()->setMethod($selectedPaymentMethod);
                $quote->setDataChanges(true);
                $this->quoteRepository->save($quote);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
