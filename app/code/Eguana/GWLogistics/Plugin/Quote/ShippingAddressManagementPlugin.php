<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/8/20
 * Time: 4:44 AM
 */

namespace Eguana\GWLogistics\Plugin\Quote;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\ShippingAddressManagementInterface;

class ShippingAddressManagementPlugin
{
    /**
     * @var \Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface
     */
    private $quoteCvsLocationRepository;
    /**
     * @var \Magento\Quote\Api\Data\AddressExtensionFactory
     */
    private $addressExtensionFactory;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * SetCvsLocationPlugin constructor.
     * @param \Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface $quoteCvsLocationRepository
     * @param \Magento\Quote\Api\Data\AddressExtensionFactory $addressExtensionFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface $quoteCvsLocationRepository,
        \Magento\Quote\Api\Data\AddressExtensionFactory $addressExtensionFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->quoteCvsLocationRepository = $quoteCvsLocationRepository;
        $this->addressExtensionFactory = $addressExtensionFactory;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Quote\Model\ShippingAddressManagementInterface $subject
     * @param $result
     * @param int $cartId
     */
    public function afterGet(\Magento\Quote\Model\ShippingAddressManagementInterface $subject, \Magento\Quote\Api\Data\AddressInterface $result, $cartId)
    {
        if ($result->getData('cvs_location_id')) {
            $extensionAttributes = $result->getExtensionAttributes();
            try {
                $extensionAttributes->setCvsLocationId($result->getData('cvs_location_id'));
            } catch (\Exception $e) {
                $extensionAttributes->setCvsLocationId(null);
            }
            $result->setExtensionAttributes($extensionAttributes);
        }
        return $result;
    }

    /**
     * @param \Magento\Quote\Model\ShippingAddressManagementInterface $subject
     * @param int $cartId
     * @param AddressInterface $address
     * @return array
     */
    public function beforeAssign(\Magento\Quote\Model\ShippingAddressManagementInterface $subject, $cartId, AddressInterface $address)
    {
        if ($address->getData('limit_carrier') === 'gwlogistics') {
            $extensionAttributes = $address->getExtensionAttributes();
            try {
                $cvsLocation = $this->quoteCvsLocationRepository->getByQuoteId($cartId);
                if ($cvsLocation->getLocationId()) {
                    $extensionAttributes->setCvsLocationId($cvsLocation->getLocationId());
                    $address->setCvsLocationId($extensionAttributes->getCvsLocationId());
                }
            } catch (\Exception $e) {
                $extensionAttributes->setCvsLocationId(null);
            }
            $address->setExtensionAttributes($extensionAttributes);
        }
        return [$cartId, $address];
    }
}
