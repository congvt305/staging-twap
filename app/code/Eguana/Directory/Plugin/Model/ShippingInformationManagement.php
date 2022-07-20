<?php
namespace Eguana\Directory\Plugin\Model;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
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
     * ShippingInformationManagement constructor.
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
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
            $this->validateAddress($address);
        }
        if (!$address->getLastname() || !$address->getFirstname() || !$address->getStreet() || !$address->getTelephone()) {
            throw new StateException(__('The shipping address is missing. Please edit the address and try again.'));
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
}
