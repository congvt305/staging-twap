<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/30/20
 * Time: 9:12 AM
 */

namespace Eguana\GWLogistics\Model;


use Eguana\GWLogistics\Api\Data\QuoteCvsLocationInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;

class CartCvsLocationManagement implements \Eguana\GWLogistics\Api\CartCvsLocationManagementInterface
{
    /**
     * @var \Magento\Quote\Model\ShippingAddressManagement
     */
    private $shippingAddressManagement;
    /**
     * @var \Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface
     */
    private $quoteCvsLocationRepository;

    public function __construct(
        \Magento\Quote\Model\ShippingAddressManagement $shippingAddressManagement,
        \Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface $quoteCvsLocationRepository
    ) {
        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->quoteCvsLocationRepository = $quoteCvsLocationRepository;
    }

    /**
     * important notice : this function should not be called only when the cart is not active!!!
     * @param string $cartId
     * @param string|null $data
     * @return bool
     * @throws CouldNotSaveException
     */
    public function selectCvsLocation(string $cartId, string $data = null): bool
    {
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
     * @param int $cartId
     * @return string
     */
    public function getSelectedCvsLocation(int $cartId): string
    {
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
