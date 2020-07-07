<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/30/20
 * Time: 9:12 AM
 */

namespace Eguana\GWLogistics\Model;


use Eguana\GWLogistics\Api\Data\QuoteCvsLocationInterface;
use Magento\Framework\Exception\CouldNotSaveException;

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

    public function selectCvsLocation(string $cartId, string $data = null): bool
    {
        //todo find location by quote address id and updated as selected then return data for form display
        try {
            $address = $this->shippingAddressManagement->get($cartId);
            $cvsLocation = $this->quoteCvsLocationRepository->getByAddressId($address->getId());
            $cvsLocation->setData('is_selected', true);
            $this->quoteCvsLocationRepository->save($cvsLocation);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to select cvs location.'), $e);
        }
        return true;
    }

    public function getCvsLocationByAddressId(string $shippingAddressId): QuoteCvsLocationInterface
    {
        //here try catch is redundant
        try {
            $cvsLocation = $this->quoteCvsLocationRepository->getByAddressId($shippingAddressId);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to select cvs location.'), $e);
        }
        return $cvsLocation;
    }
}
