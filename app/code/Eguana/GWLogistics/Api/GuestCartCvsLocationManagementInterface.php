<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/30/20
 * Time: 9:06 AM
 */

namespace Eguana\GWLogistics\Api;


use Eguana\GWLogistics\Api\Data\QuoteCvsLocationInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface GuestCartCvsLocationManagementInterface
 * @package Eguana\GWLogistics\Api
 */
interface GuestCartCvsLocationManagementInterface
{
    /**
     * @param string $cartId
     * @return bool
     */
    public function selectCvsLocation(string $cartId):bool;

    /**
     * @param int $quoteAddressId
     * @return QuoteCvsLocationInterface
     * @throws NoSuchEntityException
     */
    public function getByAddressId($quoteAddressId): QuoteCvsLocationInterface;

}
