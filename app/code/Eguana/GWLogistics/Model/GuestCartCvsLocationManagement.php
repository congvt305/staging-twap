<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/30/20
 * Time: 9:11 AM
 */

namespace Eguana\GWLogistics\Model;


use Eguana\GWLogistics\Api\Data\QuoteCvsLocationInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class GuestCartCvsLocationManagement implements \Eguana\GWLogistics\Api\GuestCartCvsLocationManagementInterface
{
    /**
     * @var \Magento\Quote\Model\ShippingAddressManagement
     */
    private $shippingAddressManagement;
    /**
     * @var \Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface
     */
    private $quoteCvsLocationRepository;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    public function __construct(
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Quote\Model\ShippingAddressManagement $shippingAddressManagement,
        \Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface $quoteCvsLocationRepository,
        \Psr\Log\LoggerInterface $logger
    ) {

        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->quoteCvsLocationRepository = $quoteCvsLocationRepository;
        $this->logger = $logger;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * @param string $cartId
     * @param string|null $data
     * @return bool
     */
    public function selectCvsLocation(string $cartId, string $data = null): bool
    {
        $this->logger->info('cartId: ', ['cartId' => $cartId]);
        try {
            $address = $this->shippingAddressManagement->get($cartId);
            $cvsLocation = $this->quoteCvsLocationRepository->getByAddressId($address->getId());
            $cvsLocation->setData('is_selected', true);
            $this->quoteCvsLocationRepository->save($cvsLocation);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to select or save cvs location.'), $e);
        }
        return true;
    }

    /**
     * @param string $cartId
     * @return string
     */
    public function getSelectedCvsLocation(string $cartId): string
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $cartId = $quoteIdMask->getQuoteId();
        $cvsLocationData = new DataObject();
        try {
            $shippingAddress = $this->shippingAddressManagement->get($cartId);
            /** @var QuoteCvsLocationInterface $cvsLocation */
            $cvsLocation = $this->quoteCvsLocationRepository->getByAddressId($shippingAddress->getId());

            $cvsLocationData['CVSStoreName'] = $cvsLocation->getCvsStoreName();
            $cvsLocationData['CVSAddress'] = $cvsLocation->getCvsAddress();
//            $cvsLocationData = [ //todo add location id
//                'selectedCvsLocation' => [
//                    'CVSStoreName' => $cvsLocation->getCvsStoreName(),
//                    'CVSAddress' => $cvsLocation->getCvsAddress(),
//                ]
//            ];
        } catch (LocalizedException $e) {
            $cvsLocationData['CVSStoreName'] = '';
            $cvsLocationData['CVSAddress'] = '';
//            $cvsLocationData = [
//                'selectedCvsLocation' => [
//                    'CVSStoreName' => '',
//                    'CVSAddress' => '',
//                ]
//            ];
        }

        return $cvsLocationData->toJson();
    }
}
