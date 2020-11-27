<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/30/20
 * Time: 9:06 AM
 */

namespace Eguana\GWLogistics\Api;


use Eguana\GWLogistics\Api\Data\QuoteCvsLocationInterface;

/**
 * Manage GWLogistics CVS Location
 * @api
 */
interface CartCvsLocationManagementInterface
{
    /**
     * @param int $cartId
     * @return string
     */
    public function getSelectedCvsLocation(int $cartId): string;

    /**
     * @param string $cartId
     * @return bool
     */
    public function selectCvsLocation(string $cartId):bool;

}
