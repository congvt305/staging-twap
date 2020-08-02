<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/6/20
 * Time: 2:50 PM
 */

namespace Eguana\GWLogistics\Plugin\Checkout\Api;


use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;

class SaveAddressPlugin
{
    /**
     * @var \Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface
     */
    private $quoteCvsLocationRepository;
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Address
     */
    private $quoteAddressResource;
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Address\CollectionFactory
     */
    private $quoteAddressCollectionFactory;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Magento\Quote\Api\Data\AddressExtensionFactory
     */
    private $addressExtensionFactory;

    /**
     * SaveAddressPlugin constructor.
     * @param \Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface $quoteCvsLocationRepository
     * @param \Magento\Quote\Api\Data\AddressExtensionFactory $addressExtensionFactory
     * @param \Magento\Quote\Model\ResourceModel\Quote\Address $quoteAddressResource
     * @param \Magento\Quote\Model\ResourceModel\Quote\Address\CollectionFactory $quoteAddressCollectionFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface $quoteCvsLocationRepository,
        \Magento\Quote\Api\Data\AddressExtensionFactory $addressExtensionFactory,
        \Magento\Quote\Model\ResourceModel\Quote\Address $quoteAddressResource,
        \Magento\Quote\Model\ResourceModel\Quote\Address\CollectionFactory $quoteAddressCollectionFactory,
        \Psr\Log\LoggerInterface $logger
    ){
        $this->quoteCvsLocationRepository = $quoteCvsLocationRepository;
        $this->quoteAddressResource = $quoteAddressResource;
        $this->quoteAddressCollectionFactory = $quoteAddressCollectionFactory;
        $this->logger = $logger;
        $this->addressExtensionFactory = $addressExtensionFactory;
    }

    public function beforeSaveAddressInformation(
        \Magento\Checkout\Api\ShippingInformationManagementInterface $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        if ($addressInformation->getShippingCarrierCode() == 'gwlogistics') {
            try {
                $cvsLocation = $this->quoteCvsLocationRepository->getByQuoteId($cartId);
            } catch (NoSuchEntityException $e) {
                $this->logger->critical($e->getMessage());
            }
            if ($cvsLocation->getLocationId()) {
                $shippingAddress = $addressInformation->getShippingAddress();

                $extensionAttributes = $shippingAddress->getExtensionAttributes();
                if ($extensionAttributes === null) {
                    $extensionAttributes = $this->addressExtensionFactory->create();
                }
                $extensionAttributes->setCvsLocationId($cvsLocation->getLocationId());
                $shippingAddress->setExtensionAttributes($extensionAttributes);
            }
        }
    }

}
