<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/30/20
 * Time: 9:11 AM
 */

namespace Eguana\GWLogistics\Model;


use Eguana\GWLogistics\Api\Data\QuoteCvsLocationInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class GuestCartCvsLocationManagement implements \Eguana\GWLogistics\Api\GuestCartCvsLocationManagementInterface
{

    public function selectCvsLocation(string $cartId): bool
    {
        // TODO: Implement selectCvsLocation() method.
    }

    /**
     * @param int $quoteAddressId
     * @return QuoteCvsLocationInterface
     * @throws NoSuchEntityException
     */
    public function getByAddressId($quoteAddressId): QuoteCvsLocationInterface
    {
        // TODO: Implement getByAddressId() method.
    }
}
