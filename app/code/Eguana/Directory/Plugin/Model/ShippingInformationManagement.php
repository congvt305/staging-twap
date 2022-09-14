<?php
namespace Eguana\Directory\Plugin\Model;

use Amasty\CheckoutCore\Model\Config;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Store\Model\StoreManagerInterface;

class ShippingInformationManagement
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    protected $storeCode = 'vn_laneige';

    /**
     * @var Config
     */
    private $amastyConfig;

    /**
     * @param StoreManagerInterface $storeManager
     * @param Config $amastyConfig
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Config $amastyConfig
    ) {
        $this->storeManager = $storeManager;
        $this->amastyConfig = $amastyConfig;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
     * @return void
     * @throws StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject, $cartId,ShippingInformationInterface $addressInformation
    ) {
        $storeCode = $this->storeManager->getStore()->getCode();
        $address = $addressInformation->getShippingAddress();
        if ($storeCode === $this->storeCode) {
            if ($this->amastyConfig->isEnabled()) {
                $this->addAddressForAmastyOnePageCheckout($addressInformation);
            } else {
                $this->validateAddress($address);
            }
        }
        if (!$address->getLastname() || !$address->getFirstname() || !$address->getStreet() || !$address->getTelephone()) {
            throw new InputException(
                __("Please select delivery method : home delivery / cvs delivery. Thanks")
            );
        }
    }

    /**
     * Validate shipping address
     *
     * @param AddressInterface|null $address
     * @return void
     * @throws StateException
     */
    private function validateAddress(?AddressInterface $address): void
    {
        if (!$address || !$address->getWard() || !$address->getWardId() || !$address->getCityId() ) {
            throw new StateException(__('The shipping address is missing. Please edit the address and try again.'));
        }
    }

    /**
     * add data to shipping address to validate it when click place order for onepage checkout
     *
     * @param ShippingInformationInterface|null $address
     * @return void
     * @throws StateException
     */
    private function addAddressForAmastyOnePageCheckout(?ShippingInformationInterface $addressInformation): void
    {
        $address = $addressInformation->getShippingAddress();
        if (!$address->getWard()) {
            $address->setCustomAttribute('ward', null);
        }
        if (!$address->getWardId()) {
            $address->setCustomAttribute('ward_id', null);
        }
        if (!$address->getCityId()) {
            $address->setCustomAttribute('city_id', null);
        }
        $billingAddress = $addressInformation->getBillingAddress();
        if (!$billingAddress->getWard()) {
            $billingAddress->setCustomAttribute('ward', null);
        }
        if (!$billingAddress->getWardId()) {
            $billingAddress->setCustomAttribute('ward_id', null);
        }
        if (!$billingAddress->getCityId()) {
            $billingAddress->setCustomAttribute('city_id', null);
        }
    }
}
