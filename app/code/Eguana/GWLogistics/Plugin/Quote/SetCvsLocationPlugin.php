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

class SetCvsLocationPlugin
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
     * @param int $cartId
     * @param AddressInterface $address
     * @return array
     */
    public function beforeAssign(\Magento\Quote\Model\ShippingAddressManagementInterface $subject, $cartId, AddressInterface $address)
    {
        if ($address->getData('limit_carrier') === 'gwlogistics') {
            try {
                $cvsLocation = $this->quoteCvsLocationRepository->getByQuoteId($cartId);
            } catch (NoSuchEntityException $e) {
                $this->logger->error($e->getMessage());
            }
            if ($cvsLocation->getLocationId()) {
                $extensionAttributes = $address->getExtensionAttributes();
                if ($extensionAttributes === null) {
                    $extensionAttributes = $this->addressExtensionFactory->create([]);
                }
                $extensionAttributes->setCvsLocationId($cvsLocation->getLocationId());
                $address->setExtensionAttributes($extensionAttributes);
                $address->setCvsLocationId($extensionAttributes->getCvsLocationId());
            }
        }

        return [$cartId, $address];
    }
}
